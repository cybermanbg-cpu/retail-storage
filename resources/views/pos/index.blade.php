@extends('layouts.app')

@section('hide_footer')
    <!-- Тази секция скрива футера -->
@endsection

@section('title', 'POS Система - Продажби')

@section('content')
<div id="pos-app">
    <div class="container mx-auto px-4 py-8">
        @include('pos.partials.header')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @include('pos.partials.products-grid')
            @include('pos.partials.cart-sidebar')
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
    
    console.log('posConfig loaded:', window.posConfig);
</script>

<script src="{{ asset('js/pos.js') }}"></script>

<script>
    $(document).ready(function() {
        console.log('Document ready, POS class:', typeof POS);
        
        if (typeof POS !== 'undefined') {
            // Създаване на глобална инстанция
            window.POSInstance = new POS();
            window.POSInstance.init(window.posConfig);
            console.log('POSInstance created successfully');
        } else {
            console.error('POS class not found! Check if pos.js is loaded');
        }
    });
</script>
@endpush