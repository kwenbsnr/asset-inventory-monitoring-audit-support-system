<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NIA RO IX AIMS - @yield('title', 'Asset Management')</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        :root{
            --navy:#0F2044;--navy-light:#1a3260;--teal:#0D9488;--teal-light:#14B8AA;--teal-bg:#F0FDFA;
            --steel:#2B6CB0;--steel-light:#3B82F6;--amber:#D97706;--amber-bg:#FFFBEB;
            --red:#DC2626;--red-bg:#FEF2F2;--green:#059669;--green-bg:#ECFDF5;
            --gray-50:#F8FAFC;--gray-100:#F1F5F9;--gray-200:#E2E8F0;--gray-300:#CBD5E1;
            --gray-400:#94A3B8;--gray-500:#64748B;--gray-600:#475569;--gray-700:#334155;
            --gray-800:#1E293B;--gray-900:#0F172A;--white:#fff;
            --radius:8px;--sidebar:220px;
        }
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;background:var(--gray-50);color:var(--gray-800);min-height:600px;display:flex;flex-direction:column;}
        .app{display:flex;min-height:600px;height:100vh;max-height:900px;}
        /* Sidebar */
        .sidebar{width:var(--sidebar);background:var(--navy);color:#fff;display:flex;flex-direction:column;flex-shrink:0;}
        .sidebar-brand{padding:16px;border-bottom:1px solid rgba(255,255,255,.1);}
        .brand-logo{display:flex;align-items:center;gap:8px;margin-bottom:4px;}
        .brand-logo .icon{width:32px;height:32px;background:var(--teal);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:16px;}
        .brand-name{font-size:13px;font-weight:600;color:#fff;line-height:1.2;}
        .brand-sub{font-size:10px;color:rgba(255,255,255,.5);margin-top:2px;}
        .nav{flex:1;padding:8px 0;overflow-y:auto;}
        .nav-section{padding:8px 12px 4px;font-size:10px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.08em;}
        .nav-item{display:flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer;color:rgba(255,255,255,.7);font-size:13px;border-radius:0;transition:all .15s;text-decoration:none;}
        .nav-item:hover{background:rgba(255,255,255,.08);color:#fff;}
        .nav-item.active{background:var(--teal);color:#fff;}
        .nav-item .ni{font-size:16px;width:20px;text-align:center;}
        .nav-badge{margin-left:auto;background:var(--amber);color:#fff;font-size:10px;font-weight:600;padding:1px 6px;border-radius:10px;}
        .sidebar-footer{padding:12px;border-top:1px solid rgba(255,255,255,.1);font-size:11px;color:rgba(255,255,255,.4);}
        /* Main */
        .main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
        .topbar{background:#fff;border-bottom:1px solid var(--gray-200);padding:0 20px;height:52px;display:flex;align-items:center;gap:12px;flex-shrink:0;}
        .topbar-title{font-size:16px;font-weight:600;color:var(--gray-900);}
        .topbar-sub{font-size:12px;color:var(--gray-500);margin-left:4px;}
        .topbar-actions{margin-left:auto;display:flex;gap:8px;}
        .btn{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:var(--radius);font-size:13px;font-weight:500;cursor:pointer;border:1px solid var(--gray-200);background:#fff;color:var(--gray-700);transition:all .15s;text-decoration:none;}
        .btn:hover{background:var(--gray-50);border-color:var(--gray-300);}
        .btn-primary{background:var(--teal);color:#fff;border-color:var(--teal);}
        .btn-primary:hover{background:#0b7a70;border-color:#0b7a70;}
        .btn-navy{background:var(--navy);color:#fff;border-color:var(--navy);}
        .btn-navy:hover{background:var(--navy-light);}
        .btn-sm{padding:4px 10px;font-size:12px;}
        .btn-icon{padding:6px;width:32px;justify-content:center;}
        .btn-danger{background:var(--red);color:#fff;border-color:var(--red);}
        .btn-danger:hover{background:#b91c1c;border-color:#b91c1c;}
        .btn-success{background:var(--green);color:#fff;border-color:var(--green);}
        .btn-success:hover{background:#047857;border-color:#047857;}
        .btn-warning{background:var(--amber);color:#fff;border-color:var(--amber);}
        .btn-warning:hover{background:#b45309;border-color:#b45309;}
        .content{flex:1;overflow-y:auto;padding:20px;}
        /* Cards */
        .card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);margin-bottom:16px;}
        .card-header{padding:12px 16px;border-bottom:1px solid var(--gray-100);display:flex;align-items:center;gap:8px;}
        .card-title{font-size:14px;font-weight:600;color:var(--gray-800);}
        .card-body{padding:16px;}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
        .grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;}
        /* Metrics */
        .metric-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:14px 16px;}
        .metric-label{font-size:11px;color:var(--gray-500);margin-bottom:4px;font-weight:500;}
        .metric-value{font-size:26px;font-weight:700;color:var(--gray-900);line-height:1;}
        .metric-sub{font-size:11px;margin-top:4px;}
        .metric-up{color:var(--green);}
        .metric-down{color:var(--red);}
        .accent-teal{border-left:3px solid var(--teal);}
        .accent-amber{border-left:3px solid var(--amber);}
        .accent-red{border-left:3px solid var(--red);}
        .accent-green{border-left:3px solid var(--green);}
        /* Table */
        .table-wrap{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;}
        thead th{text-align:left;padding:8px 12px;font-size:11px;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--gray-200);background:var(--gray-50);}
        tbody tr{border-bottom:1px solid var(--gray-100);}
        tbody tr:hover{background:var(--gray-50);}
        tbody td{padding:10px 12px;font-size:13px;color:var(--gray-700);}
        .td-code{font-family:monospace;font-size:12px;color:var(--navy);font-weight:600;}
        /* Badges */
        .badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;}
        .badge-active{background:var(--green-bg);color:#065F46;}
        .badge-inactive{background:var(--gray-100);color:var(--gray-600);}
        .badge-transferred{background:#EFF6FF;color:#1E40AF;}
        .badge-maintenance{background:var(--amber-bg);color:#92400E;}
        .badge-disposed{background:var(--red-bg);color:#991B1B;}
        .badge-missing{background:#FDF4FF;color:#6B21A8;}
        .badge-pending{background:#FEF3C7;color:#92400E;}
        .badge-completed{background:#D1FAE5;color:#065F46;}
        .dot{width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;}
        /* Form */
        .form-group{margin-bottom:14px;}
        .form-label{display:block;font-size:12px;font-weight:500;color:var(--gray-700);margin-bottom:4px;}
        .form-control{width:100%;padding:7px 10px;border:1px solid var(--gray-300);border-radius:var(--radius);font-size:13px;color:var(--gray-800);background:#fff;outline:none;}
        .form-control:focus{border-color:var(--teal);box-shadow:0 0 0 2px rgba(13,148,136,.1);}
        select.form-control{cursor:pointer;}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
        .form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}
        /* Alerts */
        .alert{padding:10px 14px;border-radius:var(--radius);font-size:13px;margin-bottom:12px;display:flex;align-items:flex-start;gap:8px;}
        .alert-warning{background:var(--amber-bg);color:#92400E;border:1px solid #FCD34D;}
        .alert-success{background:var(--green-bg);color:#065F46;border:1px solid #6EE7B7;}
        .alert-info{background:#EFF6FF;color:#1E40AF;border:1px solid #BFDBFE;}
        .alert-danger{background:var(--red-bg);color:#991B1B;border:1px solid #FCA5A5;}
        /* Progress */
        .progress-bar{height:6px;background:var(--gray-200);border-radius:3px;overflow:hidden;}
        .progress-fill{height:100%;background:var(--teal);border-radius:3px;}
        /* Tabs */
        .tabs{display:flex;border-bottom:1px solid var(--gray-200);margin-bottom:16px;}
        .tab{padding:8px 16px;font-size:13px;font-weight:500;cursor:pointer;color:var(--gray-500);border-bottom:2px solid transparent;transition:all .15s;}
        .tab:hover{color:var(--gray-700);}
        .tab.active{color:var(--teal);border-bottom-color:var(--teal);}
        /* Trail */
        .trail-item{display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--gray-100);}
        .trail-item:last-child{border-bottom:none;}
        .trail-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;margin-top:2px;}
        .trail-dot-reg{background:#EFF6FF;color:var(--steel);}
        .trail-dot-transfer{background:var(--amber-bg);color:var(--amber);}
        .trail-dot-verify{background:var(--green-bg);color:var(--green);}
        .trail-dot-update{background:#F5F3FF;color:#6D28D9;}
        .trail-dot-scan{background:#DBEAFE;color:#1D4ED8;}
        .trail-content{flex:1;}
        .trail-action{font-size:13px;font-weight:500;color:var(--gray-800);}
        .trail-meta{font-size:11px;color:var(--gray-500);margin-top:2px;}
        /* Search */
        .search-bar{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--gray-300);border-radius:var(--radius);padding:6px 12px;flex:1;max-width:340px;}
        .search-bar input{border:none;outline:none;font-size:13px;color:var(--gray-800);width:100%;background:transparent;}
        .search-bar input::placeholder{color:var(--gray-400);}
        /* Modal */
        .modal-overlay{position:fixed;inset:0;background:rgba(15,32,68,.5);display:flex;align-items:center;justify-content:center;z-index:100;}
        .modal{background:#fff;border-radius:12px;width:560px;max-height:85%;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3);}
        .modal-header{padding:16px 20px;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;justify-content:space-between;}
        .modal-title{font-size:15px;font-weight:600;color:var(--gray-900);}
        .modal-body{padding:20px;}
        .modal-footer{padding:12px 20px;border-top:1px solid var(--gray-100);display:flex;justify-content:flex-end;gap:8px;}
        /* Toast */
        .toast{position:fixed;bottom:20px;right:20px;background:var(--gray-900);color:#fff;padding:10px 16px;border-radius:var(--radius);font-size:13px;display:flex;align-items:center;gap:8px;z-index:200;opacity:0;transition:opacity .2s;pointer-events:none;}
        .toast.show{opacity:1;}
        /* QR */
        .qr-card{background:var(--navy);color:#fff;border-radius:10px;padding:16px;text-align:center;display:flex;flex-direction:column;align-items:center;gap:8px;max-width:280px;margin:0 auto;}
        .qr-wrap{background:#fff;border-radius:8px;padding:12px;display:inline-flex;}
        .qr-asset-code{font-family:monospace;font-size:11px;color:rgba(255,255,255,.7);letter-spacing:.1em;}
        .qr-asset-name{font-size:13px;font-weight:600;color:#fff;}
        /* Scanner */
        .scanner-area{border:2px dashed var(--gray-300);border-radius:var(--radius);padding:32px 16px;text-align:center;background:var(--gray-50);cursor:pointer;transition:all .15s;}
        .scanner-area:hover{border-color:var(--teal);background:var(--teal-bg);}
        .scan-icon{font-size:40px;margin-bottom:8px;}
        /* User avatar */
        .user-avatar{width:32px;height:32px;border-radius:50%;background:var(--teal-bg);color:var(--teal);font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        /* Misc */
        .empty-state{text-align:center;padding:32px 16px;color:var(--gray-400);}
        .empty-state .ei{font-size:32px;margin-bottom:8px;}
        .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;}
        .text-center{text-align:center;}
        .mt-2{margin-top:8px;}
        .mb-2{margin-bottom:8px;}
        .gap-2{gap:8px;}
        .flex{display:flex;}
        .flex-between{display:flex;justify-content:space-between;align-items:center;}
        .flex-wrap{flex-wrap:wrap;}
        .w-full{width:100%;}
        .mt-4{margin-top:16px;}
    </style>
    @stack('styles')
</head>
<body>
    <div class="app">
        <!-- SIDEBAR -->
        <nav class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-logo">
                    <div class="icon">💧</div>
                    <div>
                        <div class="brand-name">NIA RO IX AIMS</div>
                        <div class="brand-sub">Asset & Inventory System</div>
                    </div>
                </div>
            </div>
            <div class="nav">
                <div class="nav-section">Overview</div>
                <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <span class="ni">📊</span> Dashboard
                </a>
                <div class="nav-section">Asset Management</div>
                <a class="nav-item {{ request()->routeIs('assets.*') ? 'active' : '' }}" href="{{ route('assets.index') }}">
                    <span class="ni">📦</span> Asset Registry
                </a>
                <a class="nav-item {{ request()->routeIs('assets.create') ? 'active' : '' }}" href="{{ route('assets.create') }}">
                    <span class="ni">➕</span> Register Asset
                </a>
                <a class="nav-item {{ request()->routeIs('qr.*') ? 'active' : '' }}" href="{{ route('qr.index') }}">
                    <span class="ni">📷</span> QR Scanner
                </a>
                <div class="nav-section">Monitoring</div>
                <a class="nav-item {{ request()->routeIs('transfers.*') ? 'active' : '' }}" href="{{ route('transfers.index') }}">
                    <span class="ni">🔄</span> Transfers
                </a>
                <a class="nav-item {{ request()->routeIs('custodians.*') ? 'active' : '' }}" href="{{ route('custodians.index') }}">
                    <span class="ni">👤</span> Custodians
                </a>
                <div class="nav-section">Audit & Reports</div>
                <a class="nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                    <span class="ni">📋</span> Audit Trail
                </a>
                <a class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <span class="ni">📈</span> Reports
                </a>
                @if(auth()->check() && auth()->user()->role === 'admin')
                <div class="nav-section">Admin</div>
                <a class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <span class="ni">👥</span> User Management
                </a>
                @endif
            </div>
            <div class="sidebar-footer">Fiscal Year 2026 · RO IX Zamboanga</div>
        </nav>

        <!-- MAIN -->
        <div class="main">
            <!-- TOPBAR -->
            <div class="topbar">
                <span class="topbar-title">@yield('title', 'Dashboard')</span>
                <span class="topbar-sub">@yield('subtitle', '')</span>
                <div class="topbar-actions">
                    <a href="{{ route('qr.index') }}" class="btn btn-sm">📷 Scan QR</a>
                    <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm">+ Register Asset</a>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="content">
                @if(session('success'))
                    <div class="alert alert-success">
                        <span>✅</span>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <span>❌</span>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <div id="modal-container"></div>
    <div class="toast" id="toast"><span class="toast-icon">✓</span><span id="toast-msg">Action completed.</span></div>

    <script>
        function closeModal() {
            document.getElementById('modal-container').innerHTML = '';
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            document.getElementById('toast-msg').textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        function showModal(title, content) {
            const mc = document.getElementById('modal-container');
            mc.innerHTML = `
                <div class="modal-overlay" onclick="if(event.target===this)closeModal()">
                    <div class="modal">
                        <div class="modal-header">
                            <span class="modal-title">${title}</span>
                            <button class="btn btn-sm btn-icon" onclick="closeModal()">✕</button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                    </div>
                </div>
            `;
        }
    </script>
    @stack('scripts')
</body>
</html>
