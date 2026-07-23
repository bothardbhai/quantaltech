<?php

/**
 * Terms of Service - content from quantaltech.ai/terms-of-service
 * Renders via shared legal template using theme page-title + services-details typography.
 */
$page_title = 'Terms of Service - Quantal AI';
$active_page = '';

$banner_title = 'Terms of Service';
$crumb_label = 'Terms of Service';
$last_updated = date('F j, Y');

$intro_para = 'Welcome to Quantal AI ("we," "our," or "us"). These Terms of Service ("Terms") govern your access to and use of our website, services, and AI-powered solutions (collectively, the "Services"). By accessing or using our Services, you agree to be bound by these Terms. If you do not agree to these Terms, please do not use our Services. We may update these Terms from time to time, and your continued use of the Services after such changes constitutes acceptance of the updated Terms.';

$sections = [
    [
        'h' => '1. Acceptance of Terms',
        'paras' => [
            'By accessing, browsing, or using our Services, you acknowledge that you have read, understood, and agree to be bound by these Terms and all applicable laws and regulations. If you are using the Services on behalf of an organization, you represent and warrant that you have the authority to bind that organization to these Terms.',
        ],
    ],
    [
        'h' => '2. Description of Services',
        'paras' => [
            'Quantal AI provides AI-driven solutions and services for enterprise operations, including but not limited to:',
        ],
        'list' => [
            'Intelligent Voice AI solutions',
            'Text AI and natural language processing',
            'Image and document AI processing',
            'Process automation services',
            'AI agent solutions for various business functions',
            'Consulting and advisory services',
        ],
        'note' => 'We reserve the right to modify, suspend, or discontinue any aspect of our Services at any time without prior notice.',
    ],
    [
        'h' => '3. User Accounts and Registration',
        'paras' => [
            'Some features of our Services may require you to create an account. When creating an account, you agree to:',
        ],
        'list' => [
            'Provide accurate, current, and complete information',
            'Maintain and update your information to keep it accurate',
            'Maintain the security of your account credentials',
            'Accept responsibility for all activities under your account',
            'Notify us immediately of any unauthorized access',
        ],
    ],
    [
        'h' => '4. Use of Services',
        'paras' => [
            'You agree to use our Services only for lawful purposes and in accordance with these Terms. You agree not to:',
        ],
        'list' => [
            'Violate any applicable laws or regulations',
            'Infringe upon the rights of others',
            'Transmit any harmful, offensive, or illegal content',
            'Attempt to gain unauthorized access to our systems',
            'Interfere with or disrupt the Services or servers',
            'Use automated systems to access the Services without permission',
            'Reverse engineer, decompile, or disassemble any part of our Services',
            'Use our Services to compete with us or for any competitive intelligence purpose',
        ],
    ],
    [
        'h' => '5. Intellectual Property Rights',
        'paras' => [
            'All content, features, and functionality of our Services, including but not limited to text, graphics, logos, images, software, and AI models, are owned by Quantal AI or its licensors and are protected by copyright, trademark, and other intellectual property laws.',
            'You are granted a limited, non-exclusive, non-transferable license to access and use the Services for your internal business purposes. This license does not include the right to:',
        ],
        'list' => [
            'Reproduce, distribute, or create derivative works',
            'Publicly display or perform the Services',
            'Use our trademarks or logos without permission',
            'Remove any proprietary notices or labels',
        ],
    ],
    [
        'h' => '6. User Content and Data',
        'paras' => [
            'You retain ownership of any content, data, or information you submit to our Services ("User Content"). By submitting User Content, you grant us a worldwide, non-exclusive, royalty-free license to use, process, and store your User Content solely for the purpose of providing and improving our Services.',
            'You are solely responsible for your User Content and represent that you have all necessary rights to submit it. We reserve the right to remove any User Content that violates these Terms or is otherwise objectionable.',
        ],
    ],
    [
        'h' => '7. Privacy and Data Protection',
        'paras' => [
            'Your privacy is important to us. Our collection and use of personal information is governed by our Privacy Policy, which is incorporated into these Terms by reference. By using our Services, you consent to the collection and use of information as described in our Privacy Policy.',
            'We implement appropriate technical and organizational measures to protect your data, but no method of transmission over the internet is 100% secure. You use our Services at your own risk.',
        ],
    ],
    [
        'h' => '8. Payment Terms',
        'paras' => [
            'If you purchase any paid Services, you agree to pay all fees as specified in your service agreement or order form. All fees are non-refundable unless otherwise stated in writing.',
            'You are responsible for providing accurate billing information and authorizing payment. We reserve the right to change our pricing with reasonable notice. Failure to pay may result in suspension or termination of your access to paid Services.',
        ],
    ],
    [
        'h' => '9. Disclaimers',
        'note' => 'Our Services are provided "as is" and "as available" without warranties of any kind, either express or implied, including but not limited to implied warranties of merchantability, fitness for a particular purpose, and non-infringement.',
        'paras' => ['We do not warrant that:'],
        'list' => [
            'The Services will be uninterrupted or error-free',
            'Defects will be corrected',
            'The Services are free of viruses or other harmful components',
            'The results obtained from using the Services will be accurate or reliable',
        ],
    ],
    [
        'h' => '10. Limitation of Liability',
        'note' => 'To the maximum extent permitted by law, Quantal AI shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses resulting from your use of the Services.',
        'paras' => [
            'Our total liability for any claims arising from or related to the Services shall not exceed the amount you paid us in the twelve (12) months preceding the claim.',
        ],
    ],
    [
        'h' => '11. Indemnification',
        'paras' => [
            "You agree to indemnify, defend, and hold harmless Quantal AI, its officers, directors, employees, and agents from and against any claims, damages, obligations, losses, liabilities, costs, or debt, and expenses (including attorney's fees) arising from your use of the Services, your violation of these Terms, or your violation of any rights of another party.",
        ],
    ],
    [
        'h' => '12. Termination',
        'paras' => [
            'We may terminate or suspend your access to the Services immediately, without prior notice, for any reason, including if you breach these Terms.',
            'Upon termination, your right to use the Services will cease immediately. All provisions of these Terms that by their nature should survive termination shall survive, including ownership provisions, warranty disclaimers, and limitations of liability.',
        ],
    ],
    [
        'h' => '13. Governing Law and Dispute Resolution',
        'paras' => [
            'These Terms shall be governed by and construed in accordance with the laws of India, without regard to its conflict of law provisions. Any disputes arising from or relating to these Terms or the Services shall be resolved through arbitration in Mumbai, India, except where prohibited by law.',
        ],
    ],
    [
        'h' => '14. Changes to Terms',
        'paras' => [
            'We reserve the right to modify these Terms at any time. We will notify you of any material changes by posting the new Terms on this page and updating the "Last Updated" date. Your continued use of the Services after such changes constitutes acceptance of the modified Terms.',
        ],
    ],
];

$contact_block = [
    'email' => 'contact@quantaltech.ai',
    'phone' => '+1 315 809 3225',
    'address_html' => '<strong>New York:</strong> Quantal AI (UltraGenius Tech Private Limited), 430 Park Avenue, New York, NY — 10022<br><strong>GSTIN:</strong> 27AACCU9781D1Z0',
];

include __DIR__ . '/_legal.php';
