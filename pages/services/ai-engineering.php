<?php

/**
 * AI Engineering - content from quantaltech.ai/services/ai-engineering
 * Uses the new long-form service-details layout via _subservice.php.
 */
$page_title = 'AI Engineering - Quantal AI';
$active_page = 'services';

$current_slug = 'ai-engineering';
$page_label = 'AI Engineering';
$crumb = 'AI Engineering';

// --- Hero ---
$service_tag = 'Voice AI Services';
$service_title_html = 'AI Voice Solutions for <span>Smarter</span>, Faster Business <br> Conversations';
$service_desc = 'From intelligent voice assistants to enterprise-grade conversational AI, we build scalable voice solutions that automate customer interactions, improve response time, and create exceptional user experiences.';

// --- Powered-by platform strip ---
$platform_title = "Powered by the World's Leading AI & ML Platforms";
$platforms = [
    'PyTorch',
    'TensorFlow',
    'OpenAI',
    'Anthropic',
    'AWS SageMaker',
    'Hugging Face',
    'Google Gemini',
    'MLflow',
    'LangChain',
    'Pinecone',
];

// --- Impact stats ---
$impact_stats = [
    ['number' => '40+', 'title' => 'Production AI', 'desc' => 'Projects Successfully Delivered'],
    ['number' => '24 Hours', 'title' => 'AI Engineers', 'desc' => 'Ready To Join Your Team'],
    ['number' => '3× Faster', 'title' => 'Automation', 'desc' => 'AI Powered Workflow Execution'],
    ['number' => 'Global', 'title' => 'Clients', 'desc' => 'AI Systems Deployed Worldwide'],
];

// --- Service overview ---
$overview_sub = 'UNDERSTANDING THE SERVICE';
$overview_title_html = 'What Are <br> AI ML Services?';
$overview_paragraphs = [
    'AI ML services enable businesses to automate complex workflows, build intelligent applications, and transform data into actionable insights. From predictive analytics to enterprise-grade AI solutions, we create production-ready systems that deliver measurable business value.',
    'Our engineers design, deploy, monitor, and continuously optimize AI solutions using modern machine learning, generative AI, and MLOps best practices.',
    'AI ML services enable businesses to automate complex workflows, build intelligent applications, and transform data into actionable insights. From predictive analytics to enterprise-grade AI solutions, we create production-ready systems that deliver measurable business value.',
];
$overview_btn_text = 'Talk With Our Experts';
$overview_features = [
    ['icon' => 'fas fa-brain', 'title' => 'Production AI', 'desc' => 'Enterprise AI systems built for real-world deployment, not demos.'],
    ['icon' => 'fas fa-chart-line', 'title' => 'Continuous Monitoring', 'desc' => 'Performance tracking, model monitoring and automated retraining pipelines.'],
    ['icon' => 'fas fa-rocket', 'title' => 'Business Impact', 'desc' => 'AI solutions focused on measurable ROI and operational efficiency.'],
    ['icon' => 'fas fa-cogs', 'title' => 'End-to-End Engineering', 'desc' => 'Strategy, development, deployment and ongoing optimization under one team.'],
];

// --- Why AI / benefit cards ---
$benefits_sub = 'WHY AI & ML';
$benefits_title_html = 'How AI Creates <span>Business Value</span>';
$benefits_text = 'Discover how production-ready AI systems improve efficiency, reduce costs, and unlock new business opportunities.';
$benefit_cards = [
    ['icon' => 'fas fa-coins', 'title' => 'Reduce Operational Costs', 'desc' => 'ML automates repetitive decisions and manual processing tasks, reducing operational costs while improving speed and quality.', 'example' => 'Manufacturing companies reduced manual processing using AI-driven document classification pipelines.'],
    ['icon' => 'fas fa-user-chart', 'title' => 'Predict Customer Behaviour', 'desc' => 'AI predicts customer intent and buying behaviour to improve recommendations, engagement and retention.', 'example' => 'Ecommerce businesses increased average order value with AI-powered personalized recommendations.'],
    ['icon' => 'fas fa-bolt', 'title' => 'Automate Complex Decisions', 'desc' => 'Replace manual review processes with AI systems that classify, detect, and automate business workflows.', 'example' => 'Financial firms reduced fraud detection time using intelligent machine learning models.'],
    ['icon' => 'fas fa-search', 'title' => 'Uncover Hidden Patterns', 'desc' => 'Discover insights that traditional reporting misses using predictive analytics and intelligent AI models.', 'example' => 'Predictive maintenance reduced downtime and identified failures before they occurred.'],
];

