<?php
$billingAddress = json_decode($order['billing_address'] ?? '{}', true) ?: [];
$hasUploads = !empty($uploads);
$latestUpload = $hasUploads ? $uploads[0] : null;
?>

<div class="breadcrumb">
  <a href="/">Home</a> <span>/</span>
  <a href="/order/<?= $order['id'] ?>">Order <?= htmlspecialchars($order['order_number']) ?></a> <span>/</span>
  <span>Payment</span>
</div>

<div style="max-width: 900px; margin: 0 auto;">
  <div class="page-header text-center">
    <h1 class="page-title">Complete Your Payment</h1>
    <p class="page-subtitle">Order <?= htmlspecialchars($order['order_number']) ?></p>
  </div>
  
  <!-- Order Status -->
  <div class="alert alert-info mb-4">
    <div style="display: flex; align-items: center; gap: 12px;">
      <span style="font-size: 1.5rem;">📋</span>
      <div>
        <div class="alert-title">Order Status: <?= get_status_label($order['status']) ?></div>
        <p style="margin: 4px 0 0 0;">
          <?php if ($order['status'] === 'awaiting_payment'): ?>
            Please transfer the payment and upload your receipt below.
          <?php elseif ($order['status'] === 'payment_uploaded'): ?>
            Your payment receipt has been uploaded. We're reviewing it now.
          <?php elseif ($order['status'] === 'payment_confirmed'): ?>
            Payment confirmed! Your order is being processed.
          <?php endif; ?>
        </p>
      </div>
    </div>
  </div>
  
  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
    <!-- Bank Details -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Bank Transfer Details</h3>
      </div>
      <div class="card-body">
        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; font-family: monospace;">
          <div style="margin-bottom: 16px;">
            <div style="color: #64748b; font-size: 0.85rem;">Bank Name</div>
            <div style="font-weight: 600;">Deutsche Bank AG</div>
          </div>
          <div style="margin-bottom: 16px;">
            <div style="color: #64748b; font-size: 0.85rem;">Account Holder</div>
            <div style="font-weight: 600;">Streicher GmbH</div>
          </div>
          <div style="margin-bottom: 16px;">
            <div style="color: #64748b; font-size: 0.85rem;">IBAN</div>
            <div style="font-weight: 600;">DE89 3704 0044 0532 0130 00</div>
          </div>
          <div style="margin-bottom: 16px;">
            <div style="color: #64748b; font-size: 0.85rem;">BIC/SWIFT</div>
            <div style="font-weight: 600;">COBADEFFXXX</div>
          </div>
          <div>
            <div style="color: #64748b; font-size: 0.85rem;">Reference</div>
            <div style="font-weight: 600; color: #dc2626;"><?= htmlspecialchars($order['order_number']) ?></div>
          </div>
        </div>
        
        <div class="alert alert-warning mt-3" style="margin-bottom: 0;">
          <strong>Important:</strong> Please include the order number <strong><?= htmlspecialchars($order['order_number']) ?></strong> as the payment reference.
        </div>
      </div>
    </div>
    
    <!-- Order Summary -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Order Summary</h3>
      </div>
      <div class="card-body">
        <?php foreach ($items as $item): ?>
        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
          <div>
            <div style="font-weight: 500;"><?= htmlspecialchars($item['sku']) ?></div>
            <div style="font-size: 0.85rem; color: #64748b;">Qty: <?= (int)$item['qty'] ?></div>
          </div>
          <div style="font-weight: 600;"><?= format_price((float)$item['total']) ?></div>
        </div>
        <?php endforeach; ?>
        
        <div style="padding-top: 16px; margin-top: 8px; border-top: 2px solid #e2e8f0;">
          <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 700;">
            <span>Total Due</span>
            <span><?= format_price((float)$order['total']) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Upload Payment Receipt -->
  <div class="card mt-4">
    <div class="card-header">
      <h3 class="card-title">Upload Payment Receipt</h3>
    </div>
    <div class="card-body">
      <?php if ($order['status'] === 'payment_uploaded' || $order['status'] === 'payment_confirmed'): ?>
        <div class="alert alert-success">
          <div class="alert-title">Receipt Uploaded</div>
          <p style="margin: 4px 0 0 0;">
            <?php if ($latestUpload): ?>
              File: <?= htmlspecialchars($latestUpload['original_filename']) ?> 
              (uploaded <?= date('M j, Y g:i A', strtotime($latestUpload['created_at'])) ?>)
            <?php endif; ?>
          </p>
        </div>
        
        <?php if ($order['status'] === 'payment_uploaded'): ?>
        <p style="color: #64748b;">
          Your payment receipt is being reviewed. You'll receive an email once it's confirmed.
          You can also upload additional documents if needed.
        </p>
        <?php endif; ?>
      <?php endif; ?>
      
      <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error mb-3">
        <?php
        $errors = [
          'upload_failed' => 'Failed to upload file. Please try again.',
          'invalid_type' => 'Invalid file type. Please upload JPG, PNG, GIF, or PDF.',
          'save_failed' => 'Failed to save file. Please try again.',
        ];
        echo $errors[$_GET['error']] ?? 'An error occurred.';
        ?>
      </div>
      <?php endif; ?>
      
      <form action="/order/<?= $order['id'] ?>/payment" method="POST" enctype="multipart/form-data">
        <div class="payment-upload-zone" id="dropZone" onclick="document.getElementById('receiptFile').click()">
          <div class="upload-icon">📄</div>
          <div class="upload-text">Click to upload or drag and drop</div>
          <div class="upload-hint">JPG, PNG, GIF, or PDF (max 10MB)</div>
          <input type="file" name="receipt" id="receiptFile" accept=".jpg,.jpeg,.png,.gif,.pdf" style="display: none;" required>
        </div>
        
        <div id="filePreview" style="display: none; margin-top: 16px; padding: 16px; background: #f8fafc; border-radius: 8px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 2rem;">📎</span>
            <div>
              <div id="fileName" style="font-weight: 500;"></div>
              <div id="fileSize" style="font-size: 0.85rem; color: #64748b;"></div>
            </div>
          </div>
        </div>
        
        <div class="form-group mt-3">
          <label class="form-label">Notes (Optional)</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="Transaction ID, date of transfer, etc."></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">
          Upload Payment Receipt
        </button>
      </form>
    </div>
  </div>
  
  <!-- Previous Uploads -->
  <?php if ($hasUploads): ?>
  <div class="card mt-4">
    <div class="card-header">
      <h3 class="card-title">Uploaded Documents</h3>
    </div>
    <div class="card-body" style="padding: 0;">
      <table class="data-table">
        <thead>
          <tr>
            <th>File</th>
            <th>Uploaded</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($uploads as $upload): ?>
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 1.5rem;">📄</span>
                <div>
                  <div style="font-weight: 500;"><?= htmlspecialchars($upload['original_filename']) ?></div>
                  <div style="font-size: 0.85rem; color: #64748b;"><?= number_format($upload['file_size'] / 1024, 1) ?> KB</div>
                </div>
              </div>
            </td>
            <td><?= date('M j, Y g:i A', strtotime($upload['created_at'])) ?></td>
            <td>
              <span class="order-status-badge status-<?= $upload['status'] ?>">
                <?= ucfirst($upload['status']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
  
  <!-- Next Steps -->
  <div class="card mt-4">
    <div class="card-body">
      <h4 style="margin: 0 0 16px 0;">What happens next?</h4>
      <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;">
        <div style="text-align: center;">
          <div style="width: 48px; height: 48px; background: <?= $order['status'] !== 'awaiting_payment' ? '#dcfce7' : '#dbeafe' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.25rem;">
            <?= $order['status'] !== 'awaiting_payment' ? '✓' : '1' ?>
          </div>
          <div style="font-weight: 500;">Upload Receipt</div>
          <div style="font-size: 0.85rem; color: #64748b;">Submit payment proof</div>
        </div>
        <div style="text-align: center;">
          <div style="width: 48px; height: 48px; background: <?= in_array($order['status'], ['payment_confirmed', 'processing', 'shipped', 'delivered']) ? '#dcfce7' : '#f1f5f9' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.25rem;">
            <?= in_array($order['status'], ['payment_confirmed', 'processing', 'shipped', 'delivered']) ? '✓' : '2' ?>
          </div>
          <div style="font-weight: 500;">Verification</div>
          <div style="font-size: 0.85rem; color: #64748b;">We confirm payment</div>
        </div>
        <div style="text-align: center;">
          <div style="width: 48px; height: 48px; background: <?= in_array($order['status'], ['shipped', 'delivered']) ? '#dcfce7' : '#f1f5f9' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.25rem;">
            <?= in_array($order['status'], ['shipped', 'delivered']) ? '✓' : '3' ?>
          </div>
          <div style="font-weight: 500;">Shipping</div>
          <div style="font-size: 0.85rem; color: #64748b;">Order dispatched</div>
        </div>
        <div style="text-align: center;">
          <div style="width: 48px; height: 48px; background: <?= $order['status'] === 'delivered' ? '#dcfce7' : '#f1f5f9' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.25rem;">
            <?= $order['status'] === 'delivered' ? '✓' : '4' ?>
          </div>
          <div style="font-weight: 500;">Delivery</div>
          <div style="font-size: 0.85rem; color: #64748b;">Receive your order</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('receiptFile');
const filePreview = document.getElementById('filePreview');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
  dropZone.addEventListener(eventName, e => {
    e.preventDefault();
    e.stopPropagation();
  });
});

['dragenter', 'dragover'].forEach(eventName => {
  dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'));
});

['dragleave', 'drop'].forEach(eventName => {
  dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'));
});

dropZone.addEventListener('drop', e => {
  const files = e.dataTransfer.files;
  if (files.length) {
    fileInput.files = files;
    showPreview(files[0]);
  }
});

fileInput.addEventListener('change', e => {
  if (e.target.files.length) {
    showPreview(e.target.files[0]);
  }
});

function showPreview(file) {
  fileName.textContent = file.name;
  fileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
  filePreview.style.display = 'block';
}
</script>
