<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Services\WhatsAppService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class BroadcastWa extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Promo';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-message-circle';

    protected static ?string $title = 'Broadcast WhatsApp';

    protected string $view = 'filament.pages.broadcast-wa';

    public ?int $customerGroupId = null;

    public string $message = '';

    public function getGroupsProperty()
    {
        return CustomerGroup::orderBy('name')->get();
    }

    public function getRecipientCountProperty(): int
    {
        return $this->recipientQuery()->count();
    }

    protected function recipientQuery()
    {
        return Customer::query()
            ->where('active', true)
            ->whereNotNull('phone')
            ->when($this->customerGroupId, fn ($q) => $q->where('customer_group_id', $this->customerGroupId));
    }

    public function send(): void
    {
        if (blank($this->message)) {
            Notification::make()->title('Pesan masih kosong')->warning()->send();

            return;
        }

        $customers = $this->recipientQuery()->get();

        if ($customers->isEmpty()) {
            Notification::make()->title('Tidak ada customer yang cocok')->warning()->send();

            return;
        }

        $wa = new WhatsAppService;
        $sent = 0;

        foreach ($customers as $customer) {
            if ($wa->sendCustom($customer->phone, $this->message)) {
                $sent++;
            }
        }

        Notification::make()
            ->title("Broadcast terkirim ke {$sent} dari {$customers->count()} customer")
            ->success()
            ->send();

        $this->message = '';
    }
}
