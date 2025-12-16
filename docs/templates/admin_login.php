<?php
$error = $error ?? null;
?>
<h1>Admin Login</h1>

<?php if ($error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="/admin/login">
  <div class="form-group">
    <label for="email">E-Mail</label>
    <input type="email" id="email" name="email" required autocomplete="email">
  </div>
  <div class="form-group">
    <label for="password">Passwort</label>
    <input type="password" id="password" name="password" required autocomplete="current-password">
  </div>
  <button type="submit" class="btn-primary">Anmelden</button>
</form>
