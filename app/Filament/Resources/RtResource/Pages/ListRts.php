<?php

namespace App\Filament\Resources\RtResource\Pages;

use App\Filament\Resources\RtResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRts extends ListRecords
{
    protected static string $resource = RtResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
