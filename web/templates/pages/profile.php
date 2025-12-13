<?php
$pageTitle = $lang === 'de' ? 'Unternehmensprofil' : 'Company Profile';
$pageSubtitle = $lang === 'de' ? 'Langjährige Erfahrung, technische Kompetenz und ein breites Leistungsspektrum' : 'Long-time Experience, Technical Competence and a Wide Scope of Services';
?>

<div class="page-header profile-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="/"><?= $lang === 'de' ? 'Startseite' : 'Home' ?></a>
            <span>/</span>
            <span><?= $pageTitle ?></span>
        </nav>
    </div>
</div>

<section class="profile-hero" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('/images/photos/mechanical3.jpg') center/cover;">
    <div class="container">
        <div class="profile-hero-content">
            <h2 class="section-label"><?= $lang === 'de' ? 'Unternehmensübersicht' : 'Company Overview' ?></h2>
            <h1><?= $pageSubtitle ?></h1>
        </div>
    </div>
</section>

<section class="profile-sectors">
    <div class="container">
        <div class="sectors-gallery">
            <a href="/business-sectors/pipelines-plants" class="sector-thumb">
                <img src="/images/photos/pipeline1.jpg" alt="Pipelines & Plants">
                <span><?= $lang === 'de' ? 'Pipelines & Anlagen' : 'Pipelines & Plants' ?></span>
            </a>
            <a href="/business-sectors/mechanical-engineering" class="sector-thumb">
                <img src="/images/photos/mechanical1.jpg" alt="Mechanical Engineering">
                <span><?= $lang === 'de' ? 'Maschinenbau' : 'Mechanical Engineering' ?></span>
            </a>
            <a href="/business-sectors/electrical-engineering" class="sector-thumb">
                <img src="/images/photos/electrical.jpg" alt="Electrical Engineering">
                <span><?= $lang === 'de' ? 'Elektrotechnik' : 'Electrical Engineering' ?></span>
            </a>
            <a href="/business-sectors/civil-engineering" class="sector-thumb">
                <img src="/images/photos/civil.jpg" alt="Civil Engineering">
                <span><?= $lang === 'de' ? 'Hoch- & Tiefbau' : 'Civil & Structural Engineering' ?></span>
            </a>
            <a href="/business-sectors/raw-materials" class="sector-thumb">
                <img src="/images/photos/raw-materials.jpg" alt="Raw Materials">
                <span><?= $lang === 'de' ? 'Roh- & Baustoffe' : 'Raw & Construction Material' ?></span>
            </a>
        </div>
    </div>
</section>

