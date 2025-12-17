<div class="admin-header">
  <div>
    <h1 style="margin: 0;">Contractors</h1>
    <p style="margin: 4px 0 0 0; color: #64748b;"><?= count($contractors ?? []) ?> contractors</p>
  </div>
  <a href="/admin/contractors/new" class="btn btn-primary">+ New Contractor</a>
</div>

<?php if (!empty($createdCode)): ?>
<div class="alert alert-success mb-4">
  <div class="alert-title">Contractor Created</div>
  <p style="margin: 4px 0 0 0;">New contractor code: <strong style="font-family: monospace; font-size: 1.1rem;"><?= htmlspecialchars($createdCode) ?></strong></p>
</div>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_info'])): ?>
<div class="alert alert-info mb-4">
  <p style="margin: 0;"><?= htmlspecialchars($_SESSION['flash_info']) ?></p>
</div>
<?php unset($_SESSION['flash_info']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="alert alert-error mb-4">
  <p style="margin: 0;"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="card">
  <?php if (empty($contractors)): ?>
  <div class="card-body text-center" style="padding: 64px;">
    <div style="font-size: 3rem; margin-bottom: 16px;">👥</div>
    <h3>No contractors found</h3>
    <p style="color: #64748b;">Create your first contractor to get started.</p>
    <a href="/admin/contractors/new" class="btn btn-primary" style="margin-top: 16px;">+ New Contractor</a>
  </div>
  <?php else: ?>
  <div style="overflow-x: auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th>Company</th>
          <th>Contact</th>
          <th>Contractor Code</th>
          <th>Discount</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contractors as $c): ?>
        <tr>
          <td>
            <div style="font-weight: 600;"><?= htmlspecialchars($c['company_name'] ?? '') ?></div>
          </td>
          <td>
            <div><?= htmlspecialchars($c['full_name'] ?? '') ?></div>
          </td>
          <td>
            <code style="background: #2a2a2a; color: #00bfff; padding: 6px 10px; border-radius: 6px; font-size: 0.9rem; border: 1px solid #444;"><?= htmlspecialchars($c['contractor_code'] ?? '') ?></code>
          </td>
          <td>
            <?php if (!empty($c['discount_eligible'])): ?>
              <span style="font-weight: 600; color: #0f766e;"><?= number_format((float)($c['discount_percent'] ?? 0), 1) ?>%</span>
            <?php else: ?>
              <span style="color: #64748b;">—</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($c['active'])): ?>
              <span class="order-status-badge status-delivered">Active</span>
            <?php else: ?>
              <span class="order-status-badge status-cancelled">Inactive</span>
            <?php endif; ?>
          </td>
          <td style="font-size: 0.9rem; color: #64748b;">
            <?= date('M j, Y', strtotime($c['created_at'])) ?>
          </td>
          <td style="display: flex; gap: 8px; align-items: center;">
            <a class="btn btn-sm btn-outline" href="/admin/contractors/<?= (int)$c['id'] ?>/edit">Edit</a>
            <a class="btn btn-sm btn-outline" href="/admin/contractors/<?= (int)$c['id'] ?>/export-csv" title="Export supply requests to CSV">📥 CSV</a>
            <form method="POST" action="/admin/contractors/<?= (int)$c['id'] ?>/delete" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this contractor? This cannot be undone.');">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
