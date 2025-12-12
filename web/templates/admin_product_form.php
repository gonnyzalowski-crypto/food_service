<?php
// expects $product (array|null)
$isEdit = !empty($product);
?>
<h1><?= $isEdit ? 'Produkt bearbeiten' : 'Neues Produkt anlegen' ?></h1>

<form method="post" action="<?= $isEdit ? '/admin/products/' . (int)$product['id'] : '/admin/products/new' ?>">
  <div class="form-group">
    <label for="sku">SKU</label>
    <input type="text" id="sku" name="sku" required value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="short_desc">Kurzbeschreibung</label>
    <textarea id="short_desc" name="short_desc" rows="3"><?= htmlspecialchars($product['short_desc'] ?? '') ?></textarea>
  </div>

  <div class="form-group">
    <label for="unit_price">Basispreis (EUR)</label>
    <input type="number" step="0.01" id="unit_price" name="unit_price"
           value="<?= htmlspecialchars($product['unit_price'] ?? '') ?>">
  </div>

  <div class="form-group">
    <label for="moq">Mindestbestellmenge (MOQ)</label>
    <input type="number" id="moq" name="moq" value="<?= htmlspecialchars($product['moq'] ?? '1') ?>">
  </div>

  <button class="btn-primary" type="submit">Speichern</button>
</form>
