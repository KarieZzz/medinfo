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
            height: calc(100vh - 80px);
        }
    </style>
    <title id="Description">@yield('title')</title>
    <!-- Bootstrap core CSS -->
    <link href="{{ asset('/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- jQWidgets CSS -->
    <link href="{{ asset('/jqwidgets/styles/jqx.base.css?v=003') }}" rel="stylesheet">
    <link href="{{ asset('/jqwidgets/styles/jqx.bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
</head>

<body>
{{--<div id="alertmessage" class="col-md-4 "></div>--}}

<div class="container-fluid" >
    @include('reports.navbar')
    <div id="content">
        @yield('content')
    </div>
    <div id="popups" style="display: none"></div>
    @include('jqxdatainput.notifications')
    @include('jqxdatainput.confirmpopup')
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="{{ asset('/jqwidgets/jqx-all.js?v=001') }}"></script>
<script src="{{ asset('/jqwidgets/localization.js') }}"></script>
<script src="{{ asset('/medinfo/admin/admin.js') }}"></script>
<script src="{{ asset('/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
@stack('loadjsscripts')
<script type="text/javascript">
    let theme = 'bootstrap';
    let confirm_action = false;
    let confirmpopup = $('#confirmPopup');
    initnotifications();
    initConfirmWindow();
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("#content").show();
        $("#popups").show();
    });

</script>
@yield('inlinejs')
</body>
</html>
