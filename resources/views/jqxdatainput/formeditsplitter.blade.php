<div id="formEditLayout">
    <div>
        <nav class="navbar navbar-default" style="margin-bottom: 0px">
            <div class="container-fluid">
                <div id="TableList" class="btn btn-default"><div id="FormTables"></div></div>
                <button class="btn btn-default navbar-btn" id="Сalculate" title="Рассчитать"> <span class='fa fa-calculator'></span></button>
                <button class="btn btn-default navbar-btn" id="ToggleFullscreen" title="Полноэкранный режим"> <span class='glyphicon glyphicon-fullscreen'></span></button>
                <button class="btn btn-default navbar-btn" id="TableCheck" title="Контроль таблицы внутриформенный">Контроль таблицы (ВФ)</button>
                <button class="btn btn-default navbar-btn" id="IPTableCheck" title="Контроль таблицы межпериодный">Контроль таблицы (МП)</button>
                <form class="navbar-form navbar-right">
                <div class="input-group">
                    <input type="text" class="form-control" id="SearchField" placeholder="Поиск строки">
                    <div class="input-group-btn">
                        <button class="btn btn-default" id="ClearFilter" type="button">
                            <i class="glyphicon glyphicon-remove"></i>
                        </button>
                    </div>
                </div>
                </form>
            </div>
        </nav>

        <div class="row" >
            <div class="col-lg-12" style="margin-left: 10px"><h4 id="TableTitle"></h4></div>
        </div>
        <div id="DataGrid"></div>


    </div>
    <div>
        <div class="jqx-hideborder jqx-hidescrollbars" id="controltabs" style="padding-top: 10px">
            <ul>
                <li style="margin-left: 30px;">Контроль таблицы</li>
                <li>Контроль формы</li>
            </ul>
            <div>
                <div id="TableControlPanel">
                    <div style="padding: 4px" id="ProtocolToolbar">
                        <div style="padding: 4px" id="extrabuttons">
                            <div id="showallrule" class="extrabutton" style="float: left"><span>Показать только ошибки</span></div>
                            <a id="togglecontrolscreen" style="margin-left: 2px;" target="_blank" title="Рассширить"><span class='glyphicon glyphicon-resize-full'></span></a>
                            <a id='printtableprotocol' style="margin-left: 2px;" target="_blank" title="Распечатать протокол"><span class='glyphicon glyphicon-print'></span></a>
                        </div>
                    </div>
                    <div style="clear: both"></div>
                    <div style="display: none" id="protocolloader"><h5>Выполнение проверки и загрузка протокола контроля <img src='/jqwidgets/styles/images/loader-small.gif' /></h5></div>
                    <div style="display: none" class="inactual-protocol"><span class='text-danger'>Протокол неактуален (в таблице произведены изменения после его формирования)</span></div>
                    <div style="width: 100%; height: 950px" id="tableprotocol"></div>
                </div>
            </div>
            <div>
                Content 2
            </div>
        </div>
    </div>
</div>