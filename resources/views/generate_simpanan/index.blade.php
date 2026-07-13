<!DOCTYPE html>
<html lang="id" translate="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Simpanan</title>
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
        .step.done::before { content: '✓'; }
        .step.done { background: var(--success); color: white; }

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
            max-width: 560px;
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
        h2 { font-size: 20px; font-weight: 700; color: var(--text); }
        .subtitle { color: var(--text-muted); font-size: 14px; margin-bottom: 24px; }

        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
        input[type="text"] {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            background: #fff;
            transition: all 0.2s;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        .hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; line-height: 1.5; }
        .hint code {
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 11px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            transition: all 0.2s;
            text-decoration: none;
            width: 100%;
        }
        .btn:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .alert { padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
        .alert-error { background: #FEE2E2; color: #991B1B; border-left: 3px solid #EF4444; }

        @media (max-width: 480px) {
            .card { padding: 22px; }
            .step { font-size: 11px; padding: 8px 4px; }
            .step-num { display: none; }
        }
    </style>
</head>
<body>
    <header class="step-header">
        <div class="step-container">
            <div class="step active"><span class="step-num">1</span>STEP 1</div>
            <div class="step"><span class="step-num">2</span>STEP 2</div>
            <div class="step"><span class="step-num">3</span>STEP 3</div>
        </div>
    </header>

    <div class="content">
        <div class="card">
            <div class="card-header">
                <div class="icon-box">⚙</div>
                <h2>Proses Generate Simpanan</h2>
            </div>
            <p class="subtitle">Sinkronkan data real simpanan dengan transaksi berdasarkan CIF.</p>

            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <form action="{{ url('/generate_simpanan') }}" method="get" id="mainForm">
                <div class="form-group">
                    <label for="id">CIF</label>
                    <input type="text" id="id" name="id" placeholder="Contoh: 101, 102, 103 atau kosongkan untuk semua CIF" autofocus>
                    <div class="hint">
                        Kosongkan untuk memproses <strong>semua CIF</strong>, atau masukkan beberapa CIF dipisah koma, contoh: <code>101, 102, 103</code>
                    </div>
                </div>
                <button type="submit" class="btn" id="submitBtn">Mulai Proses</button>
            </form>
            <script>
                document.getElementById('mainForm').addEventListener('submit', function(e) {
                    var idInput = document.getElementById('id');
                    if (idInput.value.trim() === '') {
                        e.preventDefault();
                        idInput.value = 'all';
                        this.submit();
                    }
                });
            </script>
        </div>
    </div>
</body>
</html>
