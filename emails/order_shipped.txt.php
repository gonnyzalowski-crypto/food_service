<?php
// variables: $order_number, $tracking_number, $contact_name, $carrier, $items, $track_domain
?>
Subject: Ihre Bestellung <?= htmlspecialchars($order_number) ?> wurde versendet (Tracking: <?= htmlspecialchars($tracking_number) ?>)

Hallo <?= htmlspecialchars($contact_name) ?>,

Ihre Bestellung <?= htmlspecialchars($order_number) ?> wurde versendet via <?= htmlspecialchars($carrier) ?>.

Sendungsnummer: <?= htmlspecialchars($tracking_number) ?>

Sie können Ihre Sendung hier verfolgen:
<?= rtrim($track_domain, '/') ?>/track?tracking=<?= urlencode($tracking_number) ?>


Positionen:
<?php foreach ($items as $item): ?>
- <?= htmlspecialchars($item['sku']) ?> x <?= (int)$item['qty'] ?> — <?= htmlspecialchars($item['name']) ?>
<?php endforeach; ?>

Bei Rückfragen stehen wir Ihnen gerne zur Verfügung.

Mit freundlichen Grüßen
Streicher GmbH
