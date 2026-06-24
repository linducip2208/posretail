<?php

namespace App\Filament\Pages\Settings;

use App\Models\SystemSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
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
            'whatsapp_number' => SystemSetting::getValue('whatsapp_number', '6281234567890'),
            'hero_headline' => SystemSetting::getValue('hero_headline', 'Solusi Kasir Modern untuk Toko Retail Anda'),
            'hero_subheadline' => SystemSetting::getValue('hero_subheadline', 'Kelola produk, transaksi penjualan, inventori, pelanggan, dan laporan — semua dalam satu dashboard.'),
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
                            ->helperText('Contoh: 6281234567890 (kode negara tanpa +). Muncul di CTA & button chat.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if ($value !== null) {
                SystemSetting::setValue($key, $value, $data['outlet_id'] ?? 1);
            }
        }

        Notification::make()
            ->title('Pengaturan disimpan!')
            ->success()
            ->send();
    }
}
