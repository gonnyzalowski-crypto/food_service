<?php
$pageTitle = 'HSE-Q';
$pageSubtitle = $lang === 'de' ? 'Gesundheit, Sicherheit, Umwelt & Qualität' : 'Health, Safety, Environment & Quality';
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">Gordon Food Service</a>
            <span>-</span>
            <span><?= $pageTitle ?></span>
        </nav>
    </div>
</div>

<section class="hseq-hero" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1600') center/cover;">
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        <p class="hero-subtitle"><?= $pageSubtitle ?></p>
    </div>
</section>

<section class="hseq-intro">
    <div class="container">
        <div class="intro-content">
            <?php if ($lang === 'de'): ?>
            <p class="lead">Bei Gordon Food Service hat die Sicherheit und Gesundheit unserer Mitarbeiter, Kunden und Partner höchste Priorität. Wir sind bestrebt, alle unsere Aktivitäten unter Einhaltung höchster Qualitäts- und Umweltstandards durchzuführen.</p>
            <?php else: ?>
            <p class="lead">At Gordon Food Service, the safety and health of our employees, customers and partners is our top priority. We are committed to carrying out all our activities in compliance with the highest quality and environmental standards.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="hseq-pillars">
    <div class="container">
        <div class="pillars-grid">
            <div class="pillar-card">
                <div class="pillar-icon">🏥</div>
                <h3><?= $lang === 'de' ? 'Gesundheit' : 'Health' ?></h3>
                <p><?= $lang === 'de' 
                    ? 'Wir fördern aktiv die Gesundheit unserer Mitarbeiter durch präventive Maßnahmen und ein umfassendes Gesundheitsmanagement.'
                    : 'We actively promote the health of our employees through preventive measures and comprehensive health management.' ?></p>
            </div>
            <div class="pillar-card">
                <div class="pillar-icon">🛡️</div>
                <h3><?= $lang === 'de' ? 'Sicherheit' : 'Safety' ?></h3>
                <p><?= $lang === 'de' 
                    ? 'Null Unfälle ist unser Ziel. Wir investieren kontinuierlich in Schulungen und modernste Sicherheitsausrüstung.'
                    : 'Zero accidents is our goal. We continuously invest in training and state-of-the-art safety equipment.' ?></p>
            </div>
            <div class="pillar-card">
                <div class="pillar-icon">🌿</div>
                <h3><?= $lang === 'de' ? 'Umwelt' : 'Environment' ?></h3>
                <p><?= $lang === 'de' 
                    ? 'Nachhaltigkeit ist ein zentraler Wert. Wir minimieren unseren ökologischen Fußabdruck und setzen auf umweltfreundliche Technologien.'
                    : 'Sustainability is a core value. We minimize our ecological footprint and rely on environmentally friendly technologies.' ?></p>
            </div>
            <div class="pillar-card">
                <div class="pillar-icon">✅</div>
                <h3><?= $lang === 'de' ? 'Qualität' : 'Quality' ?></h3>
                <p><?= $lang === 'de' 
                    ? 'Höchste Qualitätsstandards in allen Prozessen. Unsere Zertifizierungen belegen unser Engagement für Exzellenz.'
                    : 'Highest quality standards in all processes. Our certifications demonstrate our commitment to excellence.' ?></p>
            </div>
        </div>
    </div>
</section>

<section class="certifications">
    <div class="container">
        <h2 class="section-title"><?= $lang === 'de' ? 'Zertifizierungen' : 'Certifications' ?></h2>
        <div class="certs-grid">
            <div class="cert-item">
                <div class="cert-badge">ISO 9001:2015</div>
                <p><?= $lang === 'de' ? 'Qualitätsmanagementsystem' : 'Quality Management System' ?></p>
            </div>
            <div class="cert-item">
                <div class="cert-badge">ISO 14001:2015</div>
                <p><?= $lang === 'de' ? 'Umweltmanagementsystem' : 'Environmental Management System' ?></p>
            </div>
            <div class="cert-item">
                <div class="cert-badge">ISO 45001:2018</div>
                <p><?= $lang === 'de' ? 'Arbeitsschutzmanagementsystem' : 'Occupational Health & Safety' ?></p>
            </div>
            <div class="cert-item">
                <div class="cert-badge">SCC**</div>
                <p><?= $lang === 'de' ? 'Sicherheits Certifikat Contraktoren' : 'Safety Certificate Contractors' ?></p>
            </div>
            <div class="cert-item">
                <div class="cert-badge">API</div>
                <p><?= $lang === 'de' ? 'American Petroleum Institute' : 'American Petroleum Institute' ?></p>
            </div>
            <div class="cert-item">
                <div class="cert-badge">EN 1090</div>
                <p><?= $lang === 'de' ? 'Stahlbau-Zertifizierung' : 'Steel Construction Certification' ?></p>
            </div>
        </div>
    </div>
</section>

<style>
.hseq-hero {
    min-height: 350px;
    display: flex;
    align-items: center;
    color: white;
    text-align: center;
}

.hseq-hero h1 {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.hero-subtitle {
    font-size: 1.5rem;
    opacity: 0.9;
}

.hseq-intro {
    padding: 4rem 0;
    background: white;
}

.intro-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.lead {
    font-size: 1.25rem;
    line-height: 1.8;
    color: var(--gray-700);
}

.hseq-pillars {
    padding: 4rem 0;
    background: var(--gray-50);
}

.pillars-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
}

.pillar-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s;
}

.pillar-card:hover {
    transform: translateY(-5px);
}

.pillar-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.pillar-card h3 {
    color: var(--primary);
    margin-bottom: 1rem;
}

.pillar-card p {
    color: var(--gray-600);
    line-height: 1.6;
}

.certifications {
    padding: 4rem 0;
    background: var(--dark);
    color: white;
}

.certifications .section-title {
    text-align: center;
    margin-bottom: 3rem;
    color: white;
}

.certs-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1.5rem;
}

.cert-item {
    text-align: center;
}

.cert-badge {
    background: var(--primary);
    color: white;
    padding: 1rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.75rem;
}

.cert-item p {
    font-size: 0.85rem;
    color: var(--gray-400);
}

@media (max-width: 1024px) {
    .pillars-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .certs-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .pillars-grid {
        grid-template-columns: 1fr;
    }
    
    .certs-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .hseq-hero h1 {
        font-size: 2rem;
    }
}
</style>
