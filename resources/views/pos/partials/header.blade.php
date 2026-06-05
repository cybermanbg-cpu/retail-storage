<div class="flex justify-between items-center mb-4 bg-white p-4 rounded-2xl shadow flex-shrink-0">
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
            <i class="fas fa-cash-register text-green-600"></i>
            <span class="font-bold">POS Система</span>
            <div class="h-4 w-px bg-gray-300"></div>
            <div class="text-gray-600">📍 {{ $storageObject->name }}</div>
            <div class="h-4 w-px bg-gray-300"></div>
            <div class="text-gray-600">👤 {{ Auth::user()->name }}</div>
        </div>
    </div>

    <div class="flex items-center gap-2">
        <button id="newCartBtn" class="text-gray-500 hover:text-blue-600 p-2 rounded-lg transition" title="Нова сметка">
            <i class="fas fa-plus-circle text-xl"></i>
        </button>
        <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 p-2 rounded-lg transition" title="Начало">
            <i class="fas fa-home text-xl"></i>
        </a>
        <a href="{{ route('logout') }}" class="text-gray-500 hover:text-red-600 p-2 rounded-lg transition" 
           onclick="event.preventDefault(); document.getElementById('logout-form-pos').submit();" title="Изход">
            <i class="fas fa-sign-out-alt text-xl"></i>
        </a>
        <form id="logout-form-pos" action="{{ route('logout') }}" method="GET" style="display: none;">
            @csrf
        </form>
    </div>
</div>

<div class="mb-6 overflow-x-auto">
    <div class="flex space-x-2" id="cartsList">
        @foreach ($activeCarts as $cart)
            <button class="cart-tab px-4 py-2 rounded-lg transition {{ $currentCart->id == $cart->id ? 'bg-primary-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}"
                    data-cart-id="{{ $cart->id }}">
                <i class="fas fa-shopping-cart mr-1"></i>
                {{ $cart->cart_name }}
                <span class="cart-badge text-xs ml-1 px-1.5 py-0.5 rounded-full {{ $currentCart->id == $cart->id ? 'bg-white text-primary-600' : 'bg-primary-600 text-white' }}">
                    {{ count($cart->items ?? []) }}
                </span>
            </button>
        @endforeach
    </div>
</div>