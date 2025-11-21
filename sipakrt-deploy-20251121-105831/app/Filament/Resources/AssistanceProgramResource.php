<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssistanceProgramResource\Pages;
use App\Filament\Resources\AssistanceProgramResource\RelationManagers;
use App\Models\AssistanceProgram;
use App\Models\Rt;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AssistanceProgramResource extends Resource
{
    protected static ?string $model = AssistanceProgram::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static ?string $navigationGroup = 'Administratif Warga';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'Program Bantuan';

    protected static ?string $pluralModelLabel = 'Program Bantuan Sosial';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Program')
                    ->schema([
                        Forms\Components\Select::make('rt_id')
                            ->label('RT Penyelenggara')
                            ->options(fn () => Rt::query()->orderBy('number')->pluck('number', 'id')->toArray())
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Program')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('category')
                            ->label('Kategori')
                            ->maxLength(120),
                        Forms\Components\Select::make('source')
                            ->label('Sumber')
                            ->options([
                                'internal' => 'Internal',
                                'external' => 'Eksternal',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Mulai'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Selesai'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Masih Aktif')
                            ->default(true),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Program')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rt.number')
                    ->label('RT')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Sumber')
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipients_count')
                    ->label('Penerima')
                    ->counts('recipients')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Sumber')
                    ->options([
                        'internal' => 'Internal',
                        'external' => 'Eksternal',
                    ]),
                Tables\Filters\SelectFilter::make('rt')
                    ->label('RT')
                    ->relationship('rt', 'number'),
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
            RelationManagers\RecipientsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssistancePrograms::route('/'),
            'create' => Pages\CreateAssistanceProgram::route('/create'),
            'edit' => Pages\EditAssistanceProgram::route('/{record}/edit'),
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
