<?php

namespace App\Filament\Resources\HouseholdMovementResource\Pages;

use App\Filament\Resources\HouseholdMovementResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHouseholdMovement extends EditRecord
{
    protected static string $resource = HouseholdMovementResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        parent::afterSave();

        HouseholdMovementResource::applyMovementEffects($this->record);
    }
}
