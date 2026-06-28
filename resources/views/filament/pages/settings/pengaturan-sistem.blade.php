<x-filament-panels::page>
    <form wire:submit="save">
        <div class="space-y-6">
            {{-- Identitas Usaha --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header px-6 py-4">
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Identitas Usaha</h3>
                </div>
                <div class="fi-section-content p-6 pt-0 grid gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Usaha</label>
                        <input type="text" wire:model="app_name" required maxlength="100" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Usaha</label>
                        <input type="text" wire:model="store_address" maxlength="255" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                        <p class="text-xs text-gray-500 mt-1">Tampil di struk cetak (opsional).</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Telepon Usaha</label>
                        <input type="text" wire:model="store_phone" maxlength="50" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                        <p class="text-xs text-gray-500 mt-1">Tampil di struk cetak (opsional).</p>
                    </div>
                </div>
            </div>

            {{-- Pengaturan Transaksi --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header px-6 py-4">
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Pengaturan Transaksi</h3>
                </div>
                <div class="fi-section-content p-6 pt-0 grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Persen Pajak (PPN)</label>
                        <input type="number" wire:model="tax_percent" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Mata Uang</label>
                        <input type="text" wire:model="currency" maxlength="10" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Threshold Approval (Rp)</label>
                        <input type="number" wire:model="approval_threshold" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                        <p class="text-xs text-gray-500 mt-1">Transaksi di atas nominal ini perlu approval manager.</p>
                    </div>
                </div>
            </div>

            {{-- Halaman Marketing --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header px-6 py-4">
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Halaman Marketing</h3>
                    <p class="text-sm text-gray-500">Konten yang tampil di halaman depan (landing page)</p>
                </div>
                <div class="fi-section-content p-6 pt-0 grid gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Headline Hero</label>
                        <input type="text" wire:model="hero_headline" maxlength="200" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                        <p class="text-xs text-gray-500 mt-1">Teks besar di bagian atas halaman depan.</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sub-headline Hero</label>
                        <input type="text" wire:model="hero_subheadline" maxlength="500" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                        <p class="text-xs text-gray-500 mt-1">Deskripsi singkat di bawah headline.</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nomor WhatsApp</label>
                        <input type="text" wire:model="whatsapp_number" maxlength="20" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                        <p class="text-xs text-gray-500 mt-1">Contoh: 6281296052010 (tanpa +). Muncul di CTA & button chat.</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Harga Source Code</label>
                        <input type="text" wire:model="pos_price" maxlength="50" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                        <p class="text-xs text-gray-500 mt-1">Harga yang tampil di popup jual source code dan PSEO.</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fitur di Popup</label>
                        <textarea wire:model="pos_features" rows="8" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Satu fitur per baris. Ditampilkan di popup jual source code (25 detik).</p>
                    </div>
                </div>
            </div>

            {{-- Struk --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header px-6 py-4">
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Struk / Nota</h3>
                    <p class="text-sm text-gray-500">Logo dan teks footer yang tampil di struk cetak</p>
                </div>
                <div class="fi-section-content p-6 pt-0 grid gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Logo Struk</label>
                        @if($currentLogo)
                        <div class="mb-2 flex items-center gap-2">
                            <img src="{{ $currentLogo }}" class="h-12 border rounded p-1">
                            <button type="button" wire:click="deleteLogo" wire:confirm="Hapus logo struk?" class="text-red-600 hover:text-red-800 text-xs underline">Hapus</button>
                        </div>
                        @endif
                        <input type="file" wire:model="logo" accept="image/png,image/jpg,image/jpeg,image/gif,image/svg+xml" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border text-sm">
                        <p class="text-xs text-gray-500 mt-1">Maks 2MB. Direkomendasikan lebar max 250px (PNG transparan).</p>
                        <div wire:loading wire:target="logo" class="text-xs text-blue-600 mt-1">Uploading...</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Teks Footer Struk</label>
                        <input type="text" wire:model="receipt_footer" maxlength="255" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white px-4 py-2.5 border">
                        <p class="text-xs text-gray-500 mt-1">Teks di bagian bawah struk (contoh: "Terima kasih telah berbelanja!").</p>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Opsi Cetak di Struk</label>
                        <p class="text-xs text-gray-500 mb-3">Pilih elemen mana yang ditampilkan saat struk dicetak.</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" wire:model="receipt_show_logo" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Logo Usaha
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" wire:model="receipt_show_name" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Nama Usaha
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" wire:model="receipt_show_address" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Alamat Usaha
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" wire:model="receipt_show_phone" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Telepon Usaha
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" wire:model="receipt_show_footer" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Teks Footer
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end">
                <button type="submit" wire:loading.attr="disabled" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg px-6 py-3 text-base shadow-sm bg-blue-600 text-white hover:bg-blue-500 dark:bg-blue-500 dark:hover:bg-blue-400">
                    <span wire:loading.remove>Simpan Pengaturan</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </div>
    </form>
</x-filament-panels::page>
