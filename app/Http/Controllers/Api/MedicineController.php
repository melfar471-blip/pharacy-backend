<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{

    public function index(Request $request)
    {
        $query = Medicine::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
        }

        return response()->json($query->paginate(10), 200);
    }

   
    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|unique:medicines,barcode',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'quantity' => 'required|integer|min:0',
            'alert_threshold' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expiry_date' => 'required|date',
            'supplier_name' => 'nullable|string'
        ]);

        $medicine = Medicine::create($validated);

        return response()->json([
            'message' => 'تم إضافة الدواء بنجاح',
            'data' => $medicine
        ], 201);
    }

    
    public function lowStock()
    {
        $medicines = Medicine::whereColumn('quantity', '<=', 'alert_threshold')->get();

        return response()->json([
            'count' => $medicines->count(),
            'data' => $medicines
        ], 200);
    }

            public function expiryAlerts(Request $request)
            {
                $days = $request->get('days', 90); 
             $targetDate = now()->addDays($days)->toDateString();
            $expiringMedicines = Medicine::where('expiry_date', '<=', $targetDate)
            ->orderBy('expiry_date', 'asc')
            ->get();

            return response()->json([
            'count' => $expiringMedicines->count(),
            'message' => "الأدوية المنتهية أو التي ستنتهي خلال {$days} يوماً",
            'data' => $expiringMedicines
                ], 200);
            }

}

?>
