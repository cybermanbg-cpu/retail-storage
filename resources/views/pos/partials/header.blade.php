<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">
        <i class="fas fa-cash-register text-green-600"></i> POS Система
    </h1>
    <div class="flex items-center space-x-4">
        <button id="newCartBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus mr-2"></i> Нова количка
        </button>
        <div class="text-sm text-gray-600 bg-white px-4 py-2 rounded-lg shadow">
            Обект: <span class="font-semibold">{{ $storageObject->name }}</span>
        </div>
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