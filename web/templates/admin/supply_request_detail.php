<?php
$req = $request ?? [];
$payment = $payment ?? null;
$payload = $paymentPayload ?? null;
$types = json_decode($req['supply_types'] ?? '[]', true) ?: [];
?>

<div class="admin-header" style="display:flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
  <div>
    <h1 style="margin: 0;">Supply Request</h1>
    <p style="margin: 4px 0 0 0; color: #64748b;">
      <?= htmlspecialchars($req['request_number'] ?? '') ?>
    </p>
  </div>
  <div style="display:flex; gap: 10px; flex-wrap: wrap; align-items: center;">
    <a href="/admin/supply-requests/<?= (int)$req['id'] ?>/edit" class="btn btn-primary">Edit Request</a>
    <form method="POST" action="/admin/supply-requests/<?= (int)$req['id'] ?>/delete" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this supply request? This cannot be undone.');">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <button type="submit" class="btn btn-danger">Delete Request</button>
    </form>
    <a href="/admin/supply-requests" class="btn btn-outline">← Back</a>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body" style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
    <div>
      <div style="font-weight: 700; font-size: 1.1rem;"><?= htmlspecialchars($req['company_name'] ?? '') ?></div>
      <div style="color:#64748b;">Contractor: <?= htmlspecialchars($req['full_name'] ?? '') ?></div>
      <div style="color:#64748b;">Code: <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"><?= htmlspecialchars($req['contractor_code'] ?? '') ?></span></div>
    </div>
    <div style="text-align:right;">
      <?php $basePrice = isset($req['base_price']) && $req['base_price'] !== null ? (float)$req['base_price'] : (float)($req['calculated_price'] ?? 0); ?>
      <div style="font-size: 0.9rem; color: #64748b;">Price: <?= format_price($basePrice, (string)($req['currency'] ?? 'USD')) ?></div>
      <div style="font-weight: 800; font-size: 1.2rem;">Discounted: <?= format_price((float)($req['calculated_price'] ?? 0), (string)($req['currency'] ?? 'USD')) ?></div>
      <div style="margin-top: 8px;">
        <span class="order-status-badge status-<?= htmlspecialchars(str_replace('_', '-', (string)($req['status'] ?? ''))) ?>">
          <?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)($req['status'] ?? '')))) ?>
        </span>
      </div>
    </div>
  </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items:start;">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Request Details</h3>
    </div>
    <div class="card-body">
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <div style="color:#64748b; font-size: 0.85rem;">Crew / Duration</div>
          <div style="font-weight: 600;">Crew: <?= (int)($req['crew_size'] ?? 0) ?> · <?= (int)($req['duration_days'] ?? 0) ?> days</div>
        </div>
        <div>
          <div style="color:#64748b; font-size: 0.85rem;">Delivery</div>
          <div style="font-weight: 600;">
            <?= htmlspecialchars(str_replace('_', ' ', (string)($req['delivery_location'] ?? ''))) ?>
            · <?= htmlspecialchars(str_replace('_', ' ', (string)($req['delivery_speed'] ?? ''))) ?>
          </div>
        </div>
        <div>
          <div style="color:#64748b; font-size: 0.85rem;">Supply Types</div>
          <div style="font-weight: 600;">
            <?= htmlspecialchars(implode(', ', array_map(fn($t) => str_replace('_', ' ', (string)$t), $types))) ?>
          </div>
        </div>
        <div>
          <div style="color:#64748b; font-size: 0.85rem;">Storage Life</div>
          <div style="font-weight: 600;"><?= htmlspecialchars((string)($req['storage_life_months'] ?? '—')) ?> months</div>
        </div>
        <div>
          <div style="color:#64748b; font-size: 0.85rem;">Effective Date</div>
          <div style="font-weight: 600;"><?= !empty($req['effective_date']) ? htmlspecialchars((string)$req['effective_date']) : '—' ?></div>
        </div>
        <div>
          <div style="color:#64748b; font-size: 0.85rem;">Created</div>
          <div style="font-weight: 600;"><?= htmlspecialchars(date('Y-m-d H:i', strtotime((string)($req['created_at'] ?? 'now')))) ?></div>
        </div>
      </div>

      <?php if (!empty($req['notes'])): ?>
      <div style="margin-top: 16px;">
        <div style="color:#64748b; font-size: 0.85rem;">Notes</div>
        <div style="margin-top: 6px; white-space: pre-wrap;"><?= htmlspecialchars((string)$req['notes']) ?></div>
      </div>
      <?php endif; ?>

      <?php if (!empty($req['decline_reason'])): ?>
      <div style="margin-top: 16px;">
        <div style="color:#b91c1c; font-weight: 700;">Decline Reason</div>
        <div style="margin-top: 6px; white-space: pre-wrap;"><?= htmlspecialchars((string)$req['decline_reason']) ?></div>
      </div>
      <?php endif; ?>

      <?php if (!empty($req['payment_instructions'])): ?>
      <div style="margin-top: 16px;">
        <div style="color:#0f172a; font-weight: 700;">Payment Instructions</div>
        <div style="margin-top: 6px; white-space: pre-wrap;"><?= htmlspecialchars((string)$req['payment_instructions']) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div>
    <?php if (($req['status'] ?? '') === 'awaiting_review'): ?>
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Review</h3>
      </div>
      <div class="card-body">
        <form method="POST" action="/admin/supply-requests/<?= (int)$req['id'] ?>/accept">
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label">Payment Instructions (shown to contractor after acceptance)</label>
            <textarea name="payment_instructions" class="form-control" rows="4" placeholder="Example: Please submit your card details in the Supply Portal. Our team will process the transaction and confirm completion."></textarea>
          </div>
          <button type="submit" class="btn btn-success" style="width:100%;">Accept Request</button>
        </form>

        <div style="height: 12px;"></div>

        <form method="POST" action="/admin/supply-requests/<?= (int)$req['id'] ?>/decline">
          <?= csrf_field() ?>
          <div class="form-group">
            <label class="form-label">Decline Reason</label>
            <textarea name="decline_reason" class="form-control" rows="3" placeholder="Why was this request declined?"></textarea>
          </div>
          <button type="submit" class="btn btn-danger" style="width:100%;">Decline Request</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header" style="display:flex; justify-content: space-between; align-items:center;">
        <h3 class="card-title" style="margin:0;">Payment Submission</h3>
        <?php if (!empty($payment) && !empty($payment['expires_at'])): ?>
          <div style="font-size: 0.85rem; color:#64748b;">Expires: <?= htmlspecialchars(date('Y-m-d H:i', strtotime((string)$payment['expires_at']))) ?></div>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if (($req['status'] ?? '') === 'approved_awaiting_payment'): ?>
          <div style="color:#64748b;">Awaiting contractor payment submission.</div>
          <?php if (!empty($req['payment_instructions'])): ?>
            <div class="alert alert-info" style="margin-top: 16px;">
              <div class="alert-title">Instructions</div>
              <p style="margin: 4px 0 0 0; white-space: pre-wrap;"><?= htmlspecialchars((string)$req['payment_instructions']) ?></p>
            </div>
          <?php endif; ?>
        <?php elseif (($req['status'] ?? '') === 'payment_submitted_processing'): ?>
          <?php if (empty($paymentPayload)): ?>
            <div class="alert alert-error">
              <div class="alert-title">Payment details unavailable</div>
              <p style="margin: 4px 0 0 0;">The payment submission may have expired (24h) or could not be decrypted.</p>
            </div>
          <?php else: ?>
            <div class="alert alert-info">
              <div class="alert-title">Payment submitted (processing)</div>
              <p style="margin: 4px 0 0 0;">Review the card details below and mark the transaction as completed when done.</p>
            </div>

            <div style="display:grid; gap: 12px; margin-top: 16px;">
              <div>
                <div style="color:#64748b; font-size: 0.85rem;">Billing Name</div>
                <div style="font-weight: 700;"><?= htmlspecialchars((string)($paymentPayload['billing_name'] ?? '')) ?></div>
              </div>
              <div>
                <div style="color:#64748b; font-size: 0.85rem;">Phone</div>
                <div style="font-weight: 600;"><?= htmlspecialchars((string)($paymentPayload['phone'] ?? '')) ?></div>
              </div>
              <div>
                <div style="color:#64748b; font-size: 0.85rem;">Billing Address</div>
                <div style="font-weight: 600; white-space: pre-wrap;">
                  <?php
                    $addr = $paymentPayload['billing_address'] ?? [];
                    $parts = [];
                    if (!empty($addr['line1'])) $parts[] = $addr['line1'];
                    if (!empty($addr['line2'])) $parts[] = $addr['line2'];
                    $cityLine = trim((string)($addr['city'] ?? ''));
                    $state = trim((string)($addr['state'] ?? ''));
                    $zip = trim((string)($addr['zip'] ?? ''));
                    $csz = trim($cityLine . ($state !== '' ? ', ' . $state : '') . ($zip !== '' ? ' ' . $zip : ''));
                    if ($csz !== '') $parts[] = $csz;
                    if (!empty($addr['country'])) $parts[] = $addr['country'];
                    echo htmlspecialchars(implode("\n", $parts));
                  ?>
                </div>
              </div>

              <div style="padding: 12px; border-radius: 10px; background: #0b1220; color: #e2e8f0;">
                <div style="font-weight: 800; margin-bottom: 6px;">Card Details (expires in 24h)</div>
                <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                  Name: <?= htmlspecialchars((string)($paymentPayload['card_name'] ?? '')) ?>
                </div>
                <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                  Number: <?= htmlspecialchars((string)($paymentPayload['card_number'] ?? '')) ?>
                </div>
                <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                  Exp: <?= htmlspecialchars((string)($paymentPayload['exp_month'] ?? '')) ?>/<?= htmlspecialchars((string)($paymentPayload['exp_year'] ?? '')) ?>
                </div>
                <div style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                  CVV: <?= htmlspecialchars((string)($paymentPayload['cvv'] ?? '')) ?>
                </div>
              </div>

              <form method="POST" action="/admin/supply-requests/<?= (int)$req['id'] ?>/complete">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary" style="width:100%;">Mark Transaction Completed</button>
              </form>
            </div>
          <?php endif; ?>
        <?php elseif (($req['status'] ?? '') === 'transaction_completed'): ?>
          <div class="alert alert-success">
            <div class="alert-title">Transaction completed</div>
            <p style="margin: 4px 0 0 0;">Payment details have been cleared.</p>
          </div>
        <?php else: ?>
          <div style="color:#64748b;">No payment activity yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
