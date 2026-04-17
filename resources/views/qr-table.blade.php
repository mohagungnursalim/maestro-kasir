<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Meja {{ $desk }} – {{ $storeName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 24px 16px;
            gap: 20px;
        }

        /* ===== PRINT CONTROLS (screen only) ===== */
        .print-controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: #1e293b; color: white; }
        .btn-primary:hover { background: #0f172a; }
        .btn-secondary { background: white; color: #374151; border: 1.5px solid #e5e7eb; }
        .btn-secondary:hover { background: #f9fafb; }

        /* ===== QR CARD ===== */
        .qr-card {
            background: white;
            border-radius: 24px;
            padding: 32px 28px;
            max-width: 380px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
            text-align: center;
        }

        .card-header {
            background: linear-gradient(135deg, #fbbf24, #f97316);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .card-header .store-name {
            font-size: 20px;
            font-weight: 900;
            color: #1e293b;
            letter-spacing: -0.5px;
        }
        .card-header .tagline {
            font-size: 12px;
            color: #78350f;
            margin-top: 2px;
        }

        .desk-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef3c7;
            border: 2px solid #fbbf24;
            color: #92400e;
            font-size: 13px;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 99px;
            margin-bottom: 16px;
            letter-spacing: 0.3px;
        }

        .scan-title {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 4px;
        }
        .scan-sub {
            font-size: 11px;
            color: #9ca3af;
            margin-bottom: 20px;
        }

        /* QR image container */
        .qr-wrapper {
            background: #fff;
            border: 3px solid #1e293b;
            border-radius: 16px;
            padding: 14px;
            display: inline-block;
            margin-bottom: 20px;
            position: relative;
        }
        .qr-wrapper img {
            display: block;
            width: 200px;
            height: 200px;
        }

        /* Corner decorators */
        .qr-wrapper::before,
        .qr-wrapper::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border-color: #fbbf24;
            border-style: solid;
        }
        .qr-wrapper::before {
            top: -5px; left: -5px;
            border-width: 4px 0 0 4px;
            border-radius: 4px 0 0 0;
        }
        .qr-wrapper::after {
            bottom: -5px; right: -5px;
            border-width: 0 4px 4px 0;
            border-radius: 0 0 4px 0;
        }

        .menu-url {
            font-size: 10px;
            color: #6b7280;
            word-break: break-all;
            background: #f8fafc;
            border-radius: 8px;
            padding: 6px 10px;
            margin-bottom: 16px;
            border: 1px dashed #e2e8f0;
        }

        .steps {
            border-top: 1px dashed #e2e8f0;
            padding-top: 16px;
            text-align: left;
        }
        .steps-title {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
            text-align: center;
        }
        .step {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 8px;
        }
        .step-num {
            width: 22px;
            height: 22px;
            background: #fbbf24;
            color: #1e293b;
            font-size: 12px;
            font-weight: 800;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .step-text {
            font-size: 12px;
            color: #374151;
            line-height: 1.4;
            padding-top: 2px;
        }

        .footer-note {
            margin-top: 16px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #f0f0f0;
            padding-top: 12px;
        }

        /* ===== PRINT ===== */
        @media print {
            body { background: white; padding: 0; }
            .print-controls { display: none; }
            .qr-card { box-shadow: none; max-width: 100%; border-radius: 0; }
        }
    </style>
</head>
<body>
    {{-- Tombol aksi (hanya tampil di layar) --}}
    <div class="print-controls">
        <button class="btn btn-primary" onclick="window.print()">
            🖨️ Cetak QR Code
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            ✕ Tutup
        </button>
    </div>

    {{-- Kartu QR Code --}}
    <div class="qr-card" id="printable">

        {{-- Header --}}
        <div class="card-header">
            <div class="store-name">{{ $storeName }}</div>
            <div class="tagline">Self-Order Menu Digital</div>
        </div>

        {{-- Badge Meja --}}
        <div class="desk-badge">
            🪑 Meja / Nama: <strong>{{ $desk }}</strong>
        </div>

        <p class="scan-title">Scan untuk Pesan</p>
        <p class="scan-sub">Arahkan kamera HP ke QR di bawah ini</p>

        {{-- QR Code Image (via Google Charts API) --}}
        <div class="qr-wrapper">
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&ecc=M&data={{ urlencode($menuUrl) }}"
                alt="QR Code Meja {{ $desk }}"
            >
        </div>

        {{-- URL fallback --}}
        <div class="menu-url">{{ $menuUrl }}</div>

        {{-- Cara Pesan --}}
        <div class="steps">
            <div class="steps-title">Cara Pesan</div>
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text">Scan QR Code di atas menggunakan kamera HP</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text">Pilih menu yang kamu inginkan</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text">Klik "Kirim Pesanan ke Dapur"</div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <div class="step-text">Bayar di kasir setelah selesai menikmati pesanan</div>
            </div>
        </div>

        <div class="footer-note">
            Pesanan dikirim langsung ke dapur · Pembayaran di kasir
        </div>
    </div>
</body>
</html>
