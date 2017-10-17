<div id="formEditLayout">
    <div data-container="FormPanel">
        <div id="flist" style="width: 100%; height: 100%">
            <div id="formTables" class="no-border"></div>
        </div>
    </div>
    <div data-container="FormControlPanel" id="fcp">
        <div id="formControlToolbar" style="padding: 4px;">
            <button type="button" style="float: left" class="btn btn-primary" id="checkform">Контроль формы</button>
            <div style="padding: 4px; display: none" id="fc_extrabuttons">
                <i style='margin-left: 2px;height: 14px; float: left' id="toggle_formcontrolscreen" title='Обновить/пересоздать протокол контроля'>
                    <span class='glyphicon glyphicon-fullscreen'></span>
                </i>
                {{--<i id="toggle_formcontrolscreen" style="margin-left: 2px;" target="_blank"><span class='glyphicon glyphicon-fullscreen'></span></i>--}}
                <i id='printformprotocol' style="margin-left: 2px; height: 14px; float: left" ><span class='glyphicon glyphicon-print'></span></i>
            </div>
        </div>
        <div style="clear: both"></div>
        <div style="display: none" class="inactual-protocol"><span class='text-danger'>Протокол неактуален (в форме произведены изменения после его формирования)</span></div>
        <div style="display: none" id="formprotocolloader"><h5>Выполнение проверки и загрузка протокола контроля <img src='/jqwidgets/styles/images/loader-small.gif' /></h5></div>
        <div id="formprotocol"></div>
    </div>
    <div data-container="ValueChangeLogPanel">
        <div id="log" style="font-size: 0.9em">Изменений не было</div>
    </div>
    <div data-container="FullValueChangeLogPanel">
        <button type="button" style="float: left" class="btn btn-primary" id="openFullChangeLog">Открыть протокол изменений в новом окне</button>
        {{--<input id="openFullChangeLog" type="button" value="Открыть протокол изменений в новом окне" />--}}
    </div>
    <div data-container="TableEditPanel">
        <div id="DataGrid"></div>
    </div>
    <div data-container="TableControlPanel" id="TableControlPanel">
        <div style="padding: 4px" id="ProtocolToolbar">
           {{-- <input style="float: left" id="checktable" type="button" value="Контроль таблицы (МИ)" />--}}
            <button type="button" style="float: left" class="btn btn-primary" id="datacheck">Контроль таблицы</button>
           {{-- <input style="float: left" id="datacheck" type="button" value="Контроль таблицы" />--}}
            {{--<input style="float: left" id="compareprevperiod" type="button" value="Сравнить с предыдущим периодом" />--}}

            <div style="padding: 4px" id="extrabuttons">
                <div id="showallrule" class="extrabutton" style="float: left"><span>Показать только ошибки</span></div>
                <a id="togglecontrolscreen" style="margin-left: 2px;" target="_blank"><span class='glyphicon glyphicon-fullscreen'></span></a>
                <a id='printtableprotocol' style="margin-left: 2px;" target="_blank" ><span class='glyphicon glyphicon-print'></span></a>
                {{--<a id='expandprotocolrow' style="margin-left: 2px;" target="_blank" title="Развернуть"><span class='glyphicon glyphicon-folder-close'></span></a>--}}
            </div>
        </div>
        <div style="clear: both"></div>
        <div style="display: none" id="protocolloader"><h5>Выполнение проверки и загрузка протокола контроля <img src='/jqwidgets/styles/images/loader-small.gif' /></h5></div>
        <div style="display: none" class="inactual-protocol"><span class='text-danger'>Протокол неактуален (в таблице произведены изменения после его формирования)</span></div>
        <div style="width: 100%; height: 85%" id="tableprotocol"></div>
    </div>
    <div data-container="CellControlPanel">
        <div id="cellprotocol"></div>
    </div>
    @yield('additionalPanel')
</div>