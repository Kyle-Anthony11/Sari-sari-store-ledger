<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductLists extends Component
{
    use WithPagination;
    protected $paginationTheme = 'simple-tailwind-custom';

    protected $items;
    public string $name = '';
    public string $unitType = 'Per Piece';
    public ?float $price = null;

    // Search, edit, delete
    public string $searchProducts = '';
    public string $priceSort = '';
    public ?int $editingProductId = null;
    public array $editForm = [
        'name' => '',
        'unit_type' => 'Per Piece, Per Kilo',
        'price' => 0,
    ];
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public ?int $productToDelete = null;

    public function mount(): void
    {
        $this->resetForm();
    }

    public function loadItems(): void
    {
        $query = Product::query()->where('is_active', true);

        if (trim($this->searchProducts) !== '') {
            $query->where('name', 'like', '%' . trim($this->searchProducts) . '%');
        }

        if ($this->priceSort === 'high_low') {
            $query->orderByDesc('price');
        } elseif ($this->priceSort === 'low_high') {
            $query->orderBy('price');
        } else {
            $query->orderBy('name');
        }

        $this->items = $query->paginate(5);
    }

    public function updatedSearchProducts(): void
    {
        $this->resetPage();
        $this->loadItems();
    }

    public function updatedPriceSort(): void
    {
        $this->resetPage();
        $this->loadItems();
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->unitType = 'Per Piece';
        $this->price = null;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'unitType' => 'required|in:Per Piece,Per Kilos',
            'price' => 'required|numeric|min:0.01',
        ];
    }

    public function createItem()
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = Product::where('name', $value)
                        ->where('is_active', true)
                        ->exists();
                    if ($exists) {
                        $fail('A product with this name already exists.');
                    }
                },
            ],
            'unitType' => 'required|in:Per Piece,Per Kilos',
            'price' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        $roundedPrice = round((float) $this->price, 2);

        $unit = match ($this->unitType) {
            'Per Piece' => 'Pieces',
            'Per Kilos' => 'Kilos',
        };

        Product::create([
            'name' => $this->name,
            'unit' => $unit,
            'price' => $roundedPrice,
            'is_active' => true,
        ]);

        $this->resetForm();
        $this->loadItems();
        $this->dispatch('showAlert', 'Product created successfully!');
    }

    // EDIT
    public function editProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->editingProductId = $product->id;
        $this->editForm = [
            'name' => $product->name,
            'unit_type' => $product->unit === 'Kilos' ? 'Per Kilos' : 'Per Piece',
            'price' => (float) $product->price,
        ];
        $this->showEditModal = true;
    }

    public function updateProduct(): void
    {
        $this->validate([
            'editForm.name' => 'required|string|max:255',
            'editForm.unit_type' => 'required|in:Per Piece,Per Kilos',
            'editForm.price' => 'required|numeric|min:0.01',
        ]);

        $product = Product::findOrFail($this->editingProductId);
        $unit = $this->editForm['unit_type'] === 'Per Kilos' ? 'Kilos' : 'Pieces';

        $product->update([
            'name' => $this->editForm['name'],
            'unit' => $unit,
            'price' => (float) $this->editForm['price'],
        ]);

        $this->showEditModal = false;
        $this->editingProductId = null;
        $this->loadItems();
        $this->dispatch('showAlert', 'Product updated successfully!');
    }

    // DELETE
    public function confirmDeleteProduct(int $id): void
    {
        $product = Product::find($id);
        if (!$product) {
            session()->flash('message', 'Product not found.');
            return;
        }

        if ($product->transactions()->exists()) {
            $this->dispatch('showAlert', 'Cannot delete product because it has existing transactions.');
            return;
        }

        $this->productToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteProduct(): void
    {
        if (!$this->productToDelete) {
            return;
        }

        $product = Product::find($this->productToDelete);
        if ($product && !$product->transactions()->exists()) {
            $product->delete();
            $this->dispatch('showAlert', 'Product deleted successfully!');
        } else {
            $this->dispatch('showAlert', 'Cannot delete product with transactions.');
        }

        $this->showDeleteModal = false;
        $this->productToDelete = null;
        $this->loadItems();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->productToDelete = null;
    }

    public function render()
    {
        $this->loadItems();
        return view('livewire.product-list', [
            'items' => $this->items,
        ]);
    }
}
