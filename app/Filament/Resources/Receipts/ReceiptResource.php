<?php

namespace App\Filament\Resources\Receipts;

use App\Filament\Resources\Receipts\Pages\CreateReceipt;
use App\Filament\Resources\Receipts\Pages\EditReceipt;
use App\Filament\Resources\Receipts\Pages\ListReceipts;
use App\Filament\Resources\Receipts\Pages\ViewReceipt;
use App\Filament\Resources\Receipts\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\Receipts\Schemas\ReceiptForm;
use App\Filament\Resources\Receipts\Tables\ReceiptsTable;
use App\Models\Receipt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

     protected static ?string $navigationLabel = 'Продажби';

    protected static ?string $modelLabel = 'Продажба';

    protected static ?string $pluralModelLabel = 'Продажби';

    public static function form(Schema $schema): Schema
    {
        return ReceiptForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiptsTable::configure($table);
    }

    /**
     * Филтриране според ролята
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
        
        // Собственик вижда само своите продажби
        if ($user->hasRole('owner') && $user->owner_id) {
            return $query->where('owner_id', $user->owner_id);
        }
        
        // Мениджър вижда продажбите на своите обекти
        if ($user->hasRole('manager') && $user->owner_id) {
            return $query->whereHas('storageObject', function ($q) use ($user) {
                $q->where('owner_id', $user->owner_id);
            });
        }
        
        // Касиер вижда само своите продажби
        if ($user->hasRole('cashier')) {
            return $query->where('user_id', $user->id);
        }
        
        return $query->whereRaw('0 = 1');
    }

    /**
     * Всички могат да виждат ресурса
     */
    public static function canViewAny(): bool
    {
        return Auth::check();
    }
    
    /**
     * Всички могат да преглеждат конкретен запис
     */
    public static function canView($record): bool
    {
        return Auth::check();
    }
    
    /**
     * ⭐ НИКОЙ НЕ МОЖЕ ДА СЪЗДАВА РЪЧНО ⭐
     * Само през POS модула
     */
    public static function canCreate(): bool
    {
        return false;
    }
    
    /**
     * ⭐ САМО АДМИНИСТРАТОРИ МОГАТ ДА РЕДАКТИРАТ ⭐
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Само super_admin, owner и manager могат да редактират
        return $user->hasRole('super_admin') || 
               $user->hasRole('owner') || 
               $user->hasRole('manager');
    }
    
    /**
     * ⭐ САМО SUPER_ADMIN МОЖЕ ДА ИЗТРИВА ⭐
     */
    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
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
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceipts::route('/'),
            'create' => CreateReceipt::route('/create'),
            'edit' => EditReceipt::route('/{record}/edit'),
            'view' => ViewReceipt::route('/{record}/view'),
        ];
    }
}
