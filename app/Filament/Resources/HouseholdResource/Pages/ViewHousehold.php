<?php

namespace App\Filament\Resources\HouseholdResource\Pages;

use App\Filament\Resources\HouseholdResource;
use Filament\Resources\Pages\ViewRecord;

class ViewHousehold extends ViewRecord
{
    protected static string $resource = HouseholdResource::class;

    protected function getActions(): array
    {
        return [
            \Filament\Pages\Actions\EditAction::make(),
        ];
    }
}
