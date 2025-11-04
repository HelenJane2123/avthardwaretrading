
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
</head>
<body class="app sidebar-mini rtl">
<!-- Navbar-->
<!-- Sidebar menu-->

@yield('content')


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


@stack('js')
@include('partials.help-modal')
</body>
</html>