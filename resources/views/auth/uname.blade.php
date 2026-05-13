<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Developer Login - {{ $kec->nama_kec ?? 'Koperasi' }}</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|Noto+Sans:300,400,500,600,700,800"
        rel="stylesheet" />
    <script src="https://kit.fontawesome.com/349ee9c857.js" crossorigin="anonymous"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #0f1117;
            min-height: 100vh;
            color: #e2e8f0;
        }

        .header-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 50px;
            z-index: 9999;
            background-color: #1a1d2e;
            color: #a78bfa;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.4);
            border-bottom: 1px solid rgba(167, 139, 250, 0.15);
        }

        .header-bar .left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-bar .dev-badge {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-bar .title {
            font-weight: 700;
            font-size: 15px;
            color: #e2e8f0;
        }

        .header-bar .kec-name {
            font-size: 13px;
            opacity: 0.7;
            color: #a78bfa;
        }

        .main-content {
            padding-top: 70px;
            padding-bottom: 40px;
            max-width: 1000px;
            margin: 0 auto;
            padding-left: 20px;
            padding-right: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 8px;
        }

        .page-title h2 {
            font-size: 22px;
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 6px;
        }

        .page-title h2 i {
            color: #a78bfa;
        }

        .page-title p {
            font-size: 13px;
            color: #64748b;
        }

        .hint-bar {
            text-align: center;
            margin-bottom: 24px;
            padding: 10px 16px;
            background: rgba(167, 139, 250, 0.08);
            border: 1px solid rgba(167, 139, 250, 0.15);
            border-radius: 8px;
            font-size: 12px;
            color: #a78bfa;
        }

        .hint-bar i {
            margin-right: 6px;
        }

        .user-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 14px;
        }

        .user-card {
            background: #1a1d2e;
            border-radius: 10px;
            padding: 18px;
            border: 1px solid #2d3148;
            transition: all 0.25s ease;
            cursor: pointer;
            user-select: none;
            position: relative;
            overflow: hidden;
        }

        .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #7c3aed, #a78bfa, #7c3aed);
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .user-card:hover {
            border-color: #7c3aed;
            box-shadow: 0 4px 20px rgba(124, 58, 237, 0.15);
            transform: translateY(-2px);
        }

        .user-card:hover::before {
            opacity: 1;
        }

        .user-card:active {
            transform: translateY(0);
        }

        .user-card.logging-in {
            border-color: #22c55e;
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.2);
        }

        .user-card.logging-in::before {
            background: linear-gradient(90deg, #22c55e, #4ade80, #22c55e);
            opacity: 1;
        }

        .user-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 17px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #e2e8f0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-meta-badges {
            display: flex;
            gap: 6px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .badge {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-level {
            background-color: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        .badge-jabatan {
            background-color: rgba(52, 211, 153, 0.1);
            color: #34d399;
            border: 1px solid rgba(52, 211, 153, 0.2);
        }

        .credentials {
            background: #12141f;
            border-radius: 8px;
            padding: 10px 14px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .cred-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .cred-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
        }

        .cred-value {
            font-size: 13px;
            font-family: 'Courier New', monospace;
            color: #a78bfa;
            font-weight: 600;
            word-break: break-all;
            cursor: pointer;
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 6px;
            border-radius: 4px;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .cred-value:hover {
            background: rgba(167, 139, 250, 0.15);
            color: #c4b5fd;
        }

        .cred-value .copy-icon {
            font-size: 10px;
            opacity: 0;
            transition: opacity 0.2s ease;
            color: #64748b;
        }

        .cred-value:hover .copy-icon {
            opacity: 1;
        }

        .cred-value .copied-tip {
            position: absolute;
            top: -28px;
            left: 50%;
            transform: translateX(-50%) scale(0.8);
            background: #22c55e;
            color: white;
            font-size: 10px;
            font-family: 'Open Sans', sans-serif;
            padding: 3px 10px;
            border-radius: 4px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .cred-value .copied-tip.show {
            opacity: 1;
            transform: translateX(-50%) scale(1);
        }

        .cred-value .copied-tip::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #22c55e;
        }

        .login-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 17, 23, 0.85);
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            z-index: 10;
        }

        .login-overlay.active {
            display: flex;
        }

        .login-overlay .spinner {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #22c55e;
            font-size: 14px;
            font-weight: 600;
        }

        .login-overlay .spinner i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .card-footer-section {
            border-top: 1px solid #2d3148;
            padding-top: 16px;
            text-align: center;
            margin-top: 30px;
        }

        .card-footer-section p {
            font-size: 11px;
            color: #475569;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #a78bfa;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #475569;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #2d3148;
        }

        .empty-state p {
            font-size: 15px;
        }

        /* Hidden login form */
        #login-form {
            display: none;
        }

        @media (max-width: 600px) {
            .user-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="header-bar">
        <div class="left">
            <span class="dev-badge"><i class="fas fa-code"></i> DEV</span>
            <span class="title">{{ $kec->nama_lembaga_long ?? 'Koperasi' }}</span>
        </div>
        <span class="kec-name">{{ $kec->nama_kec ?? '' }}</span>
    </div>

    <div class="main-content">
        <a href="/" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Login
        </a>

        <div class="page-title">
            <h2><i class="fas fa-terminal"></i> Developer Quick Login</h2>
            <p>{{ $kec->nama_kec ?? '' }} &mdash; {{ $kec->kabupaten->nama_kab ?? '' }}</p>
        </div>

        <div class="hint-bar">
            <i class="fas fa-mouse-pointer"></i>
            Double-klik pada kartu user untuk login otomatis sebagai user tersebut
        </div>

        @if($users->count() > 0)
            <div class="user-grid">
                @foreach($users as $user)
                    <div class="user-card" data-uname="{{ $user->uname }}" data-pass="{{ $user->pass }}" ondblclick="quickLogin(this)">
                        <div class="login-overlay">
                            <div class="spinner">
                                <i class="fas fa-circle-notch"></i> Logging in...
                            </div>
                        </div>
                        <div class="user-card-header">
                            <div class="user-avatar">
                                @if($user->foto)
                                    <img src="/storage/foto/{{ $user->foto }}" alt="{{ $user->namadepan }}">
                                @else
                                    {{ strtoupper(substr($user->namadepan ?? 'U', 0, 1)) }}
                                @endif
                            </div>
                            <div class="user-info">
                                <div class="user-name">{{ $user->namadepan }} {{ $user->namabelakang }}</div>
                                <div class="user-meta-badges">
                                    @if($user->l)
                                        <span class="badge badge-level">{{ $user->l->nama_level ?? '' }}</span>
                                    @endif
                                    @if($user->j)
                                        <span class="badge badge-jabatan">{{ $user->j->nama_jabatan ?? '' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="credentials">
                            <div class="cred-item">
                                <span class="cred-label">Username</span>
                                <span class="cred-value" onclick="copyToClipboard(event, this, '{{ $user->uname }}')">{{ $user->uname }}<i class="fas fa-copy copy-icon"></i><span class="copied-tip">Copied!</span></span>
                            </div>
                            <div class="cred-item">
                                <span class="cred-label">Password</span>
                                <span class="cred-value" onclick="copyToClipboard(event, this, '{{ $user->pass }}')">{{ $user->pass }}<i class="fas fa-copy copy-icon"></i><span class="copied-tip">Copied!</span></span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <p>Tidak ada pengguna terdaftar untuk kecamatan ini.</p>
            </div>
        @endif

        <div class="card-footer-section">
            <p>&copy; {{ date('Y') }} Developer Tools &mdash; Sistem Informasi Koperasi</p>
        </div>
    </div>

    <!-- Hidden form for login -->
    <form id="login-form" method="POST" action="/login">
        @csrf
        <input type="hidden" name="username" id="form-username">
        <input type="hidden" name="password" id="form-password">
    </form>

    <script>
        function copyToClipboard(event, el, text) {
            event.stopPropagation();

            navigator.clipboard.writeText(text).then(function() {
                const tip = el.querySelector('.copied-tip');
                tip.classList.add('show');
                setTimeout(() => tip.classList.remove('show'), 1200);
            }).catch(function() {
                // Fallback for older browsers
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);

                const tip = el.querySelector('.copied-tip');
                tip.classList.add('show');
                setTimeout(() => tip.classList.remove('show'), 1200);
            });
        }

        function quickLogin(card) {
            const uname = card.getAttribute('data-uname');
            const pass = card.getAttribute('data-pass');

            // Show loading overlay
            card.classList.add('logging-in');
            card.querySelector('.login-overlay').classList.add('active');

            // Disable further clicks
            document.querySelectorAll('.user-card').forEach(c => {
                c.style.pointerEvents = 'none';
            });

            // Fill hidden form and submit
            document.getElementById('form-username').value = uname;
            document.getElementById('form-password').value = pass;

            setTimeout(() => {
                document.getElementById('login-form').submit();
            }, 300);
        }
    </script>
</body>

</html>
