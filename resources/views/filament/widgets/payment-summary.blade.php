<div class="rounded-lg border border-[#E2EAF4] bg-white p-5 shadow-sm" style="box-shadow:0 1px 3px rgba(28,63,104,0.05);border-top:3px solid #16A34A;">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-[15px] font-bold text-[#0B2854]"><span style="display:inline-flex;width:26px;height:26px;border-radius:8px;background:#E8F8F0;color:#16A34A;align-items:center;justify-content:center;"><x-ti name="wallet" class="h-4 w-4" /></span>Ringkasan Pembayaran</h3>
        <span class="rounded-full bg-[#E8F8F0] px-2.5 py-0.5 text-[11px] font-semibold text-[#16A34A]">Hari ini</span>
    </div>
    @if ($rows->isEmpty())
        <div class="py-6 text-center">
            <x-ti name="wallet" class="mx-auto mb-2 h-8 w-8 text-gray-300" />
            <p class="text-[13px] font-semibold text-gray-700 dark:text-gray-200">Belum ada pembayaran hari ini</p>
            <p class="mt-1 text-xs text-gray-500">Transaksi yang dibayar akan diringkas per metode di sini.</p>
        </div>
    @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($rows as $row)
                <li class="flex items-center justify-between py-2.5">
                    <div>
                        <div class="text-[13px] font-semibold text-gray-800 dark:text-gray-100">{{ $row->method }}</div>
                        <div class="text-xs text-gray-500">{{ $row->transactions }} transaksi</div>
                    </div>
                    <div class="font-mono text-[13px] font-bold text-gray-900 dark:text-white">Rp {{ number_format($row->total, 0, ',', '.') }}</div>
                </li>
            @endforeach
        </ul>
        <div class="mt-2 flex items-center justify-between border-t-2 border-[#16A34A] pt-3">
            <span class="text-[13px] font-bold text-gray-700 dark:text-gray-200">TOTAL</span>
            <span class="font-mono text-base font-extrabold text-[#0B2854]">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>
    @endif
</div>
