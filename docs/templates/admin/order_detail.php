<?php
$billing = json_decode($order['billing_address'] ?? '{}', true) ?: [];
$shipping = json_decode($order['shipping_address'] ?? '{}', true) ?: [];
?>

<div class="admin-header">
  <div>
    <div style="display: flex; align-items: center; gap: 16px;">
      <a href="/admin/orders" style="color: #64748b; text-decoration: none;">← Back to Orders</a>
    </div>
    <h1 style="margin: 8px 0 0 0;">Order <?= htmlspecialchars($order['order_number']) ?></h1>
  </div>
  <div>
    <span class="order-status-badge status-<?= str_replace('_', '-', $order['status']) ?>" style="font-size: 1rem; padding: 8px 16px;">
      <?= get_status_label($order['status']) ?>
    </span>
  </div>
</div>

<!-- Action Buttons Based on Status -->
<?php if ($order['status'] === 'payment_uploaded'): ?>
<div class="alert alert-warning mb-4">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <div class="alert-title">Payment Receipt Uploaded</div>
      <p style="margin: 4px 0 0 0;">Review the payment receipt below and confirm if payment has been received.</p>
    </div>
    <div style="display: flex; gap: 12px;">
      <button type="button" class="btn btn-danger" onclick="document.getElementById('decline-modal').style.display='flex'">✗ Decline Payment</button>
      <form action="/admin/orders/<?= $order['id'] ?>/confirm-payment" method="POST">
        <button type="submit" class="btn btn-success">✓ Confirm Payment Received</button>
      </form>
    </div>
  </div>
</div>

<!-- Decline Payment Modal -->
<div id="decline-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: white; padding: 24px; border-radius: 12px; max-width: 500px; width: 90%;">
    <h3 style="margin: 0 0 16px 0;">❌ Decline Payment</h3>
    <p style="color: #64748b; margin-bottom: 16px;">Please provide a reason for declining this payment. The customer will be notified.</p>
    <form action="/admin/orders/<?= $order['id'] ?>/decline-payment" method="POST">
      <div class="form-group">
        <label class="form-label">Reason for Decline *</label>
        <select name="decline_reason" class="form-control" required>
          <option value="">Select a reason...</option>
          <option value="invalid_receipt">Invalid or unreadable receipt</option>
          <option value="amount_mismatch">Payment amount doesn't match order total</option>
          <option value="payment_not_received">Payment not received in bank account</option>
          <option value="duplicate_submission">Duplicate submission</option>
          <option value="fraudulent">Suspected fraudulent transaction</option>
          <option value="other">Other (specify below)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Additional Notes</label>
        <textarea name="decline_notes" class="form-control" rows="3" placeholder="Provide additional details for the customer..."></textarea>
      </div>
      <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('decline-modal').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-danger">Decline Payment</button>
      </div>
    </form>
  </div>
</div>
<?php elseif ($order['status'] === 'payment_confirmed'): ?>
<div class="alert alert-info mb-4">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <div class="alert-title">Ready to Ship</div>
      <p style="margin: 4px 0 0 0;">Payment has been confirmed. This order is ready to be shipped.</p>
    </div>
    <a href="#ship" class="btn btn-primary">📦 Create Shipment</a>
  </div>
