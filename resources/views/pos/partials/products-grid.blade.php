<div class="lg:col-span-2">
    <div class="bg-white rounded-lg shadow p-4">
        
        <!-- Секция за търсене - центрирана -->
        <div class="mb-6">
            <div class="relative max-w-2xl mx-auto">
                <!-- Икона за търсене в ляво -->
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-sm"></i>
                </div>
                
                <!-- Поле за търсене -->
                <input type="text" id="searchInput" 
                       placeholder="Търси продукт по име, артикул или сканирай баркод..."
                       class="w-full pl-10 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all duration-200"
                       autofocus>
                
                <!-- Бутон за изчистване -->
                <button id="clearSearchBtn" 
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition-all duration-200 hidden w-7 h-7 rounded-full hover:bg-red-50 items-center justify-center">
                    <i class="fas fa-times-circle text-lg"></i>
                </button>
            </div>
            
            <!-- Индикация под полето -->
            <div class="text-center mt-2">
                <p class="text-xs text-gray-400 inline-flex items-center gap-2 bg-gray-50 px-3 py-1 rounded-full">
                    <i class="fas fa-barcode text-primary-500"></i>
                    <span>Сканирайте баркод или въведете име/артикул</span>
                    <i class="fas fa-keyboard text-gray-400"></i>
                </p>
            </div>
        </div>

        <!-- Грид с продукти -->
        <div id="productsGrid" class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[600px] overflow-y-auto">
            @foreach ($products as $product)
                @foreach ($product->variants as $variant)
                    <div class="product-card bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-primary-500 hover:shadow-md transition-all duration-200"
                         data-variant-id="{{ $variant->id }}" 
                         data-product-name="{{ $product->name }}"
                         data-variant-name="{{ $variant->color?->name ?? '' }} {{ $variant->size?->name ?? '' }}"
                         data-price="{{ $variant->final_price }}">
                        <div class="font-semibold text-gray-800">{{ $product->name }}</div>
                        @if ($variant->color || $variant->size)
                            <div class="text-sm text-gray-600">
                                {{ $variant->color?->name ?? '' }} {{ $variant->size?->name ?? '' }}
                            </div>
                        @endif
                        <div class="text-lg font-bold text-primary-600 mt-2">
                            {{ number_format($variant->final_price, 2) }} €
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
        
        <!-- Индикатор за край на списъка -->
        <div class="text-center mt-4 text-xs text-gray-400">
            <i class="fas fa-arrow-up mr-1"></i> Скролвай за още продукти
        </div>
    </div>
</div>