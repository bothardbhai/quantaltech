<?php

/**
 * Voice AI Solutions - content from quantaltech.ai/services/voice
 * Uses theme's service-details layout via _subservice.php.
 */
$page_title = 'Voice AI Solutions - Quantal AI';
$active_page = 'services';

$current_slug = 'voice';
$page_label = 'Voice AI Solutions';
$crumb = 'Voice AI';
$hero_image = 'images/quantal/services/voice_ai.png';

$overview_h = 'Service Overview';
$overview_p1 = 'Conversational, multilingual, sentiment-aware voice agents that transform customer interactions. Our voice AI solutions handle complex conversations with human-like understanding - from 24/7 multilingual customer service to lead nurturing, payment reminders, and interview coaching.';
$overview_p2 = 'Built with production-grade voice infrastructure (Vapi, Twilio, Retell), our agents handle real-time speech with low latency, sentiment detection, and seamless handoff to human agents when needed. Each deployment is integrated with your CRM, ticketing system, and business workflows.';

$center_h = 'Capabilities';
$center_p = 'Our voice AI capabilities are built on top of leading speech and LLM platforms, customized for your industry, language, and tone of voice.';
$blockquote = '"They built a fully functional AI voice agent - voice logic, integrations, testing, production rollout - with impressive technical skill and attention to detail." - Thai Nguyen, Desert Recovery Centres';

$capabilities = [
    ['name' => '24/7 Multilingual Customer Service', 'desc' => 'NLU in 50+ languages, real-time sentiment detection, integration with existing CRM and ticketing.'],
    ['name' => 'Voice Lead Nurturing', 'desc' => 'Automated lead scoring, personalized follow-up sequences, calendar booking and analytics.'],
    ['name' => 'Payment Reminder Calls', 'desc' => 'Tone-appropriate reminders, payment plan negotiation, compliance with collection regulations.'],
    ['name' => 'Voice Interview Coaching', 'desc' => 'Industry-specific questions, real-time feedback on pace and clarity, personalized recommendations.'],
];

$faq_intro = 'Common questions about deploying production voice AI for your business.';
$faqs = [
    ['How quickly can a voice agent be deployed?',
        'Most deployments are live in 4-8 weeks. Simple use-cases (e.g. payment reminders) can launch in 2 weeks; complex multi-intent agents take 8-12 weeks including QA and supervised launch.'],
    ['Which voice platforms do you work with?',
        'We build on top of Vapi, Retell, Twilio Voice, and direct LLM streaming pipelines (OpenAI, Anthropic). We pick the platform based on latency, language, and cost requirements.'],
    ['Can it handle accents and code-switching?',
        'Yes - we design for the languages and accents your callers actually use, not just the demo languages. We support Indic, MENA, LATAM, and English-variant accents with code-switching.'],
    ['How do handoffs to human agents work?',
        'When the agent detects frustration, an out-of-scope query, or a high-value request, it can transfer to a live agent with full conversation context as a Slack/Salesforce/Zendesk handoff.'],
];

include __DIR__ . '/_subservice.php';
