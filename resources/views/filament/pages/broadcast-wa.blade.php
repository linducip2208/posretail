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
                    Penerima: <span class="font-bold text-[#206bc4]">{{ number_format($this->recipientCount, 0, ',', '.') }}</span> customer
                </div>
                <button wire:click="send"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#2fb344] hover:bg-[#268f36] rounded-lg transition-colors shadow-sm">
                    <x-ti name="speakerphone" class="w-4 h-4" />
                    Kirim Broadcast
                </button>
            </div>
        </div>
    </div>

    <div class="mt-4 bg-[#f59f00]/12 border border-[#ffe1a8]/25 rounded-lg p-4 text-sm text-[#a86e00]">
        <strong>Catatan:</strong> Kirim hanya ke customer aktif yang punya nomor HP. Pastikan WhatsApp provider sudah dikonfigurasi di menu Integrasi → Provider.
    </div>
</div>
