<?php
$pageTitle = $lang === 'de' ? 'Veranstaltungen' : 'Events';

$events = [
    [
        'title_de' => 'Hannover Messe 2026',
        'title_en' => 'Hannover Messe 2026',
        'date' => '2026-04-13',
        'end_date' => '2026-04-17',
        'location' => 'Hannover, Germany',
        'desc_de' => 'Die weltweit wichtigste Industriemesse mit Fokus auf Automatisierung, Digitalisierung und Energielösungen für die Industrie.',
        'desc_en' => 'The world\'s most important industrial trade fair focusing on automation, digitalization and energy solutions for industry.',
        'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600',
    ],
    [
        'title_de' => 'ACHEMA 2026',
        'title_en' => 'ACHEMA 2026',
        'date' => '2026-06-15',
        'end_date' => '2026-06-19',
        'location' => 'Frankfurt, Germany',
        'desc_de' => 'Weltforum der Prozessindustrie und Leitmesse für Chemietechnik, Verfahrenstechnik und Biotechnologie.',
        'desc_en' => 'World forum for the process industry and leading trade fair for chemical engineering, process engineering and biotechnology.',
        'image' => 'https://images.unsplash.com/photo-1591115765373-5207764f72e7?w=600',
    ],
    [
        'title_de' => 'Pipeline Technology Conference 2026',
        'title_en' => 'Pipeline Technology Conference 2026',
        'date' => '2026-03-16',
        'end_date' => '2026-03-19',
        'location' => 'Berlin, Germany',
        'desc_de' => 'Internationale Konferenz für Pipeline-Technologie, Inspektion und Integrität mit Fokus auf Öl- und Gastransport.',
        'desc_en' => 'International conference for pipeline technology, inspection and integrity with focus on oil and gas transportation.',
        'image' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=600',
    ],
    [
        'title_de' => 'SMM Hamburg 2026',
        'title_en' => 'SMM Hamburg 2026',
        'date' => '2026-09-08',
        'end_date' => '2026-09-11',
        'location' => 'Hamburg, Germany',
        'desc_de' => 'Die führende internationale Messe der maritimen Wirtschaft mit Fokus auf Offshore-Öl- und Gastechnologie.',
        'desc_en' => 'The leading international maritime trade fair with focus on offshore oil and gas technology.',
        'image' => 'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=600',
    ],
    [
        'title_de' => 'HUSUM Wind 2026',
        'title_en' => 'HUSUM Wind 2026',
        'date' => '2026-09-14',
        'end_date' => '2026-09-17',
        'location' => 'Husum, Germany',
        'desc_de' => 'Fachmesse für Windenergie mit Schwerpunkt auf Offshore-Technologien und Energieinfrastruktur.',
        'desc_en' => 'Trade fair for wind energy with focus on offshore technologies and energy infrastructure.',
        'image' => 'https://images.unsplash.com/photo-1532601224476-15c79f2f7a51?w=600',
    ],
    [
        'title_de' => 'E-world energy & water 2026',
        'title_en' => 'E-world energy & water 2026',
        'date' => '2026-02-10',
        'end_date' => '2026-02-12',
        'location' => 'Essen, Germany',
        'desc_de' => 'Europas führende Energiefachmesse für Öl, Gas, Strom und Wasser mit Fokus auf Energiewende.',
        'desc_en' => 'Europe\'s leading energy trade fair for oil, gas, electricity and water with focus on energy transition.',
        'image' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600',
    ],
];

$pastEvents = [
    [
        'title_de' => 'bauma 2025',
        'title_en' => 'bauma 2025',
        'date' => '2025-04-07',
        'location' => 'München, Germany',
        'desc_de' => 'Die Weltleitmesse für Baumaschinen, Baustoffmaschinen, Bergbaumaschinen, Baufahrzeuge und Baugeräte.',
        'desc_en' => 'The world\'s leading trade fair for construction machinery, building material machines, mining machines, construction vehicles and construction equipment.',
    ],
    [
        'title_de' => 'Hannover Messe 2025',
        'title_en' => 'Hannover Messe 2025',
        'date' => '2025-03-31',
        'location' => 'Hannover, Germany',
        'desc_de' => 'Die weltweit wichtigste Industriemesse mit Fokus auf Automatisierung und Digitalisierung.',
        'desc_en' => 'The world\'s most important industrial trade fair focusing on automation and digitalization.',
    ],
    [
        'title_de' => 'Pipeline Technology Conference 2025',
        'title_en' => 'Pipeline Technology Conference 2025',
        'date' => '2025-05-12',
        'location' => 'Berlin, Germany',
        'desc_de' => 'Internationale Konferenz für Pipeline-Technologie und -Innovation.',
        'desc_en' => 'International conference for pipeline technology and innovation.',
    ],
    [
        'title_de' => 'all about automation Straubing',
        'title_en' => 'all about automation Straubing',
        'date' => '2024-07-03',
        'location' => 'Straubing, Germany',
        'desc_de' => 'Regionale Fachmesse für Industrieautomation.',
        'desc_en' => 'Regional trade fair for industrial automation.',
    ],
];
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

