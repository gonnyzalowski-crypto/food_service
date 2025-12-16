<?php
// expects $product (array or null)
?>
<h1>Produktdetails</h1>
<div class="grid-2">
  <section class="product-images">
    <img src="<?= htmlspecialchars($product['image_url'] ?? '/assets/product-placeholder.svg') ?>"
         alt="Produktbild">
  </section>
  <section>
    <h2><?= htmlspecialchars($product['name'] ?? 'Unbekanntes Produkt') ?></h2>
    <p><strong>SKU:</strong> <?= htmlspecialchars($product['sku'] ?? '') ?></p>
    <p><?= nl2br(htmlspecialchars($product['short_desc'] ?? '')) ?></p>

    <p><strong>Preis:</strong>
      <?= isset($product['unit_price']) ? number_format($product['unit_price'], 2, ',', '.') . ' EUR' : 'auf Anfrage' ?>
    </p>

    <form id="add-to-cart" method="post" action="/api/cart">
      <div class="form-group">
        <label for="qty">Menge</label>
        <input type="number" id="qty" name="qty" value="1" min="1">
      </div>
      <input type="hidden" name="sku" value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
      <button class="btn-primary" type="submit">In den Warenkorb</button>
    </form>

    <?php if (!empty($product['datasheet_url'])): ?>
      <p style="margin-top:12px;">
        <a href="<?= htmlspecialchars($product['datasheet_url']) ?>" target="_blank">Datenblatt herunterladen</a>
      </p>
    <?php endif; ?>
  </section>
</div>

<script>
document.getElementById('add-to-cart').addEventListener('submit', async function (e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const payload = {
    sku: formData.get('sku'),
    qty: parseInt(formData.get('qty') || '1', 10)
  };
  const res = await fetch('/api/cart', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(payload)
  });
  if (res.ok) {
    alert('Zum Warenkorb hinzugefügt');
  } else {
    alert('Fehler beim Hinzufügen zum Warenkorb');
  }
});
</script>