// --- What we build ---
$grid_sub = 'WHAT WE BUILD';
$grid_title_html = 'Our <span>AI ML Services</span>';
$grid_text = 'Eight ML capability areas — all production-ready, business-outcome focused, and delivered in weeks, not months.';
$grid_services = array_fill(0, 8, [
    'icon' => 'fas fa-brain',
    'title' => 'Custom ML Model Development',
    'desc' => 'We build production-ready machine learning models tailored to your business use case, from recommendation engines to fraud detection.',
    'tags' => ['Python', 'TensorFlow', 'Scikit-Learn'],
]);

// --- What you get ---
$whatyouget_sub = 'WHAT YOU GET';
$whatyouget_title_html = 'Benefits of Our <span>AI/ML Services</span>';
$whatyouget_text = 'Working with Quantal AI yields concrete, measurable results that impact your bottom line—not just technical deliverables.';
$whatyouget_cards = [
    ['title' => 'Increased Productivity', 'desc' => 'Automate repetitive workflows, reduce manual effort, and enable your teams to focus on high-value work.'],
    ['title' => 'Reduced Risk', 'desc' => 'Production-ready AI systems with monitoring, governance, and transparent model performance.'],
    ['title' => 'Faster Time to Market', 'desc' => 'Accelerate AI implementation with proven frameworks, reusable components, and expert engineering.'],
    ['title' => 'Data-Driven Decisions', 'desc' => 'Turn raw business data into actionable insights using predictive analytics and intelligent automation.'],
];

// --- Industries ---
$industries_sub = 'WHO WE SERVE';
$industries_title_html = 'Industries We Serve & <span>Use Cases</span>';
$industries_text = 'We build production-ready AI solutions across multiple industries, helping organizations automate workflows, improve decision-making, and accelerate digital transformation.';
$industries = [
    ['title' => 'Healthcare', 'items' => ['Medical imaging & diagnostics', 'Patient risk prediction', 'Clinical NLP', 'Drug discovery']],
    ['title' => 'Finance & FinTech', 'items' => ['Fraud Detection', 'Credit Scoring', 'Risk Modelling', 'Trading Signals']],
    ['title' => 'E-commerce & Retail', 'items' => ['Recommendations', 'Demand Forecasting', 'Search AI', 'Dynamic Pricing']],
    ['title' => 'Manufacturing', 'items' => ['Predictive Maintenance', 'Computer Vision', 'Quality Inspection', 'Supply Chain AI']],
    ['title' => 'SaaS & Tech', 'items' => ['Churn Prediction', 'Usage Analytics', 'AI Features', 'Semantic Search']],
    ['title' => 'Recruitment & HR', 'items' => ['Resume Screening', 'Candidate Ranking', 'Skill Matching', 'Interview Prediction']],
    ['title' => 'Legal', 'items' => ['Contract Analysis', 'Document Intelligence', 'Compliance Monitoring', 'Legal NLP']],
    ['title' => 'Insurance', 'items' => ['Claims Automation', 'Fraud Detection', 'Risk Assessment', 'Customer Retention']],
];

