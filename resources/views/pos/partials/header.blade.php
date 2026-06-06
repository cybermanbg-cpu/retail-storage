<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-4 bg-white p-4 rounded-2xl shadow flex-shrink-0">
    <!-- Лява част - информация -->
    <div class="flex flex-wrap items-center gap-2 md:gap-4">
        <div class="flex items-center gap-2">
            <i class="fas fa-cash-register text-green-600 text-xl md:text-base"></i>
            <span class="font-bold text-sm md:text-base">POS Система</span>
            <div class="h-4 w-px bg-gray-300 hidden md:block"></div>
        </div>
        
        <div class="flex items-center gap-2 text-xs md:text-sm text-gray-600">
            <i class="fas fa-warehouse hidden md:inline"></i>
            <span class="truncate max-w-[120px] md:max-w-none">{{ $storageObject->name }}</span>
            <div class="h-4 w-px bg-gray-300 hidden md:block"></div>
        </div>
        
        <div class="flex items-center gap-2 text-xs md:text-sm text-gray-600">
            <i class="fas fa-user"></i>
            <span class="truncate max-w-[100px] md:max-w-none">{{ Auth::user()->name }}</span>
        </div>
    </div>

    <!-- Дясна част - бутони -->
    <div class="flex items-center gap-2 w-full md:w-auto justify-end">
        <button id="newCartBtn" class="text-gray-500 hover:text-blue-600 p-2 rounded-lg transition" title="Нова сметка">
            <i class="fas fa-plus-circle text-xl md:text-base"></i>
            <span class="hidden md:inline text-sm ml-1">Нова</span>
        </button>
        <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 p-2 rounded-lg transition" title="Начало">
            <i class="fas fa-home text-xl md:text-base"></i>
            <span class="hidden md:inline text-sm ml-1">Начало</span>
        </a>
        <a href="{{ route('logout') }}" class="text-gray-500 hover:text-red-600 p-2 rounded-lg transition" 
           onclick="event.preventDefault(); document.getElementById('logout-form-pos').submit();" title="Изход">
            <i class="fas fa-sign-out-alt text-xl md:text-base"></i>
            <span class="hidden md:inline text-sm ml-1">Изход</span>
        </a>
        <form id="logout-form-pos" action="{{ route('logout') }}" method="GET" style="display: none;">
            @csrf
        </form>
    </div>
</div>

<!-- Списък с колички - responsive хоризонтален скрол -->
<div class="mb-6 overflow-x-auto -mx-4 px-4">
    <div class="flex space-x-2 min-w-max pb-2" id="cartsList">
        @foreach ($activeCarts as $cart)
            <button class="cart-tab px-3 md:px-4 py-2 rounded-lg transition text-sm md:text-base whitespace-nowrap {{ $currentCart->id == $cart->id ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
                    data-cart-id="{{ $cart->id }}">
                <i class="fas fa-shopping-cart mr-1 text-xs md:text-sm"></i>
                {{ $cart->cart_name }}
                <span class="cart-badge text-xs ml-1 px-1.5 py-0.5 rounded-full {{ $currentCart->id == $cart->id ? 'bg-white text-primary-600' : 'bg-primary-600 text-white' }}">
                    {{ count($cart->items ?? []) }}
                </span>
            </button>
        @endforeach
    </div>
</div>