<?php $flash = \App\Http\Session::pullFlash(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $e($title ?? 'Loyalty Rewards') ?></title>
  <link rel="stylesheet" href="<?= $e($url('/assets/app.css')) ?>">
</head>
<body>
  <header class="topbar">
    <strong>Loyalty Rewards</strong>
    <?php if (\App\Http\Session::userId() !== null): ?>
      <form method="post" action="<?= $e($url('/logout')) ?>">
        <?= \App\Http\Csrf::field() ?>
        <button type="submit" class="link-button">Log out</button>
      </form>
    <?php endif; ?>
  </header>
  <main class="container">
    <?php if ($flash): ?>
      <div class="alert alert-<?= $e($flash['type']) ?>"><?= $e($flash['message']) ?></div>
    <?php endif; ?>
    <?= $content ?>
  </main>
</body>
</html>