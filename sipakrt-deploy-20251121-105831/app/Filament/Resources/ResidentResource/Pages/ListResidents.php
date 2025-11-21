<?php

namespace App\Filament\Resources\ResidentResource\Pages;

use App\Filament\Resources\ResidentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResidents extends ListRecords
{
    protected static string $resource = ResidentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
