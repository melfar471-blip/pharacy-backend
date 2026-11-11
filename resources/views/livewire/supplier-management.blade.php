<div class="py-6 max-w-7xl mx-auto px-4 space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">إدارة الموردين</h1>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-700 rounded-lg">{{ session('message') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
       
        <div class="bg-white p-6 rounded-lg shadow h-fit">
            <h2 class="text-lg font-bold mb-4">{{ $supplierId ? 'تعديل مورد' : 'إضافة مورد جديد' }}</h2>
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">اسم المورد</label>
                    <input type="text" wire:model="name" class="w-full border rounded p-2">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">الشخص المسؤول</label>
                    <input type="text" wire:model="contact_person" class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">رقم الهاتف</label>
                    <input type="text" wire:model="phone" class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                    <input type="email" wire:model="email" class="w-full border rounded p-2">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">حفظ</button>
            </form>
        </div>

       
        <div class="md:col-span-2 bg-white p-6 rounded-lg shadow">
            <table class="w-full text-right text-sm">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="p-2">المورد</th>
                        <th class="p-2">الهاتف</th>
                        <th class="p-2">طلبات الشراء</th>
                        <th class="p-2 text-center">التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-2 font-semibold">{{ $supplier->name }}</td>
                            <td class="p-2">{{ $supplier->phone }}</td>
                            <td class="p-2">{{ $supplier->purchase_orders_count }} طلبات</td>
                            <td class="p-2 text-center space-x-2 space-x-reverse">
                                <button wire:click="showDetails({{ $supplier->id }})" class="text-green-600 hover:underline">التفاصيل</button>
                                <button wire:click="edit({{ $supplier->id }})" class="text-blue-600 hover:underline">تعديل</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $suppliers->links() }}</div>
        </div>
    </div>
</div>