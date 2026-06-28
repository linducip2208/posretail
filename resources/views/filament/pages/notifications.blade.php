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
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
            <x-filament::icon icon="heroicon-o-check-badge" class="w-4 h-4" />
            Tandai Semua Dibaca
        </button>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($this->notifications as $notification)
        @php
            $icon = match(true) {
                str_contains($notification->type, 'Order') => 'heroicon-o-shopping-bag',
                str_contains($notification->type, 'Stock') => 'heroicon-o-archive-box',
                str_contains($notification->type, 'Purchase') => 'heroicon-o-truck',
                str_contains($notification->type, 'Payment') => 'heroicon-o-credit-card',
                str_contains($notification->type, 'Customer') => 'heroicon-o-user-group',
                str_contains($notification->type, 'Product') => 'heroicon-o-tag',
                default => 'heroicon-o-bell-alert',
            };
        @endphp
        <div @class([
            'bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-5',
            'border-blue-300 dark:border-blue-700 border-l-4' => is_null($notification->read_at),
            'border-gray-100 dark:border-gray-700' => !is_null($notification->read_at),
        ])>
            <div class="flex items-start gap-4">
                <div class="mt-0.5 flex-shrink-0">
                    <x-filament::icon :icon="$icon" @class([
                        'w-5 h-5',
                        'text-blue-500' => is_null($notification->read_at),
                        'text-gray-400 dark:text-gray-500' => !is_null($notification->read_at),
                    ]) />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            {{ class_basename($notification->type) }}
                        </span>
                        @if(is_null($notification->read_at))
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">
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
                            class="text-xs text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 font-medium transition-colors">
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
            <x-filament::icon icon="heroicon-o-bell-slash" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
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