<section class="events-hero" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1600') center/cover;">
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        <p class="hero-subtitle">
            <?= $lang === 'de' 
                ? 'Treffen Sie uns auf Messen und Veranstaltungen weltweit.' 
                : 'Meet us at trade fairs and events worldwide.' ?>
        </p>
    </div>
</section>

<section class="events-section upcoming">
    <div class="container">
        <h2 class="section-title"><?= $lang === 'de' ? 'Kommende Veranstaltungen' : 'Upcoming Events' ?></h2>
        
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
            <div class="event-card">
                <div class="event-image">
                    <img src="<?= $event['image'] ?>" alt="<?= $lang === 'de' ? $event['title_de'] : $event['title_en'] ?>">
                    <div class="event-date-badge">
                        <span class="day"><?= date('d', strtotime($event['date'])) ?></span>
                        <span class="month"><?= date('M', strtotime($event['date'])) ?></span>
                    </div>
                </div>
                <div class="event-content">
                    <h3><?= $lang === 'de' ? $event['title_de'] : $event['title_en'] ?></h3>
                    <div class="event-meta">
                        <span class="event-dates">
                            📅 <?= date('d.m.Y', strtotime($event['date'])) ?> - <?= date('d.m.Y', strtotime($event['end_date'])) ?>
                        </span>
                        <span class="event-location">📍 <?= $event['location'] ?></span>
                    </div>
                    <p><?= $lang === 'de' ? $event['desc_de'] : $event['desc_en'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="events-section past">
    <div class="container">
        <h2 class="section-title"><?= $lang === 'de' ? 'Vergangene Veranstaltungen' : 'Past Events' ?></h2>
        
        <div class="past-events-list">
            <?php foreach ($pastEvents as $event): ?>
            <div class="past-event-item">
                <div class="past-event-date">
                    <span class="day"><?= date('d', strtotime($event['date'])) ?></span>
                    <span class="month"><?= date('M Y', strtotime($event['date'])) ?></span>
                </div>
                <div class="past-event-content">
                    <h4><?= $lang === 'de' ? $event['title_de'] : $event['title_en'] ?></h4>
                    <span class="location">📍 <?= $event['location'] ?></span>
                    <p><?= $lang === 'de' ? $event['desc_de'] : $event['desc_en'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.events-hero {
    min-height: 350px;
    display: flex;
    align-items: center;
    color: white;
    text-align: center;
}

.events-hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.hero-subtitle {
    font-size: 1.25rem;
    opacity: 0.9;
}

.events-section {
    padding: 4rem 0;
}

.events-section.past {
    background: var(--gray-50);
}

.section-title {
    color: var(--primary);
    font-size: 1.75rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--primary);
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.event-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.event-card:hover {
    transform: translateY(-5px);
}

.event-image {
    position: relative;
    aspect-ratio: 16/10;
    overflow: hidden;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.event-date-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: var(--primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-align: center;
}

.event-date-badge .day {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.event-date-badge .month {
    font-size: 0.8rem;
    text-transform: uppercase;
}

.event-content {
    padding: 1.5rem;
}

.event-content h3 {
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
}

.event-meta {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: var(--gray-600);
}

.event-content p {
    color: var(--gray-600);
    line-height: 1.6;
}

.past-events-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.past-event-item {
    display: flex;
    gap: 2rem;
    padding: 1.5rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.past-event-date {
    min-width: 80px;
    text-align: center;
    padding: 1rem;
    background: var(--gray-100);
    border-radius: 8px;
}

.past-event-date .day {
    display: block;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary);
}

.past-event-date .month {
    font-size: 0.8rem;
    color: var(--gray-500);
}

.past-event-content h4 {
    margin-bottom: 0.25rem;
}

.past-event-content .location {
    font-size: 0.9rem;
    color: var(--gray-500);
}

.past-event-content p {
    margin-top: 0.5rem;
    color: var(--gray-600);
    font-size: 0.9rem;
}

@media (max-width: 1024px) {
    .events-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .events-grid {
        grid-template-columns: 1fr;
    }
    
    .past-event-item {
        flex-direction: column;
        gap: 1rem;
    }
    
    .events-hero h1 {
        font-size: 2rem;
    }
}
</style>
