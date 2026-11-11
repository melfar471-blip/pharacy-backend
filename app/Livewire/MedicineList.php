<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Medicine;

class MedicineList extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';

    protected $queryString = ['search', 'category'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteMedicine($id)
    {
        $medicine = Medicine::findOrFail($id);

        
        if ($medicine->saleItems()->exists()) {
            $medicine->update(['is_archived' => true, 'quantity' => 0]);
            session()->flash('message', 'الدواء مرتبط بمبيعات سابقة، تم تحويله إلى الأرشيف بنجاح.');
        } else {
            $medicine->delete();
            session()->flash('message', 'تم حذف الدواء نهائياً.');
        }
    }

    public function render()
    {
        $query = Medicine::where('is_archived', false);

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->category)) {
            $query->where('category', $this->category);
        }

        $medicines = $query->orderBy('created_at', 'desc')->paginate(10);
        $categories = Medicine::select('category')->distinct()->pluck('category');

        return view('livewire.medicine-list', [
            'medicines' => $medicines,
            'categories' => $categories
        ]);
    }
}
?>