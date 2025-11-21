<?php

namespace App\Filament\Resources\RtResource\Pages;

use App\Filament\Resources\RtResource;
use App\Models\Resident;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRt extends EditRecord
{
    protected static string $resource = RtResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return RtResource::populateHouseholdRepeater($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = RtResource::prepareHouseholdRepeater($data);

        if (! empty($data['leader_resident_id'])) {
            $data['leader_name'] = Resident::find($data['leader_resident_id'])?->name;
        }

        return $data;
    }
}
