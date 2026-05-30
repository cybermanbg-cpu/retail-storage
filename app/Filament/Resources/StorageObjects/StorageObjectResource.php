<?php

namespace App\Filament\Resources\StorageObjects;

use App\Filament\Resources\StorageObjects\Pages\CreateStorageObject;
use App\Filament\Resources\StorageObjects\Pages\EditStorageObject;
use App\Filament\Resources\StorageObjects\Pages\ListStorageObjects;
use App\Filament\Resources\StorageObjects\Schemas\StorageObjectForm;
use App\Filament\Resources\StorageObjects\Tables\StorageObjectsTable;
use App\Models\StorageObject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StorageObjectResource extends Resource
{
    protected static ?string $model = StorageObject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StorageObjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StorageObjectsTable::configure($table);
    }

    /**
     * Филтриране на потребителите според ролята на логнатия потребител
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        // Ако няма логнат потребител
        if (!$user) {
            return $query->whereRaw('0 = 1');
        }
        
        // Касиер и мениджър не виждат потребители
        if ($user->hasRole('cashier') || $user->hasRole('manager')) {
            return $query->whereRaw('0 = 1');
        }
        
        // Супер администратор вижда всички
        if ($user->hasRole('super_admin')) {
            return $query;
        }
        
        // Собственик вижда само потребителите към неговата фирма
        if ($user->hasRole('owner') && $user->owner_id) {
            return $query->where('owner_id', $user->owner_id);
        }
        
        // За всеки друг случай
        return $query->whereRaw('0 = 1');
    }
    
    /**
     * Кой може да вижда ресурса
     */
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиер и мениджър НЯМАТ достъп
        if ($user->hasRole('cashier') || $user->hasRole('manager')) {
            return false;
        }
        
        return $user->hasRole('super_admin') || $user->hasRole('owner');
    }
    
    /**
     * Кой може да създава потребители
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиер и мениджър НЯМАТ достъп
        if ($user->hasRole('cashier') || $user->hasRole('manager')) {
            return false;
        }
        
        return $user->hasRole('super_admin') || $user->hasRole('owner');
    }
    
    /**
     * Кой може да редактира потребител
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиер и мениджър НЯМАТ достъп
        if ($user->hasRole('cashier') || $user->hasRole('manager')) {
            return false;
        }
        
        // Супер администратор може да редактира всички
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Собственик може да редактира само потребителите от неговата фирма
        if ($user->hasRole('owner') && $user->owner_id) {
            return $record->owner_id === $user->owner_id;
        }
        
        return false;
    }
    
    /**
     * Кой може да изтрива потребител
     */
    public static function canDelete($record): bool
    {
        return static::canEdit($record);
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
            'index' => ListStorageObjects::route('/'),
            'create' => CreateStorageObject::route('/create'),
            'edit' => EditStorageObject::route('/{record}/edit'),
        ];
    }
}
