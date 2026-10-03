<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h2 class="text-[22px] font-extrabold tracking-tight text-gray-900 dark:text-white">Dashboard</h2>
        <p class="mt-1 text-[13px] text-gray-500 dark:text-gray-400">
            Ringkasan aktivitas retail hari ini &bull; {{ $dateLabel }} &bull; {{ $outletCount }} outlet aktif
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="/pos" target="_blank"
           class="inline-flex items-center gap-1.5 rounded-lg bg-[#206bc4] px-4 py-2 text-[13px] font-semibold text-white shadow-sm transition hover:bg-[#1a569d]">
            <x-ti name="shopping-cart" class="h-4 w-4" />
            Penjualan Baru
        </a>
        <a href="{{ $exportSalesUrl }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-[#dfe3e8] bg-white px-4 py-2 text-[13px] font-semibold text-gray-700 shadow-sm transition hover:border-[#206bc4] hover:text-[#206bc4] dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
            <x-ti name="download" class="h-4 w-4" />
            Export Hari Ini
        </a>
        <a href="{{ $exportItemsUrl }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-[#dfe3e8] bg-white px-4 py-2 text-[13px] font-semibold text-gray-700 shadow-sm transition hover:border-[#206bc4] hover:text-[#206bc4] dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
            <x-ti name="receipt" class="h-4 w-4" />
            Export Item
        </a>
    </div>
</div>
