<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
  
    public function index(Request $request)
    {
        $query = Supplier::query();

        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $suppliers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $suppliers
        ], 200);
    }

   
    public function store(SupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة المورد بنجاح',
            'data' => $supplier
        ], 201);
    }

    
    public function show($id)
    {
       
        $supplier = Supplier::with('purchaseOrders')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $supplier
        ], 200);
    }

   
    public function update(SupplierRequest $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        
        $supplier->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات المورد بنجاح',
            'data' => $supplier
        ], 200);
    }

   
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->purchaseOrders()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن حذف المورد لوجود طلبات شراء مرتبطة به.'
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف المورد بنجاح.'
        ], 200);
    }
}

?>