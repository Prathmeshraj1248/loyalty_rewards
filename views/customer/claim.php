<?php use App\Http\Csrf; ?>
<section class="rise mx-auto max-w-md">
  <h1 class="text-2xl font-bold text-slate-900">Claim your coins</h1>
  <p class="mt-1 text-slate-500">Enter the code printed under the QR sticker inside your product box.</p>

  <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form method="post" action="<?= $e($url('/claim')) ?>" class="space-y-4">
      <?= Csrf::field() ?>
      <label class="block text-sm font-medium text-slate-700">Product code
        <input type="text" name="code" required maxlength="30" autocomplete="off" autocapitalize="characters" spellcheck="false"
               placeholder="K7QM-3WPX-9TRD-H"
               class="mt-1.5 w-full rounded-xl border border-slate-300 px-3.5 py-2.5 font-mono uppercase tracking-wider outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
      </label>
      <button class="w-full rounded-xl bg-indigo-600 py-2.5 font-semibold text-white shadow-sm transition hover:bg-indigo-700">Claim coins</button>
    </form>
    <p class="mt-4 text-xs text-slate-400">Dashes and spaces do not matter. Each code can be used only once.</p>
  </div>
</section>
