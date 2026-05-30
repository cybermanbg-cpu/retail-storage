<?php

namespace App\Filament\Resources\Stocks;

use App\Filament\Resources\Stocks\Pages\CreateStock;
use App\Filament\Resources\Stocks\Pages\EditStock;
use App\Filament\Resources\Stocks\Pages\ListStocks;
use App\Filament\Resources\Stocks\Schemas\StockForm;
use App\Filament\Resources\Stocks\Tables\StocksTable;
use App\Models\Stock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StocksTable::configure($table);
    }

     /**
     * Филтриране на наличностите според ролята на логнатия потребител
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        // Ако няма логнат потребител
        if (!$user) {
            return $query->whereRaw('0 = 1');
        }
        
        // Супер администратор вижда всички
        if ($user->hasRole('super_admin')) {
            return $query;
        }
        
        // Собственик вижда само наличностите към неговата фирма
        if ($user->hasRole('owner') && $user->owner_id) {
            return $query->whereHas('productVariant.product', function ($q) use ($user) {
                $q->where('owner_id', $user->owner_id);
            });
        }
        
        // Мениджър вижда наличностите в неговите обекти
        if ($user->hasRole('manager') && $user->owner_id) {
            return $query->whereHas('storageObject', function ($q) use ($user) {
                $q->where('owner_id', $user->owner_id);
            });
        }
        
        // Касиер вижда само наличности (само за четене)
        if ($user->hasRole('cashier')) {
            return $query->whereHas('storageObject', function ($q) use ($user) {
                $q->where('owner_id', $user->owner_id);
            });
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
        
        return $user->hasRole('super_admin') || 
               $user->hasRole('owner') || 
               $user->hasRole('manager') ||
               $user->hasRole('cashier');
    }
    
    /**
     * Кой може да създава наличности
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ може да създава наличности
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        return $user->hasRole('super_admin') || 
               $user->hasRole('owner') || 
               $user->hasRole('manager');
    }
    
    /**
     * Кой може да редактира наличност
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ може да редактира наличности
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        // Супер администратор може да редактира всички
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Собственик и мениджър могат да редактират само своите
        if (($user->hasRole('owner') || $user->hasRole('manager')) && $user->owner_id) {
            // Проверка дали наличността е към техния собственик
            $productOwnerId = $record->productVariant->product->owner_id ?? null;
            return $productOwnerId === $user->owner_id;
        }
        
        return false;
    }
    
    /**
     * Кой може да изтрива наличност
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
            'index' => ListStocks::route('/'),
            'create' => CreateStock::route('/create'),
            'edit' => EditStock::route('/{record}/edit'),
        ];
    }
}
