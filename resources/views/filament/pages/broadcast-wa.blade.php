<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="font-bold text-gray-800 mb-4">Broadcast WhatsApp ke Customer</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Segment Customer</label>
                <select wire:model.live="customerGroupId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Semua Customer</option>
                    @foreach($this->groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
                <textarea wire:model.live="message" rows="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                    placeholder="Tulis pesan broadcast..."></textarea>
                <p class="text-xs text-gray-400 mt-1">Gunakan placeholder: {nama} untuk nama customer.</p>
            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Penerima: <span class="font-bold text-indigo-600">{{ number_format($this->recipientCount, 0, ',', '.') }}</span> customer
                </div>
                <button wire:click="send"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12L3.27 3.13a59.77 59.77 0 0118.06 0L18 12m-12 0l-3 9 6.75-3.375M6 12l6 3.375L18 12m0 0l3-9M9 12l-6-3m6 3l9 3m-9-3L12 3"/></svg>
                    Kirim Broadcast
                </button>
            </div>
        </div>
    </div>

    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
        <strong>Catatan:</strong> Kirim hanya ke customer aktif yang punya nomor HP. Pastikan WhatsApp provider sudah dikonfigurasi di menu Integrasi → Provider.
    </div>
</div>
