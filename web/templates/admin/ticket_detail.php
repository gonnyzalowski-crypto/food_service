<div class="admin-page-header">
  <div>
    <a href="/admin/tickets" style="color: #64748b; text-decoration: none; font-size: 0.9rem;">← Back to Tickets</a>
    <h1 class="admin-page-title" style="margin-top: 8px;">🎫 Ticket <?= htmlspecialchars($ticket['ticket_number']) ?></h1>
    <p class="admin-page-subtitle">Submitted on <?= date('F j, Y \a\t g:i A', strtotime($ticket['created_at'])) ?></p>
  </div>
  <div>
    <?php
    $statusColors = [
      'new' => 'background: #fee2e2; color: #dc2626;',
      'in_progress' => 'background: #fef3c7; color: #d97706;',
      'resolved' => 'background: #dcfce7; color: #16a34a;',
      'closed' => 'background: #e2e8f0; color: #64748b;',
    ];
    $statusStyle = $statusColors[$ticket['status']] ?? '';
    ?>
    <span style="<?= $statusStyle ?> padding: 8px 16px; border-radius: 6px; font-size: 1rem; font-weight: 600;">
      <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
    </span>
  </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  <!-- Main Content -->
  <div>
    <!-- Customer Message -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">📩 Customer Message</h3>
      </div>
      <div class="card-body">
        <div style="margin-bottom: 16px;">
          <span style="background: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 500;">
            <?= ucfirst(htmlspecialchars($ticket['subject'])) ?>
          </span>
        </div>
        <div style="white-space: pre-wrap; line-height: 1.8; color: #334155;">
<?= htmlspecialchars($ticket['message']) ?>
        </div>
      </div>
    </div>
    
    <!-- Update Status & Notes -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">📝 Update Ticket</h3>
      </div>
      <div class="card-body">
        <form action="/admin/tickets/<?= $ticket['id'] ?>/update" method="POST">
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
              <option value="new" <?= $ticket['status'] === 'new' ? 'selected' : '' ?>>New</option>
              <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
              <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
              <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Admin Notes (Internal)</label>
            <textarea name="admin_notes" class="form-control" rows="4" placeholder="Add internal notes about this ticket..."><?= htmlspecialchars($ticket['admin_notes'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Update Ticket</button>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Sidebar -->
  <div>
    <!-- Customer Info -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">👤 Customer Information</h3>
      </div>
      <div class="card-body">
        <div style="margin-bottom: 16px;">
          <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 4px;">Name</div>
          <div style="font-weight: 600;"><?= htmlspecialchars($ticket['name']) ?></div>
        </div>
        <?php if ($ticket['company']): ?>
        <div style="margin-bottom: 16px;">
          <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 4px;">Company</div>
          <div style="font-weight: 600;"><?= htmlspecialchars($ticket['company']) ?></div>
        </div>
        <?php endif; ?>
        <div style="margin-bottom: 16px;">
          <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 4px;">Email</div>
          <div>
            <a href="mailto:<?= htmlspecialchars($ticket['email']) ?>" style="color: #2563eb; font-weight: 500;">
              <?= htmlspecialchars($ticket['email']) ?>
            </a>
          </div>
        </div>
        <?php if ($ticket['phone']): ?>
        <div style="margin-bottom: 16px;">
          <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 4px;">Phone</div>
          <div>
            <a href="tel:<?= htmlspecialchars($ticket['phone']) ?>" style="color: #2563eb;">
              <?= htmlspecialchars($ticket['phone']) ?>
            </a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">⚡ Quick Actions</h3>
      </div>
      <div class="card-body">
        <a href="mailto:<?= htmlspecialchars($ticket['email']) ?>?subject=Re: <?= urlencode($ticket['ticket_number'] . ' - ' . ucfirst($ticket['subject'])) ?>" class="btn btn-primary btn-block mb-2">
          ✉️ Reply via Email
        </a>
        <?php if ($ticket['phone']): ?>
        <a href="tel:<?= htmlspecialchars($ticket['phone']) ?>" class="btn btn-outline btn-block">
          📞 Call Customer
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
