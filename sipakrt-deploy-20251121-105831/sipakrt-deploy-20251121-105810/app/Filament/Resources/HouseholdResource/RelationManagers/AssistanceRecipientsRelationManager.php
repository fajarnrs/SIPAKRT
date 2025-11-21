<?php

namespace App\Filament\Resources\HouseholdResource\RelationManagers;

use App\Models\AssistanceProgram;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class AssistanceRecipientsRelationManager extends RelationManager
{
    protected static string $relationship = 'assistanceRecipients';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('assistance_program_id')
                    ->label('Program Bantuan')
                    ->options(fn () => AssistanceProgram::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->required(),
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
                Tables\Columns\TextColumn::make('program.name')
                    ->label('Program')
                    ->searchable()
                    ->sortable(),
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
                    ->enum([
                        'diajukan' => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'diterima' => 'Telah Diterima',
                    ])
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
