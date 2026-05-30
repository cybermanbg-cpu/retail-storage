<?php

namespace Database\Seeders;

use App\Models\Owner;
use App\Models\StorageObject;
use App\Models\Color;
use App\Models\Size;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Client;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Invoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // 1. СОБСТВЕНИЦИ
        // ========================================
        
        $owner1 = Owner::create([
            'name' => 'Иван Иванов',
            'company_name' => 'Иванов ЕООД',
            'email' => 'ivan@ivanov.com',
            'phone' => '0888123456',
            'vat_number' => 'BG123456789',
            'is_active' => true,
        ]);
        
        $owner2 = Owner::create([
            'name' => 'Мария Петрова',
            'company_name' => 'Мария ЕТ',
            'email' => 'maria@petrova.com',
            'phone' => '0888111222',
            'vat_number' => 'BG987654321',
            'is_active' => true,
        ]);
        
        // ========================================
        // 2. ОБЕКТИ (магазини/складове)
        // ========================================
        
        // Обекти за Собственик 1
        $object1_1 = StorageObject::create([
            'owner_id' => $owner1->id,
            'name' => 'Основен склад София',
            'address' => 'София, ул. Примерна 1',
            'phone' => '028123456',
            'manager_name' => 'Петър Петров',
            'is_active' => true,
        ]);
        
        $object1_2 = StorageObject::create([
            'owner_id' => $owner1->id,
            'name' => 'Магазин Варна',
            'address' => 'Варна, бул. Приморски 5',
            'phone' => '052123456',
            'manager_name' => 'Георги Георгиев',
            'is_active' => true,
        ]);
        
        $object1_3 = StorageObject::create([
            'owner_id' => $owner1->id,
            'name' => 'Магазин Пловдив',
            'address' => 'Пловдив, ул. Главна 10',
            'phone' => '032123456',
            'manager_name' => 'Димитър Димитров',
            'is_active' => false, // неактивен обект
        ]);
        
        // Обекти за Собственик 2
        $object2_1 = StorageObject::create([
            'owner_id' => $owner2->id,
            'name' => 'Склад Бургас',
            'address' => 'Бургас, Индустриална зона',
            'phone' => '056123456',
            'manager_name' => 'Стоян Стоянов',
            'is_active' => true,
        ]);
        
        // ========================================
        // 3. ЦВЕТОВЕ
        // ========================================
        
        // Глобални цветове (owner_id = null)
        $red = Color::create([
            'owner_id' => null,
            'name' => 'Червен',
            'code' => '#FF0000',
            'is_active' => true,
        ]);
        
        $blue = Color::create([
            'owner_id' => null,
            'name' => 'Син',
            'code' => '#0000FF',
            'is_active' => true,
        ]);
        
        $green = Color::create([
            'owner_id' => null,
            'name' => 'Зелен',
            'code' => '#00FF00',
            'is_active' => true,
        ]);
        
        $black = Color::create([
            'owner_id' => null,
            'name' => 'Черен',
            'code' => '#000000',
            'is_active' => true,
        ]);
        
        $white = Color::create([
            'owner_id' => null,
            'name' => 'Бял',
            'code' => '#FFFFFF',
            'is_active' => true,
        ]);
        
        // Цветове само за Собственик 1
        $yellow = Color::create([
            'owner_id' => $owner1->id,
            'name' => 'Жълт',
            'code' => '#FFFF00',
            'is_active' => true,
        ]);
        
        // ========================================
        // 4. РАЗМЕРИ
        // ========================================
        
        // Глобални размери
        $xs = Size::create(['owner_id' => null, 'name' => 'XS', 'sort_order' => 0, 'is_active' => true]);
        $s = Size::create(['owner_id' => null, 'name' => 'S', 'sort_order' => 1, 'is_active' => true]);
        $m = Size::create(['owner_id' => null, 'name' => 'M', 'sort_order' => 2, 'is_active' => true]);
        $l = Size::create(['owner_id' => null, 'name' => 'L', 'sort_order' => 3, 'is_active' => true]);
        $xl = Size::create(['owner_id' => null, 'name' => 'XL', 'sort_order' => 4, 'is_active' => true]);
        $xxl = Size::create(['owner_id' => null, 'name' => 'XXL', 'sort_order' => 5, 'is_active' => true]);
        
        // Размери само за Собственик 2
        $customSize = Size::create([
            'owner_id' => $owner2->id,
            'name' => 'Kids M',
            'sort_order' => 10,
            'is_active' => true,
        ]);
        
        // ========================================
        // 5. ПРОДУКТИ
        // ========================================
        
        // --- Продукти за Собственик 1 ---
        
        // Продукт с варианти (цветове и размери)
        $product1 = Product::create([
            'owner_id' => $owner1->id,
            'name' => 'Техническа тениска',
            'sku' => 'TSHIRT001',
            'description' => 'Памучна тениска за спорт и ежедневие. Дишаща материя.',
            'type' => 'product',
            'base_price' => 39.90,
            'cost' => 20.00,
            'vat_rate' => 20,
            'has_variants' => true,
            'is_active' => true,
        ]);
        
        // Продукт без варианти (само един вид)
        $product2 = Product::create([
            'owner_id' => $owner1->id,
            'name' => 'Чаша керамична',
            'sku' => 'CUP001',
            'description' => 'Керамична чаша 350ml',
            'type' => 'product',
            'base_price' => 12.90,
            'cost' => 5.00,
            'vat_rate' => 20,
            'has_variants' => false,
            'is_active' => true,
        ]);
        
        // Продукт с варианти (само размери, без цветове)
        $product3 = Product::create([
            'owner_id' => $owner1->id,
            'name' => 'Дънки класически',
            'sku' => 'JEANS001',
            'description' => 'Класически дънки прав крои',
            'type' => 'product',
            'base_price' => 89.90,
            'cost' => 45.00,
            'vat_rate' => 20,
            'has_variants' => true,
            'is_active' => true,
        ]);
        
        // Услуга
        $service1 = Product::create([
            'owner_id' => $owner1->id,
            'name' => 'Монтаж на продукт',
            'sku' => 'SERV001',
            'description' => 'Монтаж и настройка на закупен продукт',
            'type' => 'service',
            'base_price' => 25.00,
            'cost' => 10.00,
            'vat_rate' => 20,
            'has_variants' => false,
            'is_active' => true,
        ]);
        
        // --- Продукти за Собственик 2 ---
        
        $product4 = Product::create([
            'owner_id' => $owner2->id,
            'name' => 'Детска тениска',
            'sku' => 'KIDS001',
            'description' => 'Детска памучна тениска',
            'type' => 'product',
            'base_price' => 19.90,
            'cost' => 10.00,
            'vat_rate' => 20,
            'has_variants' => true,
            'is_active' => true,
        ]);
        
        $service2 = Product::create([
            'owner_id' => $owner2->id,
            'name' => 'Доставка до адрес',
            'sku' => 'DELIVERY',
            'description' => 'Куриерска доставка',
            'type' => 'service',
            'base_price' => 5.99,
            'cost' => 3.00,
            'vat_rate' => 20,
            'has_variants' => false,
            'is_active' => true,
        ]);
        
        // ========================================
        // 6. БАРКОДОВЕ
        // ========================================
        
        ProductBarcode::create([
            'product_id' => $product1->id,
            'barcode' => '1234567890123',
            'type' => 'EAN13',
            'is_primary' => true,
        ]);
        
        ProductBarcode::create([
            'product_id' => $product1->id,
            'barcode' => 'TAG-TSHIRT-001',
            'type' => 'internal',
            'is_primary' => false,
        ]);
        
        ProductBarcode::create([
            'product_id' => $product2->id,
            'barcode' => '5901234123457',
            'type' => 'EAN13',
            'is_primary' => true,
        ]);
        
        ProductBarcode::create([
            'product_id' => $product3->id,
            'barcode' => '1234567890999',
            'type' => 'EAN13',
            'is_primary' => true,
        ]);
        
        // ========================================
        // 7. ВАРИАНТИ (комбинации цвят+размер)
        // ========================================
        
        // Варианти за Техническа тениска (продукт 1)
        $variants1 = [
            ['color' => $red, 'size' => $s, 'suffix' => 'RED-S', 'price_adj' => 0, 'qty_sofia' => 10, 'qty_varna' => 5],
            ['color' => $red, 'size' => $m, 'suffix' => 'RED-M', 'price_adj' => 0, 'qty_sofia' => 15, 'qty_varna' => 8],
            ['color' => $red, 'size' => $l, 'suffix' => 'RED-L', 'price_adj' => 0, 'qty_sofia' => 7, 'qty_varna' => 3],
            ['color' => $blue, 'size' => $s, 'suffix' => 'BLUE-S', 'price_adj' => 5, 'qty_sofia' => 5, 'qty_varna' => 2],
            ['color' => $blue, 'size' => $m, 'suffix' => 'BLUE-M', 'price_adj' => 5, 'qty_sofia' => 8, 'qty_varna' => 4],
            ['color' => $blue, 'size' => $l, 'suffix' => 'BLUE-L', 'price_adj' => 5, 'qty_sofia' => 4, 'qty_varna' => 1],
            ['color' => $black, 'size' => $s, 'suffix' => 'BLACK-S', 'price_adj' => 0, 'qty_sofia' => 12, 'qty_varna' => 6],
            ['color' => $black, 'size' => $m, 'suffix' => 'BLACK-M', 'price_adj' => 0, 'qty_sofia' => 20, 'qty_varna' => 10],
            ['color' => $black, 'size' => $l, 'suffix' => 'BLACK-L', 'price_adj' => 0, 'qty_sofia' => 8, 'qty_varna' => 3],
            ['color' => $green, 'size' => $m, 'suffix' => 'GREEN-M', 'price_adj' => 10, 'qty_sofia' => 3, 'qty_varna' => 0],
        ];
        
        foreach ($variants1 as $data) {
            $variant = ProductVariant::create([
                'product_id' => $product1->id,
                'color_id' => $data['color']->id,
                'size_id' => $data['size']->id,
                'sku_suffix' => $data['suffix'],
                'price_adjustment' => $data['price_adj'],
                'is_active' => true,
            ]);
            
            // Наличност в Основен склад София
            Stock::create([
                'product_variant_id' => $variant->id,
                'storage_object_id' => $object1_1->id,
                'quantity' => $data['qty_sofia'],
                'reserved_quantity' => 0,
                'min_quantity' => 3,
            ]);
            
            // Наличност в Магазин Варна
            Stock::create([
                'product_variant_id' => $variant->id,
                'storage_object_id' => $object1_2->id,
                'quantity' => $data['qty_varna'],
                'reserved_quantity' => 0,
                'min_quantity' => 2,
            ]);
        }
        
        // Варианти за Дънки (само размери, без цветове)
        $variants3 = [
            ['size' => $xs, 'suffix' => 'XS', 'price_adj' => -10, 'qty_sofia' => 5, 'qty_varna' => 2],
            ['size' => $s, 'suffix' => 'S', 'price_adj' => -5, 'qty_sofia' => 8, 'qty_varna' => 4],
            ['size' => $m, 'suffix' => 'M', 'price_adj' => 0, 'qty_sofia' => 12, 'qty_varna' => 6],
            ['size' => $l, 'suffix' => 'L', 'price_adj' => 0, 'qty_sofia' => 10, 'qty_varna' => 5],
            ['size' => $xl, 'suffix' => 'XL', 'price_adj' => 5, 'qty_sofia' => 4, 'qty_varna' => 1],
        ];
        
        foreach ($variants3 as $data) {
            $variant = ProductVariant::create([
                'product_id' => $product3->id,
                'color_id' => null,
                'size_id' => $data['size']->id,
                'sku_suffix' => $data['suffix'],
                'price_adjustment' => $data['price_adj'],
                'is_active' => true,
            ]);
            
            Stock::create([
                'product_variant_id' => $variant->id,
                'storage_object_id' => $object1_1->id,
                'quantity' => $data['qty_sofia'],
                'reserved_quantity' => 0,
                'min_quantity' => 2,
            ]);
            
            Stock::create([
                'product_variant_id' => $variant->id,
                'storage_object_id' => $object1_2->id,
                'quantity' => $data['qty_varna'],
                'reserved_quantity' => 0,
                'min_quantity' => 1,
            ]);
        }
        
        // Продукт без варианти (Чаша) - създаваме един "празен" вариант за уеднаквяване
        $variantCup = ProductVariant::create([
            'product_id' => $product2->id,
            'color_id' => null,
            'size_id' => null,
            'sku_suffix' => null,
            'price_adjustment' => 0,
            'is_active' => true,
        ]);
        
        Stock::create([
            'product_variant_id' => $variantCup->id,
            'storage_object_id' => $object1_1->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
            'min_quantity' => 10,
        ]);
        
        Stock::create([
            'product_variant_id' => $variantCup->id,
            'storage_object_id' => $object1_2->id,
            'quantity' => 20,
            'reserved_quantity' => 0,
            'min_quantity' => 5,
        ]);
        
        // Услуга Монтаж – създаваме празен вариант
        $variantService = ProductVariant::create([
            'product_id' => $service1->id,
            'color_id' => null,
            'size_id' => null,
            'sku_suffix' => null,
            'price_adjustment' => 0,
            'is_active' => true,
        ]);
        
        // Варианти за Собственик 2 (Детска тениска)
        $variants4 = [
            ['color' => $red, 'size' => $customSize, 'suffix' => 'RED-KM', 'price_adj' => 0, 'qty' => 15],
            ['color' => $blue, 'size' => $customSize, 'suffix' => 'BLUE-KM', 'price_adj' => 0, 'qty' => 12],
            ['color' => $green, 'size' => $customSize, 'suffix' => 'GREEN-KM', 'price_adj' => 0, 'qty' => 8],
        ];
        
        foreach ($variants4 as $data) {
            $variant = ProductVariant::create([
                'product_id' => $product4->id,
                'color_id' => $data['color']->id,
                'size_id' => $data['size']->id,
                'sku_suffix' => $data['suffix'],
                'price_adjustment' => $data['price_adj'],
                'is_active' => true,
            ]);
            
            Stock::create([
                'product_variant_id' => $variant->id,
                'storage_object_id' => $object2_1->id,
                'quantity' => $data['qty'],
                'reserved_quantity' => 0,
                'min_quantity' => 3,
            ]);
        }
        
        // Услуга Доставка за Собственик 2
        $variantDelivery = ProductVariant::create([
            'product_id' => $service2->id,
            'color_id' => null,
            'size_id' => null,
            'sku_suffix' => null,
            'price_adjustment' => 0,
            'is_active' => true,
        ]);
        
        // ========================================
        // 8. КЛИЕНТИ
        // ========================================
        
        $client1 = Client::create([
            'owner_id' => $owner1->id,
            'name' => 'Георги Георгиев',
            'email' => 'georgi@abv.bg',
            'phone' => '0887778888',
            'company_name' => null,
            'vat_number' => null,
            'address' => 'София, ж.к. Младост 1, бл.10',
            'default_discount' => 0,
            'is_active' => true,
        ]);
        
        $client2 = Client::create([
            'owner_id' => $owner1->id,
            'name' => 'Петър Петров',
            'email' => 'petar@company.com',
            'phone' => '0889990000',
            'company_name' => 'Петров АД',
            'vat_number' => 'BG555555555',
            'address' => 'Варна, бул. Цар Освободител 20',
            'default_discount' => 10, // 10% отстъпка
            'is_active' => true,
        ]);
        
        $client3 = Client::create([
            'owner_id' => $owner1->id,
            'name' => 'Мария Иванова',
            'email' => 'maria@mail.bg',
            'phone' => '0895111222',
            'company_name' => null,
            'vat_number' => null,
            'address' => 'Пловдив, ул. Капитан Райчо 5',
            'default_discount' => 0,
            'is_active' => true,
        ]);
        
        $client4 = Client::create([
            'owner_id' => $owner2->id,
            'name' => 'Ивайло Димитров',
            'email' => 'ivaylo@example.com',
            'phone' => '0888555666',
            'company_name' => null,
            'vat_number' => null,
            'address' => 'Бургас, ж.к. Изгрев 15',
            'default_discount' => 5,
            'is_active' => true,
        ]);
        
        // ========================================
        // 9. СТОКОВИ РАЗПИСКИ (продажби)
        // ========================================
        
        // Ползваме създадения вариант за тениска черен/M
        $blackMVariant = ProductVariant::where('product_id', $product1->id)
            ->whereHas('color', fn($q) => $q->where('name', 'Черен'))
            ->whereHas('size', fn($q) => $q->where('name', 'M'))
            ->first();
        
        // Стокова разписка 1 – продажба на клиент Георги
        $receipt1 = Receipt::create([
            'owner_id' => $owner1->id,
            'storage_object_id' => $object1_1->id,
            'client_id' => $client1->id,
            'user_id' => null, // ще трябва да създадеш admin user първо
            'receipt_number' => 'R-2025-0001',
            'type' => 'sale',
            'total_amount' => 39.90,
            'total_vat' => 6.65,
            'notes' => 'Първа продажба',
            'is_invoiced' => false,
            'created_at' => now(),
        ]);
        
        // Намаляваме наличността
        $stockBlackM = Stock::where('product_variant_id', $blackMVariant->id)
            ->where('storage_object_id', $object1_1->id)
            ->first();
        if ($stockBlackM) {
            $stockBlackM->decrement('quantity', 1);
        }
        
        ReceiptItem::create([
            'receipt_id' => $receipt1->id,
            'product_variant_id' => $blackMVariant->id,
            'product_name_snapshot' => $product1->name,
            'sku_snapshot' => $blackMVariant->full_sku,
            'color_name' => 'Черен',
            'size_name' => 'M',
            'quantity' => 1,
            'unit_price' => 39.90,
            'vat_rate' => 20,
            'total' => 39.90,
        ]);
        
        // Стокова разписка 2 – продажба на клиент Петър (с отстъпка)
        $blueSVaraint = ProductVariant::where('product_id', $product1->id)
            ->whereHas('color', fn($q) => $q->where('name', 'Син'))
            ->whereHas('size', fn($q) => $q->where('name', 'S'))
            ->first();
        
        $receipt2 = Receipt::create([
            'owner_id' => $owner1->id,
            'storage_object_id' => $object1_2->id,
            'client_id' => $client2->id,
            'user_id' => 1,
            'receipt_number' => 'R-2025-0002',
            'type' => 'sale',
            'total_amount' => 44.90, // с отстъпката на клиента
            'total_vat' => 7.48,
            'notes' => 'Продажба с отстъпка',
            'is_invoiced' => false,
            'created_at' => now(),
        ]);
        
        $stockBlueS = Stock::where('product_variant_id', $blueSVaraint->id)
            ->where('storage_object_id', $object1_2->id)
            ->first();
        if ($stockBlueS) {
            $stockBlueS->decrement('quantity', 1);
        }
        
        ReceiptItem::create([
            'receipt_id' => $receipt2->id,
            'product_variant_id' => $blueSVaraint->id,
            'product_name_snapshot' => $product1->name,
            'sku_snapshot' => $blueSVaraint->full_sku,
            'color_name' => 'Син',
            'size_name' => 'S',
            'quantity' => 1,
            'unit_price' => 44.90,
            'vat_rate' => 20,
            'total' => 44.90,
        ]);
        
        // ========================================
        // 10. ФАКТУРИ (обединяване на разписки)
        // ========================================
        
        $invoice1 = Invoice::create([
            'owner_id' => $owner1->id,
            'client_id' => $client1->id,
            'invoice_number' => 'INV-2025-0001',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'total_amount' => 39.90,
            'total_vat' => 6.65,
            'status' => 'issued',
            'notes' => 'Фактура за закупена тениска',
            'created_at' => now(),
        ]);
        
        // Свързваме разписката с фактурата
        DB::table('invoice_receipt')->insert([
            'invoice_id' => $invoice1->id,
            'receipt_id' => $receipt1->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $invoice2 = Invoice::create([
            'owner_id' => $owner1->id,
            'client_id' => $client2->id,
            'invoice_number' => 'INV-2025-0002',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'total_amount' => 44.90,
            'total_vat' => 7.48,
            'status' => 'draft',
            'notes' => 'Чернова фактура',
            'created_at' => now(),
        ]);
        
        DB::table('invoice_receipt')->insert([
            'invoice_id' => $invoice2->id,
            'receipt_id' => $receipt2->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // ========================================
        // ИЗХОДНА ИНФОРМАЦИЯ
        // ========================================
        
        $this->command->info('✅ InitialDataSeeder завършен успешно!');
        $this->command->newLine();
        $this->command->info('📊 СЪЗДАДЕНИ ДАННИ:');
        $this->command->info("- Собственици: 2");
        $this->command->info("- Обекти: 4");
        $this->command->info("- Цветове: 6");
        $this->command->info("- Размери: 8");
        $this->command->info("- Продукти: 6");
        $this->command->info("- Варианти: " . ProductVariant::count());
        $this->command->info("- Наличности: " . Stock::count());
        $this->command->info("- Клиенти: 4");
        $this->command->info("- Стокови разписки: 2");
        $this->command->info("- Фактури: 2");
    }
}