// --- Framework ---
$framework_sub = 'OUR FRAMEWORK';
$framework_title_html = 'Turning AI Into <span>Enterprise Reality</span>';
$framework_text = 'Our AI framework is designed to deliver measurable business outcomes through a structured process—from strategy and model development to deployment, monitoring, and continuous optimization.';
$framework_steps = [
    ['icon' => 'fas fa-brain', 'title' => 'AI Readiness Assessment', 'desc' => 'We evaluate your business objectives, existing infrastructure, available data, and AI maturity to identify the highest-impact opportunities.'],
    ['icon' => 'fas fa-brain', 'title' => 'Use Case Definition', 'desc' => 'We identify practical AI use cases aligned with your business goals, prioritizing projects that deliver measurable value quickly.'],
    ['icon' => 'fas fa-brain', 'title' => 'Model Selection & Training', 'desc' => 'We choose the right AI models, train them with your business data, and optimize performance for production environments.'],
    ['icon' => 'fas fa-brain', 'title' => 'Testing & Validation', 'desc' => 'Every solution undergoes rigorous testing to ensure accuracy, reliability, security, and compliance before deployment.'],
    ['icon' => 'fas fa-brain', 'title' => 'Integration & Deployment', 'desc' => 'We integrate AI seamlessly into your existing systems, workflows, APIs, and cloud infrastructure with minimal disruption.'],
    ['icon' => 'fas fa-brain', 'title' => 'Monitoring & Iteration', 'desc' => 'Continuous monitoring, performance optimization, model retraining, and ongoing improvements keep your AI delivering long-term value.'],
];

// --- Why Quantal ---
$why_sub = 'WHY QUANTAL AI';
$why_title_html = 'Why Choose Quantal for <br> <span>AI ML Services?</span>';
$why_text = 'Most ML projects stall in experimentation. Ours go live in weeks. Here is what separates Quantal AI from traditional ML development firms.';
$why_cards = [
    ['title' => '2–4 Week Delivery', 'desc' => 'Rapid implementation using proven AI frameworks, reusable accelerators, and experienced engineers.'],
    ['title' => 'Fixed-Price, No Surprises', 'desc' => 'Transparent pricing with clearly defined deliverables, timelines, and milestones from day one.'],
    ['title' => 'Gen AI + ML Unified', 'desc' => 'Combine Generative AI with Machine Learning into one scalable production-ready ecosystem.'],
    ['title' => 'No ML Team Required', 'desc' => 'Our specialists become an extension of your business, delivering complete end-to-end AI implementation.'],
    ['title' => 'Production MLOps as Standard', 'desc' => 'Every AI solution includes deployment pipelines, monitoring, governance, and continuous optimization.'],
    ['title' => 'Business ROI, Not Model Accuracy', 'desc' => 'We measure success through measurable business outcomes, productivity gains, and long-term ROI.'],
];

// --- Engagement models ---
$engagement_sub = 'HOW TO WORK WITH US';
$engagement_title_html = 'Three Ways to Engage with <br> <span>Quantal AI</span>';
$engagement_text = "Not every ML project starts the same way. Choose the engagement model that fits where you are—whether you're evaluating AI for the first time or ready to build a production-ready solution.";
$engagement_models = [
    [
        'badge' => 'Model 01',
        'title' => 'ML Consulting',
        'desc' => 'Ideal for organizations exploring AI opportunities, validating use cases, or creating an implementation roadmap before development begins.',
        'features' => ['AI Readiness Assessment', 'Use Case Discovery', 'Technology Roadmap', 'ROI & Feasibility Analysis'],
        'btn_text' => 'Book Consultation',
        'featured' => false,
    ],
    [
        'badge' => 'Most Popular',
        'title' => 'Fixed-Price ML Build',
        'desc' => 'End-to-end AI solution development with clearly defined scope, timeline, and pricing from strategy through deployment.',
        'features' => ['Complete AI Development', 'Production Deployment', 'Testing & Validation', 'Knowledge Transfer'],
        'btn_text' => 'Start Your Project',
        'featured' => true,
    ],
    [
        'badge' => 'Model 03',
        'title' => 'ML Retainer',
        'desc' => 'Ongoing AI engineering support for continuous optimization, monitoring, model improvements, and long-term innovation.',
        'features' => ['Dedicated AI Team', 'MLOps Monitoring', 'Continuous Improvement', 'Monthly Strategic Reviews'],
        'btn_text' => 'Schedule a Call',
        'featured' => false,
    ],
];

