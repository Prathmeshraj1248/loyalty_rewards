<?php use App\Http\Csrf; ?>
<section class="rise">
  <h1 class="text-2xl font-bold text-slate-900">Customers</h1>

  <form method="get" action="<?= $e($url('/admin/customers')) ?>" class="mt-6 flex gap-3">
    <input type="search" name="q" value="<?= $e($search) ?>" placeholder="Search by name or email" maxlength="100"
           class="w-full max-w-sm rounded-xl border border-slate-300 px-3 py-2 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
    <button class="rounded-xl bg-indigo-600 px-5 py-2 font-semibold text-white transition hover:bg-indigo-700">Search</button>
  </form>

  <div class="mt-6 rounded-2xl bg-white p-2 shadow-sm ring-1 ring-slate-200 sm:p-6">
    <?php if (!$customers): ?>
      <div class="py-12 text-center">
        <p class="font-medium text-slate-700"><?= $search !== '' ? 'No customers match your search' : 'No customers yet' ?></p>
        <p class="mt-1 text-sm text-slate-500">Customers appear here after they register.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="text-xs uppercase tracking-wide text-slate-400">
            <tr><th class="px-3 py-2">Customer</th><th class="px-3 py-2">Joined</th><th class="px-3 py-2 text-right">Claims</th>
                <th class="px-3 py-2 text-right">Balance</th><th class="px-3 py-2">Status</th><th class="px-3 py-2"></th></tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($customers as $c): ?>
              <tr>
                <td class="px-3 py-3"><p class="font-medium"><?= $e($c['name']) ?></p><p class="text-xs text-slate-400"><?= $e($c['email']) ?></p></td>
                <td class="whitespace-nowrap px-3 py-3 text-slate-500"><?= $e(date('d M Y', strtotime($c['created_at']))) ?></td>
                <td class="px-3 py-3 text-right"><?= $e((int) $c['claims']) ?></td>
                <td class="px-3 py-3 text-right font-semibold"><?= $e(number_format((int) $c['balance'])) ?></td>
                <td class="px-3 py-3">
                  <span class="rounded-full px-2.5 py-1 text-xs font-semibold <?= $c['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>"><?= $e(ucfirst($c['status'])) ?></span>
                </td>
                <td class="px-3 py-3 text-right">
                  <form method="post" action="<?= $e($url('/admin/customers/toggle')) ?>">
                    <?= Csrf::field() ?><input type="hidden" name="user_id" value="<?= $e($c['id']) ?>">
                    <button class="text-xs font-semibold <?= $c['status'] === 'active' ? 'text-rose-600' : 'text-emerald-600' ?> hover:underline"><?= $c['status'] === 'active' ? 'Block' : 'Unblock' ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>