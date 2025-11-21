<?php

namespace App\Filament\Resources\AssistanceProgramResource\RelationManagers;

use App\Models\AssistanceRecipient;
use App\Models\Household;
use App\Models\Resident;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class RecipientsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipients';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('household_id')
                    ->label('Kartu Keluarga')
                    ->options(fn () => Household::query()->orderBy('family_card_number')->pluck('family_card_number', 'id')->toArray())
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('resident_id')
                    ->label('Anggota Keluarga')
                    ->options(fn () => Resident::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable(),
                Forms\Components\DatePicker::make('received_at')
                    ->label('Tanggal Terima'),
                Forms\Components\TextInput::make('amount')
                    ->label('Nominal')
                    ->numeric()
                    ->prefix('Rp')
                    ->rules(['nullable', 'numeric', 'min:0']),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'diajukan' => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'diterima' => 'Telah Diterima',
                    ])
                    ->default('diajukan')
                    ->required(),
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
                Tables\Columns\TextColumn::make('household.family_card_number')
                    ->label('No. KK')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('resident.name')
                    ->label('Nama Warga')
                    ->searchable(),
                Tables\Columns\TextColumn::make('received_at')
                    ->label('Tanggal Terima')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Rp ' . number_format((float) $state, 0, ',', '.') : '-')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'diajukan',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                        'primary' => 'diterima',
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
