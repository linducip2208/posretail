<?php

namespace App\Filament\Widgets;

use App\Models\SupplierRating;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class SupplierScoreSummaryWidget extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '300s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Agregat per supplier sebagai subquery agar ORDER BY bawaan
                // Filament (by key) tetap valid di ONLY_FULL_GROUP_BY:
                // semua kolom order adalah output subquery.
                SupplierRating::query()->fromSub(
                    DB::table('supplier_ratings')
                        ->select('supplier_id')
                        ->selectRaw('MAX(id) as id')
                        ->selectRaw('ROUND(AVG(on_time), 1) as avg_on_time')
                        ->selectRaw('ROUND(AVG(quality), 1) as avg_quality')
                        ->selectRaw('ROUND(AVG(price_competitiveness), 1) as avg_price')
                        ->selectRaw('ROUND(AVG(communication), 1) as avg_communication')
                        ->selectRaw('ROUND(AVG(avg_score), 1) as avg_overall')
                        ->selectRaw('COUNT(*) as total_ratings')
                        ->groupBy('supplier_id'),
                    'supplier_ratings'
                )->with('supplier')
            )
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),

                TextColumn::make('total_ratings')
                    ->label('Total Rating'),

                TextColumn::make('avg_on_time')
                    ->label('Ketepatan Waktu')
                    ->html()
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state)),

                TextColumn::make('avg_quality')
                    ->label('Kualitas')
                    ->html()
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state)),

                TextColumn::make('avg_price')
                    ->label('Daya Saing Harga')
                    ->html()
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state)),

                TextColumn::make('avg_communication')
                    ->label('Komunikasi')
                    ->html()
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state)),

                TextColumn::make('avg_overall')
                    ->label('Rata-rata')
                    ->html()
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state) . ' (' . $state . ')'),
            ])
            ->heading('Ringkasan Skor Supplier')
            ->defaultSort('avg_overall', 'desc')
            ->emptyStateHeading('Belum ada rating')
            ->emptyStateDescription('Rating supplier akan muncul setelah ada penilaian.');
    }

    protected static function renderStars(float $score): string
    {
        // Bintang kompak (1 span, bukan 5 SVG) agar ringan & rapi.
        $filled = (int) round($score);
        $color = match (true) {
            $score >= 4 => 'text-[#2fb344]',
            $score >= 3 => 'text-[#f59f00]',
            default => 'text-[#d63939]',
        };
        $out = '<span class="' . $color . '" style="letter-spacing:2px;font-size:15px;line-height:1;white-space:nowrap;" aria-label="Skor ' . $score . ' dari 5">';
        for ($i = 1; $i <= 5; $i++) {
            $out .= $i <= $filled ? '★' : '<span class="text-slate-300">★</span>';
        }

        return $out . '</span>';
    }

    protected static function isVisibleToRole(?string $role): bool
    {
        return in_array($role, ['owner', 'manager', 'admin']);
    }
}
