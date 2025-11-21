<?php

namespace App\Filament\Resources\HouseholdMovementResource\Pages;

use App\Filament\Resources\HouseholdMovementResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListHouseholdMovements extends ListRecords
{
    protected static string $resource = HouseholdMovementResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if ($user && $user->role === 'warga') {
            return [
                Actions\CreateAction::make('recordMove')
                    ->label('Catat Pindah')
                    ->icon('heroicon-o-arrow-right')
                    ->form(HouseholdMovementResource::movementFormSchema())
                    ->mutateFormDataUsing(fn (array $data): array => array_merge($data, ['type' => 'pindah_keluar'])),
                Actions\CreateAction::make('recordDeath')
                    ->label('Catat Meninggal')
                    ->color('danger')
                    ->icon('heroicon-o-exclamation')
                    ->form(HouseholdMovementResource::movementFormSchema())
                    ->mutateFormDataUsing(fn (array $data): array => array_merge($data, ['type' => 'meninggal'])),
            ];
        }

        return [
            Actions\CreateAction::make(),
        ];
    }
}
