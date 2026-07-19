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
                SupplierRating::query()
                    ->select('supplier_id')
                    ->selectRaw('ROUND(AVG(on_time), 1) as avg_on_time')
                    ->selectRaw('ROUND(AVG(quality), 1) as avg_quality')
                    ->selectRaw('ROUND(AVG(price_competitiveness), 1) as avg_price')
                    ->selectRaw('ROUND(AVG(communication), 1) as avg_communication')
                    ->selectRaw('ROUND(AVG(avg_score), 1) as avg_overall')
                    ->selectRaw('COUNT(*) as total_ratings')
                    ->groupBy('supplier_id')
                    ->with('supplier')
            )
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),

                TextColumn::make('total_ratings')
                    ->label('Total Rating'),

                TextColumn::make('avg_on_time')
                    ->label('Ketepatan Waktu')
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state)),

                TextColumn::make('avg_quality')
                    ->label('Kualitas')
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state)),

                TextColumn::make('avg_price')
                    ->label('Daya Saing Harga')
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state)),

                TextColumn::make('avg_communication')
                    ->label('Komunikasi')
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state)),

                TextColumn::make('avg_overall')
                    ->label('Rata-rata')
                    ->formatStateUsing(fn (float $state): string => self::renderStars($state) . ' (' . $state . ')'),
            ])
            ->heading('Ringkasan Skor Supplier')
            ->emptyStateHeading('Belum ada rating')
            ->emptyStateDescription('Rating supplier akan muncul setelah ada penilaian.');
    }

    protected static function renderStars(float $score): string
    {
        $rounded = (int) round($score);
        $color = match (true) {
            $score >= 4 => '#16a34a',
            $score >= 3 => '#ca8a04',
            default => '#dc2626',
        };
        $out = '';
        for ($i = 1; $i <= 5; $i++) {
            $out .= $i <= $rounded
                ? '<span style="color: ' . $color . '">★</span>'
                : '<span style="color: #d1d5db">★</span>';
        }
        return $out;
    }

    protected static function isVisibleToRole(?string $role): bool
    {
        return in_array($role, ['owner', 'manager', 'admin']);
    }
}
