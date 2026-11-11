
<div class="py-6 max-w-7xl mx-auto px-4 space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">لوحة المؤشرات والتقارير الشاملة</h1>
        <span class="text-sm text-gray-500">التحديث لحظي: {{ now()->format('Y-m-d H:i') }}</span>
    </div>

  
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
        <div class="bg-white p-6 rounded-lg shadow border-r-4 border-blue-500">
            <p class="text-sm text-gray-500 font-medium">مبيعات اليوم</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($todaySalesAmount, 2) }} ج.م</h3>
        </div>

      
        <div class="bg-white p-6 rounded-lg shadow border-r-4 border-green-500">
            <p class="text-sm text-gray-500 font-medium">أرباح اليوم التقديرية</p>
            <h3 class="text-3xl font-bold text-green-600 mt-2">{{ number_format($todayNetProfit, 2) }} ج.م</h3>
        </div>

       
        <div class="bg-white p-6 rounded-lg shadow border-r-4 border-purple-500">
            <p class="text-sm text-gray-500 font-medium">عدد الفواتير المنفذة</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $todayInvoicesCount }} فاتورة</h3>
        </div>
    </div>

  
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-bold text-gray-800 mb-4">🔥 أكثر 5 أدوية مبيعاً</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead>
                    <tr class="border-b bg-gray-50 text-gray-600">
                        <th class="p-3">اسم الدواء</th>
                        <th class="p-3">الباركود</th>
                        <th class="p-3">إجمالي الكمية المباعة</th>
                        <th class="p-3">إجمالي الإيرادات المحققة</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-gray-700">
                    @forelse($topMedicines as $medicine)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 font-semibold">{{ $medicine->name }}</td>
                            <td class="p-3 font-mono text-gray-500">{{ $medicine->barcode }}</td>
                            <td class="p-3 font-bold text-blue-600">{{ $medicine->total_qty }} وحدة</td>
                            <td class="p-3 font-bold">{{ number_format($medicine->total_revenue, 2) }} ج.م</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-400">لا توجد مبيعات مسجلة اليوم.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

   
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
       
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-bold text-red-600 mb-4">⚠️ الأدوية منخفضة المخزون</h2>
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-right text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 sticky top-0">
                            <th class="p-2">الدواء</th>
                            <th class="p-2">المخزون الحالي</th>
                            <th class="p-2">حد التنبيه</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($lowStockMedicines as $med)
                            <tr class="hover:bg-gray-50">
                                <td class="p-2 font-medium">{{ $med->name }}</td>
                                <td class="p-2 text-red-600 font-bold">{{ $med->total_stock }}</td>
                                <td class="p-2 text-gray-500">{{ $med->alert_threshold ?? 10 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-4 text-center text-gray-400">المخزون مستقر ولا توجد نواقص.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

       
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-bold text-orange-600 mb-4">⏳ الأدوية قريبة انتهاء الصلاحية (90 يوماً)</h2>
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-right text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 sticky top-0">
                            <th class="p-2">الدواء</th>
                            <th class="p-2">رقم الباتش</th>
                            <th class="p-2">تاريخ الانتهاء</th>
                            <th class="p-2">الكمية</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($nearExpiryBatches as $batch)
                            <tr class="hover:bg-gray-50">
                                <td class="p-2 font-medium">{{ $batch->medicine->name }}</td>
                                <td class="p-2 font-mono text-xs text-gray-600">{{ $batch->batch_number }}</td>
                                <td class="p-2 text-orange-600 font-bold">{{ $batch->expiry_date->format('Y-m-d') }}</td>
                                <td class="p-2">{{ $batch->quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-400">لا توجد باتشات قريبة الانتهاء.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
