<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Services\CostCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'storageObject', 'items'])
            ->orderBy('purchase_date', 'desc')
            ->paginate(20);
        
        return view('purchases.index', compact('purchases'));
    }
    
    public function create()
    {
        return view('purchases.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'storage_object_id' => 'required|exists:storage_objects,id',
            'supplier_id' => 'nullable|exists:clients,id',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Изчисляване на общите суми
            $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_cost']);
            
            // Разпределяне на транспортните разходи
            $itemsWithDelivery = CostCalculationService::distributeDeliveryCost(
                $request->items,
                $request->delivery_cost ?? 0
            );
            
            // Създаване на покупката
            $purchase = Purchase::create([
                'owner_id' => 1, // от сесията
                'storage_object_id' => $request->storage_object_id,
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'purchase_number' => 'PO-' . date('Ymd') . '-' . rand(1000, 9999),
                'purchase_date' => $request->purchase_date,
                'invoice_date' => $request->invoice_date,
                'supplier_invoice' => $request->supplier_invoice,
                'subtotal' => $subtotal,
                'discount' => $request->discount ?? 0,
                'delivery_cost' => $request->delivery_cost ?? 0,
                'vat' => $request->vat ?? 0,
                'total' => $subtotal + ($request->delivery_cost ?? 0) + ($request->vat ?? 0) - ($request->discount ?? 0),
                'status' => 'completed',
                'notes' => $request->notes,
            ]);
            
            // Създаване на артикулите и актуализиране на наличността
            foreach ($itemsWithDelivery as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                    'delivery_cost_share' => $item['delivery_cost_share'],
                    'final_unit_cost' => $item['final_unit_cost'],
                ]);
                
                // Актуализиране на наличността
                $stock = Stock::where('product_variant_id', $item['product_variant_id'])
                    ->where('storage_object_id', $request->storage_object_id)
                    ->first();
                
                if ($stock) {
                    // Актуализиране на средната цена
                    $costData = CostCalculationService::calculateNewAverageCost(
                        $item['product_variant_id'],
                        $request->storage_object_id,
                        $item['quantity'],
                        $item['final_unit_cost']
                    );
                    
                    $stock->quantity += $item['quantity'];
                    $stock->average_cost = $costData['new_average_cost'];
                    $stock->save();
                    
                    // Добавяне на cost layer за FIFO
                    CostCalculationService::addCostLayer(
                        $item['product_variant_id'],
                        $request->storage_object_id,
                        $item['quantity'],
                        $item['final_unit_cost']
                    );
                } else {
                    Stock::create([
                        'product_variant_id' => $item['product_variant_id'],
                        'storage_object_id' => $request->storage_object_id,
                        'quantity' => $item['quantity'],
                        'average_cost' => $item['final_unit_cost'],
                        'reserved_quantity' => 0,
                        'min_quantity' => 0,
                    ]);
                    
                    CostCalculationService::addCostLayer(
                        $item['product_variant_id'],
                        $request->storage_object_id,
                        $item['quantity'],
                        $item['final_unit_cost']
                    );
                }
            }
            
            DB::commit();
            
            return redirect()->route('purchases.index')
                ->with('success', 'Покупката е записана успешно!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function show(Purchase $purchase)
    {
        $purchase->load(['items.productVariant.product', 'supplier', 'storageObject', 'user']);
        return view('purchases.show', compact('purchase'));
    }
    
    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();
        
        try {
            // Възстановяване на наличността и себестойността
            foreach ($purchase->items as $item) {
                $stock = Stock::where('product_variant_id', $item->product_variant_id)
                    ->where('storage_object_id', $purchase->storage_object_id)
                    ->first();
                
                if ($stock) {
                    $stock->quantity -= $item->quantity;
                    
                    // Преизчисляване на средната цена (трябва да се вземе предвид предишния слой)
                    // Това е опростена версия, за FIFO трябва да се възстановят слоевете
                    $stock->save();
                }
            }
            
            $purchase->delete();
            
            DB::commit();
            
            return redirect()->route('purchases.index')
                ->with('success', 'Покупката е изтрита успешно!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}