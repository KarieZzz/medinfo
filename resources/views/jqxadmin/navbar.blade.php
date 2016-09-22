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
        <div class="col-md-5">
            <div style="visibility: hidden;" id="navBar" >
                <ul>
                    <li><a href="/admin">Home</a></li>
                    <li>
                        <a href="#">Данные</a>
                        <ul style="width: 250px;">
                            <li><a href="/datainput">Ввод и корректировка</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">Пользователи</a>
                        <ul style="width: 250px;">
                            <li><a href="/admin/workers">Исполнители</a></li>
                            <li><a href="#">Адмнистраторы</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">Структура</a>
                        <ul style="width: 250px;">
                            <li><a href="/admin/periods">Отчетные периоды</a></li>
                            <li><a href="/admin/forms">Формы</a></li>
                            <li><a href="/admin/tables">Таблицы</a></li>
                            <li><a href="/admin/rc">Строки и Графы</a></li>
                            <li type="separator"></li>
                            <li><a href="/admin/noteditablecells">Закрещенные ячейки</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">Организационные единицы</a>
                        <ul style="width: 250px;">
                            <li><a href="/admin/units">Территории/Медицинские организации</a></li>
                            <li><a href="/admin/units/groups">Группы медицинских организаций</a></li>
                        </ul>
                    </li>
                    <li><a href="/admin/documents">Документы</a></li>
                    @yield('local_actions')
                    <li><a href="/admin/logout">Завершение работы</a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </div>

