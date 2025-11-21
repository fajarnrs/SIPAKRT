<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseholdResource;
use App\Filament\Resources\RtResource\Pages;
use App\Filament\Resources\RtResource\RelationManagers;
use App\Models\Resident;
use App\Models\Rt;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class RtResource extends Resource
{
    protected static ?string $model = Rt::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Pendataan Warga';

    protected static ?string $modelLabel = 'RT';

    protected static ?string $pluralModelLabel = 'Daftar RT';

    protected static ?string $navigationLabel = 'Daftar RT';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi RT')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Nomor RT')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->label('Alamat RT')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('leader_resident_id')
                            ->label('Ketua RT (Pilih Warga)')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->helperText('Pilih warga yang ditunjuk sebagai Ketua RT. Nama akan terisi otomatis.')
                            ->options(fn () => Resident::query()->orderBy('name')->limit(50)->pluck('name', 'id')->toArray())
                            ->getSearchResultsUsing(fn (string $search): array => Resident::query()
                                ->where('name', 'like', "%{$search}%")
                                ->orderBy('name')
                                ->limit(50)
                                ->pluck('name', 'id')
                                ->toArray())
                            ->getOptionLabelUsing(fn ($value): ?string => Resident::find($value)?->name)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('leader_name', Resident::find($state)?->name)),
                        Forms\Components\TextInput::make('leader_name')
                            ->label('Nama Ketua RT')
                            ->disabled(fn ($get) => filled($get('leader_resident_id')))
                            ->placeholder('Isi manual bila belum ada di daftar warga')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email RT')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Kartu Keluarga dalam RT ini')
                    ->schema([
                        Forms\Components\Repeater::make('households')
                            ->relationship('households')
                            ->label('Daftar Kartu Keluarga (KK)')
                            ->defaultItems(0)
                            ->minItems(0)
                            ->columns(1)
                            ->schema([
                                Forms\Components\Section::make('Data KK')
                                    ->schema([
                                        Forms\Components\TextInput::make('family_card_number')
                                            ->label('No. KK')
                                            ->required()
                                            ->maxLength(20)
                                            ->unique(ignoreRecord: true),
                                        Forms\Components\TextInput::make('head_name')
                                            ->label('Nama Kepala Keluarga')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('head_nik')
                                            ->label('NIK Kepala Keluarga')
                                            ->maxLength(20),
                                        Forms\Components\Select::make('head_gender')
                                            ->label('Jenis Kelamin Kepala')
                                            ->options([
                                                'male' => 'Laki-laki',
                                                'female' => 'Perempuan',
                                            ])
                                            ->required(),
                                        Forms\Components\TextInput::make('address')
                                            ->label('Alamat KK')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('head_birth_place')
                                            ->label('Tempat Lahir Kepala')
                                            ->maxLength(120),
                                        Forms\Components\DatePicker::make('head_birth_date')
                                            ->label('Tanggal Lahir Kepala'),
                                        Forms\Components\Select::make('head_religion')
                                            ->label('Agama Kepala')
                                            ->options([
                                                'Islam' => 'Islam',
                                                'Kristen' => 'Kristen',
                                                'Katolik' => 'Katolik',
                                                'Hindu' => 'Hindu',
                                                'Buddha' => 'Buddha',
                                                'Konghucu' => 'Konghucu',
                                            ])
                                            ->searchable(),
                                        Forms\Components\TextInput::make('head_education')
                                            ->label('Pendidikan Kepala')
                                            ->maxLength(120),
                                        Forms\Components\TextInput::make('head_occupation')
                                            ->label('Pekerjaan Kepala')
                                            ->maxLength(120),
                                        Forms\Components\Select::make('head_marital_status')
                                            ->label('Status Kawin Kepala')
                                            ->options([
                                                'Belum Kawin' => 'Belum Kawin',
                                                'Kawin' => 'Kawin',
                                                'Cerai' => 'Cerai',
                                            ]),
                                        Forms\Components\TextInput::make('head_nationality')
                                            ->label('Kewarganegaraan Kepala')
                                            ->default('WNI')
                                            ->maxLength(50),
                                        Forms\Components\Select::make('head_status')
                                            ->label('Status Warga Kepala')
                                            ->options(Resident::statusOptions())
                                            ->default(Resident::STATUS_ACTIVE)
                                            ->required(),
                                        Forms\Components\Textarea::make('head_notes')
                                            ->label('Catatan Kepala')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Forms\Components\DatePicker::make('issued_at')
                                            ->label('Tanggal Mulai Menetap'),
                                    ])
                                    ->columns(2),
                                Forms\Components\Section::make('Anggota Keluarga')
                                    ->schema([
                                        Forms\Components\Repeater::make('residents')
                                            ->relationship('residents')
                                            ->label('Anggota')
                                            ->defaultItems(0)
                                            ->minItems(0)
                                            ->columns(3)
                                            ->createItemButtonLabel('Tambah Anggota')
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Nama Lengkap')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('nik')
                                                    ->label('NIK')
                                                    ->maxLength(20),
                                                Forms\Components\Select::make('relationship')
                                                    ->label('Hubungan')
                                                    ->options([
                                                        'Kepala Keluarga' => 'Kepala Keluarga',
                                                        'Istri' => 'Istri',
                                                        'Anak' => 'Anak',
                                                        'Saudara' => 'Saudara',
                                                        'Lainnya' => 'Lainnya',
                                                    ])
                                                    ->required()
                                                    ->searchable(),
                                                Forms\Components\Select::make('gender')
                                                    ->label('Jenis Kelamin')
                                                    ->options([
                                                        'male' => 'Laki-laki',
                                                        'female' => 'Perempuan',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('birth_place')
                                                    ->label('Tempat Lahir')
                                                    ->maxLength(120),
                                                Forms\Components\DatePicker::make('birth_date')
                                                    ->label('Tanggal Lahir'),
                                                Forms\Components\Select::make('religion')
                                                    ->label('Agama')
                                                    ->options([
                                                        'Islam' => 'Islam',
                                                        'Kristen' => 'Kristen',
                                                        'Katolik' => 'Katolik',
                                                        'Hindu' => 'Hindu',
                                                        'Buddha' => 'Buddha',
                                                        'Konghucu' => 'Konghucu',
                                                    ])
                                                    ->searchable(),
                                                Forms\Components\TextInput::make('education')
                                                    ->label('Pendidikan')
                                                    ->maxLength(120),
                                                Forms\Components\TextInput::make('occupation')
                                                    ->label('Pekerjaan')
                                                    ->maxLength(120),
                                                Forms\Components\Select::make('marital_status')
                                                    ->label('Status Kawin')
                                                    ->options([
                                                        'Belum Kawin' => 'Belum Kawin',
                                                        'Kawin' => 'Kawin',
                                                        'Cerai' => 'Cerai',
                                                    ]),
                                                Forms\Components\TextInput::make('nationality')
                                                    ->label('Kewarganegaraan')
                                                    ->default('WNI')
                                                    ->maxLength(50),
                                                Forms\Components\Select::make('status')
                                                    ->label('Status Warga')
                                                    ->options(Resident::statusOptions())
                                                    ->default(Resident::STATUS_ACTIVE)
                                                    ->required(),
                                                Forms\Components\DatePicker::make('status_effective_at')
                                                    ->label('Tanggal Status'),
                                                Forms\Components\Textarea::make('notes')
                                                    ->label('Catatan')
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                                    ])
                                    ->columns(1),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('RT')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Wilayah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_leader')
                    ->label('Ketua RT')
                    ->getStateUsing(fn (Rt $record) => $record->currentLeaderName())
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('currentLeader', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('households_count')
                    ->counts('households')
                    ->label('Jumlah KK')
                    ->sortable(),
                Tables\Columns\TextColumn::make('residents_count')
                    ->counts('residents')
                    ->label('Jumlah Warga')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function populateHouseholdRepeater(array $data): array
    {
        $data['households'] = collect($data['households'] ?? [])
            ->map(fn (array $household): array => HouseholdResource::populateHeadFields($household))
            ->toArray();

        return $data;
    }

    public static function prepareHouseholdRepeater(array $data): array
    {
        $data['households'] = collect($data['households'] ?? [])
            ->map(function (array $household): array {
                $household['residents'] = HouseholdResource::prepareResidentsForSave($household);
                unset($household['head_resident_id']);

                return $household;
            })
            ->toArray();

        return $data;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HouseholdsRelationManager::class,
            RelationManagers\OfficialsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'officials' => fn ($relationship) => $relationship
                    ->with('resident')
                    ->orderByDesc('started_at'),
            ]);

        $user = Auth::user();

        if ($user && ! $user->is_admin && $user->role === 'rt' && $user->rt_id) {
            $query->where('id', $user->rt_id);
        }

        if ($user && $user->role === 'warga') {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRts::route('/'),
            'create' => Pages\CreateRt::route('/create'),
            'edit' => Pages\EditRt::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if ($user && $user->role === 'warga') {
            return false;
        }

        return parent::shouldRegisterNavigation();
    }
}
