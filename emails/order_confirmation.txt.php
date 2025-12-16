<?php
// variables: $order_number, $contact_name, $items, $subtotal, $vat_amount, $total
?>
Subject: Bestellbestätigung <?= htmlspecialchars($order_number) ?>

Hallo <?= htmlspecialchars($contact_name) ?>,

vielen Dank für Ihre Bestellung bei der Gordon Food Service.

Bestellnummer: <?= htmlspecialchars($order_number) ?>

Bestellübersicht:
<?php foreach ($items as $item): ?>
- <?= htmlspecialchars($item['sku']) ?> x <?= (int)$item['qty'] ?> — <?= htmlspecialchars($item['name']) ?>
<?php endforeach; ?>

Zwischensumme: <?= number_format($subtotal, 2, ',', '.') ?> EUR
MwSt.: <?= number_format($vat_amount, 2, ',', '.') ?> EUR
Gesamt: <?= number_format($total, 2, ',', '.') ?> EUR

Sie können den Status Ihrer Lieferung verfolgen, sobald eine Sendungsnummer vorliegt.

Mit freundlichen Grüßen
Gordon Food Service
