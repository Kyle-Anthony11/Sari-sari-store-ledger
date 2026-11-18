<div class="space-y-4 sm:space-y-6">
    @if (session()->has('message'))
        <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('message') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6">
        {{-- Add Product (first on mobile, right column on desktop) --}}
        <div class="lg:col-span-1 lg:order-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">Add Product</h2>
                <p class="text-xs text-slate-500 mt-0.5">Create a new product entry</p>
            </div>
            <div class="p-4 sm:p-5 space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Item Name</label>
                    <input id="name" type="text" wire:model="name"
                           class="block w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                           readonly onfocus="this.removeAttribute('readonly')" autocomplete="off" />
                    @error('name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="unitType" class="block text-sm font-semibold text-slate-700 mb-1.5">Unit Type</label>
                    <select id="unitType" wire:model.live="unitType" class="block w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        <option value="Per Piece">Per Piece (quantity)</option>
                        <option value="Per Kilos">Per Kilos (kg)</option>
                    </select>
                    @error('unitType') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Price per {{ $unitType === 'Per Piece' ? 'Piece' : 'Kilo' }} (₱)
                    </label>
                    <input id="price" type="number" inputmode="decimal" step="0.01" wire:model="price"
                           class="block w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                           readonly onfocus="this.removeAttribute('readonly')" autocomplete="off" />
                    @error('price') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>

                <button type="button" wire:click="createItem"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Add Product
                </button>
            </div>
        </div>

        {{-- Product List (right, 75%) --}}
        <div class="lg:col-span-3 lg:order-1 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Product List</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $items->total() }} products</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <div class="relative w-full sm:w-48">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" wire:model.live="searchProducts" placeholder="Search..."
                               autocomplete="new-password"
                               class="w-full rounded-xl border border-slate-300 pl-9 pr-3 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                    </div>
                    <select wire:model.live="priceSort"
                            class="w-full sm:w-auto rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                        <option value="">Default (A-Z)</option>
                        <option value="high_low">Price ↑ (High to Low)</option>
                        <option value="low_high">Price ↓ (Low to High)</option>
                    </select>
                </div>
            </div>

            {{-- Mobile cards --}}
            <div class="md:hidden divide-y divide-slate-100 max-h-[520px] overflow-y-auto">
                @forelse ($items as $item)
                    <div class="p-4 space-y-3 active:bg-slate-50/60 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-slate-900 truncate">{{ $item->name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $item->unit }}</div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-base font-bold text-slate-900">₱{{ number_format($item->price, 2) }}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="editProduct({{ $item->id }})"
                                    class="rounded-xl bg-indigo-600 px-3 py-2.5 text-xs font-semibold text-white active:bg-indigo-700 transition">Edit</button>
                            <button type="button" wire:click="confirmDeleteProduct({{ $item->id }})"
                                    class="rounded-xl border border-rose-200 bg-white px-3 py-2.5 text-xs font-semibold text-rose-700 active:bg-rose-50 transition">Delete</button>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </div>
                            <p class="text-sm font-medium text-slate-600">No products yet</p>
                            <p class="text-xs text-slate-500">Add your first product above</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <div class="hidden md:block overflow-x-auto max-h-[520px] overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50/80 sticky top-0 border-b border-slate-100">
                        <tr class="text-left">
                            <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                            <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Unit</th>
                            <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Price</th>
                            <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($items as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-5 font-medium text-slate-900">{{ $item->name }}</td>
                                <td class="py-3 px-5 text-slate-600">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $item->unit }}</span>
                                </td>
                                <td class="py-3 px-5 font-semibold text-slate-900">₱{{ number_format($item->price, 2) }}</td>
                                <td class="py-3 px-5">
                                    <div class="flex gap-2 justify-end">
                                        <button type="button" wire:click="editProduct({{ $item->id }})" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 transition">Edit</button>
                                        <button type="button" wire:click="confirmDeleteProduct({{ $item->id }})" class="rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-12 text-center text-sm text-slate-500">No products yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="px-4 sm:px-5 py-4 border-t border-slate-100 flex justify-center">{{ $items->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Edit Modal --}}
    <x-modal wire:model="showEditModal" maxWidth="md">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">Edit Product</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="$set('showEditModal', false)" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form wire:submit.prevent="updateProduct" class="p-5 sm:p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Name</label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model="editForm.name" />
                    @error('editForm.name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Unit Type</label>
                    <select class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model="editForm.unit_type">
                        <option value="Per Piece">Per Piece</option>
                        <option value="Per Kilos">Per Kilos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Price (₱)</label>
                    <input type="number" inputmode="decimal" step="0.01" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model="editForm.price" />
                    @error('editForm.price') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showEditModal', false)" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">Save Changes</button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Delete Modal --}}
    <x-modal wire:model="showDeleteModal" maxWidth="md">
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-rose-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-semibold text-slate-900">Delete Product?</h2>
                    <p class="text-sm text-slate-500 mt-1">This action cannot be undone.</p>
                </div>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                <button type="button" wire:click="cancelDelete" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Cancel</button>
                <button type="button" wire:click="deleteProduct" class="w-full sm:w-auto rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 active:scale-[0.98] transition">Delete Product</button>
            </div>
        </div>
    </x-modal>
</div>