<section class="rise">
  <div class="rounded-2xl bg-amber-50 p-5 ring-1 ring-amber-200 print:hidden">
    <p class="font-semibold text-amber-900">Batch #<?= $e($batchId) ?> created: <?= $e(count($items)) ?> codes for <?= $e($product) ?></p>
    <p class="mt-1 text-sm text-amber-800">These codes are shown only once. Print this page or choose "Save as PDF" in the print window now.</p>
    <button type="button" onclick="window.print()" class="mt-3 rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">Print / Save as PDF</button>
  </div>

  <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 print:mt-0 print:grid-cols-4">
    <?php foreach ($items as $item): ?>
      <div class="break-inside-avoid rounded-lg bg-white p-3 text-center ring-1 ring-slate-300">
        <img src="<?= $e($item['qr']) ?>" alt="QR code" class="mx-auto h-28 w-28">
        <p class="mt-1 font-mono text-xs font-semibold tracking-wide"><?= $e($item['code']) ?></p>
        <p class="truncate text-[10px] text-slate-400"><?= $e($product) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>