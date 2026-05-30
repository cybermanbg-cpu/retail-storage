<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\StocksRelationManager;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

   /**
     * Филтриране на продуктите според ролята на логнатия потребител
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
        
        // Собственик вижда само своите продукти
        if ($user->hasRole('owner') && $user->owner_id) {
            return $query->where('owner_id', $user->owner_id);
        }
        
        // Мениджър вижда продуктите на своя собственик
        if ($user->hasRole('manager') && $user->owner_id) {
            return $query->where('owner_id', $user->owner_id);
        }
        
        // Касиер вижда продуктите на своя собственик (само за четене)
        if ($user->hasRole('cashier') && $user->owner_id) {
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
        
        return $user->hasRole('super_admin') || 
               $user->hasRole('owner') || 
               $user->hasRole('manager') ||
               $user->hasRole('cashier');
    }
    
    /**
     * Кой може да създава продукти
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ може да създава продукти
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        return $user->hasRole('super_admin') || 
               $user->hasRole('owner') || 
               $user->hasRole('manager');
    }
    
    /**
     * Кой може да редактира продукт
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ може да редактира продукти
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        // Супер администратор може да редактира всички
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Собственик и мениджър могат да редактират само своите продукти
        if (($user->hasRole('owner') || $user->hasRole('manager')) && $user->owner_id) {
            return $record->owner_id === $user->owner_id;
        }
        
        return false;
    }
    
    /**
     * Кой може да изтрива продукт
     */
    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Касиерът НЕ може да изтрива продукти
        if ($user->hasRole('cashier')) {
            return false;
        }
        
        // Само супер администратор и собственик могат да изтриват
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        if ($user->hasRole('owner') && $user->owner_id) {
            return $record->owner_id === $user->owner_id;
        }
        
        // Мениджърът НЕ може да изтрива продукти
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
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
