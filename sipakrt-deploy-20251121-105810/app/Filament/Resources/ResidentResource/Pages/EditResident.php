<?php

namespace App\Filament\Resources\ResidentResource\Pages;

use App\Filament\Resources\ResidentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResident extends EditRecord
{
    protected static string $resource = ResidentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
