<?php
$req = $request ?? null;
$isEdit = !empty($req);
$contractors = $contractors ?? [];
$types = $isEdit ? (json_decode($req['supply_types'] ?? '[]', true) ?: []) : ['water', 'dry_food'];
$statuses = [
    'awaiting_review' => 'Awaiting Review',
    'approved_awaiting_payment' => 'Approved (Awaiting Payment)',
    'payment_submitted_processing' => 'Payment Processing',
    'transaction_completed' => 'Transaction Completed',
    'declined' => 'Declined',
];
?>

<div class="admin-header">
  <div>
    <a href="/admin/supply-requests<?= $isEdit ? '/' . (int)$req['id'] : '' ?>" style="color: #64748b; text-decoration: none;">← Back</a>
    <h1 style="margin: 8px 0 0 0;"><?= $isEdit ? 'Edit Supply Request' : 'New Supply Request' ?></h1>
    <?php if ($isEdit): ?>
    <p style="margin: 4px 0 0 0; color: #64748b;"><?= htmlspecialchars($req['request_number'] ?? '') ?></p>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error mb-4">
  <div class="alert-title">Error</div>
  <p style="margin: 4px 0 0 0;"><?= htmlspecialchars($error) ?></p>
</div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<div class="alert alert-success mb-4">
  <div class="alert-title">Success</div>
  <p style="margin: 4px 0 0 0;"><?= htmlspecialchars($success) ?></p>
</div>
<?php endif; ?>

<form action="<?= $isEdit ? '/admin/supply-requests/' . (int)$req['id'] . '/edit' : '/admin/supply-requests/new' ?>" method="POST">
  <?= csrf_field() ?>
  
  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;" class="product-form-grid">
    <div>
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Request Details</h3>
        </div>
        <div class="card-body">
          <?php if (!$isEdit): ?>
          <div class="form-group">
            <label class="form-label">Contractor *</label>
            <select name="contractor_id" class="form-control" required>
              <option value="">Select Contractor</option>
              <?php foreach ($contractors as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['company_name']) ?> (<?= htmlspecialchars($c['contractor_code']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php else: ?>
          <div class="form-group">
            <label class="form-label">Contractor</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars(($req['company_name'] ?? '') . ' (' . ($req['contractor_code'] ?? '') . ')') ?>" disabled>
          </div>
          <div class="form-group">
            <label class="form-label">Request Number (ID)</label>
            <input type="text" name="request_number" class="form-control" value="<?= htmlspecialchars($req['request_number'] ?? '') ?>" placeholder="TXN-SUP-YYYYMMDD-XXXXXX">
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">Admin can edit the unique request identifier.</p>
          </div>
          <?php endif; ?>
          
          <div class="form-row-2col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Crew Size *</label>
              <input type="number" name="crew_size" class="form-control" min="1" required value="<?= htmlspecialchars($req['crew_size'] ?? '10') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Duration (days) *</label>
              <input type="number" name="duration_days" class="form-control" min="1" required value="<?= htmlspecialchars($req['duration_days'] ?? '14') ?>">
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Supply Types *</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
              <label style="display:flex; align-items:center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="supply_types[]" value="water" <?= in_array('water', $types) ? 'checked' : '' ?>> Water
              </label>
              <label style="display:flex; align-items:center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="supply_types[]" value="dry_food" <?= in_array('dry_food', $types) ? 'checked' : '' ?>> Dry Food
              </label>
              <label style="display:flex; align-items:center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="supply_types[]" value="canned_food" <?= in_array('canned_food', $types) ? 'checked' : '' ?>> Canned Food
              </label>
              <label style="display:flex; align-items:center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="supply_types[]" value="mixed_supplies" <?= in_array('mixed_supplies', $types) ? 'checked' : '' ?>> Mixed Supplies
              </label>
              <label style="display:flex; align-items:center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="supply_types[]" value="toiletries" <?= in_array('toiletries', $types) ? 'checked' : '' ?>> Toiletries
              </label>
            </div>
          </div>
          
          <div class="form-row-2col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Delivery Location *</label>
              <select name="delivery_location" class="form-control" required>
                <option value="onshore" <?= ($req['delivery_location'] ?? '') === 'onshore' ? 'selected' : '' ?>>Onshore</option>
                <option value="offshore_rig" <?= ($req['delivery_location'] ?? '') === 'offshore_rig' ? 'selected' : '' ?>>Offshore Rig</option>
                <option value="nearshore" <?= ($req['delivery_location'] ?? '') === 'nearshore' ? 'selected' : '' ?>>Nearshore</option>
                <option value="pickup" <?= ($req['delivery_location'] ?? '') === 'pickup' ? 'selected' : '' ?>>Pickup</option>
                <option value="local" <?= ($req['delivery_location'] ?? '') === 'local' ? 'selected' : '' ?>>Local</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Delivery Speed *</label>
              <select name="delivery_speed" class="form-control" required>
                <option value="standard" <?= ($req['delivery_speed'] ?? '') === 'standard' ? 'selected' : '' ?>>Standard</option>
                <option value="priority" <?= ($req['delivery_speed'] ?? '') === 'priority' ? 'selected' : '' ?>>Priority</option>
                <option value="emergency" <?= ($req['delivery_speed'] ?? '') === 'emergency' ? 'selected' : '' ?>>Emergency</option>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Storage Life (months)</label>
            <input type="number" name="storage_life_months" class="form-control" min="1" value="<?= htmlspecialchars($req['storage_life_months'] ?? '6') ?>">
          </div>
          
          <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Rig name, dock instructions, crew constraints, etc."><?= htmlspecialchars($req['notes'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>
    
    <div>
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Pricing</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Base Price (USD) *</label>
            <input type="number" name="base_price" class="form-control" step="0.01" min="0" required value="<?= htmlspecialchars($req['base_price'] ?? $req['calculated_price'] ?? '0') ?>">
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">The discounted price will be auto-calculated using the contractor's discount.</p>
          </div>
          
          <?php if ($isEdit): ?>
          <div class="alert alert-info" style="margin: 0;">
            <div class="alert-title">Current Discounted Price</div>
            <p style="margin: 4px 0 0 0; font-weight: 700; font-size: 1.1rem;"><?= format_price((float)($req['calculated_price'] ?? 0), 'USD') ?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Dates</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Effective Date</label>
            <input type="date" name="effective_date" class="form-control" value="<?= htmlspecialchars($req['effective_date'] ?? '') ?>">
          </div>
          
          <?php if ($isEdit): ?>
          <div class="form-group">
            <label class="form-label">Created At (backdate)</label>
            <input type="datetime-local" name="created_at" class="form-control" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($req['created_at'] ?? 'now'))) ?>">
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">Change the original creation date.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
      
      <?php if ($isEdit): ?>
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Status</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Request Status</label>
            <select name="status" class="form-control">
              <?php foreach ($statuses as $val => $label): ?>
              <option value="<?= htmlspecialchars($val) ?>" <?= ($req['status'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label class="form-label">Payment Instructions</label>
            <textarea name="payment_instructions" class="form-control" rows="3" placeholder="Instructions shown to contractor after approval"><?= htmlspecialchars($req['payment_instructions'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      <?php endif; ?>
      
      <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
        <?= $isEdit ? 'Update Supply Request' : 'Create Supply Request' ?>
      </button>
    </div>
  </div>
</form>
