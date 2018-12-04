<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#">@yield('title')</a>
        </div>
        <ul class="nav navbar-nav pull-right">
            @yield('rp-open')
            <li class="dropdown pull-right">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" title="Пользователь: {{ Auth::guard('datainput')->user()->description }} ">
                    <i class="fa fa-user fa-lg text-info"></i> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a><strong class="text-info">{{ Auth::guard('datainput')->user()->description }}</strong></a></li>
                    <li><a href="#" id="openProfileEditor"><span class="fa fa-user"></span> Профиль</a></li>
                    {{--<li><a href="#"><span class="fa fa-cog"></span> Настройки</a></li>--}}
                    <li><a href="/workerlogout"><span class="glyphicon glyphicon-log-out"></span> Выход</a></li>
                </ul>
            </li>
            @if($worker->role !==1 )
            <li class="dropdown pull-right" id="messageFeedToggle">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" title="Сообщения">
                    <i class="fa fa-comments fa-lg text-info"></i> <span class="badge" style="background-color: #ca0909" id="newMessagesBadge"></span> <span class="caret"></span>
                </a>
                <div class="dropdown-menu" style="width: 500px; height: 400px; padding-top: 0; overflow-x: hidden; overflow-y:auto ;">
                    <div class="row" style="margin: 0" >
                        <div class="col-md-offset-1 col-md-4">
                            <h6 class="text">Сообщения</h6>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info"><a href="#" id="markAllAsRead">Пометить все как прочтенные</a></h6>
                        </div>
                        <div class="col-md-1">
                            <h6 class="text-info"><a href="#" id="refreshMessageFeed"><i class="fa fa-refresh text-info"></i></a></h6>
                        </div>
                    </div>
                    <div>
                        <div style="display: none; margin-left: 10px" id="formloader">
                            <h5 class="text small">Загрузка данных <img src='/jqwidgets/styles/images/loader-small.gif' /></h5>
                        </div>
                    </div>
                    <div id="messageFeed"></div>
                </div>
            </li>
            @endif
        </ul>
    </div>
</nav>