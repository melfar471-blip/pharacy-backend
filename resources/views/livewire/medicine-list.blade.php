<div class="py-6 max-w-7xl mx-auto px-4 space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">إدارة الأدوية والمخزون</h1>
        <a href="{{ route('medicines.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">+ إضافة دواء جديد</a>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-700 rounded-lg">{{ session('message') }}</div>
    @endif

  
    <div class="bg-white p-4 rounded-lg shadow flex flex-col md:flex-row gap-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="ابحث بالاسم أو الباركود..." 
               class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
        
        <select wire:model.live="category" class="border rounded-lg px-4 py-2">
            <option value="">جميع التصنيفات</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>
    </div>

    
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-right border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b text-gray-600 text-sm">
                    <th class="p-3">الباركود</th>
                    <th class="p-3">اسم الدواء</th>
                    <th class="p-3">التصنيف</th>
                    <th class="p-3">سعر البيع</th>
                    <th class="p-3">المخزون الإجمالي</th>
                    <th class="p-3 text-center">التحكم</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm text-gray-700">
                @forelse($medicines as $medicine)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-mono">{{ $medicine->barcode }}</td>
                        <td class="p-3 font-semibold">{{ $medicine->name }}</td>
                        <td class="p-3">{{ $medicine->category }}</td>
                        <td class="p-3">{{ $medicine->selling_price }} ج.م</td>
                        <td class="p-3 {{ $medicine->total_stock <= ($medicine->alert_threshold ?? 10) ? 'text-red-600 font-bold' : '' }}">
                            {{ $medicine->total_stock }}
                        </td>
                        <td class="p-3 text-center space-x-2 space-x-reverse">
                            <a href="{{ route('medicines.edit', $medicine->id) }}" class="text-blue-600 hover:underline">تعديل</a>
                            <button wire:click="deleteMedicine({{ $medicine->id }}})" wire:confirm="هل أنت متأكد من حذف أو أرشفة هذا الدواء؟" class="text-red-600 hover:underline">حذف</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-400">لا توجد أدوية مطابقة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $medicines->links() }}
        </div>
    </div>
</div>