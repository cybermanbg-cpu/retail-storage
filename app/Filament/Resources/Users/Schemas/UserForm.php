<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Owner;
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

                                        TextInput::make('phone')
                                            ->label('Телефон')
                                            ->tel()
                                            ->maxLength(255)
                                            ->nullable(),
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

                        // ====================== TAB 3 - РОЛИ И ПРАВА ======================
                        Tabs\Tab::make('Роли и права')
                            ->icon('heroicon-o-shield-check')
                            ->visible($isSuperAdmin || $isOwner)
                            ->schema([
                                Section::make('Достъп и права')
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
                                            ->helperText('Изберете собственика, към който принадлежи този потребител'),

                                        Select::make('roles')
                                            ->label('Роли')
                                            ->options(fn() => Role::where('name', '!=', 'super_admin')
                                                ->orderBy('name')
                                                ->pluck('name', 'name'))
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->visible($isSuperAdmin || $isOwner)
                                            ->helperText('Изберете ролите за този потребител'),
                                    ])
                                    ->columns(2),
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