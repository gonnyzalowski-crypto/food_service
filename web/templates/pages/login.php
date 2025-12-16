<div style="max-width: 450px; margin: 80px auto;">
  <div class="card">
    <div class="card-body" style="padding: 48px;">
      <div style="text-align: center; margin-bottom: 32px;">
        <div class="logo-icon" style="width: 64px; height: 64px; font-size: 2rem; margin: 0 auto 16px;">S</div>
        <h1 style="margin: 0 0 8px 0; font-size: 1.5rem;">Welcome Back</h1>
        <p style="margin: 0; color: #64748b;">Sign in to your Gordon Food Service account</p>
      </div>
      
      <?php if (!empty($error)): ?>
      <div class="alert alert-error mb-3">
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>
      
      <form action="/login" method="POST">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required placeholder="email@company.com">
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required placeholder="••••••••">
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" name="remember">
            <span style="font-size: 0.9rem;">Remember me</span>
          </label>
          <a href="/forgot-password" style="font-size: 0.9rem;">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Sign In</button>
      </form>
      
      <div style="text-align: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
        <p style="margin: 0; color: #64748b;">
          Don't have an account? <a href="/register">Create one</a>
        </p>
      </div>
    </div>
  </div>
  
  <div style="text-align: center; margin-top: 24px;">
    <a href="/admin/login" style="color: #64748b; font-size: 0.9rem;">Admin Login →</a>
  </div>
</div>
