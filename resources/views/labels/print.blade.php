<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label CV Amanda</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <style>
        * { 
            box-sizing: border-box; 
            font-family: 'Arial', sans-serif; 
            color: #000; 
            margin: 0; 
            padding: 0; 
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        body { background: #fff; display: flex; justify-content: center; }
        
        @media print {
            @page { size: 100mm 75mm; margin: 0; }
            body { width: 100mm; height: 75mm; }
        }
        
        .label-wrapper {
            width: 96mm; 
            height: 71mm; 
            margin: 2mm auto;
            border: 2px solid #000;
            border-radius: 6px;
            padding: 1mm 2.5mm 2.5mm 2.5mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }
        
        /* Header */
        .header {
            text-align: left;
            border-bottom: 2px solid #000;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }
        .header-line {
            display: flex;
            align-items: baseline;
            white-space: nowrap;
        }
        .header-top { font-size: 10px; font-weight: normal; }
        .header-company { font-size: 11px; font-weight: normal; letter-spacing: 0px; margin-left: 2px; }
        .address { font-size: 8px; line-height: 1; margin-top: 1px; font-weight: normal; }

        /* Product Title Block */
        .product-title {
            background: #000;
            color: #fff !important;
            text-align: left;
            font-size: 18px;
            font-weight: 900;
            padding: 3px 4px 3px 12px;
            border-radius: 4px;
            margin-bottom: 3px;
            text-transform: uppercase;
            line-height: 1.1;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Info Grid */
        .info-grid {
            display: flex;
            flex: 1;
            border: 2px solid #000;
            border-radius: 4px;
            margin-bottom: 3px;
        }
        
        /* Main Left Box */
        .main-box {
            flex: 0 0 68%;
            position: relative;
            border-right: 2px solid #000;
            display: flex;
            flex-direction: column;
            padding: 2px 4px;
        }
        .pcs-absolute { 
            position: absolute; 
            top: 2px; 
            right: 4px; 
            font-size: 16px; 
            font-weight: 900; 
        }
        .weight-value { display: flex; align-items: baseline; margin-top: 10px; }
        .weight-num { font-size: 40px; font-weight: 900; letter-spacing: -1px; line-height: 1; }
        .weight-unit { font-size: 14px; font-weight: bold; margin-left: 2px; }

        .dates-area {
            margin-top: auto; /* Mentokin ke garis bawah */
            display: flex;
            flex-direction: column;
            font-size: 11px;
            gap: 3px;
            padding-bottom: 2px;
        }
        .date-line {
            display: flex;
        }
        .date-label {
            width: 55px;
            font-weight: bold;
        }

        /* Halal Right Box */
        .halal-box {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 4px;
        }
        .halal-img {
            max-width: 35mm;
            max-height: 22mm;
            object-fit: contain;
        }

        /* Type Bar */
        .type-bar {
            background: #000;
            color: #fff !important;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            padding: 3px;
            border-radius: 4px;
            margin-bottom: 2px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .type-bar span, .type-bar svg { color: #fff !important; }
        
        /* Barcode Area */
        .barcode-area {
            text-align: center;
            margin-top: auto;
            padding-top: 4px; /* Agak turunin kebawah */
        }
        .barcode-svg { height: 10.5mm; width: 100%; }
        .barcode-text { font-size: 13px; font-weight: bold; letter-spacing: 2px; margin-top: -3px; }
    </style>
</head>
<body>
    <div class="label-wrapper">
        <!-- Header -->
        <div class="header">
            <div class="header-line">
                <span class="header-top">Prod :</span>
                <span class="header-company">PT. BERKAH MARSHA SEJAHTERA</span>
            </div>
            <div class="address">Murnisari, Kec. Mande, Kabupaten Cianjur, Jawa Barat 43292</div>
        </div>
        
        <!-- Product Title Block -->
        <div class="product-title">
            {{ $label->product->name }}
        </div>
        
        <!-- Info Grid (Weight, Pcs, Dates) -->
        <div class="info-grid">
            <div class="main-box">
                @if($label->qty_pcs > 0)
                <div class="pcs-absolute">{{ $label->qty_pcs }}-Pcs</div>
                @endif
                <div class="weight-value">
                    <span class="weight-num">{{ number_format($label->weight, 2) }}</span>
                    <span class="weight-unit">Kg</span>
                </div>
                
                <div class="dates-area">
                    <div class="date-line">
                        <span class="date-label">PACKED</span>
                        <span>{{ \Carbon\Carbon::parse($label->production_date)->format('d-M-y') }}</span>
                    </div>
                    @if($label->print_expired && $label->expired_date)
                    <div class="date-line">
                        <span class="date-label">EXPIRED</span>
                        <span>{{ \Carbon\Carbon::parse($label->expired_date)->format('d-M-y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="halal-box">
                <img src="{{ asset('img/halal.png') }}" class="halal-img" alt="Halal">
            </div>
        </div>
        
        <!-- Type (Chill/Frozen) Bar -->
        <div class="type-bar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20M2 12h20M19 5l-14 14M5 5l14 14M16 12l-4-4M8 12l4 4M12 8l4-4M12 16l-4 4"/>
            </svg>
            <span>
                KEEP {{ strtoupper($label->type->name) }} 
                @if(strtoupper($label->type->name) == 'CHILL') 0&deg;C 
                @elseif(strtoupper($label->type->name) == 'FROZEN') -18&deg;C 
                @endif
            </span>
        </div>
        
        <!-- Barcode -->
        <div class="barcode-area">
            <svg id="barcode" class="barcode-svg"></svg>
            <div class="barcode-text">{{ $label->barcode }}</div>
        </div>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $label->barcode }}", {
            format: "CODE128",
            width: 2.2,
            height: 38,
            displayValue: false,
            margin: 0
        });

        window.onload = function() {
            window.print();
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>
