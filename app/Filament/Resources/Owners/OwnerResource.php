<?php

namespace App\Filament\Resources\Owners;

use App\Filament\Resources\Owners\Pages\CreateOwner;
use App\Filament\Resources\Owners\Pages\EditOwner;
use App\Filament\Resources\Owners\Pages\ListOwners;
use App\Filament\Resources\Owners\Pages\ViewOwner;
use App\Filament\Resources\Owners\Schemas\OwnerForm;
use App\Filament\Resources\Owners\Tables\OwnersTable;
use App\Models\Owner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OwnerResource extends Resource
{
    protected static ?string $model = Owner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingStorefront;
    
    protected static ?string $navigationLabel = 'Собственици';
    
    protected static ?string $modelLabel = 'Собственик';
    
    protected static ?string $pluralModelLabel = 'Собственици';

    public static function form(Schema $schema): Schema
    {
        return OwnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OwnersTable::configure($table);
    }

    /**
     * Филтриране на собствениците според ролята на логнатия потребител
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        // Ако няма логнат потребител
        if (!$user) {
            return $query->whereRaw('0 = 1');
        }
        
        // Супер администратор вижда всички собственици
        if ($user->hasRole('super_admin')) {
            return $query;
        }
        
        // Собственик вижда само себе си
        if ($user->hasRole('owner') && $user->owner_id) {
            return $query->where('id', $user->owner_id);
        }
        
        // Мениджър и касиер не виждат собственици
        return $query->whereRaw('0 = 1');
    }
    
    /**
     * Кой може да вижда ресурса
     */
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Само супер администратор и собственик могат да виждат собственици
        return $user->hasRole('super_admin') || $user->hasRole('owner');
    }
    
    /**
     * Кой може да създава собственици
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Само супер администратор може да създава собственици
        return $user->hasRole('super_admin');
    }
    
    /**
     * Кой може да редактира собственик
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Супер администратор може да редактира всички
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Собственик може да редактира само себе си
        if ($user->hasRole('owner') && $user->owner_id) {
            return $record->id === $user->owner_id;
        }
        
        return false;
    }
    
    /**
     * Кой може да изтрива собственик
     */
    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Само супер администратор може да изтрива собственици
        return $user->hasRole('super_admin');
    }
    
    /**
     * Скриване на навигацията за неупълномощени
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
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
            'index' => ListOwners::route('/'),
            'create' => CreateOwner::route('/create'),
            'edit' => EditOwner::route('/{record}/edit'),
            'view' => ViewOwner::route('/{record}/view'),
        ];
    }
}