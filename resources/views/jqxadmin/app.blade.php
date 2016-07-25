<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
            /* The html and body elements cannot have any padding or margin. */
        }

        /* Wrapper for page content to push down footer */
        #wrap {
            min-height: 100%;
            height: auto;

        }
        #widgets-content-wrap {
            position: absolute;
            width:100%;
            height: 100%;
            /* Pad bottom by footer height */
            padding: 0 0 120px;
        }
        #alertmessage {
            position: absolute;
            display: table;
            left: 50%;
            width:600px;
            height: 30px;
            z-index: 10000;
        }
    </style>
    <title id="Description">Админ-страница (Bootstrap и jQWidgets дизайн)</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ asset('/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- jQWidgets CSS -->
    <link href="{{ asset('/jqwidgets/styles/jqx.base.css') }}" rel="stylesheet">
    <link href="{{ asset('/jqwidgets/styles/jqx.bootstrap.css') }}" rel="stylesheet">
</head>

<body>

<!-- Wrap all page content here -->
<div id="alertmessage" class="col-md-4 "></div>
<div id="wrap">
    @include('jqxadmin.navbar')
    <!-- Begin page content -->
    <div class="container-fluid" style="margin-top: 20px">
        <div class="page-header">
            @yield('title')
        </div>
    </div>
    <div id="widgets-content-wrap" style="visibility: hidden">
        @yield('content')
    </div>
</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="{{ asset('/plugins/jquery/jquery-1.12.4.min.js') }}" type="text/javascript" ></script>
<script src="{{ asset('/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
<!-- jQWidgets JavaScript files -->
<script src="{{ asset('/jqwidgets/jqxcore.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxmenu.js') }}"></script>
@stack('loadjsscripts')
<script type="text/javascript">
    var theme = 'bootstrap';
    $(document).ready(function () {
        $("#navBar").jqxMenu({ autoSizeMainItems: true, theme: theme, showTopLevelArrows: true, width: '100%' });
        $("#navBar").css("visibility", "visible");
        $("#widgets-content-wrap").css("visibility", "visible");
    });
</script>

@yield('inlinejs')

</body>
</html>
