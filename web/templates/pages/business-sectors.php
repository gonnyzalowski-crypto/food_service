<?php
$pageTitle = $lang === 'de' ? 'Geschäftsbereiche' : 'Business Sectors';

$sectors = [
    [
        'slug' => 'pipelines-plants',
        'title_de' => 'Pipelines & Anlagen',
        'title_en' => 'Pipelines & Plants',
        'desc_de' => 'Pipelinebau, Anlagenbau, Digitale & Kommunale Infrastruktur, Erdkabel, Tiefbohrungen, Horizontalbohrungen (HDD), Dienstleistungen',
        'desc_en' => 'Pipeline Construction, Plant Construction, Digital & Municipal Infrastructure, Underground Power Lines, Deep Drilling, Horizontal Directional Drilling (HDD), Services',
        'image' => '/images/photos/pipeline1.jpg',
        'icon' => '🔧'
    ],
    [
        'slug' => 'mechanical-engineering',
        'title_de' => 'Maschinenbau',
        'title_en' => 'Mechanical Engineering',
        'desc_de' => 'Apparatebau, Anlagentechnik, Bohrtechnik, Verfahrenstechnik, Prozessanlagen, After Sales Service',
        'desc_en' => 'Apparatus Engineering, Equipment Technology, Drilling Technology, Process Engineering, Process Plants, After Sales Service',
        'image' => '/images/photos/mechanical1.jpg',
        'icon' => '⚙️'
    ],
    [
        'slug' => 'electrical-engineering',
        'title_de' => 'Elektrotechnik',
        'title_en' => 'Electrical Engineering',
        'desc_de' => 'Elektroinstallation und Energietechnik, Mess- und Regeltechnik, Automatisierungstechnik, Funktionale Sicherheit, Elektronikwerkstatt, Simulationen und Berechnungen',
        'desc_en' => 'Electrical Installation and Energy Technology, Electrical Measurement and Control Technology, Automation Technology, Functional Safety, Electronics Workshop, Simulations and Calculations',
        'image' => '/images/photos/electrical.jpg',
        'icon' => '⚡'
    ],
    [
        'slug' => 'civil-engineering',
        'title_de' => 'Hoch- & Tiefbau',
        'title_en' => 'Civil & Structural Engineering',
        'desc_de' => 'Straßenbau und Tiefbau, Brückenbau und Ingenieurbau, Wasserbau, Deponiebau, Industriebau und Hochbau, Leitungsbau',
        'desc_en' => 'Road Construction and Civil Engineering, Bridge Construction and Civil Engineering, Hydraulic Engineering, Landfill Construction, Industrial Construction and Structural Engineering, Conduit Construction',
        'image' => '/images/photos/civil.jpg',
        'icon' => '🏗️'
    ],
    [
        'slug' => 'raw-materials',
        'title_de' => 'Roh- & Baustoffe',
        'title_en' => 'Raw & Construction Material',
        'desc_de' => 'Asphaltmischanlagen, Steinbrüche und Kieswerke, Sand- und Kiesgewinnung, Baustoffannahme',
        'desc_en' => 'Asphalt Mixing Plants, Quarries and Gravel Mills, Sand and Gravel Extraction, Construction Material Acceptance',
        'image' => '/images/photos/raw-materials.jpg',
        'icon' => '🪨'
    ],
];
?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/">STREICHER</a>
            <span>-</span>
            <span><?= $pageTitle ?></span>
        </nav>
    </div>
</div>

<section class="sectors-hero" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('/images/photos/pipeline2.jpg') center/cover;">
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        <p class="hero-subtitle">
            <?= $lang === 'de' 
                ? 'Die STREICHER Gruppe ist in fünf Geschäftsbereichen tätig und bietet ein breites Spektrum an Dienstleistungen.' 
                : 'The STREICHER Group operates in five business sectors, offering a wide range of services.' ?>
        </p>
    </div>
</section>

<section class="sectors-list">
    <div class="container">
        <?php foreach ($sectors as $index => $sector): ?>
        <div class="sector-item <?= $index % 2 === 1 ? 'reverse' : '' ?>">
            <div class="sector-image">
                <img src="<?= $sector['image'] ?>" alt="<?= $lang === 'de' ? $sector['title_de'] : $sector['title_en'] ?>">
            </div>
            <div class="sector-content">
                <span class="sector-icon"><?= $sector['icon'] ?></span>
                <h2><?= $lang === 'de' ? $sector['title_de'] : $sector['title_en'] ?></h2>
                <p><?= $lang === 'de' ? $sector['desc_de'] : $sector['desc_en'] ?></p>
                <a href="/business-sectors/<?= $sector['slug'] ?>" class="btn btn-primary">
                    <?= $lang === 'de' ? 'Mehr erfahren' : 'Learn more' ?> →
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="sectors-cta">
    <div class="container">
        <h2><?= $lang === 'de' ? 'Haben Sie ein Projekt?' : 'Have a project?' ?></h2>
        <p><?= $lang === 'de' 
            ? 'Kontaktieren Sie uns für eine unverbindliche Beratung zu Ihrem Projekt.' 
            : 'Contact us for a no-obligation consultation about your project.' ?></p>
        <div class="cta-buttons">
            <a href="/contact" class="btn btn-primary btn-lg"><?= $lang === 'de' ? 'Kontakt aufnehmen' : 'Get in touch' ?></a>
            <a href="/reference-projects" class="btn btn-outline-light btn-lg"><?= $lang === 'de' ? 'Referenzprojekte ansehen' : 'View reference projects' ?></a>
        </div>
    </div>
</section>

<style>
.sectors-hero {
    min-height: 400px;
    display: flex;
    align-items: center;
    color: white;
    text-align: center;
}

.sectors-hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.hero-subtitle {
    font-size: 1.25rem;
    max-width: 700px;
    margin: 0 auto;
    opacity: 0.9;
}

.sectors-list {
    padding: 4rem 0;
}

.sector-item {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    margin-bottom: 4rem;
    padding-bottom: 4rem;
    border-bottom: 1px solid var(--gray-200);
}

.sector-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.sector-item.reverse {
    direction: rtl;
}

.sector-item.reverse > * {
    direction: ltr;
}

.sector-image {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

.sector-image img {
    width: 100%;
    height: auto;
    display: block;
}

.sector-content {
    padding: 2rem 0;
}

.sector-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 1rem;
}

.sector-content h2 {
    font-size: 2rem;
    color: var(--primary);
    margin-bottom: 1rem;
}

.sector-content p {
    font-size: 1.1rem;
    color: var(--gray-600);
    line-height: 1.8;
    margin-bottom: 2rem;
}

.sectors-cta {
    background: var(--primary);
    color: white;
    padding: 4rem 0;
    text-align: center;
}

.sectors-cta h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.sectors-cta p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-outline-light {
    border: 2px solid white;
    color: white;
    background: transparent;
}

.btn-outline-light:hover {
    background: white;
    color: var(--primary);
}

@media (max-width: 1024px) {
    .sector-item {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .sector-item.reverse {
        direction: ltr;
    }
}

@media (max-width: 768px) {
    .sectors-hero h1 {
        font-size: 2rem;
    }
    
    .sector-content h2 {
        font-size: 1.5rem;
    }
}
</style>
