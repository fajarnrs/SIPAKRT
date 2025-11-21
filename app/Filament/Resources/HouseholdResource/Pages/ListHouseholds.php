<?php

namespace App\Filament\Resources\HouseholdResource\Pages;

use App\Filament\Resources\HouseholdResource;
use App\Exports\HouseholdsExport;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListHouseholds extends ListRecords
{
    protected static string $resource = HouseholdResource::class;

    protected function getTitle(): string
    {
        return 'Daftar KK';
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export')
                ->label('Export Semua ke Excel')
                ->icon('heroicon-o-download')
                ->color('success')
                ->action(function () {
                    // Ambil query dengan filter yang aktif
                    $query = $this->getFilteredTableQuery();
                    $query->with(['rt', 'residents']);
                    
                    return Excel::download(
                        new HouseholdsExport($query),
                        'data-kk-semua-' . now()->format('Y-m-d-His') . '.xlsx'
                    );
                }),
        ];
    }
}
