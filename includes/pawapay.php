<?php

require_once __DIR__ . '/env.php';

function pawapayBuildMetadata(array $fields)
{
    $metadata = [];
    foreach ($fields as $name => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $metadata[] = [(string) $name => (string) $value];
    }
    return $metadata;
}

class PawaPayClient
{
    private $baseUrl;
    private $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(getenv('PAWAPAY_BASE_URL') ?: 'https://api.sandbox.pawapay.io', '/');
        $this->token = getenv('PAWAPAY_API_TOKEN') ?: '';
    }

    public function isConfigured()
    {
        return $this->token !== '';
    }

    private function request($method, $path, $body = null)
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $raw = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($raw === false) {
            return ['code' => 0, 'body' => null, 'raw' => '', 'error' => $curlError ?: 'Network error'];
        }
        $decoded = json_decode($raw, true);
        return ['code' => $httpCode, 'body' => is_array($decoded) ? $decoded : null, 'raw' => $raw, 'error' => null];
    }

    public function initiateDeposit($depositId, $amount, $currency, $phoneNumber, $provider, $metadataFields = [])
    {
        $metadata = pawapayBuildMetadata($metadataFields);
        if ($metadata === []) {
            $metadata = [['source' => 'cypadi-marketplace']];
        }
        $payload = [
            'depositId' => $depositId,
            'amount' => (string) (int) round((float) $amount),
            'currency' => $currency,
            'payer' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => $phoneNumber,
                    'provider' => $provider,
                ],
            ],
            'metadata' => $metadata,
        ];
        return $this->request('POST', '/v2/deposits', $payload);
    }

    public function initiatePayout($payoutId, $amount, $currency, $phoneNumber, $provider, $metadataFields = [])
    {
        $metadata = pawapayBuildMetadata($metadataFields);
        $payload = [
            'payoutId' => $payoutId,
            'amount' => (string) (int) round((float) $amount),
            'currency' => $currency,
            'recipient' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => $phoneNumber,
                    'provider' => $provider,
                ],
            ],
        ];
        if ($metadata !== []) {
            $payload['metadata'] = $metadata;
        }
        return $this->request('POST', '/v2/payouts', $payload);
    }

    public function checkDepositStatus($depositId)
    {
        return $this->request('GET', '/v2/deposits/' . rawurlencode($depositId));
    }

    public function checkPayoutStatus($payoutId)
    {
        return $this->request('GET', '/v2/payouts/' . rawurlencode($payoutId));
    }

    public function predictProvider($phoneNumber)
    {
        return $this->request('POST', '/v2/predict-provider', ['phoneNumber' => $phoneNumber]);
    }

    public function getActiveConfig($country = 'RWA', $operationType = 'DEPOSIT')
    {
        $query = http_build_query(['country' => $country, 'operationType' => $operationType]);
        return $this->request('GET', '/v2/active-conf?' . $query);
    }
}

function pawapayParseDepositSearchResult($body)
{
    if (!is_array($body)) {
        return ['search' => '', 'depositStatus' => '', 'failureMessage' => ''];
    }
    $search = strtoupper((string) ($body['status'] ?? ''));
    $data = $body['data'] ?? [];
    if (!is_array($data)) {
        $data = [];
    }
    return [
        'search' => $search,
        'depositStatus' => strtoupper((string) ($data['status'] ?? '')),
        'failureMessage' => (string) ($data['failureReason']['failureMessage'] ?? ''),
    ];
}

function pawapayGenerateUuid()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function pawapayCurrency()
{
    return getenv('PAWAPAY_CURRENCY') ?: 'RWF';
}

function pawapayCountry()
{
    return getenv('PAWAPAY_COUNTRY') ?: 'RWA';
}

function pawapayCountryPrefix()
{
    $prefixes = ['RWA' => '250', 'KEN' => '254', 'UGA' => '256', 'TZA' => '255', 'ZMB' => '260'];
    return $prefixes[pawapayCountry()] ?? '250';
}

function pawapayNormalizePhone($input, $prefix = null)
{
    $prefix = $prefix ?: pawapayCountryPrefix();
    $digits = preg_replace('/\D+/', '', (string) $input);
    if ($digits === '') {
        return '';
    }
    if (strpos($digits, $prefix) === 0) {
        return $digits;
    }
    return $prefix . ltrim($digits, '0');
}

function pawapayFinalizeOrder($con, $orderNumber)
{
    $orderNumberEsc = mysqli_real_escape_string($con, $orderNumber);
    $orderQuery = mysqli_query($con, "SELECT user_id FROM orders WHERE orderNumber='$orderNumberEsc' LIMIT 1");
    if (!$orderQuery || mysqli_num_rows($orderQuery) === 0) {
        return false;
    }
    $order = mysqli_fetch_assoc($orderQuery);
    $userId = (int) $order['user_id'];

    $detailsQuery = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM order_items WHERE orderNumber='$orderNumberEsc'");
    $detailsCount = $detailsQuery ? (int) mysqli_fetch_assoc($detailsQuery)['cnt'] : 0;

    if ($detailsCount === 0) {
        mysqli_query(
            $con,
            "INSERT INTO order_items (orderNumber, user_id, product_id, seller_id, quantity, unit_price, line_total)
             SELECT '$orderNumberEsc', c.user_id, c.product_id, p.seller_id, c.number_of_products, p.price_current,
                    (c.number_of_products * p.price_current)
             FROM cart c
             JOIN products p ON p.id = c.product_id
             WHERE c.user_id = '$userId'"
        );
        mysqli_query($con, "DELETE FROM cart WHERE user_id='$userId'");
    }

    mysqli_query($con, "UPDATE orders SET paymentStatus='paid', orderStatus='Paid' WHERE orderNumber='$orderNumberEsc'");
    return true;
}

function pawapayApplyDepositStatus($con, $depositId, $depositStatus, $failureMessage = '')
{
    $depositIdEsc = mysqli_real_escape_string($con, $depositId);
    $depositStatus = strtoupper((string) $depositStatus);

    $orderQuery = mysqli_query(
        $con,
        "SELECT orderNumber, paymentStatus FROM orders WHERE depositId='$depositIdEsc' LIMIT 1"
    );
    if (!$orderQuery || mysqli_num_rows($orderQuery) === 0) {
        return ['handled' => false, 'reason' => 'order_not_found'];
    }
    $order = mysqli_fetch_assoc($orderQuery);
    if ($order['paymentStatus'] === 'paid') {
        return ['handled' => true, 'reason' => 'already_paid', 'orderNumber' => $order['orderNumber']];
    }
    if (in_array($depositStatus, ['COMPLETED', 'SUCCESS'], true)) {
        pawapayFinalizeOrder($con, $order['orderNumber']);
        return ['handled' => true, 'reason' => 'completed', 'orderNumber' => $order['orderNumber']];
    }
    if ($depositStatus === 'FAILED') {
        $failureMessageEsc = mysqli_real_escape_string($con, $failureMessage);
        mysqli_query(
            $con,
            "UPDATE orders SET paymentStatus='failed', orderStatus='Payment Failed' WHERE depositId='$depositIdEsc'"
        );
        return [
            'handled' => true,
            'reason' => 'failed',
            'orderNumber' => $order['orderNumber'],
            'message' => $failureMessageEsc,
        ];
    }
    return ['handled' => true, 'reason' => 'pending', 'orderNumber' => $order['orderNumber'], 'status' => $depositStatus];
}
