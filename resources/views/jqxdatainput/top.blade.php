<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">@yield('title')</a>
        </div>
        <ul class="nav navbar-nav pull-right">
            <li class="dropdown pull-right">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-user fa-lg text-info"></i> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li><a><strong class="text-info">{{ Auth::guard('datainput')->user()->description }}</strong></a></li>
                    {{--<li><a href="#" id="openProfileEditor"><span class="fa fa-user"></span> Профиль</a></li>--}}
                    {{--<li><a href="#"><span class="fa fa-cog"></span> Настройки</a></li>--}}
                    <li><a href="/workerlogout"><span class="glyphicon glyphicon-log-out"></span> Выход</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>