<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->invoice_number }} - {{ $pharmacyName }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            background: #fff;
            color: #000;
        }
        @media print {
            .no-print { display: none !important; }
            body { width: 100%; padding: 0; }
        }
    </style>
</head>
<body class="text-xs antialiased">
    <div class="no-print mb-4 flex gap-2 justify-center py-2 bg-slate-100 rounded-xl">
        <button onclick="window.print()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-sans font-bold rounded-lg shadow-sm text-xs flex items-center gap-1.5">
            <i class="fa-solid fa-print"></i> Print Receipt
        </button>
        <button onclick="window.close()" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-sans font-bold rounded-lg text-xs">
            Close
        </button>
    </div>

    <!-- Receipt Container -->
    <div class="space-y-3">
        <!-- Pharmacy Header -->
        <div class="text-center space-y-0.5 border-b border-dashed border-black pb-2">
            <h1 class="text-sm font-black uppercase tracking-wider">{{ $pharmacyName }}</h1>
            <p class="text-[11px]">{{ $pharmacyAddress }}</p>
            <p class="text-[11px]">Tel: {{ $pharmacyPhone }}</p>
            <p class="text-[10px] uppercase font-bold pt-1">** SALES RECEIPT **</p>
        </div>

        <!-- Metadata -->
        <div class="text-[11px] space-y-0.5 border-b border-dashed border-black pb-2">
            <div class="flex justify-between">
                <span>Invoice:</span>
                <span class="font-bold font-mono">{{ $sale->invoice_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Date:</span>
                <span>{{ $sale->sale_date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Cashier:</span>
                <span>{{ $sale->seller?->name ?? 'Staff' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Customer:</span>
                <span>{{ $sale->customer?->name ?? 'Walk-in Customer' }}</span>
            </div>
            @if($sale->customer?->phone)
                <div class="flex justify-between">
                    <span>Phone:</span>
                    <span>{{ $sale->customer->phone }}</span>
                </div>
            @endif
        </div>

        <!-- Items Table -->
        <div class="border-b border-dashed border-black pb-2">
            <table class="w-full text-left text-[11px]">
                <thead>
                    <tr class="border-b border-black font-bold">
                        <th class="pb-1">Item</th>
                        <th class="pb-1 text-center">Qty</th>
                        <th class="pb-1 text-right">Price</th>
                        <th class="pb-1 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dotted divide-slate-300">
                    @foreach($sale->items as $item)
                        <tr class="align-top">
                            <td class="py-1">
                                <div class="font-bold">{{ $item->medicine->name }}</div>
                                <div class="text-[9px] text-slate-600">
                                    Batch: {{ $item->batch?->batch_number ?? 'N/A' }}
                                    (Exp: {{ $item->batch?->expiry_date?->format('m/y') ?? 'N/A' }})
                                </div>
                            </td>
                            <td class="py-1 text-center font-bold">{{ $item->quantity }}</td>
                            <td class="py-1 text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-1 text-right font-bold">{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Summary -->
        <div class="text-[11px] space-y-1 border-b border-dashed border-black pb-2">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ $currency }} {{ number_format($sale->subtotal, 2) }}</span>
            </div>
            @if($sale->discount > 0)
                <div class="flex justify-between">
                    <span>Discount:</span>
                    <span>-{{ $currency }} {{ number_format($sale->discount, 2) }}</span>
                </div>
            @endif
            @if($sale->tax > 0)
                <div class="flex justify-between">
                    <span>Tax:</span>
                    <span>{{ $currency }} {{ number_format($sale->tax, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-xs font-black pt-1 border-t border-black">
                <span>TOTAL:</span>
                <span>{{ $currency }} {{ number_format($sale->total, 2) }}</span>
            </div>
            <div class="flex justify-between pt-1">
                <span>Paid ({{ strtoupper($sale->payment_method) }}):</span>
                <span>{{ $currency }} {{ number_format($sale->amount_paid, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold">
                <span>Change:</span>
                <span>{{ $currency }} {{ number_format($sale->change_due, 2) }}</span>
            </div>
        </div>

        <!-- Footer Notice -->
        <div class="text-center text-[10px] space-y-1 pt-1">
            <p class="font-bold">Thank you for your patronage!</p>
            <p>Medicines sold in good condition are not returnable unless approved by the Pharmacist.</p>
            <p class="font-mono text-[9px] pt-1">*** System Powered by PIMS ***</p>
        </div>
    </div>
</body>
</html>
