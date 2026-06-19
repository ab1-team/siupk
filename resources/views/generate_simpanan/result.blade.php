<!DOCTYPE html>
<html lang="id" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Simpanan - Proses</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5DC4BF;
            --primary-dark: #47B3AE;
            --primary-light: #E0F4F3;
            --bg: #F5F8FA;
            --card: #ffffff;
            --text: #1F2937;
            --text-muted: #6B7280;
            --border: #E5E7EB;
            --success: #10B981;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .step-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 18px 0;
            box-shadow: 0 2px 8px rgba(93, 196, 191, 0.15);
        }
        .step-container {
            display: flex;
            justify-content: center;
            max-width: 720px;
            margin: 0 auto;
            gap: 4px;
            padding: 0 16px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px 8px;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
            background: rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.7);
        }
        .step:first-child { border-radius: 8px 0 0 8px; }
        .step:last-child { border-radius: 0 8px 8px 0; }
        .step.active { background: rgba(255,255,255,0.95); color: var(--primary-dark); }
        .step-num {
            display: inline-block;
            width: 22px;
            height: 22px;
            line-height: 22px;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            color: white;
            font-size: 12px;
            margin-right: 6px;
            vertical-align: middle;
        }
        .step.active .step-num { background: var(--primary); color: white; }
        .step.done { background: var(--success); color: white; }
        .step.done::before { content: '✓'; margin-right: 4px; }

        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 32px 16px;
        }
        .card {
            background: var(--card);
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            padding: 32px;
            width: 100%;
            max-width: 600px;
        }
        .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; }
        .icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--primary-light);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        h2 { font-size: 20px; font-weight: 700; }
        .subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 24px; }

        .stat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px;
            background: var(--bg);
            border-radius: 10px;
            margin-bottom: 14px;
        }
        .stat-label { color: var(--text-muted); font-size: 13px; }
        .stat-value { font-size: 28px; font-weight: 700; color: var(--primary-dark); }
        .stat-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        .progress {
            height: 10px;
            background: var(--border);
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            transition: width 0.4s ease;
        }
        .progress-text {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .form-row { display: flex; gap: 12px; align-items: center; margin-bottom: 12px; }
        .input-start {
            width: 100px;
            padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            text-align: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary);
            color: white;
            padding: 12px 22px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            transition: all 0.2s;
            text-decoration: none;
            flex: 1;
        }
        .btn:hover:not(:disabled) { background: var(--primary-dark); transform: translateY(-1px); }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        .btn-success { background: var(--success); }
        .btn-success:hover:not(:disabled) { background: #059669; }

        .success-card {
            background: #D1FAE5;
            border-left: 3px solid var(--success);
            padding: 16px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success-card .title { font-size: 16px; font-weight: 700; color: #065F46; display: flex; align-items: center; gap: 8px; }
        .success-card .desc { color: #047857; font-size: 13px; margin-top: 4px; }

        .btn-group { display: flex; gap: 10px; margin-top: 8px; }
        .btn-outline { background: transparent; color: var(--text-muted); border: 1.5px solid var(--border); }
        .btn-outline:hover:not(:disabled) { background: var(--bg); }

        @media (max-width: 480px) {
            .card { padding: 22px; }
            .step { font-size: 11px; padding: 8px 4px; }
            .step-num { display: none; }
            .form-row { flex-direction: column; align-items: stretch; }
            .input-start { width: 100%; }
        }
    </style>
</head>
<body>
    <header class="step-header">
        <div class="step-container">
            <div class="step done"><span class="step-num">1</span>STEP 1</div>
            <div class="step active"><span class="step-num">2</span>STEP 2</div>
            <div class="step {{ $isDone ? 'active' : '' }}"><span class="step-num">3</span>STEP 3</div>
        </div>
    </header>

    <div class="content">
        <div class="card">
            <div class="card-header">
                <div class="icon-box">⚙</div>
                <h2>Generate Simpanan</h2>
            </div>
            <p class="subtitle">Menyinkronkan data real simpanan dengan transaksi...</p>

            <div class="stat">
                <div>
                    <div class="stat-label">Total Simpanan</div>
                    <div class="stat-sub">yang akan diproses</div>
                </div>
                <div class="stat-value">{{ $total }}</div>
            </div>

            @php
                $processed = min($start, $total);
                $pct = $total > 0 ? min(100, round($processed / $total * 100)) : 0;
            @endphp
            <div class="progress">
                <div class="progress-bar" style="width: {{ $pct }}%"></div>
            </div>
            <div class="progress-text">
                <span>{{ $processed }} / {{ $total }} data</span>
                <span>{{ $pct }}%</span>
            </div>

            @if ($isDone)
                <div class="success-card">
                    <div class="title">✓ Proses Generate Selesai</div>
                    <div class="desc">Semua data simpanan telah berhasil disinkronkan ke real_simpanan.</div>
                </div>
                <div class="btn-group">
                    <a href="{{ url('/generate_simpanan') }}" class="btn btn-outline">Generate Ulang</a>
                    <a href="{{ url('/generate_bunga.php') }}" class="btn btn-success">Lanjut Step 3 →</a>
                </div>
            @else
                <form action="{{ url('/generate_simpanan') }}" method="get" id="runForm">
                    <input type="hidden" name="id" value="{{ $id }}">
                    <input type="hidden" name="limit" value="{{ $limit }}">
                    <div class="form-row">
                        <input type="text" name="start" id="start" value="{{ $start }}" readonly class="input-start">
                        <button type="submit" id="runButton" class="btn">
                            <span>Jalankan</span><span id="loadingDots">.</span>
                        </button>
                    </div>
                </form>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var button = document.getElementById('runButton');
                        var loadingDots = document.getElementById('loadingDots');
                        var dotCount = 0;

                        function animateDots() {
                            dotCount = (dotCount % 4) + 1;
                            loadingDots.textContent = '.'.repeat(dotCount);
                        }

                        var interval = setInterval(animateDots, 500);
                        button.addEventListener('click', function() {
                            clearInterval(interval);
                            button.disabled = true;
                            button.querySelector('span').textContent = 'Memproses...';
                        });

                        setTimeout(function() {
                            document.getElementById('runForm').submit();
                        }, 800);
                    });
                </script>
            @endif
        </div>
    </div>
</body>
</html>