</div>
<?php elseif ($order['status'] === 'payment_declined'): ?>
<div class="alert alert-danger mb-4">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <div class="alert-title">❌ Payment Declined</div>
      <p style="margin: 4px 0 0 0;">
        <strong>Reason:</strong> <?= htmlspecialchars($order['decline_reason'] ?? 'No reason provided') ?>
      </p>
      <?php if (!empty($order['payment_declined_at'])): ?>
      <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #64748b;">
        Declined on <?= date('F j, Y g:i A', strtotime($order['payment_declined_at'])) ?>
      </p>
      <?php endif; ?>
    </div>
    <form action="/admin/orders/<?= $order['id'] ?>/revert-to-awaiting" method="POST">
      <button type="submit" class="btn btn-outline">↩ Revert to Awaiting Payment</button>
    </form>
  </div>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  <div>
    <!-- Order Items -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Order Items</h3>
      </div>
      <div style="overflow-x: auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>SKU</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
              <td style="font-weight: 500;"><?= htmlspecialchars($item['product_name'] ?? $item['sku']) ?></td>
              <td style="color: #64748b;"><?= htmlspecialchars($item['sku']) ?></td>
              <td><?= format_price((float)$item['unit_price']) ?></td>
              <td><?= (int)($item['quantity'] ?? $item['qty'] ?? 0) ?></td>
              <td style="font-weight: 600;"><?= format_price((float)($item['total_price'] ?? $item['total'] ?? 0)) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" style="text-align: right; font-weight: 600;">Order Total:</td>
              <td style="font-weight: 700; font-size: 1.1rem;"><?= format_price((float)$order['total']) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    
    <!-- Payment Uploads -->
    <?php if (!empty($paymentUploads)): ?>
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Payment Receipts</h3>
      </div>
      <div class="card-body">
        <?php foreach ($paymentUploads as $upload): ?>
        <div style="display: flex; align-items: center; gap: 16px; padding: 16px; background: #f8fafc; border-radius: 8px; margin-bottom: 12px;">
          <div style="font-size: 2.5rem;">
            <?= strpos($upload['mime_type'], 'pdf') !== false ? '📄' : '🖼️' ?>
          </div>
          <div style="flex: 1;">
            <div style="font-weight: 500;"><?= htmlspecialchars($upload['original_filename']) ?></div>
            <div style="font-size: 0.85rem; color: #64748b;">
              Uploaded <?= date('M j, Y g:i A', strtotime($upload['created_at'])) ?>
              • <?= number_format($upload['file_size'] / 1024, 1) ?> KB
            </div>
            <?php if (!empty($upload['notes'])): ?>
            <div style="font-size: 0.9rem; margin-top: 8px; color: #475569;">
              Note: <?= htmlspecialchars($upload['notes']) ?>
            </div>
            <?php endif; ?>
          </div>
          <div>
            <span class="order-status-badge status-<?= $upload['status'] ?>">
              <?= ucfirst($upload['status']) ?>
            </span>
          </div>
          <a href="/<?= htmlspecialchars($upload['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline">View</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    
    <!-- Shipments -->
    <?php if (!empty($shipments)): ?>
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Shipments</h3>
      </div>
      <div class="card-body">
        <?php foreach ($shipments as $shipment): 
          $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
        ?>
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 16px;">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
            <div>
              <div style="font-weight: 600; font-size: 1.1rem;"><?= htmlspecialchars($shipment['tracking_number']) ?></div>
              <div style="color: #64748b;"><?= htmlspecialchars($shipment['carrier']) ?></div>
            </div>
            <span class="order-status-badge status-<?= str_replace('_', '-', $shipment['status']) ?>">
              <?= get_status_label($shipment['status']) ?>
            </span>
          </div>
          
          <!-- Add Tracking Update -->
          <form action="/admin/shipments/<?= $shipment['id'] ?>/update-tracking" method="POST" style="background: #f8fafc; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
            <h4 style="margin: 0 0 12px 0; font-size: 0.9rem;">Add Tracking Update</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <div>
                <select name="status" class="form-control" style="padding: 8px;">
                  <option value="picked_up">Picked Up from Warehouse</option>
                  <option value="in_transit">In Transit</option>
                  <option value="arrived_hub">Arrived at Hub</option>
                  <option value="departed_hub">Departed Hub</option>
                  <option value="customs_hold">Customs Hold</option>
                  <option value="customs_cleared">Customs Cleared</option>
                  <option value="arrived_destination">Arrived at Destination Country</option>
                  <option value="out_for_delivery">Out for Delivery</option>
                  <option value="delivery_attempted">Delivery Attempted</option>
                  <option value="delivered">Delivered</option>
                </select>
              </div>
              <div>
                <input type="text" name="location" class="form-control" placeholder="Location (e.g., Munich, DE)" style="padding: 8px;">
              </div>
            </div>
            <div style="margin-top: 12px;">
              <input type="text" name="description" class="form-control" placeholder="Status description" style="padding: 8px;">
            </div>
            <button type="submit" class="btn btn-sm btn-primary mt-2">Add Update</button>
          </form>
          
          <!-- Customs Hold Controls -->
          <?php if ($shipment['status'] !== 'delivered'): ?>
          <div style="background: #fef3c7; padding: 16px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #f59e0b;">
            <h4 style="margin: 0 0 12px 0; font-size: 0.9rem; color: #92400e;">⚠️ Customs Control</h4>
            <?php if ($shipment['status'] === 'customs_hold'): ?>
            <div style="margin-bottom: 12px; padding: 12px; background: white; border-radius: 6px;">
              <strong>Currently on Customs Hold</strong>
              <?php if (!empty($shipment['customs_memo'])): ?>
              <p style="margin: 8px 0 0 0; color: #64748b;"><?= htmlspecialchars($shipment['customs_memo']) ?></p>
              <?php endif; ?>
              <?php if (!empty($shipment['customs_duty_amount'])): ?>
              <p style="margin: 8px 0 0 0;"><strong>Duty:</strong> <?= number_format($shipment['customs_duty_amount'], 2) ?> <?= htmlspecialchars($shipment['customs_duty_currency'] ?? 'EUR') ?></p>
              <?php endif; ?>
            </div>
            <form action="/admin/shipments/<?= $shipment['id'] ?>/clear-customs" method="POST" style="display: inline;">
              <input type="hidden" name="location" value="Customs Facility">
              <button type="submit" class="btn btn-sm btn-success">✓ Clear Customs Hold</button>
            </form>
            <?php else: ?>
            <form action="/admin/shipments/<?= $shipment['id'] ?>/customs-hold" method="POST">
              <div class="form-group" style="margin-bottom: 12px;">
                <input type="text" name="customs_memo" class="form-control" placeholder="Customs memo (reason for hold)" style="padding: 8px;">
              </div>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <input type="number" name="duty_amount" class="form-control" placeholder="Duty amount" step="0.01" style="padding: 8px;">
                <select name="duty_currency" class="form-control" style="padding: 8px;">
                  <option value="EUR">EUR</option>
                  <option value="USD">USD</option>
                  <option value="GBP">GBP</option>
                </select>
              </div>
              <button type="submit" class="btn btn-sm btn-warning">⚠️ Set Customs Hold</button>
            </form>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          
          <!-- Recent Events -->
          <?php if (!empty($events)): ?>
          <div style="border-top: 1px solid #e2e8f0; padding-top: 16px;">
            <h4 style="margin: 0 0 12px 0; font-size: 0.9rem;">Tracking History</h4>
            <?php foreach (array_slice($events, 0, 5) as $event): ?>
            <div style="display: flex; gap: 12px; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
              <div style="width: 100px; font-size: 0.85rem; color: #64748b;">
                <?= date('M j, g:i A', strtotime($event['timestamp'] ?? $event['ts'] ?? 'now')) ?>
              </div>
              <div style="flex: 1;">
                <div style="font-weight: 500;"><?= htmlspecialchars($event['description'] ?? $event['status'] ?? '') ?></div>
                <div style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($event['location'] ?? '') ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    
    <!-- Tracking Communications -->
    <?php if (!empty($shipments)): ?>
    <?php foreach ($shipments as $shipment): 
      $trackingNumber = $shipment['tracking_number'];
      // Fetch communications for this order
      $commStmt = $pdo->prepare("SELECT * FROM tracking_communications WHERE order_id = ? ORDER BY created_at ASC");
      $commStmt->execute([$order['id']]);
      $communications = $commStmt->fetchAll();
    ?>
    <div class="card mb-4" id="communications-<?= $shipment['id'] ?>">
      <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="card-title">💬 Customer Communications - <?= htmlspecialchars($trackingNumber) ?></h3>
        <span style="background: #3b82f6; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">
          <?= count($communications) ?> messages
        </span>
      </div>
      <div class="card-body">
        <!-- Messages List -->
        <div style="max-height: 400px; overflow-y: auto; margin-bottom: 20px; padding: 16px; background: #f8fafc; border-radius: 8px;">
          <?php if (empty($communications)): ?>
          <p style="color: #64748b; text-align: center; padding: 20px;">No messages yet</p>
          <?php else: ?>
          <?php foreach ($communications as $comm): ?>
          <div style="margin-bottom: 16px; padding: 12px; border-radius: 8px; <?= $comm['sender_type'] === 'admin' ? 'background: #dbeafe; margin-left: 20%;' : ($comm['sender_type'] === 'system' ? 'background: #fef3c7; border-left: 4px solid #f59e0b;' : 'background: white; margin-right: 20%; border: 1px solid #e2e8f0;') ?>">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
              <span style="font-weight: 600; color: <?= $comm['sender_type'] === 'admin' ? '#1d4ed8' : ($comm['sender_type'] === 'system' ? '#92400e' : '#374151') ?>;">
                <?= $comm['sender_type'] === 'admin' ? '👨‍💼 Admin' : ($comm['sender_type'] === 'system' ? '🤖 System' : '👤 Customer') ?>
                <?php if (!empty($comm['sender_name'])): ?>
                  (<?= htmlspecialchars($comm['sender_name']) ?>)
                <?php endif; ?>
              </span>
              <span style="font-size: 0.8rem; color: #64748b;">
                <?= date('M j, Y g:i A', strtotime($comm['created_at'])) ?>
              </span>
            </div>
            <?php if (!empty($comm['message'])): ?>
            <p style="margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($comm['message']) ?></p>
            <?php endif; ?>
            <?php if (!empty($comm['document_path'])): ?>
            <div style="margin-top: 8px; padding: 8px; background: rgba(0,0,0,0.05); border-radius: 4px;">
              <a href="/<?= htmlspecialchars(ltrim($comm['document_path'], '/')) ?>" target="_blank" style="display: flex; align-items: center; gap: 8px; color: #2563eb; text-decoration: none;">
                📎 <?= htmlspecialchars($comm['document_name'] ?? 'Document') ?>
                <span style="font-size: 0.8rem; color: #64748b;">(<?= htmlspecialchars($comm['document_type'] ?? 'file') ?>)</span>
              </a>
            </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
        
        <!-- Admin Reply Form -->
        <form action="/admin/shipments/<?= $shipment['id'] ?>/send-message" method="POST" enctype="multipart/form-data" style="background: #f1f5f9; padding: 16px; border-radius: 8px;">
          <h4 style="margin: 0 0 12px 0; font-size: 0.9rem;">📝 Send Reply to Customer</h4>
          <div class="form-group" style="margin-bottom: 12px;">
            <textarea name="message" class="form-control" rows="3" placeholder="Type your message to the customer..." style="padding: 12px;"></textarea>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 8px 16px; background: white; border: 1px solid #e2e8f0; border-radius: 6px;">
                <input type="file" name="document" style="display: none;" accept=".pdf,.png,.jpg,.jpeg">
                📎 Attach Document
              </label>
              <span class="file-name" style="font-size: 0.85rem; color: #64748b; margin-left: 8px;"></span>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
          </div>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Create Shipment Form -->
    <?php if ($order['status'] === 'payment_confirmed' && empty($shipments)): ?>
    <div class="card mb-4" id="ship">
      <div class="card-header">
        <h3 class="card-title">🚛 Create Shipment - Streicher Logistics</h3>
      </div>
      <div class="card-body">
        <form action="/admin/orders/<?= $order['id'] ?>/ship" method="POST">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Carrier</label>
              <select name="carrier" class="form-control">
                <option value="Streicher Logistics" selected>🚛 Streicher Logistics</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Tracking Number (optional)</label>
              <input type="text" name="tracking_number" class="form-control" placeholder="Leave blank to auto-generate">
            </div>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Shipping Method</label>
              <select name="shipping_method" class="form-control">
                <option value="air_freight">✈️ Air Freight (International)</option>
                <option value="sea_freight">🚢 Sea Freight (Heavy Cargo)</option>
                <option value="local_van">🚐 Local Van Delivery (Germany)</option>
                <option value="motorcycle">🏍️ Motorcycle Courier (Express Local)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Package Type</label>
              <select name="package_type" class="form-control">
                <option value="crate">📦 Industrial Crate</option>
                <option value="carton">📦 Carton Box</option>
                <option value="pallet">🏗️ Pallet</option>
                <option value="container">🚛 Container</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Destination</label>
            <input type="text" name="destination" class="form-control" value="<?= htmlspecialchars(($shipping['city'] ?? '') . ', ' . ($shipping['country'] ?? '')) ?>">
          </div>
          <button type="submit" class="btn btn-primary btn-lg">📦 Create Shipment & Mark as Shipped</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
  
  <!-- Sidebar -->
  <div>
    <!-- Order Info -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Order Details</h3>
      </div>
      <div class="card-body">
        <div style="margin-bottom: 16px;">
          <div style="color: #64748b; font-size: 0.85rem;">Order Number</div>
          <div style="font-weight: 600;"><?= htmlspecialchars($order['order_number']) ?></div>
        </div>
        <div style="margin-bottom: 16px;">
          <div style="color: #64748b; font-size: 0.85rem;">Order Date</div>
          <div style="font-weight: 600;"><?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></div>
        </div>
        <div style="margin-bottom: 16px;">
          <div style="color: #64748b; font-size: 0.85rem;">Payment Method</div>
          <div style="font-weight: 600;">Bank Transfer</div>
        </div>
        <?php if (!empty($order['payment_confirmed_at'])): ?>
        <div style="margin-bottom: 16px;">
          <div style="color: #64748b; font-size: 0.85rem;">Payment Confirmed</div>
          <div style="font-weight: 600;"><?= date('F j, Y g:i A', strtotime($order['payment_confirmed_at'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($order['shipped_at'])): ?>
        <div style="margin-bottom: 16px;">
          <div style="color: #64748b; font-size: 0.85rem;">Shipped</div>
          <div style="font-weight: 600;"><?= date('F j, Y g:i A', strtotime($order['shipped_at'])) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Customer Info -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Customer</h3>
      </div>
      <div class="card-body">
        <div style="margin-bottom: 16px;">
          <div style="font-weight: 600;"><?= htmlspecialchars($billing['company'] ?? 'N/A') ?></div>
          <div><?= htmlspecialchars($billing['name'] ?? '') ?></div>
        </div>
        <div style="margin-bottom: 16px;">
          <div style="color: #64748b; font-size: 0.85rem;">Email</div>
          <div><a href="mailto:<?= htmlspecialchars($billing['email'] ?? '') ?>"><?= htmlspecialchars($billing['email'] ?? 'N/A') ?></a></div>
        </div>
        <div>
          <div style="color: #64748b; font-size: 0.85rem;">Phone</div>
          <div><?= htmlspecialchars($billing['phone'] ?? 'N/A') ?></div>
        </div>
      </div>
    </div>
    
    <!-- Shipping Address -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Shipping Address</h3>
      </div>
      <div class="card-body">
        <div>
          <?= htmlspecialchars($shipping['company'] ?? $billing['company'] ?? '') ?><br>
          <?= htmlspecialchars($shipping['name'] ?? $billing['name'] ?? '') ?><br>
          <?= htmlspecialchars($shipping['address'] ?? $billing['address'] ?? '') ?><br>
          <?= htmlspecialchars(($shipping['zip'] ?? $billing['zip'] ?? '') . ' ' . ($shipping['city'] ?? $billing['city'] ?? '')) ?><br>
          <?= htmlspecialchars($shipping['country'] ?? $billing['country'] ?? '') ?>
        </div>
      </div>
    </div>
  </div>
</div>
