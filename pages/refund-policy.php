<?php

/**
 * Refund & Cancellation Policy - content from quantaltech.ai/refund-cancellation-policy
 * Renders via shared legal template using theme page-title + services-details typography.
 */
$page_title = 'Refund & Cancellation Policy - Quantal AI';
$active_page = '';

$banner_title = 'Refund & Cancellation Policy';
$crumb_label = 'Refund Policy';
$last_updated = date('F j, Y');

$intro_para = 'This Refund and Cancellation Policy ("Policy") governs the refund and cancellation procedures for services provided by Quantal AI ("we," "our," or "us"). By engaging our services, you agree to the terms outlined in this Policy. We are committed to providing transparent and fair refund and cancellation processes.';

$sections = [
    [
        'h' => '1. General Policy',
        'paras' => [
            'All refund and cancellation requests are subject to the terms of your specific service agreement or order form. Unless otherwise specified in your agreement, the following general policies apply:',
        ],
        'list' => [
            'Refund requests must be submitted in writing to our support team',
            'Refunds are processed within 30 business days of approval',
            'Refunds will be issued to the original payment method used',
            'Partial refunds may apply based on services already rendered',
        ],
    ],
    [
        'h' => '2. Service-Specific Policies',
        'sub' => [
            [
                'h' => '2.1 Subscription Services',
                'paras' => ['For subscription-based services (monthly or annual):'],
                'list' => [
                    'You may cancel your subscription at any time',
                    'Cancellation will take effect at the end of your current billing period',
                    'No refunds are provided for the current billing period',
                    'You will continue to have access to the service until the end of your paid period',
                ],
            ],
            [
                'h' => '2.2 One-Time Services and Projects',
                'paras' => ['For one-time services, custom projects, or implementation services:'],
                'list' => [
                    'Cancellation requests must be submitted before work begins',
                    'If cancellation occurs after work has commenced, you will be charged for work completed up to the cancellation date',
                    'Refunds for unused portions will be calculated based on the percentage of work completed',
                    'Any deliverables already provided remain the property of Quantal AI until full payment is received',
                ],
            ],
            [
                'h' => '2.3 Consulting and Advisory Services',
                'paras' => ['For consulting, advisory, or professional services:'],
                'list' => [
                    "Consulting sessions may be rescheduled with at least 24 hours' notice",
                    'Cancellations made less than 24 hours before a scheduled session may be subject to a cancellation fee',
                    'Prepaid consulting packages are non-refundable but may be transferable with our approval',
                    'Unused hours in a package expire according to the terms of your agreement',
                ],
            ],
        ],
    ],
    [
        'h' => '3. Cancellation Procedure',
        'paras' => ['To cancel a service, please follow these steps:'],
        'list' => [
            'Submit a written cancellation request via email to contact@quantaltech.ai',
            'Include your account information, service details, and reason for cancellation',
            'We will acknowledge your request within 2 business days',
            'You will receive confirmation of cancellation and any applicable refund information',
        ],
        'note' => 'Cancellation requests are processed within 5-10 business days. You will receive written confirmation once your cancellation has been processed.',
    ],
    [
        'h' => '4. Refund Eligibility',
        'paras' => ['Refunds may be available under the following circumstances:'],
        'list' => [
            'Service Not Delivered: If we fail to deliver the agreed-upon service and cannot provide a suitable alternative',
            'Material Breach: If we materially breach our service agreement and cannot remedy the breach',
            'Duplicate Payment: If you are charged multiple times for the same service',
            'Early Cancellation: As specified in your service agreement for eligible services',
        ],
        'sub' => [
            [
                'h' => 'Refunds are generally NOT available for:',
                'list' => [
                    'Services that have been fully delivered and accepted',
                    'Change of mind or business circumstances',
                    'Failure to use the service within the agreed timeframe',
                    'Third-party fees or charges (e.g., payment processing fees)',
                    'Custom development work that has been completed',
                ],
            ],
        ],
    ],
    [
        'h' => '5. Refund Processing',
        'paras' => [
            'Once a refund is approved, refunds are issued to the original payment method used for the transaction. If the original payment method is no longer available, alternative arrangements may be made. You will receive email confirmation when the refund has been processed.',
        ],
        'sub' => [
            [
                'h' => 'Processing Times',
                'list' => [
                    'Credit/Debit Cards: 5-10 business days',
                    'Bank Transfers: 10-15 business days',
                    'Wire Transfers: 15-20 business days',
                ],
            ],
        ],
        'note' => 'Processing times may vary depending on your financial institution. We are not responsible for delays caused by third-party payment processors or financial institutions.',
    ],
    [
        'h' => '6. Partial Refunds',
        'paras' => [
            'In cases where services have been partially delivered, partial refunds are calculated as follows: Refund amount = Total payment − (Percentage of work completed × Total payment).',
            'Work completion percentage is determined based on deliverables and milestones achieved. Any materials, licenses, or third-party services already procured may be deducted from the refund amount.',
        ],
    ],
    [
        'h' => '7. Chargebacks and Disputes',
        'paras' => ['If you initiate a chargeback or dispute with your payment provider:'],
        'list' => [
            'We will investigate the dispute and provide relevant documentation to your payment provider',
            'Your service may be suspended pending resolution of the dispute',
            'If the chargeback is found to be invalid, you may be responsible for associated fees',
            'We encourage you to contact us directly to resolve issues before initiating a chargeback',
        ],
    ],
    [
        'h' => '8. Modifications to Services',
        'paras' => ['If we need to modify or discontinue a service:'],
        'list' => [
            "We will provide at least 30 days' notice for significant changes",
            'You may cancel your subscription without penalty if you do not agree to the changes',
            'If we discontinue a service, we will offer a prorated refund or alternative service',
            'Your rights under this Policy remain in effect',
        ],
    ],
    [
        'h' => '9. Special Circumstances',
        'paras' => ['We understand that exceptional circumstances may arise. We will consider refund requests on a case-by-case basis for:'],
        'list' => [
            'Medical emergencies preventing service use',
            'Natural disasters or force majeure events',
            'Technical issues on our end that prevent service delivery',
            'Other circumstances beyond your reasonable control',
        ],
    ],
    [
        'h' => '10. Non-Refundable Items',
        'paras' => ['The following items are generally non-refundable:'],
        'list' => [
            'Setup fees and one-time implementation charges',
            'Third-party licenses or services already procured on your behalf',
            'Custom development work that has been completed and delivered',
            'Training sessions that have been conducted',
            'Any services explicitly marked as non-refundable in your service agreement',
        ],
    ],
    [
        'h' => '11. How to Request a Refund or Cancellation',
        'paras' => ['Please include the following information in your request:'],
        'list' => [
            'Your account information or order number',
            'Service or product name',
            'Date of purchase',
            'Reason for refund or cancellation request',
            'Any relevant documentation',
        ],
    ],
    [
        'h' => '12. Policy Updates',
        'paras' => [
            'We reserve the right to update this Refund and Cancellation Policy at any time. Changes will be posted on this page with an updated "Last Updated" date. Your continued use of our services after such changes constitutes acceptance of the updated Policy. For existing service agreements, the Policy in effect at the time of your purchase will apply unless otherwise specified in your agreement.',
        ],
    ],
];

$contact_block = [
    'email' => 'contact@quantaltech.ai',
    'phone' => '+1 315 809 3225',
    'address_html' => '<strong>New York:</strong> Quantal AI (UltraGenius Tech Private Limited), 430 Park Avenue, New York, NY — 10022<br><strong>GSTIN:</strong> 27AACCU9781D1Z0',
];

include __DIR__ . '/_legal.php';