// --- Process timeline ---
$process_sub = 'HOW WE WORK';
$process_title_html = 'Our AI/ML <span>Development Process</span>';
$process_text = 'A structured five-step delivery process—from data assessment to live production models—with full transparency and weekly demos at every stage.';
$process_steps = [
    ['title' => 'Data Assessment & Use Case Definition', 'desc' => 'We understand your business goals, audit existing data, identify high-value ML opportunities, and define the project roadmap with measurable KPIs.'],
    ['title' => 'Model Architecture & Experimentation', 'desc' => 'Our AI engineers evaluate algorithms, build prototypes, compare architectures, and validate the best approach before full-scale development.'],
    ['title' => 'Model Training & Evaluation', 'desc' => 'Models are trained, tuned, validated, and benchmarked using production-quality datasets to ensure reliable performance.'],
    ['title' => 'Integration & Deployment', 'desc' => 'We integrate AI into your applications, APIs, cloud infrastructure, and business workflows with production-ready deployment pipelines.'],
    ['title' => 'MLOps & Continuous Improvement', 'desc' => 'Continuous monitoring, retraining, performance tracking, and optimization ensure your AI systems continue to deliver long-term business value.'],
];

// --- Mid CTA ---
$cta_tag = 'READY TO BUILD WITH AI?';
$cta_title_html = "Let's Build Your Next <span>AI/ML Solution</span>";
$cta_text = "Whether you're exploring AI for the first time or scaling an enterprise ML platform, our engineers help you move from strategy to production with measurable business outcomes—not just successful experiments.";

// --- Case studies ---
$cs_sub = 'PROVEN RESULTS';
$cs_title_html = 'Case Studies <span>& Success Stories</span>';
$cs_text = 'Real ML systems, real outcomes. Here is how we have helped organisations turn their data into measurable competitive advantage.';
$case_studies = array_fill(0, 4, [
    'tag' => 'Business Impact',
    'title' => 'Predictive Maintenance for Manufacturing',
    'desc' => 'Built an AI-powered predictive maintenance solution that analyzed machine data in real time, helping reduce unexpected equipment failures.',
    'result' => 'Reduced unplanned downtime by 35% while improving maintenance scheduling and operational efficiency.',
]);

// --- Tech stack ---
$tech_sub = 'OUR TECH STACK';
$tech_title_html = 'Platforms & <span>Technologies</span>';
$tech_text = 'We build ML systems across the full modern stack—from foundational frameworks to MLOps tooling and cloud infrastructure.';
$tech_categories = [
    ['title' => 'Machine Learning Frameworks', 'items' => ['PyTorch', 'TensorFlow', 'Scikit-learn', 'XGBoost', 'LightGBM']],
    ['title' => 'Large Language Models', 'items' => ['GPT-4o', 'Claude', 'Gemini', 'Llama', 'Mistral']],
    ['title' => 'MLOps & Lifecycle', 'items' => ['MLflow', 'Kubeflow', 'LangSmith', 'Apache Airflow']],
    ['title' => 'Data Engineering', 'items' => ['Pinecone', 'Weaviate', 'Apache Spark', 'LangChain']],
    ['title' => 'Cloud & Infrastructure', 'items' => ['AWS', 'Microsoft Azure', 'Google Cloud', 'Docker']],
];

