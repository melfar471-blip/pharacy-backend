<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;

class SupplierManagement extends Component
{
    use WithPagination;

    public $name, $contact_person, $phone, $email, $current_balance = 0;
    public $supplierId = null;
    public $selectedSupplier = null;

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $this->supplierId,
            'current_balance' => 'nullable|numeric|min:0',
        ]);

        if ($this->supplierId) {
            Supplier::findOrFail($this->supplierId)->update($this->all());
            session()->flash('message', 'تم تحديث بيانات المورد بنجاح.');
        } else {
            Supplier::create($this->all());
            session()->flash('message', 'تم إضافة المورد بنجاح.');
        }

        $this->reset(['name', 'contact_person', 'phone', 'email', 'current_balance', 'supplierId']);
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->contact_person = $supplier->contact_person;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->current_balance = $supplier->current_balance;
    }

    public function showDetails($id)
    {
        $this->selectedSupplier = Supplier::with('purchaseOrders')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.supplier-management', [
            'suppliers' => Supplier::withCount('purchaseOrders')->orderBy('created_at', 'desc')->paginate(10)
        ]);
    }
}

?>