<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold">Notifikasi</h2>
            <p class="text-sm text-gray-500">Pemberitahuan terkait pesanan dan pembayaran Anda.</p>
        </div>
        @if ($notifications->total() > 0)
            <button type="button" wire:click="markAllAsRead"
                class="text-sm text-primary hover:underline">
                Tandai semua sudah dibaca
            </button>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            @php
                $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
                $isUnread = is_null($notification->read_at);
            @endphp
            <div class="p-4 rounded-lg border {{ $isUnread ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-200' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold {{ $isUnread ? 'text-blue-900' : 'text-gray-800' }}">
                            {{ $data['title'] ?? 'Notifikasi' }}
                        </h4>
                        <p class="text-sm {{ $isUnread ? 'text-blue-700' : 'text-gray-600' }} mt-1">
                            {{ $data['message'] ?? '' }}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($isUnread)
                            <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                                class="text-xs text-blue-600 hover:underline whitespace-nowrap">
                                Tandai dibaca
                            </button>
                            <span class="w-2 h-2 bg-blue-500 rounded-full shrink-0"></span>
                        @endif
                    </div>
                </div>

                @if (!empty($data['order_id']))
                    <a wire:navigate href="{{ route('user.dashboard', ['tab' => 'order-detail', 'order_id' => $data['order_id']]) }}"
                        class="inline-block mt-2 text-xs text-primary hover:underline">
                        Lihat Pesanan →
                    </a>
                @endif
            </div>
        @empty
            <div class="p-8 text-center border rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-gray-500">Belum ada notifikasi.</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
