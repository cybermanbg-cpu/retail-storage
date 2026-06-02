<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$item) {
                // Конвертиране на числата
                $item['quantity']   = floatval(str_replace(',', '.', $item['quantity'] ?? 1));
                $item['unit_cost']  = floatval(str_replace(',', '.', $item['unit_cost'] ?? 0));

                // Запазваме изчислените стойности
                $item['total_cost'] = isset($item['total_cost']) 
                    ? floatval(str_replace(',', '.', $item['total_cost'])) 
                    : ($item['quantity'] * $item['unit_cost']);

                $item['final_unit_cost'] = isset($item['final_unit_cost']) 
                    ? floatval(str_replace(',', '.', $item['final_unit_cost'])) 
                    : $item['unit_cost'];

                $item['delivery_cost_share'] = isset($item['delivery_cost_share']) 
                    ? floatval(str_replace(',', '.', $item['delivery_cost_share'])) 
                    : 0;

                // Окръгляне
                $item['quantity']            = round($item['quantity'], 4);
                $item['unit_cost']           = round($item['unit_cost'], 4);
                $item['total_cost']          = round($item['total_cost'], 2);
                $item['final_unit_cost']     = round($item['final_unit_cost'], 4);
                $item['delivery_cost_share'] = round($item['delivery_cost_share'], 4);
            }
        }

        // Основни полета
        $data['discount']      = floatval(str_replace(',', '.', $data['discount'] ?? 0));
        $data['delivery_cost'] = floatval(str_replace(',', '.', $data['delivery_cost'] ?? 0));
        $data['vat']           = floatval(str_replace(',', '.', $data['vat'] ?? 20));

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        // Ако не е завършена - презареждаме формата, за да се видят актуалните стойности
        if ($record->status !== 'completed') {
            $this->fillForm();
        }
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $user = Auth::user();

        $actions = [];

        $actions[] = Action::make('back_to_view')
            ->label('Върни се в преглед')
            ->url(fn() => PurchaseResource::getUrl('view', ['record' => $record]))
            ->color('gray')
            ->icon('heroicon-o-arrow-left');

        if ($record->status === 'draft' || $user->hasRole('admin') || $user->hasRole('super_admin')) {
            
            $actions[] = Action::make('save_and_continue')
                ->label('Запази и продължи')
                ->color('success')
                ->icon('heroicon-o-document-text')
                ->action('save');

            if ($record->status === 'draft') {
                $actions[] = Action::make('complete')
                    ->label('Завърши покупката')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () {
                        $record = $this->getRecord();
                        $record->status = 'completed';
                        $record->save();

                        \Filament\Notifications\Notification::make()
                            ->title('Покупката е завършена успешно')
                            ->success()
                            ->send();

                        $this->redirect(PurchaseResource::getUrl('view', ['record' => $record]));
                    });
            }

            if ($record->status === 'draft') {
                $actions[] = DeleteAction::make();
            }
        }

        return $actions;
    }
}