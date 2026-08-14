<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="font-bold text-gray-800 mb-4">Pengaturan Toko</h2>

        <div class="space-y-6">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-6">
                <div>
                    <div class="font-semibold text-gray-900">Mode Restoran / F&B</div>
                    <p class="text-sm text-gray-500 mt-1">
                        Aktifkan jika toko Anda juga melayani dine-in (meja, reservasi, tiket dapur).<br>
                        Jika nonaktif, menu Meja, Area, Reservasi, dan Tiket Dapur disembunyikan.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" wire:model.live="restaurantEnabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div class="flex justify-end">
                <button wire:click="save"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>
</div>
