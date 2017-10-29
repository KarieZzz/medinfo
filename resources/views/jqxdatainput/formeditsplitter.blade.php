<div id="formEditLayout">
    <div>
        <div class="row">
            <div class="col-lg-12" style="margin-left: 10px">
                <div id="TableList" class="btn btn-default btn-xs"><div id="FormTables"></div></div>
                <input placeholder="поиск строки" type="text" id="SearchField">
                <button class="btn btn-default btn-xs" id="ClearFilter">Очистить</button>
                <button class="btn btn-default btn-xs" id='calculate' title='Рассчитать'><span class='fa fa-calculator'></span></button>
                <button class="btn btn-default btn-xs" id='togglefullscreen' title='Полноэкранный режим'><span class='glyphicon glyphicon-fullscreen'></span></button>
            </div>
        </div>
        <div class="row" >
            <div class="col-lg-12" style="margin-left: 10px"><h4 id="TableTitle"></h4></div>
        </div>
        <div id="DataGrid"></div>


    </div>
    <div>
        <div class="jqx-hideborder jqx-hidescrollbars" id="controltabs">
            <ul>
                <li style="margin-left: 30px;">Контроль таблицы</li>
                <li>Контроль формы</li>
            </ul>
            <div>
                <div id="TableControlPanel">
                    <div style="padding: 4px" id="ProtocolToolbar">
                        <div style="padding: 4px" id="extrabuttons">
                            <div id="showallrule" class="extrabutton" style="float: left"><span>Показать только ошибки</span></div>
                            <a id="togglecontrolscreen" style="margin-left: 2px;" target="_blank"><span class='glyphicon glyphicon-fullscreen'></span></a>
                            <a id='printtableprotocol' style="margin-left: 2px;" target="_blank" ><span class='glyphicon glyphicon-print'></span></a>
                        </div>
                    </div>
                    <div style="clear: both"></div>
                    <div style="display: none" id="protocolloader"><h5>Выполнение проверки и загрузка протокола контроля <img src='/jqwidgets/styles/images/loader-small.gif' /></h5></div>
                    <div style="display: none" class="inactual-protocol"><span class='text-danger'>Протокол неактуален (в таблице произведены изменения после его формирования)</span></div>
                    <div style="width: 100%; height: 85%" id="tableprotocol"></div>
                </div>
            </div>
            <div>
                Content 2
            </div>
        </div>
    </div>
</div>