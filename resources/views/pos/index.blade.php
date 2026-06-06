@extends('layouts.app')

@section('title', 'POS Система - Продажби')

@section('hide_navigation', true)

@section('content')
<div id="pos-app">
    <div class="container-fluid px-4 py-3 h-screen flex flex-col">
        @include('pos.partials.header')
        
        <!-- Подобрен grid за таблет и десктоп -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            <!-- Продуктите заемат повече място на таблет -->
            <div class="md:col-span-1 lg:col-span-2">
                @include('pos.partials.products-grid')
            </div>
            <!-- Количката отдясно на таблет/десктоп -->
            <div class="md:col-span-1">
                @include('pos.partials.cart-sidebar')
            </div>
        </div>
        
        @include('pos.partials.payment-modal')
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.posConfig = {
        currentCartId: {{ $currentCart->id }},
        storageObjectId: {{ $storageObject->id }},
        csrfToken: '{{ csrf_token() }}'
    };
</script>

<script src="{{ asset('js/pos.js') }}"></script>

<script>
    $(document).ready(function() {
        if (typeof POS !== 'undefined') {
            window.POSInstance = new POS();
            window.POSInstance.init(window.posConfig);
        }
        
        // Добавяме resize handler за таблети
        function handleTabletLayout() {
            const isTablet = window.innerWidth >= 768 && window.innerWidth <= 1024;
            const productsContainer = document.querySelector('.products-grid-container');
            
            if (productsContainer && isTablet) {
                // Оптимизации за таблет
                productsContainer.classList.add('tablet-view');
            } else if (productsContainer) {
                productsContainer.classList.remove('tablet-view');
            }
        }
        
        window.addEventListener('resize', handleTabletLayout);
        handleTabletLayout();
    });
</script>
@endpush