<div class="py-6 max-w-3xl mx-auto px-4">
    <div class="bg-white p-6 rounded-lg shadow">
        <h1 class="text-xl font-bold text-gray-800 mb-6">{{ $medicineId ? 'تعديل الدواء' : 'إضافة دواء جديد' }}</h1>

        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الباركود</label>
                    <input type="text" wire:model="barcode" class="w-full border rounded-lg p-2">
                    @error('barcode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الدواء</label>
                    <input type="text" wire:model="name" class="w-full border rounded-lg p-2">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">التصنيف</label>
                    <input type="text" wire:model="category" class="w-full border rounded-lg p-2">
                    @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الكمية الافتتاحية</label>
                    <input type="number" wire:model="quantity" min="0" class="w-full border rounded-lg p-2">
                    @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">سعر الشراء</label>
                    <input type="number" step="0.01" wire:model="purchase_price" min="0" class="w-full border rounded-lg p-2">
                    @error('purchase_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">سعر البيع</label>
                    <input type="number" step="0.01" wire:model="selling_price" min="0" class="w-full border rounded-lg p-2">
                    @error('selling_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الصلاحية</label>
                    <input type="date" wire:model="expiry_date" class="w-full border rounded-lg p-2">
                    @error('expiry_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">حد التنبيه بالنواقص</label>
                    <input type="number" wire:model="alert_threshold" min="0" class="w-full border rounded-lg p-2">
                    @error('alert_threshold') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-2 space-x-reverse pt-4">
                <a href="{{ route('medicines.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">إلغاء</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">حفظ</button>
            </div>
        </form>
    </div>
</div>