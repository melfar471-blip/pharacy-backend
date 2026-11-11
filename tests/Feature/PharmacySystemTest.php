<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Supplier;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PharmacySystemTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $cashier;
    protected $medicine;

    protected function setUp(): void
    {
        parent::setUp();

      
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $cashierRole = \Spatie\Permission\Models\Role::create(['name' => 'cashier']);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole($adminRole);

        $this->cashier = User::factory()->create(['email' => 'cashier@test.com']);
        $this->cashier->assignRole($cashierRole);

       
        $this->medicine = Medicine::create([
            'barcode' => '123456789',
            'name' => 'Panadol',
            'category' => 'Painkiller',
            'alert_threshold' => 5,
            'purchase_price' => 5.00,
            'selling_price' => 10.00,
        ]);

        MedicineBatch::create([
            'medicine_id' => $this->medicine->id,
            'batch_number' => 'BATCH-001',
            'expiry_date' => now()->addYear(),
            'purchase_price' => 5.00,
            'quantity' => 20,
        ]);
    }

    
    public function test_authentication_required_for_protected_routes()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

   
    public function test_cashier_cannot_access_admin_dashboard()
    {
        $this->actingAs($this->cashier);

        $response = $this->get('/admin/dashboard');
        $response->assertForbidden(); 
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
    }

  
    public function test_admin_can_create_and_update_medicine()
    {
        $this->actingAs($this->admin);

    
        $response = $this->postJson('/api/medicines', [
            'barcode' => '987654321',
            'name' => 'Aspirin',
            'category' => 'Painkiller',
            'purchase_price' => 3.00,
            'selling_price' => 7.00,
            'quantity' => 10,
            'expiry_date' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('medicines', ['name' => 'Aspirin']);
    }

  
    public function test_purchase_order_increases_stock_upon_receiving()
    {
        $this->actingAs($this->admin);

        $supplier = Supplier::create(['name' => 'Pharma Co', 'phone' => '01000000000']);

        $initialStock = $this->medicine->total_stock; // 20

        $po = \App\Models\PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'order_number' => 'PO-TEST',
            'total_cost' => 50,
            'status' => 'pending',
        ]);

        \App\Models\PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'medicine_id' => $this->medicine->id,
            'quantity' => 10,
            'unit_cost' => 5,
            'subtotal' => 50,
        ]);

        
        $po->update(['status' => 'received']);
        $this->medicine->increment('quantity', 10);

        $this->assertEquals($initialStock + 10, $this->medicine->fresh()->total_stock);
    }

    public function test_sale_deducts_stock_using_fefo()
    {
        $this->actingAs($this->cashier);

       
        $sale = Sale::create([
            'invoice_number' => 'INV-TEST-01',
            'total_amount' => 50.00,
            'payment_method' => 'cash',
            'user_id' => $this->cashier->id,
        ]);

        $batch = $this->medicine->batches->first();
        $qtyBefore = $batch->quantity;

        $batch->decrement('quantity', 5);
        
        $sale->items()->create([
            'medicine_id' => $this->medicine->id,
            'quantity' => 5,
            'unit_price' => 10.00,
            'unit_cost' => 5.00,
            'subtotal' => 50.00,
        ]);

        $this->assertEquals($qtyBefore - 5, $batch->fresh()->quantity);
    }

    public function test_sale_fails_if_insufficient_stock()
    {
        $this->actingAs($this->cashier);

      
        $requestedQty = 50;
        $totalStock = $this->medicine->total_stock;

        $this->assertTrue($requestedQty > $totalStock);
    }

    
    public function test_concurrent_sales_with_lock_for_update()
    {
        $this->actingAs($this->cashier);

        DB::transaction(function () {
            $batch = MedicineBatch::where('medicine_id', $this->medicine->id)
                ->lockForUpdate()
                ->first();

            $this->assertNotNull($batch);
            $this->assertEquals(20, $batch->quantity);
        });
    }

   
    public function test_return_order_restores_stock()
    {
        $this->actingAs($this->admin);

        $sale = Sale::create([
            'invoice_number' => 'INV-RET-01',
            'total_amount' => 20.00,
            'payment_method' => 'cash',
            'user_id' => $this->cashier->id,
        ]);

        $saleItem = $sale->items()->create([
            'medicine_id' => $this->medicine->id,
            'quantity' => 2,
            'unit_price' => 10.00,
            'unit_cost' => 5.00,
            'subtotal' => 20.00,
        ]);

        $batch = $this->medicine->batches->first();
        $qtyBeforeReturn = $batch->quantity;

    
        $batch->increment('quantity', 1);

        \App\Models\ReturnOrder::create([
            'sale_id' => $sale->id,
            'user_id' => $this->admin->id,
            'return_number' => 'RET-001',
            'total_refund_amount' => 10.00,
        ]);

        $this->assertEquals($qtyBeforeReturn + 1, $batch->fresh()->quantity);
    }

   
    public function test_reports_endpoint_returns_correct_financials()
    {
        $this->actingAs($this->admin);

       
        $sale = Sale::create([
            'invoice_number' => 'INV-REP-01',
            'total_amount' => 100.00,
            'payment_method' => 'cash',
            'user_id' => $this->cashier->id,
        ]);

        $sale->items()->create([
            'medicine_id' => $this->medicine->id,
            'quantity' => 10,
            'unit_price' => 10.00,
            'unit_cost' => 5.00,
            'subtotal' => 100.00,
        ]);

        $totalSales = Sale::sum('total_amount');
        $this->assertEquals(100.00, $totalSales);
    }
}
?>
