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
            {{--<li class="dropdown pull-right">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" title="Сообщения">
                    <i class="fa fa-comments fa-lg text-info"></i> <span class="badge" style="background-color: #ca0909">5</span> <span class="caret"></span>
                </a>
                <div class="dropdown-menu panel-group" id="messageFeed" style="width: 500px; height: 400px; padding-top: 0; overflow-x: hidden; overflow-y:auto ;">
                    <div class="panel panel-default" style="height: 30px">
                        <div class="panel-heading" style="padding: 5px">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text"><strong>Новые</strong></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text"><a>Пометить все как прочтенные</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-danger">
                        <div class="panel-heading" style="padding: 5px">
                            <div class="row">
                                <div class="col-md-2">
                                    <p><i class="fa fa-comment-o fa-lg text-info"></i></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text">Автор сообщения</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text"> Тип события </p>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" style="padding: 5px">
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="text">Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-2">
                                    <p><i class="fa fa-comment-o fa-lg text-info"></i></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text">Автор сообщения</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text"> Тип события </p>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="text">Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-2">
                                    <p><i class="fa fa-comment-o fa-lg text-info"></i></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text">Автор сообщения</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text"> Тип события </p>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="text">Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-2">
                                    <p><i class="fa fa-comment-o fa-lg text-info"></i></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text">Автор сообщения</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text"> Тип события </p>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="text">Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст Текст </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>--}}
        </ul>
    </div>
</nav>