<?php

namespace App\Filament\Resources\AssistanceProgramResource\Pages;

use App\Filament\Resources\AssistanceProgramResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssistancePrograms extends ListRecords
{
    protected static string $resource = AssistanceProgramResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
