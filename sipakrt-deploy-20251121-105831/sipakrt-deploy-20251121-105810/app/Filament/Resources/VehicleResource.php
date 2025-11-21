<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Rt;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Administratif Warga';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Kendaraan';

    protected static ?string $pluralModelLabel = 'Kendaraan Warga';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Kendaraan')
                    ->schema([
                        Forms\Components\Select::make('household_id')
                            ->label('Kartu Keluarga')
                            ->relationship('household', 'family_card_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'Mobil' => 'Mobil',
                                'Motor' => 'Motor',
                                'Sepeda' => 'Sepeda',
                                'Pickup' => 'Pickup',
                            ])
                            ->required()
                            ->searchable(),
                        Forms\Components\TextInput::make('brand')
                            ->label('Merek')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('model')
                            ->label('Model')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('license_plate')
                            ->label('Nomor Polisi')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('color')
                            ->label('Warna')
                            ->maxLength(60),
                        Forms\Components\TextInput::make('year_of_purchase')
                            ->label('Tahun Beli')
                            ->numeric()
                            ->minValue(1980)
                            ->maxValue((int) now()->year),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif Digunakan')
                            ->default(true),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('household.rt.number')
                    ->label('RT')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('household.family_card_number')
                    ->label('No. KK')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Merek')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Nomor Polisi')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('year_of_purchase')
                    ->label('Tahun Beli')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
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
