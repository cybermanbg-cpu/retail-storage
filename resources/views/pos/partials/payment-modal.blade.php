<div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 md:top-20 mx-auto p-4 md:p-5 border w-[95%] sm:w-[400px] md:w-[450px] shadow-lg rounded-md bg-white">
        <div class="mt-2 md:mt-3">
            <!-- Заглавие с икона -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg md:text-xl font-medium text-gray-900">
                    <i class="fas fa-cash-register text-green-600 mr-2"></i>
                    Начин на плащане
                </h3>
                <button onclick="window.POSInstance?.paymentManager?.closeModal()" 
                        class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Обща сума -->
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">Обща сума за плащане:</p>
                <p id="modalTotalAmount" class="text-2xl md:text-3xl font-bold text-primary-600">0.00 €</p>
            </div>

            <!-- Бутони за начини на плащане -->
            <div class="space-y-3">
                <button onclick="window.POSInstance?.paymentManager?.selectPaymentMethod('card')" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold transition transform hover:scale-[1.02] active:scale-95">
                    <i class="fas fa-credit-card mr-2"></i> Плащане с карта
                </button>

                <button onclick="window.POSInstance?.paymentManager?.showCashPayment()" 
                        class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition transform hover:scale-[1.02] active:scale-95">
                    <i class="fas fa-money-bill mr-2"></i> Плащане в брой
                </button>

                <button onclick="window.POSInstance?.paymentManager?.closeModal()" 
                        class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-3 rounded-lg font-semibold transition">
                    <i class="fas fa-arrow-left mr-2"></i> Върни се към количката
                </button>
            </div>

            <!-- Секция за плащане в брой -->
            <div id="cashPaymentSection" class="hidden mt-4 pt-4 border-t">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-euro-sign mr-1"></i> Въведете получена сума:
                </label>
                <div class="flex gap-2 mb-3">
                    <input type="text" id="cashAmount" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 text-lg"
                           placeholder="0.00"
                           onkeypress="handleCashAmountKeyPress(event)">
                    <div class="grid grid-cols-2 gap-1">
                        <button onclick="setQuickAmount(5)" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded text-sm font-medium">5€</button>
                        <button onclick="setQuickAmount(10)" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded text-sm font-medium">10€</button>
                        <button onclick="setQuickAmount(20)" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded text-sm font-medium">20€</button>
                        <button onclick="setQuickAmount(50)" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded text-sm font-medium">50€</button>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mb-2">Използвайте точка (.) или запетая (,) за дробни числа</p>
                
                <!-- Информация за ресто -->
                <div id="changeInfo" class="hidden mb-3 p-3 bg-green-100 rounded-lg">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                        <div>
                            <span class="text-sm text-gray-600">Ресто:</span>
                            <span id="changeAmount" class="text-xl font-bold text-green-700 ml-2">0.00 €</span>
                        </div>
                    </div>
                </div>
                
                <!-- Бутони за потвърждение/отказ -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <button onclick="window.POSInstance?.paymentManager?.confirmCashPayment()" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold transition">
                        <i class="fas fa-check mr-2"></i> Потвърди
                    </button>
                    <button onclick="window.POSInstance?.paymentManager?.hideCashPayment()" 
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold transition">
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
    
    // Функция за бърз избор на сума
    function setQuickAmount(amount) {
        const cashAmountInput = document.getElementById('cashAmount');
        if (cashAmountInput) {
            cashAmountInput.value = amount.toFixed(2);
            // Тригваме input event за да се преизчисли рестото
            const event = new Event('input');
            cashAmountInput.dispatchEvent(event);
            cashAmountInput.focus();
        }
    }
    
    // Автоматично преизчисляване на ресто при въвеждане
    document.addEventListener('DOMContentLoaded', function() {
        const cashAmountInput = document.getElementById('cashAmount');
        if (cashAmountInput) {
            cashAmountInput.addEventListener('input', function(e) {
                let paid = parseFloat(e.target.value.replace(',', '.')) || 0;
                let totalText = document.getElementById('modalTotalAmount')?.innerText || '0';
                let total = parseFloat(totalText) || 0;
                let change = paid - total;
                
                const changeInfo = document.getElementById('changeInfo');
                const changeAmount = document.getElementById('changeAmount');
                
                if (change >= 0 && paid > 0) {
                    if (changeAmount) changeAmount.innerText = change.toFixed(2) + ' €';
                    if (changeInfo) changeInfo.classList.remove('hidden');
                } else {
                    if (changeInfo) changeInfo.classList.add('hidden');
                }
            });
        }
    });
</script>