<?php
/**
 * The company's legal identity, in one place.
 *
 * The footer, the Terms, the Privacy Policy, About, Contact and the Refund
 * policy all print these values. Keeping them here means the RDB number and
 * the registered address can never disagree between pages — which is the
 * inconsistency a payment provider's reviewer looks for first.
 */

/** Registered legal name, exactly as it appears on the RDB certificate. */
const COMPANY_LEGAL_NAME = 'Cypadi Ltd';

/** RDB certificate of domestic company registration. */
const COMPANY_RDB = '123088034';

/**
 * RRA Tax Identification Number.
 *
 * Fill this in from the RRA certificate. While it is empty the TIN is simply
 * left off the footer and the legal pages, rather than printing a placeholder.
 * Set it and it appears everywhere at once.
 */
const COMPANY_TIN = '';

/** Registered / operating address. */
const COMPANY_ADDRESS_LINE = 'Kimironko, Gasabo';
const COMPANY_CITY = 'Kigali';
const COMPANY_COUNTRY = 'Rwanda';

/** Customer support — orders, delivery, refunds. */
const COMPANY_PHONE_SUPPORT = '+250 784 801 809';

/** Management and compliance contact. */
const COMPANY_PHONE_OFFICE = '+250 792 359 800';

const COMPANY_EMAIL = 'info@cypadi.com';
const COMPANY_EMAIL_COMPLIANCE = 'compliance@cypadi.com';

/** Public site address, used in the policy pages. */
const COMPANY_WEBSITE = 'shop.cypadi.com';

/**
 * The legal identity as a single line, e.g.
 * "Cypadi Ltd · RDB 123088034 · TIN 123456789 · Kimironko, Gasabo, Kigali, Rwanda"
 *
 * The TIN is omitted while COMPANY_TIN is empty.
 */
function companyLegalLine(): string
{
    $parts = [COMPANY_LEGAL_NAME, 'RDB ' . COMPANY_RDB];

    if (COMPANY_TIN !== '') {
        $parts[] = 'TIN ' . COMPANY_TIN;
    }

    $parts[] = COMPANY_ADDRESS_LINE . ', ' . COMPANY_CITY . ', ' . COMPANY_COUNTRY;

    return implode(' &middot; ', $parts);
}

/** The registered address on its own. */
function companyAddress(): string
{
    return COMPANY_ADDRESS_LINE . ', ' . COMPANY_CITY . ', ' . COMPANY_COUNTRY;
}
