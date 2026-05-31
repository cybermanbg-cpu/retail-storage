<div class="lg:col-span-1">
    <div class="bg-white rounded-lg shadow sticky top-24 overflow-hidden">
        
        <!-- Зелен хедер с бутон -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
            <div class="flex justify-between items-baseline text-white mb-2">
                <span class="text-sm font-medium">Общо за плащане</span>
                <span id="totalAmount" class="font-bold text-2xl tracking-tight">0.00 €</span>
            </div>
            <button id="checkoutBtn" 
                    class="w-full bg-yellow-400 hover:bg-yellow-500 text-green-900 py-2.5 rounded-lg font-bold transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-arrow-right"></i>
                <span>ЗАВЪРШИ ПРОДАЖБА</span>
                <i class="fas fa-credit-card opacity-70"></i>
            </button>
        </div>

        <!-- Тяло на количката -->
        <div class="divide-y divide-gray-100">
            <!-- Клиент -->
            <div class="p-3 bg-white">
                <label class="text-xs text-gray-500 uppercase font-semibold block mb-1">
                    <i class="fas fa-user mr-1"></i> Клиент
                </label>
                <select id="clientSelect" class="w-full text-sm border-gray-300 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    <option value="">👤 Анонимен клиент</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">👥 {{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Артикули -->
            <div>
                <div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <i class="fas fa-list mr-1"></i> Продукти 
                    (<span id="itemsCount" class="text-green-600">0</span>)
                </div>
                <div id="cartItems" class="max-h-[320px] overflow-y-auto">
                    <div class="text-center text-gray-400 py-8 text-sm">
                        <i class="fas fa-shopping-cart text-4xl mb-2 block opacity-50"></i>
                        <p>Количката е празна</p>
                        <p class="text-xs mt-1">Добавете продукти от лявата страна</p>
                    </div>
                </div>
            </div>
            
            <!-- ДДС и брой артикули -->
            <div class="px-3 py-2 bg-gray-50 flex justify-between items-center text-xs">
                <div class="flex items-center gap-3">
                    <span class="text-gray-500">
                        <i class="fas fa-boxes mr-1"></i> Артикули: 
                        <span id="itemsCountFooter" class="font-semibold text-gray-700">0</span>
                    </span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-500">
                        <i class="fas fa-percent mr-1"></i> ДДС (20%):
                    </span>
                </div>
                <span id="totalVat" class="font-medium text-gray-700">0.00 €</span>
            </div>
        </div>
    </div>
</div>

<!-- Модал за избор на количество -->
<!-- Модал за избор на количество -->
<div id="quantityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-32 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Избор на количество</h3>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-1">Продукт:</p>
                <p id="modalProductName" class="font-semibold text-gray-800">-</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Количество (<span id="modalUnitSymbol">кг</span>):</label>
                <input type="text" id="modalQuantity" 
                       class="w-full text-center px-3 py-2 border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                       placeholder="0.000"
                       autofocus>
                <p class="text-xs text-gray-400 mt-1">Използвайте точка (.) или запетая (,) за дробни числа</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Единична цена:</label>
                <p id="modalUnitPrice" class="text-xl font-bold text-primary-600">0.00 €</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Обща сума:</label>
                <p id="modalTotalPrice" class="text-2xl font-bold text-green-600">0.00 €</p>
            </div>
            
            <div class="flex space-x-3">
                <button id="confirmQuantityBtn" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold">
                    <i class="fas fa-check mr-2"></i> Добави
                </button>
                <button id="cancelQuantityBtn" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
                    <i class="fas fa-times mr-2"></i> Отказ
                </button>
            </div>
        </div>
    </div>
</div>