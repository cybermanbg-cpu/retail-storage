<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Начин на плащане</h3>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Обща сума за плащане:</p>
                <p id="modalTotalAmount" class="text-2xl font-bold text-primary-600">0.00 €</p>
            </div>

            <div class="space-y-3">
                <button onclick="window.POSInstance.paymentManager.selectPaymentMethod('card')" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-credit-card mr-2"></i> Плащане с карта
                </button>

                <button onclick="window.POSInstance.paymentManager.showCashPayment()" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-money-bill mr-2"></i> Плащане в брой
                </button>

                <button onclick="window.POSInstance.paymentManager.closeModal()" 
                        class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-arrow-left mr-2"></i> Върни се към количката
                </button>
            </div>

            <div id="cashPaymentSection" class="hidden mt-4 pt-4 border-t">
                <label class="block text-sm font-medium mb-2">Въведете получена сума:</label>
                <input type="number" id="cashAmount" step="0.01" 
                       class="w-full px-3 py-2 border rounded-lg mb-3" 
                       placeholder="0.00"
                       onkeypress="handleCashAmountKeyPress(event)">
                <div id="changeInfo" class="hidden mb-3 p-2 bg-green-100 rounded-lg">
                    <span class="text-sm">Ресто:</span>
                    <span id="changeAmount" class="text-lg font-bold text-green-600">0.00 €</span>
                </div>
                <div class="flex space-x-2">
                    <button onclick="window.POSInstance.paymentManager.confirmCashPayment()" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg">
                        <i class="fas fa-check mr-2"></i> Потвърди
                    </button>
                    <button onclick="window.POSInstance.paymentManager.hideCashPayment()" class="flex-1 bg-gray-300 hover:bg-gray-400 py-2 rounded-lg">
                        <i class="fas fa-times mr-2"></i> Отказ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Функция за Enter в полето за сума
    function handleCashAmountKeyPress(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            if (window.POSInstance && window.POSInstance.paymentManager) {
                window.POSInstance.paymentManager.confirmCashPayment();
            }
        }
    }
</script>