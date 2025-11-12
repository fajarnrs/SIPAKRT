<?php

namespace App\Filament\Resources\RtResource\RelationManagers;

use App\Models\RtOfficial;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class OfficialsRelationManager extends RelationManager
{
    protected static string $relationship = 'officials';

    protected static ?string $recordTitleAttribute = 'position';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('resident_id')
                    ->label('Warga Ditunjuk')
                    ->relationship('resident', 'name', fn (Builder $query) => $query
                        ->whereHas('household', fn (Builder $sub) => $sub->where('rt_id', request()->route('record')))
                        ->whereNotNull('email'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('position')
                    ->label('Jabatan')
                    ->default('Ketua RT')
                    ->required()
                    ->maxLength(120),
                Forms\Components\DatePicker::make('started_at')
                    ->label('Mulai Menjabat')
                    ->required(),
                Forms\Components\DatePicker::make('ended_at')
                    ->label('Selesai Menjabat'),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('resident.name')
                    ->label('Nama Pengurus')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('position')
                    ->label('Jabatan'),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Selesai')
                    ->date('d M Y'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (RtOfficial $record) => $record->isActive() ? 'Menjabat' : 'Selesai')
                    ->colors([
                        'success' => fn ($state, RtOfficial $record) => $record->isActive(),
                        'danger' => fn ($state, RtOfficial $record) => ! $record->isActive(),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('endTerm')
                    ->label('Akhiri')
                    ->icon('heroicon-o-stop-circle')
                    ->visible(fn (RtOfficial $record) => $record->isActive())
                    ->requiresConfirmation()
                    ->action(fn (RtOfficial $record) => $record->endNow()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
