<?php

namespace App\Filament\Resources\Stocks;

use App\Filament\Resources\Stocks\Pages\CreateStock;
use App\Filament\Resources\Stocks\Pages\EditStock;
use App\Filament\Resources\Stocks\Pages\ListStocks;
use App\Filament\Resources\Stocks\Pages\ViewStock;
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

    // Правилна икона
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?string $navigationLabel = 'Наличности';
    

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
     * ⭐ Само супер администратор може да създава наличности ⭐
     * (В краен случай, защото нормално идват от доставки)
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Само супер администратор
        return $user->hasRole('super_admin');
    }
    
    /**
     * ⭐ Само супер администратор може да редактира наличности ⭐
     * (В краен случай, защото нормално идват от доставки/продажби)
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Само супер администратор
        return $user->hasRole('super_admin');
    }
    
    /**
     * ⭐ НИКОЙ НЕ МОЖЕ ДА ИЗТРИВА НАЛИЧНОСТИ ⭐
     */
    public static function canDelete($record): bool
    {
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStocks::route('/'),
            'create' => CreateStock::route('/create'),
            // 'edit' => EditStock::route('/{record}/edit'),
            'view' => ViewStock::route('/{record}/view'),
        ];
    }
}