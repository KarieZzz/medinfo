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
            padding: 40px 0 0 0;
        }
    </style>
    <title id="Description">@yield('headertitle')</title>
    <!-- Bootstrap core CSS -->
    <link href="{{ asset('/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"> -->
    <!-- jQWidgets CSS -->
    <link href="{{ asset('/jqwidgets/styles/jqx.base.css?v=001') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/jqwidgets/styles/jqx.bootstrap.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
    @stack('loadcss')
</head>

<body>

<!-- Wrap all page content here -->

    @include('jqxdatainput.top')
    <!-- Begin page content -->
    <div class="container-fluid" style="height: 100%; overflow: hidden">
        @yield('content')
    </div>
    @include('jqxdatainput.notifications')

<!-- Bootstrap core JavaScript -->
{{--<script src="{{ asset('/plugins/jQuery/jquery-1.12.4.min.js') }}" type="text/javascript" ></script>--}}
{{--<script src="{{ asset('/plugins/jQuery/jquery-1.11.1.min.js') }}" type="text/javascript" ></script>--}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="{{ asset('/jqwidgets/jqxcore.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxnotification.js') }}"></script>
<script src="{{ asset('/medinfo/dashboard.js?v=011') }}"></script>
<script src="{{ asset('/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
@stack('loadjsscripts')
<script type="text/javascript">
    let theme = 'bootstrap';
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            statusCode: {
                401: function(err){
                    console.log('Login Failed.', err.responseJSON);

                }
            }
        });
        //$("#widgets-content-wrap").css("visibility", "visible");
    });
</script>
@yield('inlinejs')
</body>
</html>
