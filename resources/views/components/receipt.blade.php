@props(['receiptData'])

@php
    $isGcash = strtolower($receiptData['payment_method'] ?? 'cash') === 'gcash';
@endphp

<div class="receipt-container bg-white w-full max-w-md mx-auto">
    <div class="text-center border-b-2 border-dashed border-slate-200 pb-3 sm:pb-4 mb-3 sm:mb-4">
        <h2 class="text-lg sm:text-xl font-bold text-slate-900 break-words">{{ $receiptData['store_name'] ?? 'Store' }}</h2>
        <p class="text-xs sm:text-sm text-slate-500 break-words">{{ $receiptData['owner_name'] ?? '' }}</p>
    </div>

    <div class="space-y-1 sm:space-y-1.5 mb-3 sm:mb-4 text-xs sm:text-sm">
        <div class="flex justify-between gap-2">
            <span class="text-slate-500 flex-shrink-0">Customer:</span>
            <span class="font-semibold text-slate-900 text-right truncate">{{ $receiptData['customer_name'] }}</span>
        </div>
        <div class="flex justify-between gap-2">
            <span class="text-slate-500 flex-shrink-0">Date:</span>
            <span class="text-slate-900 text-right">{{ $receiptData['payment_date'] }}</span>
        </div>
        @if(!empty($receiptData['transaction_number']))
            <div class="flex justify-between gap-2 items-center">
                <span class="text-slate-500 flex-shrink-0">Transaction #:</span>
                <span class="font-mono text-[11px] sm:text-xs font-semibold text-slate-900 bg-slate-100 px-2 py-0.5 rounded">{{ $receiptData['transaction_number'] }}</span>
            </div>
        @endif
        @isset($receiptData['payment_method'])
            <div class="flex justify-between items-center gap-2">
                <span class="text-slate-500 flex-shrink-0">Payment Method:</span>
                @if(strtolower($receiptData['payment_method']) === 'gcash')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">GCash</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Cash</span>
                @endif
            </div>
        @endisset
        @if(!empty($receiptData['reference_number']))
            <div class="flex justify-between items-center gap-2">
                <span class="text-slate-500 flex-shrink-0">GCash Reference #:</span>
                <span class="font-mono text-[11px] sm:text-xs font-semibold text-slate-900 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">{{ $receiptData['reference_number'] }}</span>
            </div>
        @endif
    </div>

    <div class="overflow-x-auto -mx-1 px-1">
        <table class="w-full text-xs sm:text-sm mb-3 sm:mb-4">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left py-2 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">Item</th>
                    <th class="text-right py-2 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">Qty</th>
                    <th class="text-right py-2 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">Price</th>
                    <th class="text-right py-2 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($receiptData['items'] as $item)
                    <tr>
                        <td class="py-1.5 sm:py-2 text-slate-900 break-words">{{ $item['name'] }}</td>
                        <td class="text-right py-1.5 sm:py-2 text-slate-600 whitespace-nowrap">{{ number_format($item['quantity'] ?? 0, 2) }}</td>
                        <td class="text-right py-1.5 sm:py-2 text-slate-600 whitespace-nowrap">₱{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                        <td class="text-right py-1.5 sm:py-2 font-semibold text-slate-900 whitespace-nowrap">₱{{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-slate-200">
                <tr>
                    <td colspan="3" class="text-right py-2 text-xs sm:text-sm text-slate-500">Total</td>
                    <td class="text-right py-2 font-bold text-slate-900 whitespace-nowrap">₱{{ number_format($receiptData['total_amount'], 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right py-1 text-[11px] sm:text-xs text-slate-500">
                        {{ $isGcash ? 'Payment sent' : 'Cash Given' }}
                    </td>
                    <td class="text-right py-1 text-xs sm:text-sm text-slate-700 whitespace-nowrap">₱{{ number_format($receiptData['cash_given'], 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right py-1 text-[11px] sm:text-xs text-slate-500">Change</td>
                    <td class="text-right py-1 text-xs sm:text-sm text-slate-700 whitespace-nowrap">₱{{ number_format($receiptData['change'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="flex flex-col sm:flex-row sm:justify-end gap-2 mt-4 no-print">
        @if(isset($receiptData['showBack']))
            <button type="button" wire:click="{{ $receiptData['backAction'] }}" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">
                Go Back
            </button>
        @endif
        <button type="button" onclick="window.print()" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">
            Print
        </button>
        @if(isset($receiptData['closeAction']))
            <button type="button" wire:click="{{ $receiptData['closeAction'] }}" class="w-full sm:w-auto rounded-xl bg-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-300 transition">
                Okay
            </button>
        @endif
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .receipt-container { max-width: 100%; padding: 0; }
        body { background: white; }
    }
</style>