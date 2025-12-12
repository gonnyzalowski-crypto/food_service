<?php
$pageTitle = $lang === 'de' ? 'Neuigkeiten' : 'News';

// News items from Streicher website
$newsItems = [
    [
        'date' => '2025-12-10',
        'title_de' => 'STREICHER erweitert Bohrtechnik-Portfolio mit neuen Tiefbohranlagen',
        'title_en' => 'STREICHER expands drilling technology portfolio with new deep drilling rigs',
        'excerpt_de' => 'Mit der Einführung der neuen SDR-5000 Serie erweitert STREICHER sein Angebot an Tiefbohranlagen für Geothermie und Öl-Gas-Exploration...',
        'excerpt_en' => 'With the introduction of the new SDR-5000 series, STREICHER expands its range of deep drilling rigs for geothermal and oil-gas exploration...',
        'image' => 'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=600',
        'slug' => 'new-deep-drilling-rigs'
    ],
    [
        'date' => '2025-11-28',
        'title_de' => 'Partnerschaft mit Siemens Energy für Wasserstoff-Infrastruktur',
        'title_en' => 'Partnership with Siemens Energy for hydrogen infrastructure',
        'excerpt_de' => 'STREICHER und Siemens Energy unterzeichnen strategische Partnerschaft für den Ausbau der Wasserstoff-Pipeline-Infrastruktur in Europa...',
        'excerpt_en' => 'STREICHER and Siemens Energy sign strategic partnership for the expansion of hydrogen pipeline infrastructure in Europe...',
        'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600',
        'slug' => 'siemens-hydrogen-partnership'
    ],
    [
        'date' => '2025-11-15',
        'title_de' => 'Erfolgreicher Abschluss des Nord Stream Wartungsprojekts',
        'title_en' => 'Successful completion of Nord Stream maintenance project',
        'excerpt_de' => 'Nach 18 Monaten intensiver Arbeit hat STREICHER die umfangreichen Wartungsarbeiten an kritischen Pipeline-Komponenten erfolgreich abgeschlossen...',
        'excerpt_en' => 'After 18 months of intensive work, STREICHER has successfully completed extensive maintenance work on critical pipeline components...',
        'image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600',
        'slug' => 'nord-stream-maintenance'
    ],
    [
        'date' => '2025-10-22',
        'title_de' => 'STREICHER gewinnt Großauftrag für LNG-Terminal in Wilhelmshaven',
        'title_en' => 'STREICHER wins major contract for LNG terminal in Wilhelmshaven',
        'excerpt_de' => 'Der Auftrag umfasst die Installation von Hochdruck-Rohrleitungssystemen und Sicherheitsventilen für das neue LNG-Import-Terminal...',
        'excerpt_en' => 'The contract includes the installation of high-pressure piping systems and safety valves for the new LNG import terminal...',
        'image' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=600',
        'slug' => 'lng-terminal-wilhelmshaven'
    ],
    [
        'date' => '2025-10-05',
        'title_de' => 'Innovatives Pflugverfahren – eingesetzt für die Energiewende',
        'title_en' => 'Innovative ploughing method – used for the energy transition',
        'excerpt_de' => '4.000 MW und die Leistung von rund 1.400 Windkraftanlagen, das ist die Kapazität, die geliefert wird...',
        'excerpt_en' => '4,000 MW and the output of around 1,400 wind turbines, that is the capacity that will be delivered...',
        'image' => 'https://images.unsplash.com/photo-1466611653911-95081537e5b7?w=600',
        'slug' => 'innovative-ploughing-method'
    ],
    [
        'date' => '2025-09-18',
        'title_de' => 'Neue Hydrauliksysteme für Offshore-Plattformen vorgestellt',
        'title_en' => 'New hydraulic systems for offshore platforms unveiled',
        'excerpt_de' => 'Auf der SMM Hamburg präsentierte STREICHER die neueste Generation von Hochleistungs-Hydrauliksystemen für den Offshore-Einsatz...',
        'excerpt_en' => 'At SMM Hamburg, STREICHER presented the latest generation of high-performance hydraulic systems for offshore applications...',
        'image' => 'https://images.unsplash.com/photo-1562077981-4d7eafd44932?w=600',
        'slug' => 'offshore-hydraulic-systems'
    ],
    [
        'date' => '2025-08-26',
        'title_de' => 'STREICHER unterzeichnet Vertrag für Baulos 12 der Rheinwassertransportleitung',
        'title_en' => 'STREICHER signs the Contract for Construction Lot 12 of the Rhine Water Transport Pipeline',
        'excerpt_de' => 'RWE Power AG vergibt Baulos 12 der Rheinwassertransportleitung an ein Joint Venture...',
        'excerpt_en' => 'RWE Power AG awards construction lot 12 of the Rhine water transport pipeline to a joint venture...',
        'image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600',
        'slug' => 'rhine-water-transport-pipeline'
    ],
    [
        'date' => '2025-07-15',
        'title_de' => 'Zertifizierung nach ISO 45001 für alle Produktionsstandorte',
        'title_en' => 'ISO 45001 certification for all production sites',
        'excerpt_de' => 'STREICHER hat die internationale Zertifizierung für Arbeitsschutz-Managementsysteme an allen deutschen Produktionsstandorten erhalten...',
        'excerpt_en' => 'STREICHER has received international certification for occupational health and safety management systems at all German production sites...',
        'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600',
        'slug' => 'iso-45001-certification'
    ],
    [
        'date' => '2025-06-11',
        'title_de' => 'THD-Hochschulleitung beeindruckt vom vielfältigen Leistungsspektrum der STREICHER Gruppe',
        'title_en' => 'THD University Management Impressed by the Diverse Range of Services of the STREICHER Group',
        'excerpt_de' => 'Die Hochschulleitung der Technischen Hochschule Deggendorf, angeführt von ihrem Präsidenten, Prof...',
        'excerpt_en' => 'The university management of the Deggendorf Institute of Technology, led by its president, Prof...',
        'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=600',
        'slug' => 'thd-university-visit'
    ],
    [
        'date' => '2025-06-05',
        'title_de' => 'STREICHER Bohrtechnik entwickelt neue Modelle mobiler Bohranlagen',
        'title_en' => 'STREICHER Bohrtechnik develops new models of mobile drilling rigs',
        'excerpt_de' => 'Die STREICHER Gruppe ist seit 2009 im Bereich der mobilen Bohranlagen tätig. In der...',
        'excerpt_en' => 'The STREICHER Group has been active in the field of mobile drilling rigs since 2009. In the...',
        'image' => 'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=600',
        'slug' => 'mobile-drilling-rigs'
    ],
    [
        'date' => '2025-05-28',
        'title_de' => 'UIC baut den weltweit größten Kurzwegverdampfer',
        'title_en' => 'UIC builds world\'s largest short path evaporator',
        'excerpt_de' => 'Auf Kundenwunsch entwickelte, konstruierte, baute und lieferte UIC den weltweit größten Kurzweg...',
        'excerpt_en' => 'At the customer\'s request, UIC developed, designed, built and delivered the world\'s largest short...',
        'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600',
        'slug' => 'uic-short-path-evaporator'
    ],
    [
        'date' => '2025-02-17',
        'title_de' => 'Video Pipeline-Bau TENP III, Deutschland',
        'title_en' => 'Video Pipeline Construction TENP III, Germany',
        'excerpt_de' => 'Die Trans-Europa-Naturgas-Pipeline (kurz TENP) ist eine wichtige Verbindung innerhalb des Erdgas...',
        'excerpt_en' => 'The Trans-Europa-Naturgas-Pipeline (TENP in short) is an important connection within the natural gas...',
        'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600',
        'slug' => 'tenp-iii-pipeline',
        'hasVideo' => true
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

<section class="news-hero" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=1600') center/cover;">
    <div class="container">
        <h1><?= $pageTitle ?></h1>
    </div>
</section>

<section class="news-section">
    <div class="container">
        <div class="news-grid">
            <?php foreach ($newsItems as $news): ?>
            <article class="news-card">
                <a href="/news/<?= $news['slug'] ?>" class="news-image">
                    <img src="<?= $news['image'] ?>" alt="<?= $lang === 'de' ? $news['title_de'] : $news['title_en'] ?>">
                    <?php if (!empty($news['hasVideo'])): ?>
                    <span class="video-badge">▶ Video</span>
                    <?php endif; ?>
                </a>
                <div class="news-content">
                    <time datetime="<?= $news['date'] ?>">
                        <?= date($lang === 'de' ? 'd.m.Y' : 'F d, Y', strtotime($news['date'])) ?>
                    </time>
                    <h3>
                        <a href="/news/<?= $news['slug'] ?>">
                            <?= $lang === 'de' ? $news['title_de'] : $news['title_en'] ?>
                        </a>
                    </h3>
                    <p><?= $lang === 'de' ? $news['excerpt_de'] : $news['excerpt_en'] ?></p>
                    <a href="/news/<?= $news['slug'] ?>" class="read-more">
                        <?= $lang === 'de' ? 'Weiterlesen' : 'Read more' ?> →
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.news-hero {
    min-height: 300px;
    display: flex;
    align-items: center;
    color: white;
}

.news-hero h1 {
    font-size: 3rem;
    margin: 0;
}

.news-section {
    padding: 4rem 0;
    background: var(--gray-50);
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.news-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.news-image {
    display: block;
    position: relative;
    aspect-ratio: 16/10;
    overflow: hidden;
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.news-card:hover .news-image img {
    transform: scale(1.05);
}

.video-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
}

.news-content {
    padding: 1.5rem;
}

.news-content time {
    display: inline-block;
    background: var(--primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.8rem;
    margin-bottom: 1rem;
}

.news-content h3 {
    font-size: 1.1rem;
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.news-content h3 a {
    color: var(--dark);
    text-decoration: none;
}

.news-content h3 a:hover {
    color: var(--primary);
}

.news-content p {
    color: var(--gray-600);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.read-more {
    color: var(--primary);
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
}

.read-more:hover {
    text-decoration: underline;
}

@media (max-width: 1024px) {
    .news-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .news-grid {
        grid-template-columns: 1fr;
    }
    
    .news-hero h1 {
        font-size: 2rem;
    }
}
</style>
