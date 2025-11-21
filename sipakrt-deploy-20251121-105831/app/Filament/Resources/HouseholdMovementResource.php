<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseholdMovementResource\Pages;
use App\Filament\Resources\HouseholdMovementResource\RelationManagers;
use App\Models\Household;
use App\Filament\Resources\HouseholdResource;
use App\Models\HouseholdMovement;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HouseholdMovementResource extends Resource
{
    protected static ?string $model = HouseholdMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-refresh';

    protected static ?string $navigationGroup = 'Administratif Warga';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'Pergerakan Keluarga';

    protected static ?string $pluralModelLabel = 'Catatan Pindah/Meninggal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::movementFormSchema());
    }

    public static function movementFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Data Pergerakan')
                ->schema([
                    Forms\Components\Select::make('household_id')
                        ->label('Kartu Keluarga')
                        ->options(fn () => static::getHouseholdOptions())
                        ->default(fn () => static::getDefaultHouseholdId())
                        ->disabled(fn () => static::shouldLockHouseholdField())
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set): void {
                            $set('affected_resident_id', null);
                        })
                        ->required(),
                    Forms\Components\Select::make('type')
                        ->label('Jenis Peristiwa')
                        ->options(fn () => static::getMovementTypeOptions())
                        ->default(fn () => static::getDefaultMovementType())
                        ->disabled(fn () => static::shouldLockMovementType())
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set): void {
                            $set('affected_resident_id', null);
                        })
                        ->required(),
                ])
                ->columns(2),
            Forms\Components\Section::make('Detail Peristiwa')
                ->visible(fn ($get): bool => filled($get('type')))
                ->schema([
                    Forms\Components\DatePicker::make('event_date')
                        ->label(fn ($get): string => $get('type') === 'meninggal' ? 'Tanggal Meninggal' : 'Tanggal Peristiwa')
                        ->required(),
                    Forms\Components\Select::make('affected_resident_id')
                        ->label('Warga Terdampak')
                        ->options(fn ($get): array => static::getResidentOptions($get('household_id')))
                        ->searchable()
                        ->visible(fn ($get): bool => $get('type') === 'meninggal')
                        ->required(fn ($get): bool => $get('type') === 'meninggal')
                        ->dehydrated(fn ($get): bool => $get('type') === 'meninggal'),
                    Forms\Components\TextInput::make('metadata.death_burial_location')
                        ->label('Lokasi Pemakaman (TPU)')
                        ->maxLength(255)
                        ->visible(fn ($get): bool => $get('type') === 'meninggal')
                        ->dehydrated(fn ($get): bool => $get('type') === 'meninggal')
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('destination')
                        ->label(fn ($get): string => match ($get('type')) {
                            'pindah_masuk' => 'Asal',
                            'pindah_keluar' => 'Tujuan',
                            default => 'Tujuan / Asal',
                        })
                        ->maxLength(255)
                        ->visible(fn ($get): bool => in_array($get('type'), ['pindah_masuk', 'pindah_keluar'], true))
                        ->dehydrated(fn ($get): bool => in_array($get('type'), ['pindah_masuk', 'pindah_keluar'], true)),
                    Forms\Components\Select::make('processed_by')
                        ->label('Diproses Oleh')
                        ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->visible(fn () => ! static::isWargaUser()),
                    Forms\Components\Hidden::make('processed_by')
                        ->default(fn () => static::getDefaultProcessedBy())
                        ->dehydrated(fn () => static::isWargaUser()),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Draft',
                            'diproses' => 'Diproses',
                            'selesai' => 'Selesai',
                        ])
                        ->default(fn () => static::isWargaUser() ? 'draft' : 'draft')
                        ->disabled(fn () => static::isWargaUser())
                        ->required(),
                    Forms\Components\Textarea::make('details')
                        ->label('Rincian')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('household.rt.number')
                    ->label('RT')
                    ->sortable(),
                Tables\Columns\TextColumn::make('household.family_card_number')
                    ->label('No. KK')
                    ->searchable(),
                Tables\Columns\TextColumn::make('household.head_name')
                    ->label('Kepala Keluarga')
                    ->searchable(),
                Tables\Columns\TextColumn::make('affectedResident.name')
                    ->label('Warga Terdampak')
                    ->formatStateUsing(function (?string $state, HouseholdMovement $record): ?string {
                        return $record->type === 'meninggal' ? $state : null;
                    })
                    ->toggleable(),
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
                Tables\Columns\TextColumn::make('metadata.death_burial_location')
                    ->label('Lokasi Pemakaman')
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->filters([
                Tables\Filters\SelectFilter::make('rt')
                    ->label('Filter RT')
                    ->options(fn () => Rt::query()->orderBy('number')->pluck('number', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (! ($rtId = $data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('household', fn (Builder $householdQuery) =>
                            $householdQuery->where('rt_id', $rtId)
                        );
                    }),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Peristiwa')
                    ->options([
                        'pindah_masuk' => 'Pindah Masuk',
                        'pindah_keluar' => 'Pindah Keluar',
                        'meninggal' => 'Meninggal Dunia',
                        'lainnya' => 'Lainnya',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHouseholdMovements::route('/'),
            'create' => Pages\CreateHouseholdMovement::route('/create'),
            'edit' => Pages\EditHouseholdMovement::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && ! $user->is_admin && $user->role === 'rt' && $user->rt_id) {
            return $query->whereHas('household', fn (Builder $householdQuery) =>
                $householdQuery->where('rt_id', $user->rt_id)
            );
        }

        if ($user && $user->role === 'warga' && $user->household_id) {
            return $query->where('household_id', $user->household_id);
        }

        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected static function getHouseholdOptions(): array
    {
        $query = Household::query()->orderBy('family_card_number');
        $user = Auth::user();

        if ($user && $user->role === 'rt' && $user->rt_id) {
            $query->where('rt_id', $user->rt_id);
        }

        if ($user && $user->role === 'warga' && $user->household_id) {
            $query->where('id', $user->household_id);
        }

        return $query->pluck('family_card_number', 'id')->toArray();
    }

    protected static function getDefaultHouseholdId(): ?int
    {
        $user = Auth::user();

        if ($user && $user->role === 'warga') {
            return $user->household_id;
        }

        return null;
    }

    protected static function shouldLockHouseholdField(): bool
    {
        $user = Auth::user();

        return (bool) ($user && $user->role === 'warga');
    }

    protected static function getMovementTypeOptions(): array
    {
        if (static::isWargaUser()) {
            return [
                'pindah_keluar' => 'Pindah Keluar',
                'meninggal' => 'Meninggal Dunia',
            ];
        }

        return [
            'pindah_masuk' => 'Pindah Masuk',
            'pindah_keluar' => 'Pindah Keluar',
            'meninggal' => 'Meninggal Dunia',
            'lainnya' => 'Lainnya',
        ];
    }

    protected static function shouldLockMovementType(): bool
    {
        return static::isWargaUser();
    }

    protected static function getDefaultMovementType(): ?string
    {
        return static::isWargaUser() ? 'pindah_keluar' : null;
    }

    protected static function getDefaultProcessedBy(): ?int
    {
        $user = Auth::user();

        return static::isWargaUser() ? $user?->id : null;
    }

    public static function isWargaUser(): bool
    {
        $user = Auth::user();

        return (bool) ($user && $user->role === 'warga');
    }

    protected static function getResidentOptions(?int $householdId): array
    {
        if (! $householdId) {
            return [];
        }

        return Resident::query()
            ->where('household_id', $householdId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function applyMovementEffects(HouseholdMovement $movement): void
    {
        if ($movement->type === 'meninggal' && $movement->affected_resident_id) {
            $resident = Resident::find($movement->affected_resident_id);

            if ($resident) {
                $resident->status = Resident::STATUS_DECEASED;
                $resident->status_effective_at = $movement->event_date;
                $resident->save();

                if ($resident->relationship === 'Kepala Keluarga' && $resident->household) {
                    HouseholdResource::clearHouseholdHead($resident->household);
                }
            }
        }
    }
}
