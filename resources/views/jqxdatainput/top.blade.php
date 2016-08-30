<!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <div class="col-md-6">
                <div class="navbar-header">
                    @yield('title')
                </div>
            </div>
            <div class="col-md-4">
                <div class="navbar-header">
                    <h5><i class="fa fa-user fa-lg"></i> {{ Auth::guard('datainput')->user()->description }}</h5>
                </div>
            </div>
            <ul class="nav navbar-nav pull-right">
                <li><a href="/workerlogout"> <span class="glyphicon glyphicon-log-out"></span> Завершить работу</a></li>
            </ul>
        </div>
    </nav>

