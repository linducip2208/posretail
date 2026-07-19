<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use App\Models\Reservation;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $recordId = $this->record->id;

        $existing = Reservation::where('table_id', $data['table_id'])
            ->whereDate('reservation_date', $data['reservation_date'] ?? today())
            ->where('time_slot', $data['time_slot'])
            ->whereIn('status', ['booked', 'arrived'])
            ->where('id', '!=', $recordId)
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
