<?php
$c = $contractor ?? null;
$isEdit = !empty($c);
?>

<div class="admin-header">
  <div>
    <a href="/admin/contractors" style="color: #64748b; text-decoration: none;">← Back to Contractors</a>
    <h1 style="margin: 8px 0 0 0;"><?= $isEdit ? 'Edit Contractor' : 'New Contractor' ?></h1>
  </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error mb-4">
  <div class="alert-title">Error</div>
  <p style="margin: 4px 0 0 0;"><?= htmlspecialchars($error) ?></p>
</div>
<?php endif; ?>

<form action="<?= $isEdit ? '/admin/contractors/' . (int)$c['id'] . '/edit' : '/admin/contractors/new' ?>" method="POST">
  <?= csrf_field() ?>
  
  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;" class="product-form-grid">
    <div>
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Contractor Information</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Company Name *</label>
            <input type="text" name="company_name" class="form-control" required value="<?= htmlspecialchars($c['company_name'] ?? '') ?>" placeholder="e.g., Offshore Drilling Co.">
          </div>
          
          <div class="form-group">
            <label class="form-label">Contact Full Name *</label>
            <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($c['full_name'] ?? '') ?>" placeholder="e.g., John Smith">
          </div>
          
          <?php if ($isEdit): ?>
          <div class="form-group">
            <label class="form-label">Contractor Code</label>
            <input type="text" name="contractor_code" class="form-control" required value="<?= htmlspecialchars($c['contractor_code'] ?? '') ?>" style="font-family: monospace; text-transform: uppercase;">
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">This code is used by the contractor to access the Supply Portal.</p>
          </div>
          <?php else: ?>
          <div class="alert alert-info" style="margin: 0;">
            <div class="alert-title">Contractor Code</div>
            <p style="margin: 4px 0 0 0;">A unique contractor code will be automatically generated when you create this contractor.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <div>
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Discount Settings</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Discount Percent (%)</label>
            <input type="number" name="discount_percent" class="form-control" step="0.01" min="0" max="100" value="<?= htmlspecialchars($c['discount_percent'] ?? '35') ?>">
          </div>
          
          <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
              <input type="checkbox" name="discount_eligible" value="1" <?= (!$isEdit || !empty($c['discount_eligible'])) ? 'checked' : '' ?>>
              <span>Discount Eligible</span>
            </label>
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">If unchecked, contractor pays full price.</p>
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Status</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
              <input type="checkbox" name="active" value="1" <?= (!$isEdit || !empty($c['active'])) ? 'checked' : '' ?>>
              <span>Active</span>
            </label>
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">Inactive contractors cannot access the Supply Portal.</p>
          </div>
        </div>
      </div>
      
      <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
        <?= $isEdit ? 'Update Contractor' : 'Create Contractor' ?>
      </button>
    </div>
  </div>
</form>
