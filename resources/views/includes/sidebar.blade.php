<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel - панель пользователя в баковой панели не нужна наверное
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ asset('dist/img/user2-160x160.jpg') }}" class="img-circle" alt="User Image" />
            </div>
            <div class="pull-left info">
                <p>Alexander Pierce</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div> -->
        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Поиск..."/>
                <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </form>
        <!-- /.search form -->
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu">
            <li class="header">АДМНИСТРИРОВАНИЕ</li>
            <li class="active">
                <a href="/users">
                    <i class="fa fa-users"></i> <span>Пользователи</span>
                </a>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-book"></i> <span>Системные журналы</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="active"><a href="/logs/accesslog"><i class="fa fa-circle-o"></i>Журнал доступа</a></li>
                </ul>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-edit"></i> <span>Структура</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="active"><a href="/structure/forms"><i class="fa fa-circle-o"></i>Формы</a></li>
                    <li><a href="/structure/newform"><i class="fa fa-circle-o"></i>Новая форма</a></li>
                    <li><a href="/structure/rows"><i class="fa fa-circle-o"></i>Словарь строк</a></li>
                </ul>
            </li>


            <li class="header">ВВОД ДАННЫХ</li>
            <li><a href="#"><i class="fa fa-circle-o text-danger"></i> Important</a></li>
            <li><a href="#"><i class="fa fa-circle-o text-warning"></i> Warning</a></li>
            <li><a href="#"><i class="fa fa-circle-o text-info"></i> Information</a></li>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>