<section class="profile-content">
    <div class="container">
        <div class="profile-grid">
            <div class="profile-text">
                <?php if ($lang === 'de'): ?>
                <p><strong>Durch die Geschäftsbereiche <a href="/business-sectors/pipelines-plants">Pipelines und Anlagen</a>, <a href="/business-sectors/mechanical-engineering">Maschinenbau</a>, <a href="/business-sectors/electrical-engineering">Elektrotechnik</a>, <a href="/business-sectors/civil-engineering">Hoch- und Tiefbau</a> sowie <a href="/business-sectors/raw-materials">Roh- und Baustoffe</a> ist die MAX STREICHER GmbH & Co. KG aA mit Hauptsitz in Deggendorf sehr breit aufgestellt.</strong> Bei der Gründung des Unternehmens im Jahr 1909 konzentrierte sich STREICHER fast ausschließlich auf den Straßenbau. Heute ist STREICHER ein international tätiges Unternehmen, das mehr als 4.500 Mitarbeiter an ca. 30 Standorten beschäftigt.</p>
                
                <p>In den 1990er Jahren expandierte STREICHER erstmals über die bayerische Region hinaus - seitdem stellen nationale und internationale Projekte das Unternehmen vor neue Herausforderungen. Neben dem hervorragenden technischen Know-how sind eine solide Eigenkapitalstruktur und ein organisches Wachstum der STREICHER-Gruppe weitere Eckpfeiler der Unternehmensentwicklung.</p>
                
                <p>Ein steigender Energiebedarf, insbesondere an Erdgas, führte dazu, dass STREICHER in den 1970er Jahren das Leistungsspektrum auf den Geschäftsbereich <strong>Pipelines und Anlagen</strong> ausweitete. Der Beweis für diese Entscheidung zeigt sich im Erfolg nationaler und internationaler Projekte, z.B. dem Ausbau der deutschen Gasversorgungsinfrastruktur oder den Anlagen zur Energiespeicherung und Energieumwandlung.</p>
                
                <p>Ein weiteres wichtiges Element des Unternehmens ist der Geschäftsbereich <strong>Hoch- und Tiefbau</strong>. Bei komplexen Großprojekten im Straßenbau, Brückenbau oder Wasserbau sowie bei zeitkritischen Baustellen, z.B. der Sanierung von Autobahnen in einem engen Zeitrahmen, beweist STREICHER seine fachliche und organisatorische Kompetenz.</p>
                
                <p>Auch im Geschäftsbereich <strong>Maschinenbau</strong> verfügt das Unternehmen über große Expertise. Neben dem klassischen Apparatebau und dem in den letzten Jahren ausgebauten Bereich Bohrtechnik ist STREICHER auch in der Herstellung von Fahrgeschäften stark positioniert. Darüber hinaus realisiert STREICHER Modelliermaschinen und ist in der Verfahrenstechnik tätig.</p>
                
                <p>Der Geschäftsbereich <strong>Elektrotechnik</strong> vereint eine Vielzahl von Dienstleistungen - von der Elektroinstallation, Energietechnik, Mess- und Regeltechnik und Automatisierungstechnik bis hin zur funktionalen Sicherheit, Elektronikwerkstatt und Simulationen. In ihrer Funktion unterstützen diese Bereiche interdisziplinär interne und externe Projekte unterschiedlicher Art und Größenordnung.</p>
                
                <p>Eigene Steinbrüche und Kieswerke sowie Asphaltmischanlagen an verschiedenen Standorten in Deutschland liefern hochwertige Materialien für Projekte der Unternehmensgruppe, aber auch für externe Kunden. Diese Aktivitäten ergänzen das Leistungsspektrum im Bereich <strong>Roh- und Baustoffe</strong>.</p>
                <?php else: ?>
                <p><strong>Due to the business sectors <a href="/business-sectors/pipelines-plants">Pipelines and Plants</a>, <a href="/business-sectors/mechanical-engineering">Mechanical Engineering</a>, <a href="/business-sectors/electrical-engineering">Electrical Engineering</a>, <a href="/business-sectors/civil-engineering">Civil and Structural Engineering</a> as well as <a href="/business-sectors/raw-materials">Raw and Construction Material</a>, MAX STREICHER GmbH & Co. KG aA with its headquarters in Deggendorf is quite diversified.</strong> When the company was established in 1909, STREICHER focused exclusively almost on road construction. Now, STREICHER is an internationally operating company, which employs more than 4,500 people in approx. 30 locations.</p>
                
                <p>In the 1990s STREICHER first expanded beyond the Bavarian region - since then, national and international projects place new challenges to the company. Besides the excellent technical know-how, further cornerstones of the company development are a solid equity structure and an organic growth of the STREICHER Group.</p>
                
                <p>An increasing demand in energy, especially in natural gas, led STREICHER to extend the scope of services to the business sector <strong>Pipelines and Plants</strong> in the 1970s. The proof for this decision is evident in the success of national and international projects, e.g. the expansion of the German gas supply infrastructure or the plants for energy storage and energy conversion.</p>
                
                <p>Another important element of the company is the business sector <strong>Civil and Structural Engineering</strong>. In case of complex major projects in road construction, bridge construction or hydraulic engineering as well as in case of time-sensitive construction sites, e.g. the rehabilitation of motorways within a narrow time frame, STREICHER proves its professional and organisational competence.</p>
                
                <p>Also in the business sector <strong>Mechanical Engineering</strong> the company has great expertise. Besides the classical apparatus engineering and the sector drilling technology, which has expanded during recent years, STREICHER also has a strong position in the manufacturing of amusement rides. Moreover, STREICHER realises modeling machines and is active in process engineering.</p>
                
                <p>The business sector <strong>Electrical Engineering</strong> combines a variety of services - from electrical installation, energy technology, electrical measurement and control and automation technology to functional safety, electronics workshop and simulations. In their function these sections provide interdisciplinary support for internal and external projects of different type and scale.</p>
                
                <p>Own quarries and gravel mills as well as asphalt mixing plants at diverse locations in Germany supply materials of high quality for projects of the company group but also for external clients. These activities complement the scope of services in the sector <strong>Raw and Construction Material</strong>.</p>
                <?php endif; ?>
            </div>
            
            <div class="profile-sidebar">
                <div class="company-card">
                    <img src="https://www.streichergmbh.com/fileadmin/theme/templates/img/logo_streicher.svg" alt="MAX STREICHER" class="company-logo">
                    <h3>MAX STREICHER GmbH & Co. Kommanditgesellschaft auf Aktien</h3>
                    <address>
                        Schwaigerbreite 17<br>
                        94469 Deggendorf<br>
                        Germany
                    </address>
                    <p>
                        <a href="https://www.streichergmbh.com">www.streichergmbh.com</a><br>
                        <a href="mailto:info@streichergmbh.com">info@streichergmbh.com</a>
                    </p>
                    <a href="https://goo.gl/maps/v2uwGuFPi4v" class="btn btn-outline" target="_blank">
                        <?= $lang === 'de' ? 'So finden Sie uns' : 'How to find us' ?> »
                    </a>
                </div>
                
                <div class="sectors-list">
                    <h3><?= $lang === 'de' ? 'Geschäftsbereiche' : 'Business Sectors' ?></h3>
                    <ul>
                        <li><a href="/business-sectors/pipelines-plants"><?= $lang === 'de' ? 'Pipelines & Anlagen' : 'Pipelines & Plants' ?></a></li>
                        <li><a href="/business-sectors/mechanical-engineering"><?= $lang === 'de' ? 'Maschinenbau' : 'Mechanical Engineering' ?></a></li>
                        <li><a href="/business-sectors/electrical-engineering"><?= $lang === 'de' ? 'Elektrotechnik' : 'Electrical Engineering' ?></a></li>
                        <li><a href="/business-sectors/civil-engineering"><?= $lang === 'de' ? 'Hoch- & Tiefbau' : 'Civil & Structural Engineering' ?></a></li>
                        <li><a href="/business-sectors/raw-materials"><?= $lang === 'de' ? 'Roh- & Baustoffe' : 'Raw & Construction Material' ?></a></li>
                        <li><a href="/catalog?category=engineering-software">💻 Engineering Software</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="profile-stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number">1909</span>
                <span class="stat-label"><?= $lang === 'de' ? 'Gegründet' : 'Founded' ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number">4,500+</span>
                <span class="stat-label"><?= $lang === 'de' ? 'Mitarbeiter' : 'Employees' ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number">30+</span>
                <span class="stat-label"><?= $lang === 'de' ? 'Standorte' : 'Locations' ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number">6</span>
                <span class="stat-label"><?= $lang === 'de' ? 'Geschäftsbereiche' : 'Business Sectors' ?></span>
            </div>
        </div>
    </div>
