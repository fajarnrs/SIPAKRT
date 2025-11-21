<?php

namespace App\Filament\Resources\GuestLogResource\Pages;

use App\Filament\Resources\GuestLogResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuestLogs extends ListRecords
{
    protected static string $resource = GuestLogResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
