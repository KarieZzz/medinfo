<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title id="Description">Справка </title>
    <!-- Bootstrap core CSS -->
    <link href="{{ asset('/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- jQWidgets CSS -->
    <link href="{{ asset('/jqwidgets/styles/jqx.base.css') }}" rel="stylesheet">
    <link href="{{ asset('/jqwidgets/styles/jqx.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
</head>

<body>
<div id="wrap">
    <div>
        @yield('title')
    </div>
    <div>
        @yield('content')
    </div>
</div>

<script src="{{ asset('/plugins/jQuery/jquery-1.12.4.min.js') }}" type="text/javascript" ></script>
<script src="{{ asset('/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
<!-- jQWidgets JavaScript files -->
<script src="{{ asset('/jqwidgets/jqxcore.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxmenu.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxnotification.js') }}"></script>
<script src="{{ asset('/medinfo/admin/admin.js') }}"></script>
@stack('loadjsscripts')
<script type="text/javascript">
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
</script>
@yield('inlinejs')
</body>
</html>
