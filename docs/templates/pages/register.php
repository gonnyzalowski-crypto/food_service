<div style="max-width: 550px; margin: 48px auto;">
  <div class="card">
    <div class="card-body" style="padding: 48px;">
      <div style="text-align: center; margin-bottom: 32px;">
        <div class="logo-icon" style="width: 64px; height: 64px; font-size: 2rem; margin: 0 auto 16px;">S</div>
        <h1 style="margin: 0 0 8px 0; font-size: 1.5rem;">Create an Account</h1>
        <p style="margin: 0; color: #64748b;">Join Gordon Food Service to access exclusive B2B pricing</p>
      </div>
      
      <form action="/register" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div class="form-group">
            <label class="form-label">First Name *</label>
            <input type="text" name="first_name" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Last Name *</label>
            <input type="text" name="last_name" class="form-control" required>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Company Name *</label>
          <input type="text" name="company" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label class="form-label">Business Email *</label>
          <input type="email" name="email" class="form-control" required placeholder="email@company.com">
        </div>
        
        <div class="form-group">
          <label class="form-label">Phone Number *</label>
          <input type="tel" name="phone" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label class="form-label">Country *</label>
          <select name="country" class="form-control" required>
            <option value="">Select country</option>
            <option value="Germany">Germany</option>
            <option value="United States">United States</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="Netherlands">Netherlands</option>
            <option value="France">France</option>
            <option value="Other">Other</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
        </div>
        
        <div class="form-group">
          <label class="form-label">Confirm Password *</label>
          <input type="password" name="password_confirm" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label style="display: flex; align-items: start; gap: 8px; cursor: pointer;">
            <input type="checkbox" name="terms" required style="margin-top: 4px;">
            <span style="font-size: 0.9rem; color: #64748b;">
              I agree to the <a href="/terms">Terms & Conditions</a> and <a href="/privacy">Privacy Policy</a>
            </span>
          </label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg btn-block">Create Account</button>
      </form>
      
      <div style="text-align: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
        <p style="margin: 0; color: #64748b;">
          Already have an account? <a href="/login">Sign in</a>
        </p>
      </div>
    </div>
  </div>
</div>
