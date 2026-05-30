import '../css/app.css';
import $ from 'jquery';

// Глобално зареждане на jQuery
window.$ = window.jQuery = $;

// CSRF токен за AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
});

console.log('jQuery loaded:', typeof $ !== 'undefined');