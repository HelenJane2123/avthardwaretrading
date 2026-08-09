
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Primary Meta -->
    <title>AVT Hardware Trading</title>
    <meta name="description" content="AVT Hardware Trading - Sales and Inventory Management System for efficient tracking, reporting, and business growth.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AVT Hardware Trading">
    <meta property="og:title" content="AVT Hardware Trading - Sales & Inventory System">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:description" content="Manage sales, track inventory, and generate reports with AVT Hardware Trading’s system.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@avthardware">
    <meta name="twitter:title" content="AVT Hardware Trading - Sales & Inventory System">
    <meta name="twitter:description" content="A complete solution for managing sales and inventory.">
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">

    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%);
            color: #0f172a;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
        }


        .app-title {
            background: linear-gradient(135deg, #0f172a 0%, #2563eb 55%, #38bdf8 100%);
            color: #fff;
            border-radius: 20px;
            padding: 24px 28px;
            box-shadow: 0 16px 40px rgba(37, 99, 235, 0.16);
            margin-bottom: 20px;
        }

        .app-title h1 {
            color: #fff;
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .app-title .text-muted,
        .app-title p {
            color: rgba(255,255,255,0.9) !important;
        }

        .app-title .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }

        .app-title .breadcrumb-item,
        .app-title .breadcrumb-item a {
            color: rgba(255,255,255,0.92);
        }

        .tile {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 18px;
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.06);
            padding: 20px;
        }

        .tile-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 14px;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #e2e8f0;
        }

        .table td,
        .table th {
            vertical-align: middle;
            padding: 12px 14px;
        }

        .table-hover tbody tr:hover {
            background: #f8fbff;
        }

        .btn {
            border-radius: 10px;
            transition: transform .2s ease, box-shadow .2s ease;
            font-weight: 600;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .form-control,
        .select2-selection,
        .form-select {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            box-shadow: none;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 14px 16px;
        }

        .badge {
            border-radius: 999px;
            padding: 0.45em 0.7em;
        }

        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.16);
        }

        .card {
            border: none;
            border-radius: 16px;
        }
    </style>
</head>
<body class="app sidebar-mini rtl">
<!-- Navbar-->
<!-- Sidebar menu-->

@yield('content')

<div id="autoLogoutModal"
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:20px; border-radius:8px; max-width:400px; text-align:center;">
        <h4>Session Expiring Soon</h4>
        <p>You’ve been inactive. You will be logged out in
            <strong><span id="logoutCountdown">60</span></strong> seconds.
        </p>
        <button onclick="stayLoggedIn()"
                style="padding:10px 20px; background:#3490dc; border:none; color:white; border-radius:5px;">
            Stay Logged In
        </button>
    </div>
</div>
<script>

    let idleTime = 0;
    let warningShown = false;

    const sessionLifetime = {{ config('session.lifetime') }} * 60 * 1000;
    const warningTime = sessionLifetime - 60000;

    console.log('Session lifetime (minutes):', {{ config('session.lifetime') }});

    let countdownInterval;

    function resetTimer() {
        idleTime = 0;
        warningShown = false;

        hideModal();
    }

    function hideModal() {
        const modal = document.getElementById('autoLogoutModal');
        modal.style.display = 'none';
        clearInterval(countdownInterval);
    }

    function showWarning() {
        if (warningShown) return;

        warningShown = true;
        const modal = document.getElementById('autoLogoutModal');
        modal.style.display = 'flex';

        countdownInterval = setInterval(() => {
            const remaining = Math.ceil((sessionLifetime - idleTime) / 1000);
            document.getElementById('logoutCountdown').innerText = remaining;

            if (remaining <= 0) {
                autoLogout();
            }
        }, 1000);
    }

    function stayLoggedIn() {
        fetch("{{ route('keep-alive') }}", {
            method: 'GET',
            credentials: 'same-origin'
        }).finally(resetTimer);
    }

    function autoLogout() {
        window.location.href = "{{ route('login') }}";
    }
    
    // Activity listeners
    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(evt =>
        document.addEventListener(evt, resetTimer)
    );

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) resetTimer();
    });

    setInterval(() => {
        idleTime += 1000;

        if (idleTime >= warningTime && idleTime < sessionLifetime) {
            showWarning();
        }

        if (idleTime >= sessionLifetime) {
            autoLogout();
        }
    }, 1000);
</script>
<!-- Essential javascripts for application to work-->
<script src="{{asset('/')}}js/jquery-3.2.1.min.js"></script>
<script src="{{asset('/')}}js/popper.min.js"></script>
<script src="{{asset('/')}}js/bootstrap.min.js"></script>
<script src="{{asset('/')}}js/main.js"></script>
<!-- The javascript plugin to display page loading on top-->
<script src="{{asset('/')}}js/plugins/pace.min.js"></script>
<!-- Page specific javascripts-->
<script type="text/javascript" src="{{asset('/')}}js/plugins/chart.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
<!-- Auto Logout Warning Modal -->
@stack('js')
@include('partials.footer')
</body>
</html>