</section>

<style>
.profile-header {
    background: var(--gray-100);
    padding: 1rem 0;
}

.profile-hero {
    min-height: 400px;
    display: flex;
    align-items: center;
    color: white;
    text-align: center;
}

.profile-hero h2 {
    color: var(--primary);
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 1rem;
}

.profile-hero h1 {
    font-size: 2.5rem;
    max-width: 800px;
    margin: 0 auto;
}

.profile-sectors {
    padding: 3rem 0;
    background: var(--gray-100);
}

.sectors-gallery {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
}

.sector-thumb {
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
    border-radius: 8px;
}

.sector-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.sector-thumb:hover img {
    transform: scale(1.05);
}

.sector-thumb span {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    padding: 2rem 1rem 1rem;
    font-weight: 600;
    font-size: 0.85rem;
}

.profile-content {
    padding: 4rem 0;
}

.profile-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
}

.profile-text p {
    margin-bottom: 1.5rem;
    line-height: 1.8;
}

.profile-text a {
    color: var(--primary);
    font-weight: 600;
}

.profile-sidebar {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.company-card {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
}

.company-logo {
    max-width: 150px;
    margin-bottom: 1rem;
}

.company-card h3 {
    font-size: 1rem;
    margin-bottom: 1rem;
}

.company-card address {
    font-style: normal;
    margin-bottom: 1rem;
    color: var(--gray-600);
}

.sectors-list {
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    padding: 1.5rem;
}

.sectors-list h3 {
    font-size: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary);
}

.sectors-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sectors-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--gray-100);
}

.sectors-list li:last-child {
    border-bottom: none;
}

.sectors-list a {
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sectors-list a::before {
    content: '›';
    color: var(--primary);
}

.profile-stats {
    background: var(--dark);
    color: white;
    padding: 4rem 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 3rem;
    font-weight: 700;
    color: var(--primary);
}

.stat-label {
    font-size: 1rem;
    color: var(--gray-400);
}

@media (max-width: 1024px) {
    .sectors-gallery {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .profile-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .sectors-gallery {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .profile-hero h1 {
        font-size: 1.75rem;
    }
}

@media (max-width: 480px) {
    .sectors-gallery {
        grid-template-columns: 1fr;
    }
}
</style>
