<?php
$billingAddress = json_decode($order['billing_address'] ?? '{}', true) ?: [];
$shippingAddress = json_decode($order['shipping_address'] ?? '{}', true) ?: [];

// Determine progress step
$progressStep = 1;
switch ($order['status']) {
    case 'awaiting_payment': $progressStep = 1; break;
    case 'payment_uploaded': $progressStep = 2; break;
    case 'payment_confirmed': $progressStep = 3; break;
    case 'processing': $progressStep = 3; break;
    case 'shipped': $progressStep = 4; break;
    case 'in_transit': $progressStep = 4; break;
    case 'out_for_delivery': $progressStep = 4; break;
    case 'delivered': $progressStep = 5; break;
}
?>

<div class="breadcrumb">
  <a href="/">Home</a> <span>/</span>
  <span>Order <?= htmlspecialchars($order['order_number']) ?></span>
</div>

<div style="max-width: 900px; margin: 0 auto;">
  <div class="page-header text-center">
    <h1 class="page-title">Order <?= htmlspecialchars($order['order_number']) ?></h1>
    <p class="page-subtitle">Placed on <?= date('F j, Y', strtotime($order['created_at'])) ?></p>
  </div>
  
  <!-- Status Banner -->
  <div class="card mb-4">
    <div class="card-body" style="text-align: center; padding: 32px;">
      <span class="order-status-badge status-<?= str_replace('_', '-', $order['status']) ?>" style="font-size: 1.1rem; padding: 10px 20px;">
        <?= get_status_label($order['status']) ?>
      </span>
      <p style="margin: 16px 0 0 0; color: #64748b;">
        <?php
        switch ($order['status']) {
            case 'awaiting_payment':
                echo 'Please complete your payment and upload the receipt.';
                break;
            case 'payment_uploaded':
                echo 'Your payment receipt is being reviewed. This usually takes 1-2 business days.';
                break;
            case 'payment_confirmed':
                echo 'Payment confirmed! Your order is being prepared for shipment.';
                break;
            case 'shipped':
            case 'in_transit':
                echo 'Your order is on its way!';
                break;
            case 'out_for_delivery':
                echo 'Your order is out for delivery today!';
                break;
            case 'delivered':
                echo 'Your order has been delivered. Thank you for your business!';
                break;
            default:
                echo 'Order is being processed.';
        }
        ?>
      </p>
    </div>
  </div>
  
  <!-- Progress Steps -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="tracking-progress-bar" data-progress="<?= $progressStep ?>" style="margin-bottom: 0;">
        <div class="progress-step <?= $progressStep >= 1 ? 'completed' : '' ?>">
          <div class="progress-step-icon">📝</div>
          <div class="progress-step-label">Order<br>Placed</div>
        </div>
        <div class="progress-step <?= $progressStep >= 2 ? 'completed' : '' ?> <?= $progressStep === 2 ? 'active' : '' ?>">
          <div class="progress-step-icon">💳</div>
          <div class="progress-step-label">Payment<br>Uploaded</div>
        </div>
        <div class="progress-step <?= $progressStep >= 3 ? 'completed' : '' ?> <?= $progressStep === 3 ? 'active' : '' ?>">
          <div class="progress-step-icon">✓</div>
          <div class="progress-step-label">Payment<br>Confirmed</div>
        </div>
        <div class="progress-step <?= $progressStep >= 4 ? 'completed' : '' ?> <?= $progressStep === 4 ? 'active' : '' ?>">
          <div class="progress-step-icon">🚚</div>
          <div class="progress-step-label">Shipped</div>
        </div>
        <div class="progress-step <?= $progressStep >= 5 ? 'completed' : '' ?> <?= $progressStep === 5 ? 'active' : '' ?>">
          <div class="progress-step-icon">📦</div>
          <div class="progress-step-label">Delivered</div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Action Buttons -->
  <?php if ($order['status'] === 'awaiting_payment'): ?>
  <div class="alert alert-warning mb-4">
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <div>
        <div class="alert-title">Payment Required</div>
        <p style="margin: 4px 0 0 0;">Please complete your payment and upload the receipt to proceed.</p>
      </div>
      <a href="/order/<?= $order['id'] ?>/payment" class="btn btn-primary">Upload Payment Receipt</a>
    </div>
  </div>
  <?php endif; ?>
  
  <!-- Tracking Info -->
  <?php if (!empty($shipments)): ?>
  <div class="card mb-4">
    <div class="card-header">
      <h3 class="card-title">Shipment Tracking</h3>
    </div>
    <div class="card-body">
      <?php foreach ($shipments as $shipment): ?>
      <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; background: #f8fafc; border-radius: 8px;">
        <div>
          <div style="font-weight: 600;"><?= htmlspecialchars($shipment['carrier']) ?></div>
          <div style="font-family: monospace; color: #64748b;"><?= htmlspecialchars($shipment['tracking_number']) ?></div>
        </div>
        <a href="/track?tracking=<?= urlencode($shipment['tracking_number']) ?>" class="btn btn-primary">Track Shipment</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Order Items -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Order Items</h3>
      </div>
      <div class="card-body" style="padding: 0;">
        <?php foreach ($items as $item): ?>
        <div style="display: flex; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid #e2e8f0;">
          <div>
            <div style="font-weight: 500;"><?= htmlspecialchars($item['sku']) ?></div>
            <div style="font-size: 0.85rem; color: #64748b;">Qty: <?= (int)$item['qty'] ?></div>
          </div>
          <div style="font-weight: 600;"><?= format_price((float)$item['total']) ?></div>
        </div>
        <?php endforeach; ?>
        <div style="display: flex; justify-content: space-between; padding: 16px 24px; background: #f8fafc; font-weight: 700;">
          <span>Total</span>
          <span><?= format_price((float)$order['total']) ?></span>
        </div>
      </div>
    </div>
    
    <!-- Shipping Address -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Shipping Address</h3>
      </div>
      <div class="card-body">
        <div style="line-height: 1.8;">
          <?= htmlspecialchars($shippingAddress['company'] ?? $billingAddress['company'] ?? '') ?><br>
          <?= htmlspecialchars($shippingAddress['name'] ?? $billingAddress['name'] ?? '') ?><br>
          <?= htmlspecialchars($shippingAddress['address'] ?? $billingAddress['address'] ?? '') ?><br>
          <?= htmlspecialchars(($shippingAddress['zip'] ?? $billingAddress['zip'] ?? '') . ' ' . ($shippingAddress['city'] ?? $billingAddress['city'] ?? '')) ?><br>
          <?= htmlspecialchars($shippingAddress['country'] ?? $billingAddress['country'] ?? '') ?>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Help -->
  <div class="card mt-4">
    <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h4 style="margin: 0 0 4px 0;">Need Help?</h4>
        <p style="margin: 0; color: #64748b;">Contact our support team for any questions about your order.</p>
      </div>
      <div style="display: flex; gap: 12px;">
        <a href="mailto:store@streichergmbh.com" class="btn btn-outline">Email Support</a>
        <a href="tel:+499411234567" class="btn btn-outline">Call Us</a>
      </div>
    </div>
  </div>
</div>
