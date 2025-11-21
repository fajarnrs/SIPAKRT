<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestLogResource\Pages;
use App\Filament\Resources\GuestLogResource\RelationManagers;
use App\Models\GuestLog;
use App\Models\Household;
use App\Models\Resident;
use App\Models\Rt;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class GuestLogResource extends Resource
{
    protected static ?string $model = GuestLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Administratif Warga';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Buku Tamu';

    protected static ?string $pluralModelLabel = 'Buku Tamu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tamu')
                    ->schema([
                        Forms\Components\Select::make('rt_id')
                            ->label('RT')
                            ->options(fn () => static::getRtOptions())
                            ->default(fn () => static::getDefaultRtId())
                            ->disabled(fn () => static::shouldLockRtField())
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('resident_id')
                            ->label('Ditujukan Ke Warga')
                            ->options(fn (callable $get) => static::getResidentsByContext($get('rt_id')))
                            ->getSearchResultsUsing(fn (string $search, callable $get): array => static::getResidentsByContext($get('rt_id'), $search))
                            ->searchable()
                            ->disabled(fn () => static::shouldLockResidentField())
                            ->helperText('Opsional, pilih warga yang dikunjungi'),
                        Forms\Components\TextInput::make('guest_name')
                            ->label('Nama Tamu')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('guest_id_number')
                            ->label('No. Identitas')
                            ->maxLength(30),
                        Forms\Components\TextInput::make('origin')
                            ->label('Asal')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('purpose')
                            ->label('Keperluan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Waktu Kunjungan')
                    ->schema([
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Tanggal Kunjungan')
                            ->required(),
                        Forms\Components\TimePicker::make('arrival_time')
                            ->label('Jam Datang')
                            ->seconds(false),
                        Forms\Components\TimePicker::make('departure_time')
                            ->label('Jam Pulang')
                            ->seconds(false),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rt.number')
                    ->label('RT')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guest_name')
                    ->label('Nama Tamu')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('origin')
                    ->label('Asal')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Keperluan')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('resident.name')
                    ->label('Warga Dituju')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('arrival_time')
                    ->label('Datang')
                    ->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : '-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('departure_time')
                    ->label('Pulang')
                    ->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : '-')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rt')
                    ->label('Filter RT')
                    ->relationship('rt', 'number'),
                Tables\Filters\Filter::make('visit_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('visit_date', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('visit_date', '<=', $date));
                    }),
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
            'index' => Pages\ListGuestLogs::route('/'),
            'create' => Pages\CreateGuestLog::route('/create'),
            'edit' => Pages\EditGuestLog::route('/{record}/edit'),
        ];
    }

    protected static function getResidentsByContext($rtId = null, string $search = null): array
    {
        $user = Auth::user();

        if ($user && $user->role === 'warga' && $user->household_id) {
            $residentQuery = Resident::query()->where('household_id', $user->household_id);

            if ($search) {
                $residentQuery->where('name', 'like', "%{$search}%");
            }

            return $residentQuery->orderBy('name')->pluck('name', 'id')->toArray();
        }

        return static::getResidentsByRt($rtId, $search);
    }

    protected static function getResidentsByRt($rtId = null, string $search = null): array
    {
        $query = Resident::query();

        if ($rtId) {
            $query->whereHas('household', fn (Builder $sub) => $sub->where('rt_id', $rtId));
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('name')->limit(50)->pluck('name', 'id')->toArray();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && ! $user->is_admin && $user->role === 'rt' && $user->rt_id) {
            return $query->where('rt_id', $user->rt_id);
        }

        if ($user && $user->role === 'warga' && $user->household_id) {
            $residentIds = Resident::query()
                ->where('household_id', $user->household_id)
                ->pluck('id');

            return $query
                ->whereIn('resident_id', $residentIds);
        }

        return $query;
    }

    protected static function getRtOptions(): array
    {
        $query = Rt::query()->orderBy('number');
        $user = Auth::user();

        if ($user && $user->role === 'rt' && $user->rt_id) {
            $query->where('id', $user->rt_id);
        }

        if ($user && $user->role === 'warga' && $user->household_id) {
            $household = Household::find($user->household_id);
            $query->where('id', $household?->rt_id ?? 0);
        }

        return $query->pluck('number', 'id')->toArray();
    }

    protected static function getDefaultRtId(): ?int
    {
        $user = Auth::user();

        if ($user && $user->role === 'rt' && $user->rt_id) {
            return $user->rt_id;
        }

        if ($user && $user->role === 'warga' && $user->household_id) {
            return Household::find($user->household_id)?->rt_id;
        }

        return null;
    }

    protected static function shouldLockRtField(): bool
    {
        $user = Auth::user();

        return (bool) ($user && in_array($user->role, ['rt', 'warga'], true));
    }

    protected static function shouldLockResidentField(): bool
    {
        $user = Auth::user();

        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
