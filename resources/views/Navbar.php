<nav class="bg-gray-800 text-white p-4 flex justify-between items-center">
    <div class="flex space-x-4 space-x-reverse">
       
        @can('access pos')
            <a href="{{ route('pos.screen') }}" class="hover:text-blue-400">نقطة البيع (POS)</a>
        @endcan

      
        @role('admin')
            <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-400">لوحة التحكم</a>
            <a href="{{ route('medicines.index') }}" class="hover:text-blue-400">الأدوية</a>
            <a href="{{ route('suppliers.index') }}" class="hover:text-blue-400">الموردين</a>
            <a href="{{ route('purchase-orders.index') }}" class="hover:text-blue-400">طلبات الشراء</a>
        @endrole
    </div>

    <div>
        <span class="text-sm font-semibold">{{ auth()->user()->name }} ({{ auth()->user()->getRoleNames()->first() }})</span>
    </div>
</nav>