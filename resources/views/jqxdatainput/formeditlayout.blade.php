<div id="formEditLayout">
    <div data-container="FormPanel">
        <div id="flist" style="width: 100%; height: 100%">
            <div id="formTables" class="no-border"></div>
        </div>
    </div>
    <div data-container="FormControlPanel" id="fcp">
        <div id="formControlToolbar" style="padding: 4px;">
            {{--<input id="dataexport" type="button" value="Экспорт данных" />--}}
            <input id="checkform" style="float: left" type="button" value="Контроль формы" />
            <div style="padding: 4px; display: none" id="fc_extrabuttons">
                {{--<div id="showallfcrule" class="extrabutton" style="float: left"><span>Показать только ошибки</span></div>--}}
                <a id="toggle_formcontrolscreen" style="margin-left: 2px;" target="_blank"><span class='glyphicon glyphicon-fullscreen'></span></a>
                <a id='printformprotocol' style="margin-left: 2px;" target="_blank" ><span class='glyphicon glyphicon-print'></span></a>
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
        <input id="openFullChangeLog" type="button" value="Открыть протокол изменений в новом окне" />
    </div>
    <div data-container="TableEditPanel">
        <div id="DataGrid"></div>
    </div>
    <div data-container="TableControlPanel" id="TableControlPanel">
        <div style="padding: 4px" id="ProtocolToolbar">
           {{-- <input style="float: left" id="checktable" type="button" value="Контроль таблицы (МИ)" />--}}
            <input style="float: left" id="datacheck" type="button" value="Контроль таблицы" />
            {{--<input style="float: left" id="compareprevperiod" type="button" value="Сравнить с предыдущим периодом" />--}}

            <div style="padding: 4px" id="extrabuttons">
                <div id="showallrule" class="extrabutton" style="float: left"><span>Показать только ошибки</span></div>
                <a id="togglecontrolscreen" style="margin-left: 2px;" target="_blank"><span class='glyphicon glyphicon-fullscreen'></span></a>
                <a id='printtableprotocol' style="margin-left: 2px;" target="_blank" ><span class='glyphicon glyphicon-print'></span></a>
                <a id='expandprotocolrow' style="margin-left: 2px;" target="_blank" title="Развернуть"><span class='glyphicon glyphicon-folder-close'></span></a>
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