<!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <div class="col-md-9">
                <div class="navbar-header">
                    @yield('title')
                </div>
            </div>
            <div class="col-md-2">
                <div class="navbar-header">
                    <h5><i class="fa fa-user fa-lg"></i> {{ Auth::guard('datainput')->user()->description }}</h5>
                </div>
            </div>
            <div class="col-md-1"><h5><a href="/workerlogout" class="pull-right"> <span class="glyphicon glyphicon-log-out"></span> Выйти</a></h5></div>

            {{--<ul class="nav navbar-nav pull-right">

            </ul>--}}
        </div>
    </nav>