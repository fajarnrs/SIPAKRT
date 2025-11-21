<?php

namespace App\Filament\Resources\HouseholdResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $recordTitleAttribute = 'type';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Jenis Peristiwa')
                    ->options([
                        'pindah_masuk' => 'Pindah Masuk',
                        'pindah_keluar' => 'Pindah Keluar',
                        'meninggal' => 'Meninggal Dunia',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('event_date')
                    ->label('Tanggal')
                    ->required(),
                Forms\Components\TextInput::make('destination')
                    ->label('Tujuan / Asal')
                    ->maxLength(255),
                Forms\Components\Select::make('processed_by')
                    ->label('Diproses Oleh')
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\Textarea::make('details')
                    ->label('Rincian')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->enum([
                        'pindah_masuk' => 'Pindah Masuk',
                        'pindah_keluar' => 'Pindah Keluar',
                        'meninggal' => 'Meninggal',
                        'lainnya' => 'Lainnya',
                    ])
                    ->colors([
                        'success' => 'pindah_masuk',
                        'warning' => 'pindah_keluar',
                        'danger' => 'meninggal',
                        'primary' => 'lainnya',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('destination')
                    ->label('Tujuan/Asal')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Petugas')
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->enum([
                        'draft' => 'Draft',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                    ])
                    ->colors([
                        'warning' => 'draft',
                        'primary' => 'diproses',
                        'success' => 'selesai',
                    ])
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
