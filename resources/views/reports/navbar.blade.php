<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid" style="z-index: 1">
        <div class="navbar-header">
            <div class="navbar-brand">Аналитика</div>
        </div>
        <ul class="nav navbar-nav">
            <li><a href="/analytics">Справка</a></li>
            <li><a href="/analytics/reports">Отчеты</a></li>
            {{--<li disabled="disabled"><a href="/analytics/selectedcontrol" >Выборочный контроль</a></li>--}}
        </ul>
        <div class="navbar-header">
            <div class="navbar-brand"><span class="text-primary">@yield('title')</span></div>
        </div>
        <ul class="nav navbar-nav navbar-right">
            <li><a href="/analytics/logout"><span class="glyphicon glyphicon-log-in"></span> Завершить работу</a></li>
        </ul>
    </div>
</nav>
