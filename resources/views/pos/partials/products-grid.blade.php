<div class="lg:col-span-2">
    <div class="bg-white rounded-lg shadow p-3 md:p-4">
        <!-- Секция за търсене - центрирана и responsive -->
        <div class="mb-4 md:mb-6">
            <div class="relative max-w-2xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs md:text-sm"></i>
                </div>

                <input type="text" id="searchInput" placeholder="Търси продукт..."
                    class="w-full pl-9 md:pl-10 pr-10 md:pr-12 py-2 md:py-3 text-sm md:text-base border-2 border-gray-200 rounded-xl focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all duration-200"
                    autofocus>

                <button id="clearSearchBtn"
                    class="absolute right-2 md:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 transition-all duration-200 hidden w-6 h-6 md:w-7 md:h-7 rounded-full hover:bg-red-50 items-center justify-center">
                    <i class="fas fa-times-circle text-base md:text-lg"></i>
                </button>
            </div>

            <div class="text-center mt-2">
                <p
                    class="text-xs text-gray-400 inline-flex items-center gap-1 md:gap-2 bg-gray-50 px-2 md:px-3 py-1 rounded-full">
                    <i class="fas fa-barcode text-primary-500 text-xs md:text-sm"></i>
                    <span class="text-xs">Сканирайте баркод</span>
                    <i class="fas fa-keyboard text-gray-400 text-xs"></i>
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
                <span class="text-sm">Зареждане на продукти...</span>
            </div>
        </div>

        <!-- Грид с продукти - подобрен за таблет -->
        <div id="productsGrid"
            class="products-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 md:gap-3 lg:gap-4 max-h-[calc(100vh-280px)] overflow-y-auto hidden">
            @foreach ($products as $product)
                @if ($product->variants->count() > 0)
                    {{-- Продукти с варианти --}}
                    @foreach ($product->variants as $variant)
                        @php
                            $unitSymbol = $product->unitOfMeasure?->symbol ?? 'бр.';
                            $decimalPlaces = $product->unitOfMeasure?->decimal_places ?? 0;
                        @endphp
                        <div class="product-card bg-white border border-gray-200 rounded-lg p-2 md:p-3 cursor-pointer hover:border-primary-500 hover:shadow-md transition-all duration-200"
                            data-variant-id="{{ $variant->id }}" data-product-name="{{ $product->name }}"
                            data-variant-name="{{ $variant->color?->name ?? '' }} {{ $variant->size?->name ?? '' }}"
                            data-price="{{ $variant->final_price }}" data-unit="{{ $unitSymbol }}"
                            data-decimal-places="{{ $decimalPlaces }}">
                            <div class="font-semibold text-gray-800 text-sm md:text-base truncate">{{ $product->name }}
                            </div>
                            @if ($variant->color || $variant->size)
                                <div class="text-xs md:text-sm text-gray-600 truncate">
                                    {{ $variant->color?->name ?? '' }} {{ $variant->size?->name ?? '' }}
                                </div>
                            @endif
                            <div class="text-base md:text-lg font-bold text-primary-600 mt-1 md:mt-2">
                                {{ number_format($variant->final_price, 2) }} €
                            </div>
                            <div class="text-xs text-gray-400 hidden sm:block">
                                <i class="fas fa-balance-scale"></i> {{ $unitSymbol }}
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Продукти без варианти --}}
                    @php
                        $unitSymbol = $product->unitOfMeasure?->symbol ?? 'бр.';
                        $decimalPlaces = $product->unitOfMeasure?->decimal_places ?? 0;
                        $tempVariantId = 'product_' . $product->id;
                    @endphp
                    <div class="product-card bg-white border border-gray-200 rounded-lg p-2 md:p-3 cursor-pointer hover:border-primary-500 hover:shadow-md transition-all duration-200"
                        data-variant-id="{{ $tempVariantId }}" data-product-id="{{ $product->id }}"
                        data-product-name="{{ $product->name }}" data-variant-name=""
                        data-price="{{ $product->base_price }}" data-unit="{{ $unitSymbol }}"
                        data-decimal-places="{{ $decimalPlaces }}">
                        <div class="font-semibold text-gray-800 text-sm md:text-base truncate">{{ $product->name }}
                        </div>
                        <div class="text-base md:text-lg font-bold text-primary-600 mt-1 md:mt-2">
                            {{ number_format($product->base_price, 2) }} €
                        </div>
                        <div class="text-xs text-gray-400 hidden sm:block">
                            <i class="fas fa-balance-scale"></i> {{ $unitSymbol }}
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="text-center mt-3 md:mt-4 text-xs text-gray-400 hidden" id="scrollHint">
            <i class="fas fa-arrow-up mr-1"></i> Скролвай за още продукти
        </div>
    </div>
</div>
