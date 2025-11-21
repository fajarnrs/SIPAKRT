<?php

namespace App\Filament\Resources\RtResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class HouseholdsRelationManager extends RelationManager
{
    protected static string $relationship = 'households';

    protected static ?string $recordTitleAttribute = 'family_card_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('family_card_number')
                    ->label('No. Kartu Keluarga')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('head_name')
                    ->label('Nama Kepala Keluarga')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address')
                    ->label('Alamat')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('issued_at')
                    ->label('Tanggal Terbit KK'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('family_card_number')
                    ->label('No. KK')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('head_name')
                    ->label('Kepala Keluarga')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('residents_count')
                    ->label('Jumlah Anggota')
                    ->counts('residents')
                    ->sortable(),
                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Terbit')
                    ->date('d M Y')
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
