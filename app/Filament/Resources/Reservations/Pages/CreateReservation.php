<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $seq = Reservation::whereDate('created_at', today())->count() + 1;
        $data['reservation_number'] = 'RSV-' . date('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        $data['status'] = 'booked';

        $existing = Reservation::where('table_id', $data['table_id'])
            ->whereDate('reservation_date', $data['reservation_date'] ?? today())
            ->where('time_slot', $data['time_slot'])
            ->whereIn('status', ['booked', 'arrived'])
            ->exists();

        if ($existing) {
            Notification::make()
                ->title('Double Booking!')
                ->body('Meja sudah dipesan pada tanggal dan slot waktu yang sama.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
