<?php
/**
 * Text AI Solutions - uses theme service-details layout.
 */

$page_title  = 'Text AI Solutions - Quantal AI';
$active_page = 'services';

$current_slug = 'text';
$page_label   = 'Text AI Solutions';
$crumb        = 'Text AI';
$hero_image   = 'images/quantal/services/text_ai.png';

$overview_h   = 'Service Overview';
$overview_p1  = 'Intelligent chatbots, NLP-powered insights, and automation that streamline text-based communications. Our text AI solutions leverage the latest LLMs (OpenAI, Anthropic, Gemini) to understand intent, respond contextually, and automate everything from customer support to outbound sales.';
$overview_p2  = 'We build context-aware conversation agents with memory, integrated with your knowledge bases and CRMs. From multi-channel chatbots to hyper-personalized cold outreach, our text AI is tuned for your brand voice and proven in production with paying customers.';

$center_h     = 'Capabilities';
$center_p     = 'Our text AI capabilities span the full lifecycle of customer and prospect communication - across web, email, social media, and Slack/Teams.';
$blockquote   = '"Working with the team has been an absolute pleasure. Technically outstanding - creative, thoughtful, and reliable." - David F, Osteopathic Healing Hands';

$capabilities = [
    ['name' => 'Intelligent Chatbots',  'desc' => 'Multi-channel (web, email, social), context-aware with memory, KB-integrated, with seamless human handoff.'],
    ['name' => 'Market Intelligence',   'desc' => 'Real-time sentiment monitoring, competitor and brand-mention tracking, trend identification, custom dashboards.'],
    ['name' => 'AI Email Marketing',    'desc' => 'Dynamic content generation, optimal send-time prediction, A/B testing, integration with major ESPs.'],
    ['name' => 'Personalized Outreach', 'desc' => 'Prospect research and personalization, multi-touch sequence optimization, response scoring, CRM integration.'],
];

$faq_intro = 'Common questions about deploying text AI for your business.';
$faqs = [
    ['Which LLM platforms do you build on?',
     'We are platform-agnostic. We build on OpenAI (GPT-4o, GPT-4.1), Anthropic (Claude), Google Gemini, and self-hosted models. Choice depends on cost, latency, privacy, and accuracy needs.'],
    ['How do you handle hallucinations?',
     'We design retrieval-augmented (RAG) pipelines with strict grounding to your knowledge base, citation requirements, confidence thresholds, and routing low-confidence answers to humans.'],
    ['Can the chatbot learn from past conversations?',
     'Yes. Conversation logs are fed back into the system so the agent improves: better KB retrieval, better intent classification, and identifying gaps where new content is needed.'],
    ['What about data privacy?',
     'We support on-prem deployment, EU/India data residency, end-to-end encryption, and zero-retention API contracts where the LLM provider supports it (OpenAI Enterprise, Anthropic Enterprise, Azure OpenAI).'],
];

include __DIR__ . '/_subservice.php';
