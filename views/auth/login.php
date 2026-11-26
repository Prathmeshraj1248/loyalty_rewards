<section class="rise mx-auto mt-10 max-w-md">
  <div class="mb-8 text-center">
    <h1 class="text-3xl font-bold text-slate-900">Welcome back</h1>
    <p class="mt-2 text-slate-500">Log in to see your reward coins.</p>
  </div>
  <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
    <?php if ($error): ?>
      <div class="mb-5 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800"><?= $e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= $e($url('/login')) ?>" class="space-y-5">
      <?= \App\Http\Csrf::field() ?>
      <label class="block text-sm font-medium text-slate-700">Email
        <input type="email" name="email" value="<?= $e($old['email'] ?? '') ?>" required autocomplete="email"
               class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
      </label>
      <label class="block text-sm font-medium text-slate-700">Password
        <input type="password" name="password" required autocomplete="current-password"
               class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
      </label>
      <button class="w-full rounded-xl bg-indigo-600 py-2.5 font-semibold text-white shadow-sm transition hover:bg-indigo-700">Log in</button>
    </form>
  </div>
  <p class="mt-6 text-center text-sm text-slate-500">New here?
    <a href="<?= $e($url('/register')) ?>" class="font-semibold text-indigo-600 hover:underline">Create an account</a>
  </p>
</section>