<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Notifikasi</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $this->unreadCount }} belum dibaca dari {{ $this->totalCount }} notifikasi
            </p>
        </div>
        @if($this->unreadCount > 0)
        <button wire:click="markAllRead" wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#206bc4] hover:bg-[#206bc4] text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
            <x-filament::icon icon="tabler-circle-check" class="w-4 h-4" />
            Tandai Semua Dibaca
        </button>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($this->notifications as $notification)
        @php
            $icon = match(true) {
                str_contains($notification->type, 'Order') => 'tabler-shopping-bag',
                str_contains($notification->type, 'Stock') => 'tabler-archive',
                str_contains($notification->type, 'Purchase') => 'tabler-truck',
                str_contains($notification->type, 'Payment') => 'tabler-credit-card',
                str_contains($notification->type, 'Customer') => 'tabler-users',
                str_contains($notification->type, 'Product') => 'tabler-tag',
                default => 'tabler-bell',
            };
        @endphp
        <div @class([
            'bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-5',
            'border-[#7fb3e8] dark:border-[#1a569d] border-l-4' => is_null($notification->read_at),
            'border-gray-100 dark:border-gray-700' => !is_null($notification->read_at),
        ])>
            <div class="flex items-start gap-4">
                <div class="mt-0.5 flex-shrink-0">
                    <x-filament::icon :icon="$icon" @class([
                        'w-5 h-5',
                        'text-[#206bc4]' => is_null($notification->read_at),
                        'text-gray-400 dark:text-gray-500' => !is_null($notification->read_at),
                    ]) />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ class_basename($notification->type) }}
                        </span>
                        @if(is_null($notification->read_at))
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-[#206bc4]/10 dark:bg-blue-900 text-[#1a569d] dark:text-[#7fb3e8]">
                            Baru
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                        @php $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true); @endphp
                        {{ $data['message'] ?? $data['title'] ?? json_encode($data) }}
                    </p>
                    @if(!empty($data['body']) && isset($data['message']))
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $data['body'] }}</p>
                    @endif
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                        @if(is_null($notification->read_at))
                        <button wire:click="markAsRead('{{ $notification->id }}')"
                            class="text-xs text-[#206bc4] hover:text-[#206bc4] dark:text-[#5b9bd9] dark:hover:text-[#7fb3e8] font-medium transition-colors">
                            Tandai Dibaca
                        </button>
                        @else
                        <span class="text-xs text-gray-400 dark:text-gray-500">Dibaca {{ $notification->read_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
            <x-filament::icon icon="tabler-bell" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
            <p class="text-gray-500 dark:text-gray-400 font-medium">Belum ada notifikasi</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Notifikasi akan muncul di sini saat ada aktivitas baru</p>
        </div>
        @endforelse
    </div>

    @if($this->notifications->hasPages())
    <div class="mt-6">
        {{ $this->notifications->links() }}
    </div>
    @endif
</div>
