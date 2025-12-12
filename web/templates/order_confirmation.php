<?php
$billingAddress = json_decode($order['billing_address'] ?? '{}', true) ?: [];
?>

<div style="max-width: 800px; margin: 0 auto; text-align: center;">
  <div style="font-size: 5rem; margin-bottom: 24px;">✅</div>
  <h1 style="font-size: 2rem; margin: 0 0 16px 0;">Payment Receipt Uploaded!</h1>
  <p style="font-size: 1.1rem; color: #64748b; margin-bottom: 32px;">
    Thank you! We've received your payment receipt and will verify it shortly.
  </p>
  
  <div class="card mb-4">
    <div class="card-body">
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; text-align: center;">
        <div>
          <div style="color: #64748b; font-size: 0.85rem;">Order Number</div>
          <div style="font-size: 1.25rem; font-weight: 700;"><?= htmlspecialchars($order['order_number']) ?></div>
        </div>
        <div>
          <div style="color: #64748b; font-size: 0.85rem;">Total Amount</div>
          <div style="font-size: 1.25rem; font-weight: 700;"><?= format_price((float)$order['total']) ?></div>
        </div>
        <div>
          <div style="color: #64748b; font-size: 0.85rem;">Status</div>
          <div>
            <span class="order-status-badge status-payment-uploaded">Payment Uploaded</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="card mb-4">
    <div class="card-header">
      <h3 class="card-title">What Happens Next?</h3>
    </div>
    <div class="card-body">
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; text-align: center;">
        <div>
          <div style="width: 48px; height: 48px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.25rem;">✓</div>
          <div style="font-weight: 500;">Receipt Uploaded</div>
          <div style="font-size: 0.85rem; color: #64748b;">Completed</div>
        </div>
        <div>
          <div style="width: 48px; height: 48px; background: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.25rem;">⏳</div>
          <div style="font-weight: 500;">Verification</div>
          <div style="font-size: 0.85rem; color: #64748b;">1-2 business days</div>
        </div>
        <div>
          <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.25rem;">📦</div>
          <div style="font-weight: 500;">Processing</div>
          <div style="font-size: 0.85rem; color: #64748b;">After confirmation</div>
        </div>
        <div>
          <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.25rem;">🚚</div>
          <div style="font-weight: 500;">Shipping</div>
          <div style="font-size: 0.85rem; color: #64748b;">With tracking</div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="alert alert-info" style="text-align: left;">
    <div class="alert-title">📧 Confirmation Email Sent</div>
    <p style="margin: 4px 0 0 0;">
      We've sent a confirmation email to <strong><?= htmlspecialchars($billingAddress['email'] ?? 'your email') ?></strong> 
      with your order details. You'll receive another email once your payment is verified.
    </p>
  </div>
  
  <div style="display: flex; gap: 16px; justify-content: center; margin-top: 32px;">
    <a href="/order/<?= $order['id'] ?>" class="btn btn-primary btn-lg">View Order Status</a>
    <a href="/catalog" class="btn btn-outline btn-lg">Continue Shopping</a>
  </div>
</div>
