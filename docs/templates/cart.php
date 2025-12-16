<div class="breadcrumb">
  <a href="/">Home</a> <span>/</span>
  <span>Shopping Cart</span>
</div>

<div class="page-header">
  <h1 class="page-title">Shopping Cart</h1>
  <p class="page-subtitle"><?= count($cart) ?> item<?= count($cart) !== 1 ? 's' : '' ?> in your cart</p>
</div>

<?php if (empty($cart)): ?>
<div class="card">
  <div class="card-body text-center" style="padding: 64px;">
    <div style="font-size: 4rem; margin-bottom: 16px;">🛒</div>
    <h3>Your cart is empty</h3>
    <p style="color: #64748b;">Browse our catalog to find the equipment you need.</p>
    <a href="/catalog" class="btn btn-primary mt-3">Browse Products</a>
  </div>
</div>
<?php else: ?>
<div style="display: grid; grid-template-columns: 1fr 400px; gap: 32px;">
  <!-- Cart Items -->
  <div class="card">
    <table class="cart-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Total</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="cartItems">
        <?php foreach ($cart as $index => $item): ?>
        <tr data-sku="<?= htmlspecialchars($item['sku']) ?>">
          <td>
            <div style="display: flex; align-items: center; gap: 16px;">
              <div class="cart-item-image" style="display: flex; align-items: center; justify-content: center; font-size: 2rem;">⚙️</div>
              <div>
                <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="cart-item-sku">SKU: <?= htmlspecialchars($item['sku']) ?></div>
              </div>
            </div>
          </td>
          <td class="cart-item-price"><?= format_price($item['price']) ?></td>
          <td>
            <input type="number" value="<?= (int)$item['qty'] ?>" min="1" class="qty-input" style="width: 80px;" onchange="updateQuantity('<?= htmlspecialchars($item['sku']) ?>', this.value)">
          </td>
          <td class="cart-item-price item-total"><?= format_price($item['price'] * $item['qty']) ?></td>
          <td>
            <button onclick="removeItem('<?= htmlspecialchars($item['sku']) ?>')" class="btn btn-sm" style="background: #fee2e2; color: #991b1b;">✕</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  
  <!-- Order Summary -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Order Summary</h3>
      </div>
      <div class="card-body">
        <div class="cart-summary">
          <div class="cart-summary-row">
            <span>Subtotal</span>
            <span id="subtotal"><?= format_price($total) ?></span>
          </div>
          <div class="cart-summary-row">
            <span>Shipping</span>
            <span>Calculated at checkout</span>
          </div>
          <div class="cart-summary-row">
            <span>Tax (VAT)</span>
            <span>Calculated at checkout</span>
          </div>
          <div class="cart-summary-row total">
            <span>Estimated Total</span>
            <span id="total"><?= format_price($total) ?></span>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <a href="/checkout" class="btn btn-primary btn-lg btn-block">Proceed to Checkout</a>
        <a href="/catalog" class="btn btn-outline btn-block mt-2">Continue Shopping</a>
      </div>
    </div>
    
    <!-- Payment Methods -->
    <div class="card mt-3">
      <div class="card-body">
        <h4 style="margin: 0 0 12px 0; font-size: 0.9rem;">Payment Methods</h4>
        <p style="margin: 0; color: #64748b; font-size: 0.85rem;">
          We accept bank transfers and wire payments. After placing your order, you'll receive payment instructions and can upload your payment receipt for verification.
        </p>
      </div>
    </div>
    
    <!-- Need Help -->
    <div class="card mt-3">
      <div class="card-body">
        <h4 style="margin: 0 0 12px 0; font-size: 0.9rem;">Need Help?</h4>
        <p style="margin: 0 0 12px 0; color: #64748b; font-size: 0.85rem;">
          Our sales team is available 24/7 to assist you.
        </p>
        <div style="font-size: 0.9rem;">
          ✉️ store@Gordon Food Servicegmbh.com
        </div>
      </div>
    </div>
  </div>
</div>

<script>
async function updateQuantity(sku, qty) {
  qty = parseInt(qty);
  if (qty < 1) qty = 1;
  
  const res = await fetch('/api/cart', {
    method: 'PUT',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({sku, qty})
  });
  
  if (res.ok) {
    const data = await res.json();
    document.getElementById('subtotal').textContent = '$' + data.total.toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('total').textContent = '$' + data.total.toLocaleString('en-US', {minimumFractionDigits: 2});
    
    // Update item total
    const row = document.querySelector(`tr[data-sku="${sku}"]`);
    if (row) {
      const price = parseFloat(row.querySelector('.cart-item-price').textContent.replace(/[$,]/g, ''));
      row.querySelector('.item-total').textContent = '$' + (price * qty).toLocaleString('en-US', {minimumFractionDigits: 2});
    }
    
    // Update cart count
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
      cartCount.textContent = data.cart_count;
    }
  }
}

async function removeItem(sku) {
  const res = await fetch('/api/cart', {
    method: 'DELETE',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({sku})
  });
  
  if (res.ok) {
    location.reload();
  }
}
</script>
<?php endif; ?>
