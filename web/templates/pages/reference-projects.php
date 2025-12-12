<?php
$pageTitle = $lang === 'de' ? 'Referenzprojekte' : 'Reference Projects';

$projects = [
    [
        'title_de' => 'EUGAL Pipeline',
        'title_en' => 'EUGAL Pipeline',
        'location' => 'Germany',
        'year' => '2019-2020',
        'category' => 'Pipelines & Plants',
        'image' => '/images/photos/pipeline 1.png',
        'desc_de' => 'Europäische Gas-Anbindungsleitung - 480 km Pipeline für den Transport von Erdgas.',
        'desc_en' => 'European Gas Link - 480 km pipeline for natural gas transport.',
    ],
    [
        'title_de' => 'TENP III Pipeline',
        'title_en' => 'TENP III Pipeline',
        'location' => 'Germany',
        'year' => '2023-2025',
        'category' => 'Pipelines & Plants',
        'image' => '/images/photos/pipeline 2.png',
        'desc_de' => 'Trans-Europa-Naturgas-Pipeline - wichtige Verbindung im europäischen Gasnetz.',
        'desc_en' => 'Trans-Europa-Naturgas-Pipeline - important connection in the European gas network.',
    ],
    [
        'title_de' => 'Zeelink Pipeline',
        'title_en' => 'Zeelink Pipeline',
        'location' => 'Germany/Belgium',
        'year' => '2020-2021',
        'category' => 'Pipelines & Plants',
        'image' => '/images/photos/pipeline 3.png',
        'desc_de' => 'Grenzüberschreitende Pipeline zwischen Deutschland und Belgien.',
        'desc_en' => 'Cross-border pipeline between Germany and Belgium.',
    ],
    [
        'title_de' => 'Offshore Drilling Platform',
        'title_en' => 'Offshore Drilling Platform',
        'location' => 'North Sea',
        'year' => '2021-2022',
        'category' => 'Drilling Technology',
        'image' => '/images/photos/drilling.png',
        'desc_de' => 'Komplette Bohrausrüstung und Hydrauliksysteme für Offshore-Plattform.',
        'desc_en' => 'Complete drilling equipment and hydraulic systems for offshore platform.',
    ],
    [
        'title_de' => 'Amprion Erdkabelprojekt',
        'title_en' => 'Amprion Underground Cable Project',
        'location' => 'Germany',
        'year' => '2024-2027',
        'category' => 'Civil Engineering',
        'image' => '/images/photos/civil.png',
        'desc_de' => 'Über 1.300 km Tiefbauarbeiten für die Energiewende.',
        'desc_en' => 'Over 1,300 km of civil engineering works for the energy transition.',
    ],
    [
        'title_de' => 'LNG Terminal Wilhelmshaven',
        'title_en' => 'LNG Terminal Wilhelmshaven',
        'location' => 'Germany',
        'year' => '2023-2025',
        'category' => 'Pipelines & Plants',
        'image' => '/images/photos/pipeline 4.png',
        'desc_de' => 'Installation von Hochdruck-Rohrleitungssystemen für das LNG-Import-Terminal.',
        'desc_en' => 'Installation of high-pressure piping systems for the LNG import terminal.',
    ],
    [
        'title_de' => 'Geothermie-Bohrprojekt Bayern',
        'title_en' => 'Geothermal Drilling Project Bavaria',
        'location' => 'Germany',
        'year' => '2022-2023',
        'category' => 'Drilling Technology',
        'image' => '/images/photos/mechanical2.png',
        'desc_de' => 'Tiefbohrungen für Geothermie-Kraftwerk mit mobilen Bohranlagen.',
        'desc_en' => 'Deep drilling for geothermal power plant with mobile drilling rigs.',
    ],
    [
        'title_de' => 'Raffinerie-Modernisierung',
        'title_en' => 'Refinery Modernization',
        'location' => 'Germany',
        'year' => '2020-2021',
        'category' => 'Mechanical Engineering',
        'image' => '/images/photos/mechanical1.png',
        'desc_de' => 'Modernisierung der Prozessanlagen und Rohrleitungssysteme.',
        'desc_en' => 'Modernization of process plants and piping systems.',
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

<section class="projects-hero" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('/images/photos/civil2.png') center/cover;">
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        <p class="hero-subtitle">
            <?= $lang === 'de' 
                ? 'Entdecken Sie unsere erfolgreich abgeschlossenen Projekte weltweit.' 
                : 'Discover our successfully completed projects worldwide.' ?>
        </p>
    </div>
</section>

<section class="projects-section">
    <div class="container">
        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
            <div class="project-card">
                <div class="project-image">
                    <img src="<?= $project['image'] ?>" alt="<?= $lang === 'de' ? $project['title_de'] : $project['title_en'] ?>">
                    <span class="project-category"><?= $project['category'] ?></span>
                </div>
                <div class="project-content">
                    <div class="project-meta">
                        <span class="project-location">📍 <?= $project['location'] ?></span>
                        <span class="project-year">📅 <?= $project['year'] ?></span>
                    </div>
                    <h3><?= $lang === 'de' ? $project['title_de'] : $project['title_en'] ?></h3>
                    <p><?= $lang === 'de' ? $project['desc_de'] : $project['desc_en'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.projects-hero {
    min-height: 350px;
    display: flex;
    align-items: center;
    color: white;
    text-align: center;
}

.projects-hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.hero-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
}

.projects-section {
    padding: 4rem 0;
    background: var(--gray-50);
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.project-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.project-image {
    position: relative;
    aspect-ratio: 16/10;
    overflow: hidden;
}

.project-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.project-card:hover .project-image img {
    transform: scale(1.05);
}

.project-category {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: var(--primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.project-content {
    padding: 1.5rem;
}

.project-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
    color: var(--gray-500);
}

.project-content h3 {
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
    color: var(--dark);
}

.project-content p {
    color: var(--gray-600);
    line-height: 1.6;
}

@media (max-width: 1024px) {
    .projects-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .projects-grid {
        grid-template-columns: 1fr;
    }
    
    .projects-hero h1 {
        font-size: 2rem;
    }
}
</style>
