<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Household;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['is_admin'] = ($data['role'] ?? null) === 'admin';

        if (($data['role'] ?? null) !== 'rt') {
            $data['rt_id'] = null;
        }

        if (($data['role'] ?? null) !== 'warga') {
            $data['household_id'] = null;
        }

        if (($data['role'] ?? null) === 'warga' && ! empty($data['household_id'])) {
            $data['rt_id'] = $data['rt_id'] ?? Household::find($data['household_id'])?->rt_id;
        }

        if (! array_key_exists('is_active', $data)) {
            $data['is_active'] = true;
        }

        if (($data['role'] ?? null) === 'admin') {
            $data['is_active'] = true;
        }

        return $data;
    }
}
