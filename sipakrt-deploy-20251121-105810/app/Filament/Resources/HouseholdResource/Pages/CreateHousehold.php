<?php

namespace App\Filament\Resources\HouseholdResource\Pages;

use App\Filament\Resources\HouseholdResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHousehold extends CreateRecord
{
    protected static string $resource = HouseholdResource::class;

    protected array $preparedHouseholdData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['residents'] = HouseholdResource::prepareResidentsForSave($data);
        $this->preparedHouseholdData = $data;
        unset($data['head_resident_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        HouseholdResource::syncHeadResident($this->record, $this->preparedHouseholdData);
    }
}
