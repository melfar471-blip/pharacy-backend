

<div class="py-6 max-w-7xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-6">
    
   
    <div class="md:col-span-2 bg-white p-4 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4 text-gray-800">نقطة البيع (POS) - الأدوية</h2>

        
        <div class="mb-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="ابحث باسم الدواء أو الباركود..." 
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

       
        @if($errorMessage)
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">{{ $errorMessage }}</div>
        @endif
        @if($successMessage)
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">{{ $successMessage }}</div>
        @endif

      
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @forelse($medicines as $medicine)
                <div wire:click="addToCart({{ $medicine->id }})" class="p-4 border rounded-lg cursor-pointer hover:bg-blue-50 transition shadow-sm">
                    <h3 class="font-semibold text-gray-800">{{ $medicine->name }}</h3>
                    <p class="text-sm text-gray-500">السعر: {{ $medicine->selling_price }} ج.م</p>
                    <p class="text-xs mt-2 {{ $medicine->total_stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                        المخزون: {{ $medicine->total_stock }}
                    </p>
                </div>
            @empty
                @if(!empty($search))
                    <p class="text-gray-500 col-span-full">لا توجد نتائج مطابقة للبحث.</p>
                @else
                    <p class="text-gray-400 col-span-full">ابحث عن دواء لإضافته للفاتورة...</p>
                @endif
            @endforelse
        </div>
    </div>

    
    <div class="bg-white p-4 rounded-lg shadow flex flex-col justify-between">
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">سلة المشتريات</h2>

            <!-- جدول أصناف السلة -->
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($cart as $item)
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <h4 class="font-semibold text-sm">{{ $item['name'] }}</h4>
                            <span class="text-xs text-gray-500">{{ $item['selling_price'] }} × {{ $item['quantity'] }}</span>
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <input type="number" wire:change="updateQuantity({{ $item['id'] }}, $event.target.value)" 
                                   value="{{ $item['quantity'] }}" min="1" class="w-14 border text-center rounded">
                            <button wire:click="removeFromCart({{ $item['id'] }})" class="text-red-500 hover:text-red-700 font-bold">×</button>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-8">السلة فارغة حالياً</p>
                @endforelse
            </div>
        </div>

        <div class="mt-6 border-t pt-4">
            <div class="flex justify-between text-lg font-bold mb-4">
                <span>الإجمالي الكلي:</span>
                <span class="text-blue-600">{{ $this->total }} ج.م</span>
            </div>

        
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">طريقة الدفع</label>
                <select wire:model="paymentMethod" class="w-full border rounded p-2">
                    <option value="cash">نقدي (Cash)</option>
                    <option value="visa">بطاقة ائتمان (Visa)</option>
                    <option value="insurance">تمين صحي (Insurance)</option>
                </select>
            </div>

           
            <button wire:click="checkout" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition">
                إتمام البيع (Checkout)
            </button>
        </div>
    </div>

</div>
