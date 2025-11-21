<?php

namespace App\Filament\Resources\HouseholdResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class VehiclesRelationManager extends RelationManager
{
    protected static string $relationship = 'vehicles';

    protected static ?string $recordTitleAttribute = 'license_plate';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'Mobil' => 'Mobil',
                        'Motor' => 'Motor',
                        'Sepeda' => 'Sepeda',
                        'Pickup' => 'Pickup',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('brand')
                    ->label('Merek')
                    ->maxLength(120),
                Forms\Components\TextInput::make('model')
                    ->label('Model')
                    ->maxLength(120),
                Forms\Components\TextInput::make('license_plate')
                    ->label('Nomor Polisi')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                Forms\Components\TextInput::make('color')
                    ->label('Warna')
                    ->maxLength(60),
                Forms\Components\TextInput::make('year_of_purchase')
                    ->label('Tahun Beli')
                    ->numeric()
                    ->minValue(1980)
                    ->maxValue((int) now()->year),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif Digunakan')
                    ->default(true),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Merek')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Nomor Polisi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('year_of_purchase')
                    ->label('Tahun Beli')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