// --- Security & compliance ---
$security_sub = 'YOUR DATA IS SAFE WITH US';
$security_title_html = 'Data Security & <span>Compliance</span>';
$security_text = 'Every ML project requires access to sensitive business data. Here is exactly how we protect it from the first conversation to the final handover.';
$security_cards = [
    ['title' => 'Enterprise Data Encryption', 'desc' => 'All project data is encrypted both in transit and at rest using enterprise-grade security standards, ensuring your sensitive information remains protected throughout the engagement.'],
    ['title' => 'Strict NDA & Confidentiality', 'desc' => 'Every engagement is protected with comprehensive confidentiality agreements to safeguard your intellectual property, business processes, and proprietary datasets.'],
    ['title' => 'Role-Based Access Control', 'desc' => 'Access to project resources is limited only to authorised engineers and stakeholders using secure authentication and least-privilege access policies.'],
    ['title' => 'Secure Cloud Infrastructure', 'desc' => 'Solutions are deployed on trusted cloud platforms with secure networking, monitoring, automated backups, and infrastructure best practices.'],
    ['title' => 'Compliance-Ready Workflows', 'desc' => 'We design AI systems that support industry regulations and governance requirements including GDPR, HIPAA, SOC 2, and other compliance standards where applicable.'],
    ['title' => 'Complete Project Handover', 'desc' => 'At project completion, you receive the source code, documentation, deployment assets, and full ownership of your AI solution without vendor lock-in.'],
];

// --- Related services ---
$related_sub = 'EXPLORE RELATED SERVICES';
$related_title_html = 'Explore Related <span>Toptal Services</span>';
$related_text = 'Find complementary AI, ML and Digital Engineering services that support your transformation journey.';
$related_group_title = 'Recommended Solutions';
$related_items = [
    ['label' => 'AI Development', 'href' => '#'],
    ['label' => 'Machine Learning', 'href' => '#'],
    ['label' => 'Data Science', 'href' => '#'],
    ['label' => 'Computer Vision', 'href' => '#'],
    ['label' => 'NLP', 'href' => '#'],
    ['label' => 'MLOps', 'href' => '#'],
    ['label' => 'AI Consulting', 'href' => '#'],
    ['label' => 'Intelligent Automation', 'href' => '#'],
];

// --- Knowledge hub ---
$blog_sub = 'KNOWLEDGE HUB';
$blog_title_html = 'Latest Insights & <span>Resources</span>';
$blog_text = 'Stay ahead with expert perspectives on artificial intelligence, machine learning, generative AI, data engineering, and digital transformation. Explore practical guides, industry trends, and real-world implementation strategies.';
$blog_posts = [
    [
        'image' => 'images/blog/blog-1.jpg',
        'category' => 'Machine Learning',
        'title' => 'How Predictive AI is Transforming Modern Enterprises',
        'desc' => 'Learn how predictive machine learning models help businesses forecast demand, reduce operational costs, and make smarter decisions.',
        'link' => '#',
    ],
    [
        'image' => 'images/blog/blog-2.jpg',
        'category' => 'Generative AI',
        'title' => 'Building Enterprise AI Assistants with LLMs',
        'desc' => 'Discover how organizations are deploying secure, enterprise-grade AI assistants using modern large language models.',
        'link' => '#',
    ],
];

// --- FAQ ---
$faq_intro = 'Find answers to some of the most common questions about our Voice AI solutions and implementation process.';
$faqs = [
    ['What is Voice AI?', 'Voice AI uses artificial intelligence to understand, process, and respond to spoken language naturally.'],
    ['How can Voice AI help my business?', 'It automates customer support, reduces response time, improves customer satisfaction, and lowers operational costs.'],
    ['Can Voice AI integrate with existing systems?', 'Yes. Our Voice AI solutions can integrate with CRM, ERP, helpdesk software, and custom business applications.'],
    ['Is Voice AI secure?', 'Absolutely. We implement enterprise-grade security, encryption, and compliance standards to protect your data.'],
];

include __DIR__ . '/_subservice2.php';
