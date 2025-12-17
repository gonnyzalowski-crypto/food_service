<?php $s = $settings ?? []; ?>
<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">⚙️ Settings</h1>
    <p class="admin-page-subtitle">Configure your store settings</p>
  </div>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success mb-4">
  <div class="alert-title">✅ Settings Saved</div>
  <p style="margin: 4px 0 0 0;">Your settings have been updated successfully.</p>
</div>
<?php endif; ?>

<form action="/admin/settings" method="POST">
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Company Information -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">🏢 Company Information</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Company Name</label>
          <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($s['company_name'] ?? 'Gordon Food Service GmbH') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">VAT ID</label>
          <input type="text" name="vat_id" class="form-control" value="<?= htmlspecialchars($s['vat_id'] ?? 'DE123456789') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($s['address'] ?? "Industriestraße 45\n93055 Regensburg\nGermany") ?></textarea>
        </div>
      </div>
    </div>
    
    <!-- Bank Details -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">🏦 Bank Details</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Bank Name</label>
          <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($s['bank_name'] ?? 'Deutsche Bank AG') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Account Holder</label>
          <input type="text" name="account_holder" class="form-control" value="<?= htmlspecialchars($s['account_holder'] ?? 'Gordon Food Service GmbH') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">IBAN</label>
          <input type="text" name="iban" class="form-control" value="<?= htmlspecialchars($s['iban'] ?? 'DE89 3704 0044 0532 0130 00') ?>" style="font-family: monospace;">
        </div>
        <div class="form-group">
          <label class="form-label">BIC/SWIFT</label>
          <input type="text" name="bic" class="form-control" value="<?= htmlspecialchars($s['bic'] ?? 'COBADEFFXXX') ?>" style="font-family: monospace;">
        </div>
      </div>
    </div>
    
    <!-- Contact Information -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">📞 Contact Information</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Support Email</label>
          <input type="email" name="support_email" class="form-control" value="<?= htmlspecialchars($s['support_email'] ?? 'contact@gorfos.com') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Support Phone</label>
          <input type="tel" name="support_phone" class="form-control" value="<?= htmlspecialchars($s['support_phone'] ?? '+49 991 330-00') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Sales Email</label>
          <input type="email" name="sales_email" class="form-control" value="<?= htmlspecialchars($s['sales_email'] ?? 'contact@gorfos.com') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Shipping Email</label>
          <input type="email" name="shipping_email" class="form-control" value="<?= htmlspecialchars($s['shipping_email'] ?? 'contact@gorfos.com') ?>">
        </div>
      </div>
    </div>
    
    <!-- Tax & Shipping -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">💰 Tax & Shipping</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">VAT Rate (%)</label>
          <input type="number" name="vat_rate" class="form-control" value="<?= htmlspecialchars($s['vat_rate'] ?? '19') ?>" min="0" max="100" step="0.1">
        </div>
        <div class="form-group">
          <label class="form-label">Default Currency</label>
          <select name="currency" class="form-control">
            <?php $curr = $s['currency'] ?? 'USD'; ?>
            <option value="USD" <?= $curr === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
            <option value="EUR" <?= $curr === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
            <option value="GBP" <?= $curr === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
            <option value="CHF" <?= $curr === 'CHF' ? 'selected' : '' ?>>CHF - Swiss Franc</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Free Shipping Threshold</label>
          <input type="number" name="free_shipping_threshold" class="form-control" value="<?= htmlspecialchars($s['free_shipping_threshold'] ?? '5000') ?>" min="0" step="100">
          <small style="color: #64748b;">Orders above this amount get free shipping</small>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Notification Settings -->
  <div class="card mt-4">
    <div class="card-header">
      <h3 class="card-title">🔔 Notification Settings</h3>
    </div>
    <div class="card-body">
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
          <input type="checkbox" name="notify_new_order" <?= ($s['notify_new_order'] ?? '1') === '1' ? 'checked' : '' ?> style="width: 20px; height: 20px;">
          <div>
            <div style="font-weight: 500;">New Order Notifications</div>
            <div style="font-size: 0.85rem; color: #64748b;">Email when new orders are placed</div>
          </div>
        </label>
        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
          <input type="checkbox" name="notify_payment" <?= ($s['notify_payment'] ?? '1') === '1' ? 'checked' : '' ?> style="width: 20px; height: 20px;">
          <div>
            <div style="font-weight: 500;">Payment Notifications</div>
            <div style="font-size: 0.85rem; color: #64748b;">Email when payments are uploaded</div>
          </div>
        </label>
        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
          <input type="checkbox" name="notify_low_stock" <?= ($s['notify_low_stock'] ?? '1') === '1' ? 'checked' : '' ?> style="width: 20px; height: 20px;">
          <div>
            <div style="font-weight: 500;">Low Stock Alerts</div>
            <div style="font-size: 0.85rem; color: #64748b;">Email when products are low in stock</div>
          </div>
        </label>
      </div>
    </div>
  </div>
  
  <!-- Supply Pricing -->
  <div class="card mt-4">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h3 class="card-title">📦 Supply Item Pricing (per kg)</h3>
      <button type="button" class="btn btn-sm btn-primary" onclick="openAddSupplyItemModal()">+ Add Item</button>
    </div>
    <div class="card-body" style="padding: 0;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Item Name</th>
            <th>Item Key</th>
            <th>Price per kg (USD)</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $supplyPrices = $supplyPrices ?? [];
          if (empty($supplyPrices)): 
          ?>
          <tr>
            <td colspan="4" style="text-align: center; color: rgba(255,255,255,0.5); padding: 24px;">
              No supply items configured. Add items to set pricing.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($supplyPrices as $key => $item): ?>
          <tr>
            <td style="font-weight: 500;"><?= htmlspecialchars($item['name'] ?? ucwords(str_replace('_', ' ', $key))) ?></td>
            <td><code style="background: rgba(255,255,255,0.1); padding: 2px 8px; border-radius: 4px;"><?= htmlspecialchars($key) ?></code></td>
            <td>
              <div style="display: flex; align-items: center; gap: 8px;">
                <span style="color: #00bfff; font-weight: 600;">$<?= number_format((float)($item['price'] ?? 0), 2) ?></span>
              </div>
            </td>
            <td>
              <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-sm btn-outline" onclick="openEditSupplyItemModal('<?= htmlspecialchars($key) ?>', '<?= htmlspecialchars($item['name'] ?? '') ?>', '<?= (float)($item['price'] ?? 0) ?>')">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteSupplyItem('<?= htmlspecialchars($key) ?>')">Delete</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Admin Users -->
  <div class="card mt-4">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
      <h3 class="card-title">👤 Admin Users</h3>
      <button type="button" class="btn btn-sm btn-primary" onclick="alert('Add user functionality coming soon')">+ Add User</button>
    </div>
    <div class="card-body" style="padding: 0;">
      <table class="data-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Email</th>
            <th>Role</th>
            <th>Last Login</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">A</div>
                <span style="font-weight: 500;">Admin User</span>
              </div>
            </td>
            <td>admin@Gordon Food Servicegmbh.com</td>
            <td><span style="background: #dbeafe; color: #1d4ed8; padding: 4px 12px; border-radius: 12px;">Super Admin</span></td>
            <td><?= date('M j, Y g:i A') ?></td>
            <td>
              <button type="button" class="btn btn-sm btn-outline">Edit</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  
  <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
    <button type="reset" class="btn btn-outline">Reset Changes</button>
    <button type="submit" class="btn btn-primary btn-lg">💾 Save Settings</button>
  </div>
