<?php

namespace App\Filament\Resources\RtOfficialResource\Pages;

use App\Filament\Resources\RtOfficialResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRtOfficials extends ListRecords
{
    protected static string $resource = RtOfficialResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
