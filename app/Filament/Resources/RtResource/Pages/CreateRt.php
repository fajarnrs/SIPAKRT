<?php

namespace App\Filament\Resources\RtResource\Pages;

use App\Filament\Resources\RtResource;
use App\Models\Resident;
use Filament\Resources\Pages\CreateRecord;

class CreateRt extends CreateRecord
{
    protected static string $resource = RtResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = RtResource::prepareHouseholdRepeater($data);

        if (! empty($data['leader_resident_id'])) {
            $data['leader_name'] = Resident::find($data['leader_resident_id'])?->name;
        }

        return $data;
    }
}
