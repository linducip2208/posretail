<?php

namespace App\Filament\Pages\Settings;

use App\Models\SystemSetting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use UnitEnum;

class PengaturanSistem extends Page
{
    use WithFileUploads;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = '⚙️ Sistem';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Pengaturan Sistem';

    protected string $view = 'filament.pages.settings.pengaturan-sistem';

    public $app_name = '';
    public $tax_percent = '11';
    public $currency = 'IDR';
    public $receipt_footer = '';
    public $approval_threshold = '5000000';
    public $whatsapp_number = '6281296052010';
    public $hero_headline = '';
    public $hero_subheadline = '';
    public $pos_price = 'Rp 4.999.000';
    public $pos_features = '';
    public $outlet_id = '1';
    public $logo = null;
    public $currentLogo = null;
    public $store_address = '';
    public $store_phone = '';
    public bool $receipt_show_logo = true;
    public bool $receipt_show_name = true;
    public bool $receipt_show_address = true;
    public bool $receipt_show_phone = true;
    public bool $receipt_show_footer = true;

    public function mount(): void
    {
        $this->app_name = SystemSetting::getValue('app_name', 'POS Retail');
        $this->tax_percent = SystemSetting::getValue('tax_percent', '11');
        $this->currency = SystemSetting::getValue('currency', 'IDR');
        $this->receipt_footer = SystemSetting::getValue('receipt_footer', 'Terima kasih telah berbelanja!');
        $this->approval_threshold = SystemSetting::getValue('approval_threshold', '5000000');
        $this->whatsapp_number = SystemSetting::getValue('whatsapp_number', '6281296052010');
        $this->hero_headline = SystemSetting::getValue('hero_headline', 'Solusi Kasir Modern untuk Toko Retail Anda');
        $this->hero_subheadline = SystemSetting::getValue('hero_subheadline', 'Kelola produk, transaksi penjualan, inventori, pelanggan, dan laporan.');
        $this->pos_price = SystemSetting::getValue('pos_price', 'Rp 4.999.000');
        $this->pos_features = SystemSetting::getValue('pos_features', "Full source code — Laravel + Filament + TailwindCSS\n30+ admin resources\nPOS Kasir, Inventori, Pembelian, Loyalitas lengkap\nPayment gateway dinamis (Midtrans, Xendit, dll)\nCustomer portal, API v1, PSEO directory built-in\nMulti-outlet + Blog + IndexNow SEO\n52 tabel DB, approval workflow\nLifetime update + 6 bulan support");
        $this->outlet_id = SystemSetting::getValue('outlet_id', '1');
        $this->currentLogo = SystemSetting::getLogoUrl();
        $this->store_address = SystemSetting::getValue('store_address', '');
        $this->store_phone = SystemSetting::getValue('store_phone', '');
        $this->receipt_show_logo = SystemSetting::getBool('receipt_show_logo', true);
        $this->receipt_show_name = SystemSetting::getBool('receipt_show_name', true);
        $this->receipt_show_address = SystemSetting::getBool('receipt_show_address', true);
        $this->receipt_show_phone = SystemSetting::getBool('receipt_show_phone', true);
        $this->receipt_show_footer = SystemSetting::getBool('receipt_show_footer', true);
    }

    public function deleteLogo(): void
    {
        $oldLogo = SystemSetting::getValue('app_logo');
        if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
            Storage::disk('public')->delete($oldLogo);
        }
        SystemSetting::setValue('app_logo', '');
        $this->currentLogo = null;

        Notification::make()
            ->title('Logo berhasil dihapus!')
            ->success()
            ->send();
    }

    public function save(): void
    {
        if ($this->logo) {
            $this->validate([
                'logo' => 'image|mimes:png,jpg,jpeg,gif,svg|max:2048',
            ]);
            $oldLogo = SystemSetting::getValue('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $this->logo->store('settings', 'public');
            SystemSetting::setValue('app_logo', $path);
        }

        SystemSetting::setValue('app_name', (string) $this->app_name);
        SystemSetting::setValue('tax_percent', (string) $this->tax_percent);
        SystemSetting::setValue('currency', (string) $this->currency);
        SystemSetting::setValue('receipt_footer', (string) $this->receipt_footer);
        SystemSetting::setValue('approval_threshold', (string) $this->approval_threshold);
        SystemSetting::setValue('whatsapp_number', (string) $this->whatsapp_number);
        SystemSetting::setValue('hero_headline', (string) $this->hero_headline);
        SystemSetting::setValue('hero_subheadline', (string) $this->hero_subheadline);
        SystemSetting::setValue('pos_price', (string) $this->pos_price);
        SystemSetting::setValue('pos_features', (string) $this->pos_features);
        SystemSetting::setValue('store_address', (string) $this->store_address);
        SystemSetting::setValue('store_phone', (string) $this->store_phone);
        SystemSetting::setValue('receipt_show_logo', $this->receipt_show_logo ? '1' : '0');
        SystemSetting::setValue('receipt_show_name', $this->receipt_show_name ? '1' : '0');
        SystemSetting::setValue('receipt_show_address', $this->receipt_show_address ? '1' : '0');
        SystemSetting::setValue('receipt_show_phone', $this->receipt_show_phone ? '1' : '0');
        SystemSetting::setValue('receipt_show_footer', $this->receipt_show_footer ? '1' : '0');

        Notification::make()
            ->title('Pengaturan berhasil disimpan!')
            ->success()
            ->send();
    }
}