</form>

<!-- Supply Item Modal -->
<div id="supplyItemModal" class="modal" style="display: none;">
  <div class="modal-backdrop" onclick="closeSupplyItemModal()" style="background: rgba(0,0,0,0.8);"></div>
  <div class="modal-content" style="max-width: 500px; background: #1a1a1a; border: 1px solid #333; border-radius: 12px;">
    <div class="modal-header" style="border-bottom: 1px solid #333; padding: 16px 20px;">
      <h3 id="supplyItemModalTitle" style="color: #00bfff; margin: 0; font-size: 1.25rem;">Add Supply Item</h3>
      <button type="button" class="modal-close" onclick="closeSupplyItemModal()" style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; font-size: 1.2rem; cursor: pointer;">&times;</button>
    </div>
    <form id="supplyItemForm" action="/admin/settings/supply-item" method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="supplyItemAction" value="add">
      <input type="hidden" name="original_key" id="supplyItemOriginalKey" value="">
      <div class="modal-body" style="padding: 20px; background: #1a1a1a;">
        <div class="form-group" style="margin-bottom: 16px;">
          <label class="form-label" style="display: block; color: #ffffff; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Item Name</label>
          <input type="text" name="item_name" id="supplyItemName" class="form-control" placeholder="e.g., Water, Dry Food, Canned Food" required style="width: 100%; padding: 12px; background: #2a2a2a; border: 1px solid #444; border-radius: 8px; color: #fff; font-size: 1rem;">
        </div>
        <div class="form-group" style="margin-bottom: 16px;">
          <label class="form-label" style="display: block; color: #ffffff; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Item Key (lowercase, underscores)</label>
          <input type="text" name="item_key" id="supplyItemKey" class="form-control" placeholder="e.g., water, dry_food, canned_food" pattern="[a-z_]+" required style="width: 100%; padding: 12px; background: #2a2a2a; border: 1px solid #444; border-radius: 8px; color: #fff; font-size: 1rem;">
          <small style="color: rgba(255,255,255,0.5); display: block; margin-top: 6px;">Used internally. Use lowercase letters and underscores only.</small>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
          <label class="form-label" style="display: block; color: #ffffff; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Price per kg (USD)</label>
          <input type="number" name="item_price" id="supplyItemPrice" class="form-control" step="0.01" min="0" placeholder="e.g., 3.50" required style="width: 100%; padding: 12px; background: #2a2a2a; border: 1px solid #444; border-radius: 8px; color: #fff; font-size: 1rem;">
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid #333; padding: 16px 20px; display: flex; justify-content: flex-end; gap: 12px; background: #1a1a1a; border-radius: 0 0 12px 12px;">
        <button type="button" class="btn btn-outline" onclick="closeSupplyItemModal()" style="padding: 10px 20px; background: transparent; border: 1px solid #555; color: #fff; border-radius: 8px; cursor: pointer;">Cancel</button>
        <button type="submit" class="btn btn-primary" style="padding: 10px 20px; background: #00bfff; border: none; color: #000; border-radius: 8px; font-weight: 600; cursor: pointer;">Save Item</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Supply Item Form (hidden) -->
