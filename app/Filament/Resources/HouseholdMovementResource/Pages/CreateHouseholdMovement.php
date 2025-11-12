<?php

namespace App\Filament\Resources\HouseholdMovementResource\Pages;

use App\Filament\Resources\HouseholdMovementResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateHouseholdMovement extends CreateRecord
{
    protected static string $resource = HouseholdMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['processed_by'])) {
            $data['processed_by'] = Auth::id();
        }

        if (HouseholdMovementResource::isWargaUser() && isset($data['household_id'])) {
            $data['processed_by'] = Auth::id();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        parent::afterCreate();

        HouseholdMovementResource::applyMovementEffects($this->record);
    }
}
