<?php

namespace App\Filament\Resources\HouseholdResource\RelationManagers;

use App\Models\Resident;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class ResidentsRelationManager extends RelationManager
{
    protected static string $relationship = 'residents';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    ->maxLength(20),
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
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('birth_place')
                            ->label('Tempat Lahir')
                            ->maxLength(120),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Tanggal Lahir'),
                        Forms\Components\DatePicker::make('status_effective_at')
                            ->label('Tanggal Status')
                            ->helperText('Tanggal perubahan status (opsional).'),
                    ]),
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
                Forms\Components\TextInput::make('nationality')
                    ->label('Kewarganegaraan')
                    ->default('WNI')
                    ->maxLength(50),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
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
                    ])
                    ->icons([
                        'heroicon-o-badge-check' => Resident::STATUS_ACTIVE,
                        'heroicon-o-x-circle' => Resident::STATUS_DECEASED,
                        'heroicon-o-arrow-right' => Resident::STATUS_MOVED,
                        'heroicon-o-clock' => Resident::STATUS_TEMPORARY,
                    ]),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => '-',
                    }),
                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('occupation')
                    ->label('Pekerjaan')
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
