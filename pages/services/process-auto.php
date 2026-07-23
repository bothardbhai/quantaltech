<?php
/**
 * Process Automation - uses theme service-details layout.
 */

$page_title  = 'Process Automation - Quantal AI';
$active_page = 'services';

$current_slug = 'process-auto';
$page_label   = 'Process Automation';
$crumb        = 'Process Automation';
$hero_image   = 'images/quantal/services/process_ai.png';

$overview_h   = 'Service Overview';
$overview_p1  = 'Reporting, reconciliations, compliance monitoring, and end-to-end workflow automation that removes manual work and reduces errors. We use AI agents and workflow orchestration platforms (n8n, Zapier, custom) to connect the systems you already use.';
$overview_p2  = 'Each automation is designed for the specific business process - financial reconciliation across multiple accounting systems, collections orchestration with payment plan negotiation, regulatory compliance monitoring with real-time alerts, automated report generation with insights - built on top of your existing data infrastructure.';

$center_h     = 'Capabilities';
$center_p     = 'Our automation work is grounded in the operational reality of your team: we measure outcomes (cycle time, error rate, hours saved) and design for graceful failure with human-in-the-loop where it matters.';
$blockquote   = '"Communication was smooth, deadlines respected, overall collaboration professional and efficient. Recommended!" - Ivana M, Elunic AG';

$capabilities = [
    ['name' => 'Financial Reconciliation', 'desc' => 'Multi-source data integration and matching, exception handling and discrepancy resolution, real-time dashboards, audit trail.'],
    ['name' => 'Collections Automation',   'desc' => 'Automated payment reminder sequences, customer risk scoring and segmentation, payment plan negotiation, agency integration.'],
    ['name' => 'Compliance Monitoring',    'desc' => 'Regulatory change monitoring and alerts, policy compliance validation, risk assessment and mitigation, automated reporting.'],
    ['name' => 'Report Generation',        'desc' => 'Automated scheduling and distribution, customizable templates and dashboards, data visualization, multi-format export.'],
];

$faq_intro = 'Common questions about deploying AI-powered process automation.';
$faqs = [
    ['How do you measure success?',
     'We agree on baseline metrics before kickoff (cycle time, error rate, manual hours per week) and report on them weekly. Most automations deliver 50-80% reduction in manual hours within the first 30 days post-launch.'],
    ['What if a process changes after deployment?',
     'Modular design. Each automation is broken into composable steps with versioned configuration. Changing one step (e.g. a new payment gateway) doesn\'t require rebuilding the whole flow.'],
    ['Which orchestration platforms do you use?',
     'Workflow orchestration on n8n (most flexible, self-hostable), Zapier (fast for simple flows), or custom Python/Node services for high-throughput cases. We pick based on scale and self-hosting needs.'],
    ['Do you handle the integrations?',
     'Yes - Salesforce, HubSpot, ServiceNow, Tally, Zoho, custom databases, banking APIs, government portals. We have built integrations with 40+ systems across recent client projects.'],
];

include __DIR__ . '/_subservice.php';
