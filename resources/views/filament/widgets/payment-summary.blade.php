<div class="rounded-lg border border-[#dfe3e8] bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-[15px] font-bold text-gray-900 dark:text-white">Ringkasan Pembayaran</h3>
        <span class="rounded bg-[#e7f1fb] px-2 py-0.5 text-[11px] font-semibold text-[#206bc4]">Hari ini</span>
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
        <div class="mt-2 flex items-center justify-between border-t-2 border-[#206bc4] pt-3">
            <span class="text-[13px] font-bold text-gray-700 dark:text-gray-200">TOTAL</span>
            <span class="font-mono text-base font-extrabold text-[#1a569d]">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>
    @endif
</div>
