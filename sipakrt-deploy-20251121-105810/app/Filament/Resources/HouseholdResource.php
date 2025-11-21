<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HouseholdResource\Pages;
use App\Filament\Resources\HouseholdResource\RelationManagers;
use App\Models\Household;
use App\Models\Resident;
use App\Models\Rt;
use App\Exports\HouseholdsExport;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class HouseholdResource extends Resource
{
    protected static ?string $model = Household::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Pendataan Warga';

    protected static ?string $modelLabel = 'Kartu Keluarga';

    protected static ?string $pluralModelLabel = 'Daftar KK';

    protected static ?string $navigationLabel = 'Daftar KK';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Kartu Keluarga')
                    ->schema([
                        Forms\Components\Hidden::make('head_resident_id'),
                        Forms\Components\Select::make('rt_id')
                            ->label('RT')
                            ->options(fn () => static::getRtOptions())
                            ->searchable()
                            ->preload()
                            ->default(fn () => static::getRtDefault())
                            ->disabled(fn () => static::isRtRestricted())
                            ->required(),
                        Forms\Components\TextInput::make('family_card_number')
                            ->label('No. Kartu Keluarga')
                            ->required()
                            ->numeric()
                            ->length(16)
                            ->unique(ignoreRecord: true)
                            ->helperText('Nomor KK harus tepat 16 digit angka'),
                        Forms\Components\TextInput::make('head_name')
                            ->label('Nama Kepala Keluarga')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('head_nik')
                            ->label('NIK Kepala Keluarga')
                            ->numeric()
                            ->length(16)
                            ->helperText('NIK harus tepat 16 digit angka. NIK ini akan dimasukkan otomatis sebagai anggota dengan hubungan Kepala Keluarga.'),
                        Forms\Components\Select::make('head_gender')
                            ->label('Jenis Kelamin Kepala Keluarga')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Alamat')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('head_birth_place')
                            ->label('Tempat Lahir Kepala Keluarga')
                            ->maxLength(120),
                        Forms\Components\DatePicker::make('head_birth_date')
                            ->label('Tanggal Lahir Kepala Keluarga'),
                        Forms\Components\Select::make('head_religion')
                            ->label('Agama Kepala Keluarga')
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
                            ->label('Pendidikan Kepala Keluarga')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('head_occupation')
                            ->label('Pekerjaan Kepala Keluarga')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('head_email')
                            ->label('Email Kepala Keluarga')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Select::make('head_marital_status')
                            ->label('Status Kawin Kepala Keluarga')
                            ->options([
                                'Belum Kawin' => 'Belum Kawin',
                                'Kawin' => 'Kawin',
                                'Cerai' => 'Cerai',
                            ]),
                        Forms\Components\TextInput::make('head_nationality')
                            ->label('Kewarganegaraan Kepala Keluarga')
                            ->default('WNI')
                            ->maxLength(50),
                        Forms\Components\Select::make('head_status')
                            ->label('Status Warga Kepala Keluarga')
                            ->options(Resident::statusOptions())
                            ->default(Resident::STATUS_ACTIVE)
                            ->required(),
                        Forms\Components\Textarea::make('head_notes')
                            ->label('Catatan Kepala Keluarga')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('issued_at')
                            ->label('Tanggal Mulai Menetap di RT'),
                        Forms\Components\Select::make('status')
                            ->label('Status KK')
                            ->options(Household::statusOptions())
                            ->default(Household::STATUS_ACTIVE)
                            ->required()
                            ->helperText('Status akan otomatis berubah jika kepala keluarga meninggal atau cerai.'),
                        Forms\Components\DatePicker::make('status_effective_date')
                            ->label('Tanggal Efektif Status')
                            ->helperText('Tanggal berlakunya status KK.'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Anggota Keluarga')
                    ->schema([
                        Forms\Components\Repeater::make('residents')
                            ->label('Daftar Anggota')
                            ->relationship('residents')
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
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rt.number')
                    ->label('RT')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('family_card_number')
                    ->label('No. KK')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_head_name')
                    ->label('Kepala Keluarga')
                    ->getStateUsing(fn (Household $record): ?string => optional(
                        $record->residents
                            ->first(fn (Resident $resident) => $resident->relationship === 'Kepala Keluarga' && $resident->status === Resident::STATUS_ACTIVE)
                    )->name)
                    ->sortable(false)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('residents', function ($q) use ($search) {
                            $q->where('relationship', 'Kepala Keluarga')
                              ->where('status', Resident::STATUS_ACTIVE)
                              ->where('name', 'like', "%{$search}%");
                        });
                    }),
                Tables\Columns\TextColumn::make('residents_count')
                    ->label('Jumlah Anggota')
                    ->counts('residents')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => Household::STATUS_ACTIVE,
                        'danger' => Household::STATUS_INACTIVE,
                        'warning' => Household::STATUS_EXPIRED,
                    ])
                    ->enum(Household::statusOptions())
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_effective_date')
                    ->label('Tgl Efektif Status')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Terbit')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rt')
                    ->relationship('rt', 'number')
                    ->label('Filter RT'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(Household::statusOptions())
                    ->label('Filter Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('export')
                    ->label('Export ke Excel')
                    ->icon('heroicon-o-download')
                    ->action(function ($records, $livewire) {
                        // Ambil query yang sudah terfilter dari table
                        $query = static::getEloquentQuery();
                        
                        // Apply filter RT jika ada
                        $tableFilters = $livewire->tableFilters;
                        if (isset($tableFilters['rt']['value']) && $tableFilters['rt']['value']) {
                            $query->where('rt_id', $tableFilters['rt']['value']);
                        }
                        
                        // Apply filter Status jika ada
                        if (isset($tableFilters['status']['value']) && $tableFilters['status']['value']) {
                            $query->where('status', $tableFilters['status']['value']);
                        }
                        
                        // Load relationships
                        $query->with(['rt', 'residents']);
                        
                        return Excel::download(
                            new HouseholdsExport($query),
                            'data-kk-' . now()->format('Y-m-d-His') . '.xlsx'
                        );
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function populateHeadFields(array $data): array
    {
        $residents = $data['residents'] ?? [];
        $head = null;

        foreach ($residents as $index => $resident) {
            if (($resident['relationship'] ?? null) === 'Kepala Keluarga') {
                $head = $resident;
                unset($residents[$index]);
                break;
            }
        }

        $data['residents'] = array_values($residents);

        if ($head) {
            static::applyHeadData($data, $head, true);
        } elseif (! empty($data['id'])) {
            $headModel = Resident::query()
                ->where('household_id', $data['id'])
                ->where('relationship', 'Kepala Keluarga')
                ->first();

            if ($headModel && $headModel->status === Resident::STATUS_ACTIVE) {
                static::applyHeadData($data, static::residentToArray($headModel), true);
            } else {
                static::clearHeadFormData($data);
            }
        } else {
            static::clearHeadFormData($data);
        }

        return $data;
    }

    public static function prepareResidentsForSave(array &$data): array
    {
        $headFromMembers = null;

        $members = collect($data['residents'] ?? [])
            ->filter(function (array $member) use (&$headFromMembers): bool {
                if (($member['relationship'] ?? null) === 'Kepala Keluarga') {
                    if (! $headFromMembers) {
                        $headFromMembers = $member;
                    }

                    return false;
                }

                return true;
            })
            ->map(function (array $member): array {
                $member['nationality'] = $member['nationality'] ?? 'WNI';
                $member['status'] = $member['status'] ?? Resident::STATUS_ACTIVE;

                return $member;
            });

        if ($headFromMembers) {
            static::applyHeadData($data, $headFromMembers);
        }

        $headPayload = static::getHeadResidentPayload($data);

        if ($headFromMembers && $headPayload) {
            $headPayload = array_merge(
                $headPayload,
                array_filter($headFromMembers, fn ($value) => $value !== null)
            );
        }

        if ($headPayload && (($headPayload['status'] ?? Resident::STATUS_ACTIVE) === Resident::STATUS_ACTIVE)) {
            $headPayload['relationship'] = 'Kepala Keluarga';
            $headPayload['nationality'] = $headPayload['nationality'] ?? 'WNI';
            $headPayload['status'] = $headPayload['status'] ?? Resident::STATUS_ACTIVE;

            $members = $members->prepend($headPayload);
        } else {
            static::clearHeadFormData($data);
        }

        return $members->values()->all();
    }

    public static function syncHeadResident(Household $household, array $data): void
    {
        $payload = static::getHeadResidentPayload($data);

        if (! $payload) {
            Resident::query()
                ->where('household_id', $household->id)
                ->where('relationship', 'Kepala Keluarga')
                ->delete();

            static::clearHouseholdHead($household);

            return;
        }

        $householdPayload = $payload;
        $headId = $payload['id'] ?? null;
        unset($payload['id']);

        $payload['relationship'] = 'Kepala Keluarga';

        if ($headId) {
            $resident = Resident::query()
                ->where('household_id', $household->id)
                ->where('id', $headId)
                ->first();

            if ($resident) {
                $resident->fill($payload);
                $resident->save();

                static::cleanupPreviousHeads($household, $resident->id);
                static::persistHouseholdHead($household, $householdPayload);

                return;
            }
        }

        $resident = Resident::updateOrCreate(
            [
                'household_id' => $household->id,
                'relationship' => 'Kepala Keluarga',
            ],
            array_merge($payload, [
                'household_id' => $household->id,
                'relationship' => 'Kepala Keluarga',
            ])
        );

        static::cleanupPreviousHeads($household, $resident->id);
        static::persistHouseholdHead($household, $householdPayload);
    }

    protected static function cleanupPreviousHeads(Household $household, int $currentHeadId): void
    {
        Resident::query()
            ->where('household_id', $household->id)
            ->where('relationship', 'Kepala Keluarga')
            ->where('id', '!=', $currentHeadId)
            ->delete();
    }

    public static function clearHouseholdHead(Household $household): void
    {
        $household->forceFill([
            'head_name' => null,
            'head_nik' => null,
            'head_gender' => null,
            'head_email' => null,
            'head_birth_place' => null,
            'head_birth_date' => null,
            'head_religion' => null,
            'head_education' => null,
            'head_occupation' => null,
            'head_marital_status' => null,
            'head_nationality' => null,
            'head_status' => null,
            'head_notes' => null,
        ])->save();
    }

    protected static function persistHouseholdHead(Household $household, ?array $payload): void
    {
        $payload = $payload ?? [];

        $household->forceFill([
            'head_name' => $payload['name'] ?? null,
            'head_nik' => $payload['nik'] ?? null,
            'head_gender' => $payload['gender'] ?? null,
            'head_email' => $payload['email'] ?? null,
            'head_birth_place' => $payload['birth_place'] ?? null,
            'head_birth_date' => static::normalizeDate($payload['birth_date'] ?? null),
            'head_religion' => $payload['religion'] ?? null,
            'head_education' => $payload['education'] ?? null,
            'head_occupation' => $payload['occupation'] ?? null,
            'head_marital_status' => $payload['marital_status'] ?? null,
            'head_nationality' => $payload['nationality'] ?? null,
            'head_status' => $payload['status'] ?? null,
            'head_notes' => $payload['notes'] ?? null,
        ]);

        $household->save();
    }

    protected static function getHeadResidentPayload(array $data): ?array
    {
        $name = trim((string) ($data['head_name'] ?? ''));

        if ($name === '') {
            return null;
        }

        if (($data['head_status'] ?? Resident::STATUS_ACTIVE) !== Resident::STATUS_ACTIVE) {
            return null;
        }

        return [
            'id' => $data['head_resident_id'] ?? null,
            'nik' => $data['head_nik'] ?? null,
            'name' => $name,
            'gender' => $data['head_gender'] ?? null,
            'birth_place' => $data['head_birth_place'] ?? null,
            'birth_date' => static::normalizeDate($data['head_birth_date'] ?? null),
            'religion' => $data['head_religion'] ?? null,
            'education' => $data['head_education'] ?? null,
            'occupation' => $data['head_occupation'] ?? null,
            'marital_status' => $data['head_marital_status'] ?? null,
            'email' => $data['head_email'] ?? null,
            'nationality' => $data['head_nationality'] ?? 'WNI',
            'status' => $data['head_status'] ?? Resident::STATUS_ACTIVE,
            'status_effective_at' => null,
            'notes' => $data['head_notes'] ?? null,
        ];
    }

    protected static function applyHeadData(array &$data, array $head, bool $preserveExisting = false): void
    {
        $assign = function (string $target, $value, $default = null) use (&$data, $preserveExisting): void {
            if ($preserveExisting && array_key_exists($target, $data) && ! is_null($data[$target]) && $data[$target] !== '') {
                return;
            }

            if ($target === 'head_birth_date') {
                $value = static::normalizeDate($value);
            }

            if ($value === null && $default !== null) {
                $value = $default;
            }

            $data[$target] = $value;
        };

        $assign('head_resident_id', $head['id'] ?? null);
        $assign('head_name', $head['name'] ?? null);
        $assign('head_nik', $head['nik'] ?? null);
        $assign('head_gender', $head['gender'] ?? null);
        $assign('head_email', $head['email'] ?? null);
        $assign('head_birth_place', $head['birth_place'] ?? null);
        $assign('head_birth_date', $head['birth_date'] ?? null);
        $assign('head_religion', $head['religion'] ?? null);
        $assign('head_education', $head['education'] ?? null);
        $assign('head_occupation', $head['occupation'] ?? null);
        $assign('head_marital_status', $head['marital_status'] ?? null);
        $assign('head_nationality', $head['nationality'] ?? null, 'WNI');
        $assign('head_status', $head['status'] ?? null, Resident::STATUS_ACTIVE);
        $assign('head_notes', $head['notes'] ?? null);
    }

    protected static function clearHeadFormData(array &$data): void
    {
        foreach ([
            'head_name',
            'head_nik',
            'head_gender',
            'head_email',
            'head_birth_place',
            'head_birth_date',
            'head_religion',
            'head_education',
            'head_occupation',
            'head_marital_status',
            'head_nationality',
            'head_status',
            'head_notes',
        ] as $field) {
            $data[$field] = null;
        }

        $data['head_resident_id'] = null;
    }

    protected static function normalizeDate($value): ?string
    {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return null;
    }

    protected static function residentToArray(Resident $resident): array
    {
        return [
            'id' => $resident->id,
            'name' => $resident->name,
            'nik' => $resident->nik,
            'gender' => $resident->gender,
            'email' => $resident->email,
            'birth_place' => $resident->birth_place,
            'birth_date' => optional($resident->birth_date)->format('Y-m-d'),
            'religion' => $resident->religion,
            'education' => $resident->education,
            'occupation' => $resident->occupation,
            'marital_status' => $resident->marital_status,
            'nationality' => $resident->nationality,
            'status' => $resident->status,
            'notes' => $resident->notes,
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ResidentsRelationManager::class,
            RelationManagers\VehiclesRelationManager::class,
            RelationManagers\MovementsRelationManager::class,
            RelationManagers\AssistanceRecipientsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHouseholds::route('/'),
            'create' => Pages\CreateHousehold::route('/create'),
            'view' => Pages\ViewHousehold::route('/{record}'),
            'edit' => Pages\EditHousehold::route('/{record}/edit'),
        ];
    }

    protected static function isRtRestricted(): bool
    {
        $user = Auth::user();

        return (bool) ($user && ! $user->is_admin && $user->role === 'rt' && $user->rt_id);
    }

    protected static function getRtDefault(): ?int
    {
        return static::isRtRestricted() ? Auth::user()->rt_id : null;
    }

    protected static function getRtOptions(): array
    {
        $query = Rt::query()->orderBy('number');

        if (static::isRtRestricted()) {
            $query->where('id', Auth::user()->rt_id);
        }

        return $query->pluck('number', 'id')->toArray();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['residents' => fn ($relation) => $relation->select('id', 'household_id', 'name', 'relationship', 'status')]);

        if (static::isRtRestricted()) {
            $query->where('rt_id', Auth::user()->rt_id);
        }

        $user = Auth::user();

        if ($user && $user->role === 'warga' && $user->household_id) {
            $query->where('id', $user->household_id);
        }

        return $query;
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
