<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Начин на плащане</h3>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Обща сума за плащане:</p>
                <p id="modalTotalAmount" class="text-2xl font-bold text-primary-600">0.00 €</p>
            </div>

            <div class="space-y-3">
                <!-- Плащане с карта -->
                <button onclick="handleCardPayment()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-credit-card mr-2"></i> Плащане с карта
                </button>

                <!-- Плащане в брой -->
                <button onclick="handleCashPayment()" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-money-bill mr-2"></i> Плащане в брой
                </button>

                <!-- Връщане към количката -->
                <button onclick="handleCloseModal()" 
                        class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-arrow-left mr-2"></i> Върни се към количката
                </button>
            </div>

            <!-- Секция за плащане в брой -->
            <div id="cashPaymentSection" class="hidden mt-4 pt-4 border-t">
                <label class="block text-sm font-medium mb-2">Въведете получена сума:</label>
                <input type="number" id="cashAmount" step="0.01" class="w-full px-3 py-2 border rounded-lg mb-3" placeholder="0.00">
                <div id="changeInfo" class="hidden mb-3 p-2 bg-green-100 rounded-lg">
                    <span class="text-sm">Ресто:</span>
                    <span id="changeAmount" class="text-lg font-bold text-green-600">0.00 €</span>
                </div>
                <div class="flex space-x-2">
                    <button onclick="handleConfirmCashPayment()" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">
                        <i class="fas fa-check mr-2"></i> Потвърди
                    </button>
                    <button onclick="handleHideCashPayment()" class="flex-1 bg-gray-300 hover:bg-gray-400 py-2 rounded-lg">
                        <i class="fas fa-times mr-2"></i> Отказ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Глобални функции за модала (като fallback, ако POSInstance не е готов)
    function handleCardPayment() {
        if (window.POSInstance && window.POSInstance.paymentManager) {
            window.POSInstance.paymentManager.selectPaymentMethod('card');
        } else if (window.selectPaymentMethod) {
            window.selectPaymentMethod('card');
        } else {
            console.error('POSInstance not ready');
            alert('Моля, изчакайте системата да се зареди');
        }
    }
    
    function handleCashPayment() {
        if (window.POSInstance && window.POSInstance.paymentManager) {
            window.POSInstance.paymentManager.showCashPayment();
        } else if (window.showCashPayment) {
            window.showCashPayment();
        } else {
            console.error('POSInstance not ready');
            alert('Моля, изчакайте системата да се зареди');
        }
    }
    
    function handleCloseModal() {
        if (window.POSInstance && window.POSInstance.paymentManager) {
            window.POSInstance.paymentManager.closeModal();
        } else if (window.closePaymentModal) {
            window.closePaymentModal();
        } else {
            console.error('POSInstance not ready');
            document.getElementById('paymentModal').classList.add('hidden');
        }
    }
    
    function handleConfirmCashPayment() {
        if (window.POSInstance && window.POSInstance.paymentManager) {
            window.POSInstance.paymentManager.confirmCashPayment();
        } else if (window.confirmCashPayment) {
            window.confirmCashPayment();
        } else {
            console.error('POSInstance not ready');
            alert('Моля, изчакайте системата да се зареди');
        }
    }
    
    function handleHideCashPayment() {
        if (window.POSInstance && window.POSInstance.paymentManager) {
            window.POSInstance.paymentManager.hideCashPayment();
        } else if (window.hideCashPayment) {
            window.hideCashPayment();
        } else {
            console.error('POSInstance not ready');
            document.getElementById('cashPaymentSection').classList.add('hidden');
        }
    }
</script>