<?php
/**
 * The company's legal identity, in one place.
 *
 * The footer, the Terms, the Privacy Policy, About, Contact and the Refund
 * policy all print these values. Keeping them here means the registered
 * address can never disagree between pages — which is the inconsistency a
 * payment provider's reviewer looks for first.
 */

/** Registered legal name. */
const COMPANY_LEGAL_NAME = 'Cypadi Ltd';

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

/**
 * Customer support — orders, delivery, refunds, compliance.
 *
 * The one number Cypadi publishes. It is the same number that appears in the
 * signature block of the KYC and vendor due diligence documents, so a reviewer
 * comparing the paperwork with the site sees one number, not two.
 */
const COMPANY_PHONE_SUPPORT = '+250 792 359 800';

const COMPANY_EMAIL = 'info@cypadi.com';
const COMPANY_EMAIL_COMPLIANCE = 'compliance@cypadi.com';

/** Public site address, used in the policy pages. */
const COMPANY_WEBSITE = 'shop.cypadi.com';

/**
 * The legal identity as a single line, e.g.
 * "Cypadi Ltd · TIN 123456789 · Kimironko, Gasabo, Kigali, Rwanda"
 *
 * The TIN is omitted while COMPANY_TIN is empty.
 */
function companyLegalLine(): string
{
    $parts = [COMPANY_LEGAL_NAME];

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
