<?php

namespace App\Filament\Resources\Purchases;

use App\Filament\Resources\Purchases\Pages\CreatePurchase;
use App\Filament\Resources\Purchases\Pages\EditPurchase;
use App\Filament\Resources\Purchases\Pages\ListPurchases;
use App\Filament\Resources\Purchases\Schemas\PurchaseForm;
use App\Filament\Resources\Purchases\Tables\PurchasesTable;
use App\Models\Purchase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PurchaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchasesTable::configure($table);
    }

    /**
     * Филтриране на покупките според ролята на логнатия потребител
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        if (!$user) {
            return $query->whereRaw('0 = 1');
        }
        
        // Супер администратор вижда всички
        if ($user->hasRole('super_admin')) {
            return $query;
        }
        
        // Собственик вижда само своите покупки
        if ($user->hasRole('owner') && $user->owner_id) {
            return $query->where('owner_id', $user->owner_id);
        }
        
        // Мениджър вижда покупките на своите обекти
        if ($user->hasRole('manager') && $user->owner_id) {
            return $query->where('owner_id', $user->owner_id);
        }
        
        // Касиер НЕ вижда покупки
        if ($user->hasRole('cashier')) {
            return $query->whereRaw('0 = 1');
        }
        
        return $query->whereRaw('0 = 1');
    }
    
    /**
     * Кой може да вижда ресурса
     */
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ вижда покупки
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        return $user->hasRole('super_admin') || 
               $user->hasRole('owner') || 
               $user->hasRole('manager');
    }
    
    /**
     * Кой може да създава покупки
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ може да създава покупки
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        return $user->hasRole('super_admin') || 
               $user->hasRole('owner') || 
               $user->hasRole('manager');
    }
    
    /**
     * Кой може да редактира покупка
     */
    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ може да редактира
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        // Само чернови могат да се редактират
        if ($record->status !== 'draft') {
            return false;
        }
        
        // Супер администратор може да редактира всички чернови
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Собственик и мениджър могат да редактират само своите чернови
        if (($user->hasRole('owner') || $user->hasRole('manager')) && $user->owner_id) {
            return $record->owner_id === $user->owner_id;
        }
        
        return false;
    }
    
    /**
     * Кой може да изтрива покупка
     */
    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ може да изтрива
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        // Мениджърът НЕ може да изтрива
        if ($user->hasRole('manager')) {
            return false;
        }
        
        // Само чернови могат да се изтриват
        if ($record->status !== 'draft') {
            return false;
        }
        
        // Супер администратор може да изтрива всички чернови
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Собственик може да изтрива само своите чернови
        if ($user->hasRole('owner') && $user->owner_id) {
            return $record->owner_id === $user->owner_id;
        }
        
        return false;
    }
    
    /**
     * Кой може да преглежда покупка
     */
    public static function canView(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ може да преглежда
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        // Супер администратор може да преглежда всички
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Собственик и мениджър могат да преглеждат своите
        if (($user->hasRole('owner') || $user->hasRole('manager')) && $user->owner_id) {
            return $record->owner_id === $user->owner_id;
        }
        
        return false;
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
            'index' => ListPurchases::route('/'),
            'create' => CreatePurchase::route('/create'),
            'edit' => EditPurchase::route('/{record}/edit'),
        ];
    }
}