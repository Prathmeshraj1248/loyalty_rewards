<section class="card">
  <h1>Log in</h1>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= $e($error) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= $e($url('/login')) ?>">
    <?= \App\Http\Csrf::field() ?>
    <label>Email
      <input type="email" name="email" value="<?= $e($old['email'] ?? '') ?>" required>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <button type="submit">Log in</button>
  </form>
  <p>New here? <a href="<?= $e($url('/register')) ?>">Create an account</a></p>
</section>