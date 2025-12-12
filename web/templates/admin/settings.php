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
          <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($s['company_name'] ?? 'Streicher GmbH') ?>">
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
          <input type="text" name="account_holder" class="form-control" value="<?= htmlspecialchars($s['account_holder'] ?? 'Streicher GmbH') ?>">
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
          <input type="email" name="support_email" class="form-control" value="<?= htmlspecialchars($s['support_email'] ?? 'support@streichergmbh.com') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Support Phone</label>
          <input type="tel" name="support_phone" class="form-control" value="<?= htmlspecialchars($s['support_phone'] ?? '+49 991 330-00') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Sales Email</label>
          <input type="email" name="sales_email" class="form-control" value="<?= htmlspecialchars($s['sales_email'] ?? 'store@streichergmbh.com') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Shipping Email</label>
          <input type="email" name="shipping_email" class="form-control" value="<?= htmlspecialchars($s['shipping_email'] ?? 'shipping@streichergmbh.com') ?>">
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
            <td>admin@streichergmbh.com</td>
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
