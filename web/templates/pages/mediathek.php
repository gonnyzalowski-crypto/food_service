<?php
$pageTitle = $lang === 'de' ? 'Mediathek' : 'Media Library';

// Videos from Streicher website
$projectVideos = [
    [
        'title_de' => 'HDD-E Serie - Vollelektrische Horizontalbohranlagen',
        'title_en' => 'HDD-E Series - Fully electric driven horizontal drilling rigs',
        'vimeo_id' => '808681054',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'Das TENP III Projekt',
        'title_en' => 'TENP III Project',
        'vimeo_id' => '1056715750',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'Vacuum Crawler VC70',
        'title_en' => 'Vacuum Crawler VC70',
        'vimeo_id' => '761816765',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'HDD-E Serie - Vollelektrische Horizontalbohranlagen (EN)',
        'title_en' => 'HDD-E Series - Fully electric driven horizontal drilling rigs',
        'vimeo_id' => '808688561',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'PW150-E – Elektrischer Schweißtraktor',
        'title_en' => 'PW150-E - electric welding tractor',
        'vimeo_id' => '694410593',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'HDD80-E vollelektrische Horizontalbohranlage',
        'title_en' => 'HDD80-E fully electric driven horizontal drilling rig',
        'vimeo_id' => '714362863',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'Zeelink - Der Film',
        'title_en' => 'Zeelink the movie',
        'vimeo_id' => '656979304',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'HDD-Bohrungen für Erdkabelverlegung',
        'title_en' => 'HDD drillings for constructing an underground power line',
        'vimeo_id' => '656980241',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'Early Production Facility Viura - Union Fenosa Gas',
        'title_en' => 'Early Production Facility Viura - Union Fenosa Gas',
        'vimeo_id' => '378787556',
        'youtube_id' => null,
    ],
];

$imageVideos = [
    [
        'title_de' => 'STREICHER Imagevideo (Englische Version)',
        'title_en' => 'STREICHER Image Video (English Version)',
        'vimeo_id' => '378796657',
        'youtube_id' => 'v0-RQ7pYEyg',
    ],
    [
        'title_de' => 'VTA Imagevideo',
        'title_en' => 'VTA Image Video',
        'vimeo_id' => '377541638',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'STREICHER Maschinenbau',
        'title_en' => 'STREICHER Mechanical Engineering',
        'vimeo_id' => '378309699',
        'youtube_id' => null,
    ],
    [
        'title_de' => 'ZIERER - Mehr als ein Gefühl (Englische Version)',
        'title_en' => 'ZIERER - More than a feeling (English version)',
        'vimeo_id' => '379239108',
        'youtube_id' => null,
    ],
];

// Gallery images
$galleryImages = [
    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800',
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800',
    'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=800',
    'https://images.unsplash.com/photo-1518709766631-a6a7f45921c3?w=800',
    'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=800',
    'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800',
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

<section class="mediathek-hero" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://www.streichergmbh.com/fileadmin/_processed_/8/6/csm_STREICHER-Deggendorf_Drohne_DJI_0003_6b7c9f7c8a.jpg') center/cover;">
    <div class="container">
        <h1><?= $pageTitle ?></h1>
    </div>
</section>

<section class="videos-section">
    <div class="container">
        <h2 class="section-title"><?= $lang === 'de' ? 'Projekte und Anlagen' : 'Projects and plants' ?></h2>
        
        <div class="videos-grid">
            <?php foreach ($projectVideos as $video): ?>
            <div class="video-card">
                <div class="video-wrapper">
                    <iframe 
                        src="https://player.vimeo.com/video/<?= $video['vimeo_id'] ?>?title=0&byline=0&portrait=0" 
                        frameborder="0" 
                        allow="autoplay; fullscreen; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
                <h3><?= $lang === 'de' ? $video['title_de'] : $video['title_en'] ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="videos-section image-videos">
    <div class="container">
        <h2 class="section-title"><?= $lang === 'de' ? 'Imagevideos' : 'Image videos' ?></h2>
        
        <div class="videos-grid">
            <?php foreach ($imageVideos as $video): ?>
            <div class="video-card">
                <div class="video-wrapper">
                    <?php if ($video['youtube_id']): ?>
                    <iframe 
                        src="https://www.youtube-nocookie.com/embed/<?= $video['youtube_id'] ?>?rel=0" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                    <?php else: ?>
                    <iframe 
                        src="https://player.vimeo.com/video/<?= $video['vimeo_id'] ?>?title=0&byline=0&portrait=0" 
                        frameborder="0" 
                        allow="autoplay; fullscreen; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                    <?php endif; ?>
                </div>
                <h3><?= $lang === 'de' ? $video['title_de'] : $video['title_en'] ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="gallery-section">
    <div class="container">
        <h2 class="section-title"><?= $lang === 'de' ? 'Bildergalerie' : 'Photo Gallery' ?></h2>
        
        <div class="gallery-grid">
            <?php foreach ($galleryImages as $index => $image): ?>
            <a href="<?= $image ?>" class="gallery-item" data-lightbox="gallery">
                <img src="<?= $image ?>" alt="STREICHER Gallery <?= $index + 1 ?>">
                <div class="gallery-overlay">
                    <span>🔍</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.mediathek-hero {
    min-height: 300px;
    display: flex;
    align-items: center;
    color: white;
}

.mediathek-hero h1 {
    font-size: 3rem;
    margin: 0;
}

.videos-section {
    padding: 4rem 0;
}

.videos-section.image-videos {
    background: var(--gray-50);
}

.section-title {
    color: var(--primary);
    font-size: 1.75rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--primary);
}

.videos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.video-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.video-wrapper {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
}

.video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.video-card h3 {
    padding: 1rem;
    font-size: 0.95rem;
    margin: 0;
    background: var(--gray-100);
}

.gallery-section {
    padding: 4rem 0;
    background: var(--dark);
}

.gallery-section .section-title {
    color: white;
    border-bottom-color: var(--primary);
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.gallery-item {
    position: relative;
    aspect-ratio: 16/10;
    overflow: hidden;
    border-radius: 8px;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.gallery-item:hover img {
    transform: scale(1.1);
}

.gallery-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.gallery-overlay span {
    font-size: 2rem;
}

@media (max-width: 1024px) {
    .videos-grid,
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .videos-grid,
    .gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .mediathek-hero h1 {
        font-size: 2rem;
    }
}
</style>
