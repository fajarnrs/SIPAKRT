<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResidentResource\Pages;
use App\Filament\Resources\ResidentResource\RelationManagers;
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

class ResidentResource extends Resource
{
    protected static ?string $model = Resident::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Pendataan Warga';

    protected static ?string $modelLabel = 'Warga';

    protected static ?string $pluralModelLabel = 'Daftar Warga';

    protected static ?string $navigationLabel = 'Daftar Warga';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Warga')
                    ->schema([
                        Forms\Components\Select::make('household_id')
                            ->label('Kartu Keluarga')
                            ->relationship('household', 'family_card_number')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->family_card_number . ' - ' . $record->head_name)
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->numeric()
                            ->length(16)
                            ->helperText('NIK harus tepat 16 digit angka'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('relationship')
                            ->label('Status Hubungan')
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
                        Forms\Components\Select::make('status')
                            ->label('Status Warga')
                            ->options(Resident::statusOptions())
                            ->default(Resident::STATUS_ACTIVE)
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Detail Pribadi')
                    ->schema([
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
                            ->label('Pendidikan Terakhir')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('occupation')
                            ->label('Pekerjaan')
                            ->maxLength(120),
                        Forms\Components\Select::make('marital_status')
                            ->label('Status Perkawinan')
                            ->options([
                                'Belum Kawin' => 'Belum Kawin',
                                'Kawin' => 'Kawin',
                                'Cerai' => 'Cerai',
                            ]),
                        Forms\Components\DatePicker::make('status_effective_at')
                            ->label('Tanggal Status')
                            ->helperText('Tanggal terakhir perubahan status. Opsional.'),
                        Forms\Components\TextInput::make('nationality')
                            ->label('Kewarganegaraan')
                            ->default('WNI')
                            ->maxLength(50),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rt_number')
                    ->label('RT')
                    ->getStateUsing(fn (Resident $record): ?string => $record->household?->rt?->number),
                Tables\Columns\TextColumn::make('household.family_card_number')
                    ->label('No. KK')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('relationship')
                    ->label('Hubungan')
                    ->sortable()
                    ->colors([
                        'primary',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => Resident::STATUS_ACTIVE,
                        'danger' => Resident::STATUS_DECEASED,
                        'warning' => Resident::STATUS_MOVED,
                        'secondary' => Resident::STATUS_TEMPORARY,
                    ]),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => '-',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('occupation')
                    ->label('Pekerjaan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rt')
                    ->label('Filter RT')
                    ->options(fn () => Rt::query()->orderBy('number')->pluck('number', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('household', fn (Builder $householdQuery) =>
                            $householdQuery->where('rt_id', $data['value'])
                        );
                    }),
                Tables\Filters\SelectFilter::make('relationship')
                    ->label('Hubungan')
                    ->options([
                        'Kepala Keluarga' => 'Kepala Keluarga',
                        'Istri' => 'Istri',
                        'Anak' => 'Anak',
                        'Saudara' => 'Saudara',
                        'Lainnya' => 'Lainnya',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Resident::statusOptions()),
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
            'index' => Pages\ListResidents::route('/'),
            'create' => Pages\CreateResident::route('/create'),
            'edit' => Pages\EditResident::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && ! $user->is_admin && $user->role === 'rt' && $user->rt_id) {
            return $query->whereHas('household', fn (Builder $sub) => $sub->where('rt_id', $user->rt_id));
        }

        if ($user && $user->role === 'warga' && $user->household_id) {
            return $query->where('household_id', $user->household_id);
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
