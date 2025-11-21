<?php

namespace App\Filament\Widgets;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Rt;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total RT', number_format(Rt::count()))
                ->description('Jumlah Rukun Tetangga terdaftar')
                ->icon('heroicon-o-home'),
            Stat::make('Total KK', number_format(Household::count()))
                ->description('Kartu Keluarga aktif')
                ->icon('heroicon-o-users'),
            Stat::make('Total Warga', number_format(Resident::count()))
                ->description('Penduduk terdata')
                ->icon('heroicon-o-user-group'),
        ];
    }
}
