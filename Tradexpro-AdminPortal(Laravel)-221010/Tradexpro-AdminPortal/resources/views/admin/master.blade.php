<!DOCTYPE HTML>
<html class="no-js" lang="en">
<head>
    @include('admin.include.header_asset')
</head>

<body class="body-bg">
<!-- Start sidebar -->
@include('admin.include.sidebar')
<!-- End sidebar -->
<!-- top bar -->
@include('admin.include.header')
<!-- /top bar -->

<!-- main wrapper -->
<div class="main-wrapper">
    <div class="container-fluid">
        @yield('content')
    </div>
</div>
<!-- /main wrapper -->

<!-- js file start -->

<!-- JavaScript -->
@include('admin.include.footer_asset')

<script>

    (function($) {
        "use strict";
        @if(session()->has('success'))
            window.onload = function () {
            VanillaToasts.create({
                text: '{{session('success')}}',
                backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                type: 'success',
                timeout: 40000
            });
        };
        @elseif(session()->has('dismiss'))
            window.onload = function () {
            VanillaToasts.create({
                text: '{{session('dismiss')}}',
                type: 'warning',
                timeout: 40000
            });
        };
        @elseif($errors->any())
            @foreach($errors->getMessages() as $error)
            window.onload = function () {
            VanillaToasts.create({
                text: '{{ $error[0] }}',
                type: 'warning',
                timeout: 40000
                });
             };
             @break
             @endforeach
        @endif

        /* Add here all your JS customizations */
        $('.number-only').keypress(function (e) {
            alert(11);
            var regex = /^[+0-9+.\b]+$/;
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }
            e.preventDefault();
            return false;
        });
        $('.no-regx').keypress(function (e) {
            var regex = /^[a-zA-Z+0-9+\b]+$/;
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }
            e.preventDefault();
            return false;
        });

    })(jQuery)

</script>
@yield('script')
<!-- End js file -->
</body>
</html>

