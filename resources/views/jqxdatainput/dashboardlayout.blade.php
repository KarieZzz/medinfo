<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        html,
        body {
            height: 100%;
        }
        #content {
            height: calc(100vh - 60px);
            margin-top: -20px;
            margin-right: 5px;
        }
    </style>
    <title id="Description">@yield('headertitle')</title>
    <!-- Bootstrap core CSS -->
    <link href="{{ asset('/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"> -->
    <!-- jQWidgets CSS -->
    <link href="{{ asset('/jqwidgets/styles/jqx.base.css?v=003') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/jqwidgets/styles/jqx.bootstrap.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
    @stack('loadcss')
</head>

<body>
    <div class="container-fluid">
        @include('jqxdatainput.top')
        <div id="content" style="display: none">
            @yield('content')
        </div>
        <div id="popups" style="display: none">
            @include('jqxdatainput.notifications')
            @include('jqxdatainput.dashboardwindows')
        </div>
    </div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="{{ asset('/jqwidgets/jqx-all.js?v=001') }}"></script>
<script src="{{ asset('/medinfo/dashboard.js?v=013') }}"></script>
<script src="{{ asset('/jqwidgets/localization.js?v=001') }}"></script>
<script src="{{ asset('/plugins/fullscreen/jquery.fullscreen.js?v=001') }}"></script>
<script src="{{ asset('/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
@stack('loadjsscripts')
<script type="text/javascript">
    let theme = 'bootstrap';
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("#content").show();
    });
</script>
@yield('inlinejs')
</body>
</html>
