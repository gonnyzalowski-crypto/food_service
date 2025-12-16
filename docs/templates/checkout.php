<div class="breadcrumb">
  <a href="/">Home</a> <span>/</span>
  <a href="/cart">Cart</a> <span>/</span>
  <span>Checkout</span>
</div>

<div class="page-header">
  <h1 class="page-title">Checkout</h1>
  <p class="page-subtitle">Complete your order</p>
</div>

<form action="/checkout" method="POST">
  <div class="checkout-grid">
    <!-- Checkout Form -->
    <div>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Billing & Shipping Information</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Company Name *</label>
            <input type="text" name="company" class="form-control" required placeholder="Your Company GmbH">
          </div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Contact Name *</label>
              <input type="text" name="name" class="form-control" required placeholder="Full Name">
            </div>
            <div class="form-group">
              <label class="form-label">Email *</label>
              <input type="email" name="email" class="form-control" required placeholder="email@company.com">
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Phone Number *</label>
            <input type="tel" name="phone" class="form-control" required placeholder="+49 123 456 7890">
          </div>
          
          <div class="form-group">
            <label class="form-label">Street Address *</label>
            <input type="text" name="address" class="form-control" required placeholder="Street and number">
          </div>
          
          <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">City *</label>
              <input type="text" name="city" class="form-control" required placeholder="City">
            </div>
            <div class="form-group">
              <label class="form-label">Postal Code *</label>
              <input type="text" name="zip" class="form-control" required placeholder="12345">
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Country *</label>
            <select name="country" class="form-control" required>
              <option value="Germany">Germany</option>
              <option value="Austria">Austria</option>
              <option value="Switzerland">Switzerland</option>
              <option value="Netherlands">Netherlands</option>
              <option value="Belgium">Belgium</option>
              <option value="France">France</option>
              <option value="United Kingdom">United Kingdom</option>
              <option value="United States">United States</option>
              <option value="Other">Other (specify in notes)</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="form-label">Order Notes (Optional)</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Special instructions, delivery requirements, etc."></textarea>
          </div>
        </div>
      </div>
      
      <!-- Payment Information -->
      <div class="card mt-4">
        <div class="card-header">
          <h3 class="card-title">Payment Method</h3>
        </div>
        <div class="card-body">
          <div class="alert alert-info">
            <div class="alert-title">🏦 Bank Transfer / Wire Payment</div>
            <p style="margin: 8px 0 0 0;">
              Please transfer the payment to our bank account below. Once you've made the payment, 
              upload your payment receipt for verification. Your order will be processed after payment confirmation.
            </p>
          </div>
          
          <!-- Bank Account Details -->
          <?php $s = $settings ?? []; ?>
          <div style="background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%); padding: 24px; border-radius: 12px; margin-top: 16px; color: white;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
              <span style="font-size: 1.5rem;">🏦</span>
              <h4 style="margin: 0; color: white;">Bank Transfer Details</h4>
            </div>
            <div style="display: grid; gap: 16px; font-family: 'Courier New', monospace;">
              <div style="display: flex; justify-content: space-between; padding: 12px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                <span style="color: #94a3b8;">Bank Name</span>
                <span style="font-weight: 600;"><?= htmlspecialchars($s['bank_name'] ?? 'Deutsche Bank AG') ?></span>
              </div>
              <div style="display: flex; justify-content: space-between; padding: 12px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                <span style="color: #94a3b8;">Account Holder</span>
                <span style="font-weight: 600;"><?= htmlspecialchars($s['account_holder'] ?? 'Streicher GmbH') ?></span>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                <span style="color: #94a3b8;">IBAN</span>
                <span style="display: flex; align-items: center; gap: 8px;">
                  <span id="iban-value" style="font-weight: 600;"><?= htmlspecialchars($s['iban'] ?? 'DE89 3704 0044 0532 0130 00') ?></span>
                  <button type="button" onclick="copyToClipboard('DE89370400440532013000', this)" style="background: rgba(255,255,255,0.2); border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; color: white; font-size: 0.8rem;">📋 Copy</button>
                </span>
              </div>
              <div style="display: flex; justify-content: space-between; padding: 12px; background: rgba(255,255,255,0.1); border-radius: 8px;">
                <span style="color: #94a3b8;">BIC/SWIFT</span>
                <span style="font-weight: 600;"><?= htmlspecialchars($s['bic'] ?? 'COBADEFFXXX') ?></span>
              </div>
            </div>
            <div style="margin-top: 16px; padding: 12px; background: rgba(234, 179, 8, 0.2); border: 1px solid rgba(234, 179, 8, 0.5); border-radius: 8px;">
              <strong style="color: #fbbf24;">⚠️ Important:</strong> 
              <span style="color: #fef3c7;">Use your Order Number as the payment reference</span>
            </div>
          </div>
          
          <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-top: 16px;">
            <h4 style="margin: 0 0 12px 0;">📋 How it works:</h4>
            <ol style="margin: 0; padding-left: 20px; color: #475569;">
              <li style="margin-bottom: 8px;">Place your order and receive order number</li>
              <li style="margin-bottom: 8px;">Transfer payment to our bank account above</li>
              <li style="margin-bottom: 8px;">Upload your payment receipt/confirmation</li>
              <li style="margin-bottom: 8px;">We verify payment (1-2 business days)</li>
              <li>Order is shipped with full tracking</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Order Summary -->
    <div>
      <div class="card" style="position: sticky; top: 120px;">
        <div class="card-header">
          <h3 class="card-title">Order Summary</h3>
        </div>
        <div class="card-body">
          <!-- Items -->
          <?php foreach ($cart as $item): ?>
          <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
            <div>
              <div style="font-weight: 500;"><?= htmlspecialchars($item['name']) ?></div>
              <div style="font-size: 0.85rem; color: #64748b;">Qty: <?= (int)$item['qty'] ?></div>
            </div>
            <div style="font-weight: 600;"><?= format_price($item['price'] * $item['qty']) ?></div>
          </div>
          <?php endforeach; ?>
          
          <!-- Totals -->
          <div class="cart-summary" style="margin-top: 16px;">
            <div class="cart-summary-row">
              <span>Subtotal</span>
              <span><?= format_price($total) ?></span>
            </div>
            <div class="cart-summary-row">
              <span>Shipping</span>
              <span>TBD</span>
            </div>
            <div class="cart-summary-row">
              <span>Tax (VAT 19%)</span>
              <span><?= format_price($total * 0.19) ?></span>
            </div>
            <div class="cart-summary-row total">
              <span>Total</span>
              <span><?= format_price($total * 1.19) ?></span>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary btn-lg btn-block">Place Order</button>
          <p style="margin: 12px 0 0 0; font-size: 0.85rem; color: #64748b; text-align: center;">
            By placing this order, you agree to our <a href="/terms">Terms & Conditions</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</form>
