<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Terms and Conditions</title>
    <style>
        @page {
            margin: 100px 50px 80px 50px; /* top right bottom left */
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
        }

        /* ---- FIXED HEADER (repeats on every page) ---- */
        .pdf-header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 70px;
            border-bottom: 2px solid #000;
            text-align: center;
        }
        .pdf-header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .pdf-header p {
            font-size: 11pt;
            margin: 6px 0 0 0;
            font-style: italic;
            color: #333;
        }

        /* ---- FIXED FOOTER (repeats on every page) ---- */
        .pdf-footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 9pt;
            color: #555;
        }
        .pdf-footer .page-number:after {
            content: counter(page);
        }

        /* ---- Content styling (same as your docx format) ---- */
        .terms-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 20px 0;
            text-transform: uppercase;
        }
        .terms-justify { text-align: justify; }
        .terms-italic { font-style: italic; text-align: justify; }
        .terms-spacer { margin-bottom: 15px; }
        p { margin-bottom: 10px; }
        p strong { display: inline-block; margin-top: 10px; margin-bottom: 4px; }
        ul { list-style-type: disc; padding-left: 20px; margin-bottom: 12px; }
        ul ul { list-style-type: circle; margin-top: 6px; padding-left: 20px; }
        li { margin-bottom: 6px; text-align: justify; }
    </style>
