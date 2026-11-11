<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
   
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items.medicine']);

        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ], 200);
    }

  
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

          
            $totalCost = 0;
            foreach ($request->items as $item) {
                $totalCost += $item['quantity'] * $item['unit_cost'];
            }

            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'order_number' => 'PO-' . strtoupper(Str::random(8)),
                'total_cost' => $totalCost,
                'status' => 'pending', 
            ]);

          
            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إنشاء طلب الشراء بنجاح (قيد الانتظار).',
                'data' => $purchaseOrder->load('items.medicine')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إنشاء طلب الشراء: ' . $e->getMessage()
            ], 500);
        }
    }

   
    public function receiveOrder($id)
    {
        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($id);

        if ($purchaseOrder->status === 'received') {
            return response()->json([
                'status' => 'error',
                'message' => 'هذا الطلب تم استلامه مسبقاً ولا يمكن استلامه مرة أخرى.'
            ], 422);
        }

        try {
            DB::beginTransaction();

         
            $purchaseOrder->update(['status' => 'received']);

            
            foreach ($purchaseOrder->items as $item) {
                $medicine = Medicine::findOrFail($item->medicine_id);
                
               
                $medicine->increment('quantity', $item->quantity);
                
               
                $medicine->update(['purchase_price' => $item->unit_cost]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل استلام البضاعة وتحديث المخزون بنجاح.',
                'data' => $purchaseOrder->load('items.medicine')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'فشل استلام الطلب وتحديث المخزون: ' . $e->getMessage()
            ], 500);
        }
    }
}

?>