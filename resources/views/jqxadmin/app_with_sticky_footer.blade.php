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
            /* Negative indent footer by its height */
            margin: 0 auto -60px;
            /* Pad bottom by footer height */
            padding: 0 0 60px;
        }
        #widgets-content-wrap {
            position: absolute;
            width:100%;
            height: 100%;
            margin: 0 auto -173px;
            /* Pad bottom by footer height */
            padding: 0 0 173px;
        }

        /* Set the fixed height of the footer here */
        #footer {
            height: 60px;
            background-color: #f5f5f5;
        }

        /* Custom page CSS
        -------------------------------------------------- */
        #footer > .container {
            padding-left: 15px;
            padding-right: 15px;
        }
        #navBar
        {
            background: transparent !important;
            border: none;
            box-shadow: none;
            -webkit-box-shadow: none;
        }
        .navbar
        {
            min-height: 20px !important;
        }
        code {
            font-size: 80%;
        }
    </style>
    <title id="Description">Bootstrap и jQWidgets дизайн для админ-страницы</title>

    <!-- Bootstrap core CSS -->
    <link href="{{ asset('/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- jQWidgets CSS -->
    <link href="{{ asset('/jqwidgets/styles/jqx.base.css') }}" rel="stylesheet">
    <link href="{{ asset('/jqwidgets/styles/jqx.bootstrap.css') }}" rel="stylesheet">
</head>

<body>

<!-- Wrap all page content here -->
<div id="wrap">

    @include('jqxadmin.navbar')
    <!-- Begin page content -->
    <div class="container-fluid">
        <div class="page-header">
            @yield('title')
        </div>
        <div id="alertmessage"></div>
    </div>
    <div id="widgets-content-wrap" style="visibility: hidden">
        @yield('content')
    </div>
</div>

<div id="footer">
    <div class="container-fluid">
        <p class="text-muted">Содержимое футера здесь.</p>
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
