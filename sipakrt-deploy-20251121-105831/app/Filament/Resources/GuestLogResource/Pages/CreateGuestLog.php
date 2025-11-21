<?php

namespace App\Filament\Resources\GuestLogResource\Pages;

use App\Filament\Resources\GuestLogResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGuestLog extends CreateRecord
{
    protected static string $resource = GuestLogResource::class;
}
