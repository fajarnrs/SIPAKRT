<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RtOfficialResource\Pages;
use App\Filament\Resources\RtOfficialResource\RelationManagers;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\RtOfficial;
use Filament\Forms;

use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RtOfficialResource extends Resource
{
    protected static ?string $model = RtOfficial::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Administratif Warga';

    protected static ?int $navigationSort = 8;

    protected static ?string $modelLabel = 'Pengurus RT';

    protected static ?string $pluralModelLabel = 'Sistem Kepengurusan RT';

    protected static ?string $navigationLabel = 'Sistem Kepengurusan RT';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Kepengurusan')
                    ->schema([
                        Forms\Components\Select::make('rt_id')
                            ->label('RT')
                            ->options(fn () => Rt::query()->orderBy('number')->pluck('number', 'id')->toArray())
                            ->required()
                            ->searchable()
                            ->reactive(),
                        Forms\Components\Select::make('resident_id')
                            ->label('Warga Ditunjuk')
                            ->required()
                            ->searchable()
                            ->options(fn (callable $get) => static::getResidentsByRt($get('rt_id')))
                            ->getSearchResultsUsing(fn (string $search, callable $get): array => static::getResidentsByRt($get('rt_id'), $search))
                            ->placeholder('Pilih warga dalam RT'),
                        Forms\Components\TextInput::make('position')
                            ->label('Jabatan')
                            ->default('Ketua RT')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\DatePicker::make('started_at')
                            ->label('Mulai Menjabat')
                            ->required(),
                        Forms\Components\DatePicker::make('ended_at')
                            ->label('Selesai Menjabat')
                            ->helperText('Kosongkan bila masih aktif'),
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
                Tables\Columns\TextColumn::make('rt.number')
                    ->label('RT')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('resident.name')
                    ->label('Nama Pengurus')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('position')
                    ->label('Jabatan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (RtOfficial $record) => $record->isActive() ? 'Menjabat' : 'Selesai')
                    ->colors([
                        'success' => fn ($state, RtOfficial $record) => $record->isActive(),
                        'danger' => fn ($state, RtOfficial $record) => ! $record->isActive(),
                    ]),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ended_at')
                    ->label('Status Jabatan')
                    ->trueLabel('Sudah Berakhir')
                    ->falseLabel('Masih Aktif')
                    ->nullable(),
                Tables\Filters\SelectFilter::make('rt')
                    ->label('Filter RT')
                    ->relationship('rt', 'number'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('endTerm')
                    ->label('Akhiri Jabatan')
                    ->visible(fn (RtOfficial $record) => $record->isActive())
                    ->icon('heroicon-o-stop-circle')
                    ->requiresConfirmation()
                    ->action(fn (RtOfficial $record) => $record->endNow()),
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
            'index' => Pages\ListRtOfficials::route('/'),
            'create' => Pages\CreateRtOfficial::route('/create'),
            'edit' => Pages\EditRtOfficial::route('/{record}/edit'),
        ];
    }

    protected static function getResidentsByRt($rtId, string $search = null): array
    {
        return Resident::query()
            ->when($rtId, fn ($query) => $query->whereHas('household', fn (Builder $sub) => $sub->where('rt_id', $rtId)))
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->whereNotNull('email')
            ->orderBy('name')
            ->limit(50)
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && ! $user->is_admin && $user->role === 'rt' && $user->rt_id) {
            return $query->where('rt_id', $user->rt_id);
        }

        if ($user && $user->role === 'warga') {
            return $query->whereRaw('1 = 0');
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
