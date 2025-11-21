<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Household;
use App\Models\Rt;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form as ResourceForm;
use Filament\Resources\Resource;
use Filament\Resources\Table as ResourceTable;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Pengaturan Sistem';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Daftar Pengguna';

    public static function form(ResourceForm $form): ResourceForm
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email / Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->label('Peran')
                            ->options([
                                'admin' => 'Administrator',
                                'rt' => 'Ketua RT',
                                'warga' => 'Warga',
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state !== 'rt') {
                                    $set('rt_id', null);
                                }

                                if ($state !== 'warga') {
                                    $set('household_id', null);
                                }
                            })
                            ->nullable(),
                        Forms\Components\Select::make('rt_id')
                            ->label('RT Terkait')
                            ->options(fn () => Rt::query()->orderBy('number')->pluck('number', 'id')->toArray())
                            ->searchable()
                            ->placeholder('Pilih RT')
                            ->visible(fn (callable $get) => $get('role') === 'rt')
                            ->required(fn (callable $get) => $get('role') === 'rt'),
                        Forms\Components\Select::make('household_id')
                            ->label('Kartu Keluarga')
                            ->options(fn () => Household::query()->orderBy('family_card_number')->pluck('family_card_number', 'id')->toArray())
                            ->searchable()
                            ->placeholder('Pilih KK')
                            ->visible(fn (callable $get) => $get('role') === 'warga')
                            ->required(fn (callable $get) => $get('role') === 'warga'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->inline(false)
                            ->helperText('Nonaktifkan untuk menutup akses login pengguna ini.')
                            ->default(true),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Kredensial')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (?User $record): bool => $record === null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->minLength(8)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->required(fn (?User $record): bool => $record === null)
                            ->same('password')
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(ResourceTable $table): ResourceTable
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email / Username')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        // Tampilkan No. KK jika email kosong
                        return $record->email ?? $record->family_card_number;
                    })
                    ->description(function ($record) {
                        // Tampilkan No. KK sebagai deskripsi jika ada email
                        if ($record->email && $record->family_card_number) {
                            return 'No. KK: ' . $record->family_card_number;
                        }
                        return null;
                    }),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Peran')
                    ->colors([
                        'primary' => 'admin',
                        'warning' => 'rt',
                        'info' => 'warga',
                    ])
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'admin' => 'Admin',
                        'rt' => 'Ketua RT',
                        'warga' => 'Warga',
                        default => ucfirst($state ?? 'Tidak Diketahui'),
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('household.family_card_number')
                    ->label('No. KK')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BooleanColumn::make('is_admin')
                    ->label('Admin'),
                Tables\Columns\TextColumn::make('rt.number')
                    ->label('RT')
                    ->formatStateUsing(fn ($state) => $state ? "RT {$state}" : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Admin',
                        'rt' => 'Ketua RT',
                        'warga' => 'Warga',
                    ]),
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Hanya Admin'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                Tables\Filters\SelectFilter::make('household')
                    ->label('Kartu Keluarga')
                    ->relationship('household', 'family_card_number'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        return $user?->is_admin || $user?->role === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        // Tampilkan semua user (dengan email atau No. KK)
        return parent::getEloquentQuery();
    }
}
