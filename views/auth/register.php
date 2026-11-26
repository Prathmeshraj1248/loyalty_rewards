<section class="rise mx-auto mt-6 max-w-md">
  <div class="mb-8 text-center">
    <h1 class="text-3xl font-bold text-slate-900">Create your account</h1>
    <p class="mt-2 text-slate-500">Scan the code in your product box and earn coins.</p>
  </div>
  <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
    <?php if ($errors): ?>
      <div class="mb-5 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <ul class="list-disc space-y-1 pl-5">
          <?php foreach ($errors as $error): ?><li><?= $e($error) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form method="post" action="<?= $e($url('/register')) ?>" class="space-y-5">
      <?= \App\Http\Csrf::field() ?>
      <?php
      $input = 'mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100';
      ?>
      <label class="block text-sm font-medium text-slate-700">Full name
        <input type="text" name="name" value="<?= $e($old['name'] ?? '') ?>" required maxlength="100" class="<?= $input ?>">
      </label>
      <label class="block text-sm font-medium text-slate-700">Email
        <input type="email" name="email" value="<?= $e($old['email'] ?? '') ?>" required maxlength="190" class="<?= $input ?>">
      </label>
      <label class="block text-sm font-medium text-slate-700">Password <span class="font-normal text-slate-400">(8 to 72 characters)</span>
        <input type="password" name="password" required minlength="8" maxlength="72" class="<?= $input ?>">
      </label>
      <label class="block text-sm font-medium text-slate-700">Confirm password
        <input type="password" name="password_confirm" required minlength="8" maxlength="72" class="<?= $input ?>">
      </label>
      <button class="w-full rounded-xl bg-indigo-600 py-2.5 font-semibold text-white shadow-sm transition hover:bg-indigo-700">Create account</button>
    </form>
  </div>
  <p class="mt-6 text-center text-sm text-slate-500">Already registered?
    <a href="<?= $e($url('/login')) ?>" class="font-semibold text-indigo-600 hover:underline">Log in</a>
  </p>
</section>