<?php
/**
 * Image / Document AI - uses theme service-details layout.
 */

$page_title  = 'Image / Document AI - Quantal AI';
$active_page = 'services';

$current_slug = 'image';
$page_label   = 'Image / Document AI';
$crumb        = 'Image / Doc AI';
$hero_image   = 'images/quantal/services/image_ai.png';

$overview_h   = 'Service Overview';
$overview_p1  = 'KYC verification, fraud detection, signature verification, and intelligent document processing powered by computer vision and modern OCR pipelines. Our image and document AI is deployed at financial-services and recruiting clients where accuracy, audit trails, and compliance are non-negotiable.';
$overview_p2  = 'We combine traditional CV models with multimodal LLMs (Claude, GPT-4o vision) to extract structured data from messy real-world documents - invoices, IDs, signatures, contracts, scanned forms - and validate it against your business rules and external databases.';

$center_h     = 'Capabilities';
$center_p     = 'Each capability ships with audit trails, exception handling for human review, and integration with your downstream systems (ERP, CRM, banking core).';
$blockquote   = '"Quantal AI and Team are EXPERTS at building ANY AI functionality you\'re seeking - completed ON TIME and UNDER BUDGET." - Melissa C, myhomecarebiz.com';

$capabilities = [
    ['name' => 'KYC Document Verification', 'desc' => 'ID authenticity verification, facial recognition with liveness detection, OCR text extraction and validation, regulatory compliance.'],
    ['name' => 'Fraud Detection',           'desc' => 'Document tampering detection, signature forgery patterns, cross-reference validation against databases, real-time risk scoring.'],
    ['name' => 'Signature Verification',    'desc' => 'Biometric signature analysis, pressure and stroke pattern recognition, historical comparison, legal-grade audit trails.'],
    ['name' => 'Invoice Processing',        'desc' => 'Multi-format support (PDF, image, scanned), automatic extraction and validation, accounting system integration, exception workflows.'],
];

$faq_intro = 'Common questions about computer vision and document AI deployments.';
$faqs = [
    ['What accuracy can we expect?',
     'Typical production accuracy: KYC ID extraction 98%+, signature match 95%+, invoice line-item extraction 92-97% depending on document quality. Edge cases route to human review.'],
    ['Which document types are supported?',
     'Indian PAN/Aadhaar/passport/DL, US/EU/MENA IDs, GST invoices, bank statements, contracts, KYC forms, bills, prescriptions. Custom document types can be onboarded in 2-3 weeks.'],
    ['How do you handle handwritten content?',
     'For handwriting we use multimodal LLMs that handle messy handwriting better than traditional OCR - paired with a confidence threshold so low-confidence extractions go to a human reviewer.'],
    ['Can it run on-prem for data sensitivity?',
     'Yes - for financial-services and healthcare clients we deploy on-prem or in-VPC with no document data leaving your infrastructure. We use models that fit on a single GPU when needed.'],
];

include __DIR__ . '/_subservice.php';
