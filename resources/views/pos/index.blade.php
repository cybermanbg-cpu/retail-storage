@extends('layouts.app')

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
</script>

<script src="{{ asset('js/pos.js') }}"></script>

<script>
    $(document).ready(function() {
        if (typeof POS !== 'undefined') {
            window.POSInstance = new POS();
            window.POSInstance.init(window.posConfig);
        }
    });
</script>
@endpush