</head>
<body>


    {{-- MAIN CONTENT --}}
    <div class="terms-document">
        <p class="terms-justify">
            In accordance with the SÑ Variety Shop Credit Agreement Form, the
            terms and conditions are as follows and must be read prior to completing the form:
        </p>

        <h1 class="terms-title">TERMS AND CONDITIONS</h1>

        <p class="terms-justify">
            This document serves as the official Terms and Conditions ("Agreement")
            governing the extension of credit and all related transactions between
            the Store (hereafter "Creditor") and the Customer (hereafter "Debtor").
            By accepting these terms during customer registration, the Debtor agrees
            to be bound by all provisions herein.
        </p>

        <p><strong>CUSTOMER / DEBTOR INFORMATION</strong></p>
        <p><strong>Full Name</strong> <em>(First Name, Middle Name, Last Name, Suffix)</em>: ______________________</p>
        <p><strong>Birthdate</strong> <em>(dd/mm/yyyy)</em>: ______________________ &nbsp;&nbsp;&nbsp;&nbsp; <strong>Age:</strong> ______</p>

        <p class="terms-italic">
            Customer is required to meet the store owner's prescribed eligibility
            age of <strong>30 years old and above</strong> and have legal capacity to enter into
            the agreement.
        </p>

        <p><strong>Civil Status:</strong> ______________________</p>
        <p><strong>Address:</strong> ______________________</p>
        <p><strong>Contact Number:</strong> ______________________</p>
        <p><strong>Employment Status:</strong> ☐ Employed &nbsp;&nbsp;&nbsp;&nbsp; ☐ Self-Employed</p>

        <p class="terms-spacer"></p>

        <p><strong>1. Credit Account Establishment</strong></p>
        <ul>
            <li>The Creditor may extend a credit line to the Debtor, subject to the Debtor's creditworthiness and the Creditor's sole discretion.</li>
            <li>The initial credit limit is set at the time of account creation and may be adjusted periodically based on payment history, outstanding balance, and risk assessment.</li>
            <li>The Debtor shall provide accurate and complete personal and contact information. Any change in this information must be communicated to the Creditor immediately.</li>
        </ul>

        <p><strong>2. Purchases and Charges</strong></p>
        <ul>
            <li>All purchases made on credit are documented as Transactions in the Creditor's system.</li>
            <li>Each Transaction includes:
                <ul>
                    <li><strong>Principal Amount</strong> -- the cost of goods (unit price × quantity).</li>
                    <li><strong>Surcharge</strong> -- a fixed fee per unit, as determined by the Creditor.</li>
                    <li><strong>Interest</strong> -- of <strong>(5)%</strong> is applied when payment is not made by the due date.</li>
                </ul>
            </li>
            <li>The Due Date of a Transaction is calculated as the Transaction Date plus the Transaction Duration Days (as configured by the Creditor).</li>
            <li>Interest accrues <strong>weekly</strong> (<strong>every 7 days</strong>) at the prevailing <strong>Interest Rate</strong> (percentage per week) on the principal amount, starting from the day after the due date until full payment is received.</li>
        </ul>

        <p><strong>3. Payment Obligations</strong></p>
        <ul>
            <li>The Debtor is responsible for paying the full outstanding amount by the due date.</li>
            <li>Payments can be made in cash or through any other method (online payment) accepted by the Creditor.</li>
            <li>Partial payments are accepted and will be applied to the oldest outstanding balance first.</li>
            <li>If the Debtor fails to pay by the due date, the Transaction becomes <strong>OVERDUE</strong> and interest will begin accruing as described in <strong><em>Section 2</em></strong>.</li>
        </ul>

        <p><strong>4. Late Payments and Default</strong></p>
        <ul>
            <li>Persistent delinquency may result in:
                <ul>
                    <li><strong>Suspension</strong> or revocation of the credit line.</li>
                    <li><strong>Reporting</strong> to credit bureaus or collection agencies.</li>
                    <li><strong>Legal action</strong> to recover the outstanding debt, and the Debtor shall bear all costs incurred (including attorney fees or barangay mediation terms) in such recovery.</li>
                    <li>The Creditor reserves the right to seize or repossess goods in cases of severe default, to the extent permitted by law.</li>
                </ul>
            </li>
        </ul>

        <p><strong>5. Credit Limit and Risk Assessment</strong></p>
        <ul>
            <li>The Debtor's total outstanding balance <strong>shall not exceed</strong> the assigned credit limit.</li>
            <li>The Creditor may, at any time, reduce the credit limit or require immediate repayment of any amount exceeding the limit.</li>
            <li>The Creditor uses a proprietary <strong>risk-scoring model</strong> that considers payment history, overdue count, and credit utilization. This score influences credit decisions and may be updated periodically.</li>
        </ul>

        <p><strong>6. Data Privacy</strong></p>
        <ul>
            <li>The Creditor <strong>collects and processes personal data</strong> (full name, birthdate, age, civil and employment status, address, contact details, and transaction history) solely for the purpose of <strong>administering the credit account.</strong></li>
            <li>The Debtor consents to the <strong>storage and use of this data</strong> for credit <strong>evaluation</strong>, <strong>collection</strong>, and <strong>internal analytics</strong>.</li>
            <li>In the Legal Compliance of the <strong>Data Privacy Act of 2012 (RA 10173), Civil Code of the Philippines (RA 386), Republic Act No. 3765 or the Truth in Lending Act</strong>, and <strong>Consumer Act of the Philippines (RA 7394).</strong></li>
            <li>The Creditor will not share personal data with third parties <strong>except</strong>:
                <ul>
                    <li>As required by law.</li>
                    <li>With service providers (e.g., collection agencies) under confidentiality agreements.</li>
                </ul>
            </li>
            <li>The Debtor has the right to request access to or correction of their data.</li>
        </ul>

        <p><strong>7. Governing Law and Dispute Resolution</strong></p>
        <ul>
            <li>This Agreement shall be governed by the <strong>laws of the Philippines.</strong></li>
            <li>Any dispute arising from this Agreement shall first be attempted to be resolved through amicable negotiation. If unresolved, the dispute shall be submitted to the proper courts of the city/municipality where the Store is located.</li>
        </ul>

        <p><strong>8. Amendments</strong></p>
        <ul>
            <li>The Creditor reserves the right to modify these <strong>Terms and Conditions</strong> at any time. The Debtor will be notified of any material changes via the contact information provided.</li>
            <li>Continued use of the credit account after changes constitutes acceptance of the revised terms.</li>
        </ul>

        <p><strong>9. Acknowledgment and Acceptance</strong></p>
        <ul>
            <li>By checking the box <strong>"Customer has read and agreed to the Terms and Conditions"</strong> during account creation in the system, the Debtor acknowledges:
                <ul>
                    <li>They have <strong>read and understood</strong> the entire Agreement.</li>
                    <li>They agree to be <strong>legally bound</strong> by all provisions.</li>
                    <li>They consent to the <strong>collection and processing of their personal data</strong> as described.</li>
                </ul>
            </li>
            <li>The Debtor confirms that the Terms and Conditions have been provided to them for reading and understanding <strong>prior to the establishment of the credit account</strong>.</li>
            <li>The Debtor's signature below serves as their <strong>written acknowledgment and acceptance</strong> of these Terms and Conditions.</li>
        </ul>

        <p class="terms-spacer"></p>

        <p><strong>CUSTOMER ACKNOWLEDGMENT</strong></p>
        <p><strong>Customer Full Name</strong> <em>(First Name, Middle Name, Last Name, Suffix if any)</em>:</p>
        <p><strong>Customer Signature:</strong></p>
        <p>_______________________________________________________</p>
        <p><strong>Date Signed:</strong></p>

        <p class="terms-spacer"></p>

        <p><strong>STORE OWNER / ADMINISTRATIVE VERIFICATION</strong></p>
        <p><strong>Terms and Conditions Acceptance:</strong> ☐ Verified</p>
        <p><strong>Date Recorded in System:</strong></p>
        <p><strong>Verified/Recorded by:</strong></p>
        <p><strong>Signature:</strong></p>
        <p>_______________________________________________________</p>

        <p class="terms-spacer"></p>

        <p><strong>Note:</strong> Acceptance is a mandatory requirement for opening a credit account. NO TRANSACTION may be initiated without prior acceptance.</p>
    </div>

</body>
</html>