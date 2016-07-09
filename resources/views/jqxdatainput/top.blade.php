<!-- Fixed navbar -->
<style>
    #navBar
    {
        background: transparent !important;
        border: none;
        box-shadow: none;
        -webkit-box-shadow: none;
    }
    .navbar
    {
        min-height: 35px !important;
    }
</style>
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="col-md-4">
            @yield('title')
        </div>
        <div class="col-md-4">
            Пользователь: {{ Auth::guard('datainput')->user()->description }}
        </div>
        <div class="pull-right"><a href="workerlogout" class="btn btn-default btn-flat">Завершить работу</a></div>
    </div>

