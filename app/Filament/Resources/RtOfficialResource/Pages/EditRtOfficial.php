<?php

namespace App\Filament\Resources\RtOfficialResource\Pages;

use App\Filament\Resources\RtOfficialResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRtOfficial extends EditRecord
{
    protected static string $resource = RtOfficialResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
