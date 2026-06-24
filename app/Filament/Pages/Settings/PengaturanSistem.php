<?php

namespace App\Filament\Pages\Settings;

use App\Models\SystemSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PengaturanSistem extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = '⚙️ Sistem';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Pengaturan Sistem';

    protected string $view = 'filament.pages.settings.pengaturan-sistem';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'app_name' => SystemSetting::getValue('app_name', 'POS Retail'),
            'app_logo' => SystemSetting::getValue('app_logo'),
            'login_illustration' => SystemSetting::getValue('login_illustration'),
            'tax_percent' => SystemSetting::getValue('tax_percent', '11'),
            'currency' => SystemSetting::getValue('currency', 'IDR'),
            'receipt_footer' => SystemSetting::getValue('receipt_footer', 'Terima kasih telah berbelanja!'),
            'approval_threshold' => SystemSetting::getValue('approval_threshold', '5000000'),
            'whatsapp_number' => SystemSetting::getValue('whatsapp_number', '6281296052010'),
            'hero_headline' => SystemSetting::getValue('hero_headline', 'Solusi Kasir Modern untuk Toko Retail Anda'),
            'hero_subheadline' => SystemSetting::getValue('hero_subheadline', 'Kelola produk, transaksi penjualan, inventori, pelanggan, dan laporan — semua dalam satu dashboard.'),
            'pos_price' => SystemSetting::getValue('pos_price', 'Rp 4.999.000'),
            'pos_features' => SystemSetting::getValue('pos_features', "Full source code — Laravel + Filament + TailwindCSS\n30+ admin resources, 3 dashboard report pages\nPOS Kasir, Inventori, Pembelian, Loyalitas lengkap\nPayment gateway dinamis (Midtrans, Xendit, dll)\nCustomer portal, API v1, PSEO directory built-in\nMulti-outlet + Blog + IndexNow SEO\n52 tabel DB, approval workflow\nLifetime update + 6 bulan support"),
            'outlet_id' => SystemSetting::getValue('outlet_id', '1'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identitas Usaha')
                    ->schema([
                        TextInput::make('app_name')
                            ->label('Nama Usaha')
                            ->required()
                            ->maxLength(100),
                        FileUpload::make('app_logo')
                            ->label('Logo Usaha')
                            ->image()
                            ->imageEditor()
                            ->directory('logo')
                            ->maxSize(2048)
                            ->helperText('Upload logo usaha. Format: PNG, JPG. Maks 2MB.'),
                        FileUpload::make('login_illustration')
                            ->label('Ilustrasi Halaman Login')
                            ->image()
                            ->imageEditor()
                            ->directory('login-illustration')
                            ->maxSize(5120)
                            ->helperText('Gambar ilustrasi di panel kiri halaman login admin. Format: PNG, JPG. Maks 5MB.'),
                    ]),
                Section::make('Pengaturan Transaksi')
                    ->schema([
                        TextInput::make('tax_percent')
                            ->label('Persen Pajak (PPN)')
                            ->numeric()
                            ->suffix('%')
                            ->default(11),
                        TextInput::make('currency')
                            ->label('Mata Uang')
                            ->default('IDR')
                            ->maxLength(10),
                        TextInput::make('approval_threshold')
                            ->label('Threshold Approval (Rp)')
                            ->numeric()
                            ->helperText('Transaksi di atas nominal ini perlu approval manager.'),
                    ]),
                Section::make('Struk / Nota')
                    ->schema([
                        TextInput::make('receipt_footer')
                            ->label('Teks Footer Struk')
                            ->maxLength(255),
                    ]),
                Section::make('Halaman Marketing')
                    ->description('Konten yang tampil di halaman depan (landing page)')
                    ->schema([
                        TextInput::make('hero_headline')
                            ->label('Headline Hero')
                            ->maxLength(200)
                            ->helperText('Teks besar di bagian atas halaman depan.'),
                        TextInput::make('hero_subheadline')
                            ->label('Sub-headline Hero')
                            ->maxLength(500)
                            ->helperText('Deskripsi singkat di bawah headline.'),
                        TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->maxLength(20)
                            ->helperText('Contoh: 6281296052010 (kode negara tanpa +). Muncul di CTA & button chat.'),
                        TextInput::make('pos_price')
                            ->label('Harga Source Code')
                            ->maxLength(50)
                            ->default('Rp 4.999.000')
                            ->helperText('Harga yang tampil di popup jual source code dan PSEO.'),
                        Textarea::make('pos_features')
                            ->label('Fitur di Popup')
                            ->rows(8)
                            ->helperText('Satu fitur per baris. Ditampilkan di popup jual source code (25 detik).'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if ($key === 'outlet_id') continue;
            if ($value !== null && $value !== '') {
                SystemSetting::setValue($key, $value, $data['outlet_id'] ?? 1);
            }
        }

        Notification::make()
            ->title('Pengaturan disimpan!')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save'),
        ];
    }
}
