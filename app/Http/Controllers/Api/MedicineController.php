<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\MedicineController as ApiMedicineController;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

   
    public function update(Request $request, string $id)
    {
      
    }

   
    public function destroy(string $id)
    {
        
    }
}
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicineRequest;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
   
    public function index(Request $request)
    {
        $query = Medicine::query();

       
        if ($request->has('with_archived') && $request->with_archived == 'true') {
           
        } else {
            $query->where('is_archived', false);
        }

       
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

       
        $perPage = $request->get('per_page', 15);
        $medicines = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $medicines
        ], 200);
    }

   
    public function store(MedicineRequest $request)
    {
        $medicine = Medicine::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة الدواء بنجاح',
            'data' => $medicine
        ], 201);
    }

   
    public function update(MedicineRequest $request, $id)
    {
        $medicine = Medicine::findOrFail($id);
        
        $medicine->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات الدواء بنجاح',
            'data' => $medicine
        ], 200);
    }


    public function destroy($id)
    {
        $medicine = Medicine::findOrFail($id);

        
        if ($medicine->saleItems()->exists()) {
           
            $medicine->update(['is_archived' => true, 'quantity' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'الدواء مرتبط بمبيعات سابقة، تم تحويله إلى الأرشيف وتصفير رصيده بنجاح بدل حذفه.'
            ], 200);
        }

      
        $medicine->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الدواء نهائياً بنجاح.'
        ], 200);
    }


public function expiryAlerts(Request $request)
    {
    
    $days = $request->get('days', 90);
    $targetDate = now()->addDays($days);

    $nearExpiryBatches = MedicineBatch::with('medicine')
        ->where('expiry_date', '<=', $targetDate)
        ->where('quantity', '>', 0)
        ->orderBy('expiry_date', 'asc')
        ->paginate(15);

    return response()->json([
        'status' => 'success',
        'message' => 'قائمة الأدوية والباتشات قريبة الانتهاء',
        'data' => $nearExpiryBatches
        ], 200);
    }
}

?>