<form id="deleteSupplyItemForm" action="/admin/settings/supply-item" method="POST" style="display: none;">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="item_key" id="deleteSupplyItemKey" value="">
</form>

<script>
function openAddSupplyItemModal() {
  document.getElementById('supplyItemModalTitle').textContent = 'Add Supply Item';
  document.getElementById('supplyItemAction').value = 'add';
  document.getElementById('supplyItemOriginalKey').value = '';
  document.getElementById('supplyItemName').value = '';
  document.getElementById('supplyItemKey').value = '';
  document.getElementById('supplyItemPrice').value = '';
  document.getElementById('supplyItemKey').readOnly = false;
  document.getElementById('supplyItemModal').style.display = 'flex';
}

function openEditSupplyItemModal(key, name, price) {
  document.getElementById('supplyItemModalTitle').textContent = 'Edit Supply Item';
  document.getElementById('supplyItemAction').value = 'edit';
  document.getElementById('supplyItemOriginalKey').value = key;
  document.getElementById('supplyItemName').value = name || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  document.getElementById('supplyItemKey').value = key;
  document.getElementById('supplyItemPrice').value = price;
  document.getElementById('supplyItemKey').readOnly = true;
  document.getElementById('supplyItemModal').style.display = 'flex';
}

function closeSupplyItemModal() {
  document.getElementById('supplyItemModal').style.display = 'none';
}

function deleteSupplyItem(key) {
  if (confirm('Are you sure you want to delete this supply item? This cannot be undone.')) {
    document.getElementById('deleteSupplyItemKey').value = key;
    document.getElementById('deleteSupplyItemForm').submit();
  }
}

// Auto-generate key from name
document.getElementById('supplyItemName')?.addEventListener('input', function() {
  if (document.getElementById('supplyItemAction').value === 'add') {
    const key = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    document.getElementById('supplyItemKey').value = key;
  }
});
</script>
