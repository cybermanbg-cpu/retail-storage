<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Owner;
use App\Models\StorageObject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = Auth::user()?->hasRole('super_admin');
        $isOwner = Auth::user()?->hasRole('owner');
        $record = $schema->getRecord();

        return $schema
            ->components([
                Tabs::make('User Tabs')
                    ->tabs([
                        // ====================== TAB 1 - ОСНОВНИ ДАННИ ======================
                        Tabs\Tab::make('Основни данни')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Лична информация')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Име')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('email')
                                            ->label('Имейл')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),

                        // ====================== TAB 2 - ПАРОЛА ======================
                        Tabs\Tab::make('Парола')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Section::make('Смяна на парола')
                                    ->schema([
                                        TextInput::make('password')
                                            ->label('Нова парола')
                                            ->password()
                                            ->required(fn(string $context): bool => $context === 'create')
                                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                            ->dehydrated(fn($state) => filled($state))
                                            ->minLength(8)
                                            ->same('password_confirmation'),

                                        TextInput::make('password_confirmation')
                                            ->label('Потвърждение на паролата')
                                            ->password()
                                            ->dehydrated(false)
                                            ->required(fn(string $context): bool => $context === 'create'),
                                    ])
                                    ->columns(2),
                            ]),

                        // ====================== TAB 3 - РОЛИ, ПРАВА И СКЛАД ======================
                        Tabs\Tab::make('Роли, права и склад')
                            ->icon('heroicon-o-shield-check')
                            ->visible($isSuperAdmin || $isOwner)
                            ->schema([
                                Section::make('Организационни настройки')
                                    ->schema([
                                        Select::make('owner_id')
                                            ->label('Собственик')
                                            ->options(fn() => Owner::where('is_active', true)
                                                ->orderBy('name')
                                                ->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->required()
                                            ->visible($isSuperAdmin)
                                            ->helperText('Изберете собственика, към който принадлежи този потребител')
                                            ->reactive()
                                            ->afterStateUpdated(fn($set) => $set('storage_object_id', null)),

                                        Select::make('role')
                                            ->label('Роля')
                                            ->options(fn() => Role::where('name', '!=', 'super_admin')
                                                ->orderBy('name')
                                                ->pluck('name', 'name'))
                                            ->required()
                                            ->visible($isSuperAdmin || $isOwner)
                                            ->helperText('Изберете роля за този потребител')
                                            ->reactive(),
                                    ])
                                    ->columns(2),

                                // ⭐ НОВА СЕКЦИЯ - СКЛАДОВ ОБЕКТ ⭐
                                Section::make('Складов обект')
                                    ->description('Изберете складов обект, с който ще работи потребителят')
                                    ->schema([
                                        Select::make('storage_object_id')
                                            ->label('Складов обект')
                                            ->options(function ($get) use ($isSuperAdmin, $record) {
                                                // За super_admin - всички складове
                                                if ($isSuperAdmin) {
                                                    return StorageObject::where('is_active', true)
                                                        ->with('owner')
                                                        ->orderBy('name')
                                                        ->get()
                                                        ->mapWithKeys(fn($item) => [
                                                            $item->id => $item->name . ' (' . ($item->owner->name ?? 'Без собственик') . ')'
                                                        ])
                                                        ->toArray();
                                                }
                                                
                                                // За owner - само складовете на неговия собственик
                                                $ownerId = $get('owner_id') ?? $record?->owner_id ?? Auth::user()?->owner_id;
                                                
                                                if ($ownerId) {
                                                    return StorageObject::where('owner_id', $ownerId)
                                                        ->where('is_active', true)
                                                        ->orderBy('name')
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                }
                                                
                                                return [];
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->helperText('Изберете складов обект, в който потребителят ще работи (само за касиери и щандове)')
                                            ->placeholder('Изберете складов обект...'),
                                    ])
                                    ->columns(1)
                                    ->collapsible()
                                    ->collapsed(fn($get) => empty($get('storage_object_id'))),
                            ]),

                        // ====================== TAB 4 - СТАТУС ======================
                        Tabs\Tab::make('Статус')
                            ->icon('heroicon-o-flag')
                            ->schema([
                                Section::make('Активност')
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Активен потребител')
                                            ->helperText('Ако потребителят не е активен, няма да може да влиза в системата')
                                            ->default(true),

                                        TextInput::make('email_verified_at')
                                            ->label('Имейл верифициран на')
                                            ->disabled()
                                            ->visible(fn($record) => $record && $record->email_verified_at),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->activeTab(1),
            ]);
    }
}