<section class="card">
  <h1>Create your account</h1>
  <?php if ($errors): ?>
    <div class="alert alert-error"><ul>
      <?php foreach ($errors as $error): ?><li><?= $e($error) ?></li><?php endforeach; ?>
    </ul></div>
  <?php endif; ?>
  <form method="post" action="<?= $e($url('/register')) ?>">
    <?= \App\Http\Csrf::field() ?>
    <label>Full name
      <input type="text" name="name" value="<?= $e($old['name'] ?? '') ?>" required maxlength="100">
    </label>
    <label>Email
      <input type="email" name="email" value="<?= $e($old['email'] ?? '') ?>" required maxlength="190">
    </label>
    <label>Password (8 to 72 characters)
      <input type="password" name="password" required minlength="8" maxlength="72">
    </label>
    <label>Confirm password
      <input type="password" name="password_confirm" required minlength="8" maxlength="72">
    </label>
    <button type="submit">Create account</button>
  </form>
  <p>Already registered? <a href="<?= $e($url('/login')) ?>">Log in</a></p>
</section>