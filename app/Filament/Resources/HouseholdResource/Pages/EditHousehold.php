<?php

namespace App\Filament\Resources\HouseholdResource\Pages;

use App\Filament\Resources\HouseholdResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHousehold extends EditRecord
{
    protected static string $resource = HouseholdResource::class;

    protected array $preparedHouseholdData = [];

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return HouseholdResource::populateHeadFields($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['residents'] = HouseholdResource::prepareResidentsForSave($data);
        $this->preparedHouseholdData = $data;
        unset($data['head_resident_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        HouseholdResource::syncHeadResident($this->record, $this->preparedHouseholdData);
    }
}
