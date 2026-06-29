<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NIA RO IX AIMS - Login</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0F2044;display:flex;align-items:center;justify-content:center;min-height:100vh;}
        .login-box{background:#fff;border-radius:12px;padding:40px;width:400px;max-width:90%;box-shadow:0 20px 60px rgba(0,0,0,.3);}
        .login-title{font-size:24px;font-weight:700;color:#0F2044;text-align:center;margin-bottom:4px;}
        .login-sub{font-size:14px;color:#64748B;text-align:center;margin-bottom:24px;}
        .form-group{margin-bottom:16px;}
        .form-label{display:block;font-size:13px;font-weight:500;color:#334155;margin-bottom:4px;}
        .form-control{width:100%;padding:10px 12px;border:1px solid #CBD5E1;border-radius:8px;font-size:14px;outline:none;}
        .form-control:focus{border-color:#0D9488;box-shadow:0 0 0 3px rgba(13,148,136,.1);}
        .btn{width:100%;padding:10px;background:#0D9488;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;}
        .btn:hover{background:#0b7a70;}
        .alert{padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px;background:#FEF2F2;color:#991B1B;border:1px solid #FCA5A5;}
        .logo{text-align:center;font-size:48px;margin-bottom:12px;}
        .footer{text-align:center;font-size:12px;color:#94A3B8;margin-top:16px;}
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">💧</div>
        <div class="login-title">NIA RO IX AIMS</div>
        <div class="login-sub">Asset & Inventory Management System</div>

        @if(session('error'))
            <div class="alert">❌ {{ session('error') }}</div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Username</label>
                <input class="form-control" name="username" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input class="form-control" name="password" type="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="footer">Fiscal Year 2026 · National Irrigation Administration RO IX</div>
    </div>
</body>
</html>
