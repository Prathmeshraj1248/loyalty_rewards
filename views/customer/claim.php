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
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<div class="mx-auto mt-4 max-w-md rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
  <button type="button" id="camBtn" class="w-full rounded-xl py-2.5 font-semibold text-indigo-600 ring-1 ring-indigo-200 transition hover:bg-indigo-50">Scan with camera</button>
  <div id="reader" class="mt-4"></div>
</div>
<script>
  const input = document.querySelector('input[name=code]');
  // A sticker QR opens /scan#CODE: prefill the box. The customer still presses Claim, and the SERVER decides.
  if (location.hash.length > 1) input.value = decodeURIComponent(location.hash.slice(1));
  document.getElementById('camBtn').onclick = () => {
    const scanner = new Html5Qrcode('reader');
    scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: 220 }, text => {
      input.value = text.includes('#') ? text.split('#').pop() : text;
      scanner.stop();
    }).catch(() => alert('Camera not available. Please type the code instead.'));
  };
</script>
