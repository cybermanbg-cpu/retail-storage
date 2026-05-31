<div class="lg:col-span-2">
    <div class="bg-white rounded-lg shadow p-4">

        <!-- Секция за търсене - центрирана -->
        <div class="mb-6">
            <div class="relative max-w-2xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-sm"></i>
                </div>

                <input type="text" id="searchInput" placeholder="Търси продукт по име, артикул или сканирай баркод..."
                    class="w-full pl-10 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all duration-200"
                    autofocus>

                <button id="clearSearchBtn"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition-all duration-200 hidden w-7 h-7 rounded-full hover:bg-red-50 items-center justify-center">
                    <i class="fas fa-times-circle text-lg"></i>
                </button>
            </div>

            <div class="text-center mt-2">
                <p class="text-xs text-gray-400 inline-flex items-center gap-2 bg-gray-50 px-3 py-1 rounded-full">
                    <i class="fas fa-barcode text-primary-500"></i>
                    <span>Сканирайте баркод или въведете име/артикул</span>
                    <i class="fas fa-keyboard text-gray-400"></i>
                </p>
            </div>
        </div>

        <!-- Индикатор за зареждане -->
        <div id="loadingIndicator" class="text-center py-8">
            <div class="inline-flex items-center gap-2 text-gray-500">
                <svg class="animate-spin h-5 w-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span>Зареждане на продукти...</span>
            </div>
        </div>

        <!-- Грид с продукти (скрит до зареждане) -->
        <!-- Грид с продукти (скрит до зареждане) -->
        <div id="productsGrid" class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[600px] overflow-y-auto hidden">
            @foreach ($products as $product)
                @if ($product->variants->count() > 0)
                    {{-- Продукти с варианти --}}
                    @foreach ($product->variants as $variant)
                        @php
                            $unitSymbol = $product->unitOfMeasure?->symbol ?? 'бр.';
                            $decimalPlaces = $product->unitOfMeasure?->decimal_places ?? 0;
                        @endphp
                        <div class="product-card bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-primary-500 hover:shadow-md transition-all duration-200"
                            data-variant-id="{{ $variant->id }}" data-product-name="{{ $product->name }}"
                            data-variant-name="{{ $variant->color?->name ?? '' }} {{ $variant->size?->name ?? '' }}"
                            data-price="{{ $variant->final_price }}" data-unit="{{ $unitSymbol }}"
                            data-decimal-places="{{ $decimalPlaces }}">
                            <div class="font-semibold text-gray-800">{{ $product->name }}</div>
                            @if ($variant->color || $variant->size)
                                <div class="text-sm text-gray-600">
                                    {{ $variant->color?->name ?? '' }} {{ $variant->size?->name ?? '' }}
                                </div>
                            @endif
                            <div class="text-lg font-bold text-primary-600 mt-2">
                                {{ number_format($variant->final_price, 2) }} €
                            </div>
                            <div class="text-xs text-gray-400">
                                Артикул: {{ $product->sku }}
                                <span class="ml-2">(<i class="fas fa-balance-scale"></i> {{ $unitSymbol }})</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Продукти без варианти (като Домати) --}}
                    @php
                        $unitSymbol = $product->unitOfMeasure?->symbol ?? 'бр.';
                        $decimalPlaces = $product->unitOfMeasure?->decimal_places ?? 0;
                        // Създаваме временен виртуален вариант за продукта
                        $tempVariantId = 'product_' . $product->id;
                    @endphp
                    <div class="product-card bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-primary-500 hover:shadow-md transition-all duration-200"
                        data-variant-id="{{ $tempVariantId }}" data-product-id="{{ $product->id }}"
                        data-product-name="{{ $product->name }}" data-variant-name=""
                        data-price="{{ $product->base_price }}" data-unit="{{ $unitSymbol }}"
                        data-decimal-places="{{ $decimalPlaces }}">
                        <div class="font-semibold text-gray-800">{{ $product->name }}</div>
                        <div class="text-lg font-bold text-primary-600 mt-2">
                            {{ number_format($product->base_price, 2) }} €
                        </div>
                        <div class="text-xs text-gray-400">
                            Артикул: {{ $product->sku }}
                            <span class="ml-2">(<i class="fas fa-balance-scale"></i> {{ $unitSymbol }})</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="text-center mt-4 text-xs text-gray-400 hidden" id="scrollHint">
            <i class="fas fa-arrow-up mr-1"></i> Скролвай за още продукти
        </div>
    </div>
</div>
