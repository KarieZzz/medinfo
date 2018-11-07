<div id="formEditLayout">
    <div>
        <nav class="navbar navbar-default" style="margin-bottom: 0; margin-top: 48px">
            <div class="container-fluid">
                <div class="btn-group">
                    <div id="TableList" class="btn btn-default" style="margin-top: 8px"><div id="FormTables"></div></div>
                    <button class="btn btn-default navbar-btn" id="Previous" title="Предыдущая таблица"> <span class='fa fa-arrow-left'></span></button>
                    <button class="btn btn-default navbar-btn" id="Following" title="Следующая таблица"> <span class='fa fa-arrow-right'></span></button>
                </div>
                <button class="btn btn-default navbar-btn" id="Сalculate" title="Рассчитать"> <span class='fa fa-calculator'></span></button>
                <button class="btn btn-default navbar-btn" id="ToggleFullscreen" title="Полноэкранный режим"> <span class='glyphicon glyphicon-fullscreen'></span></button>
                <div class="btn-group">
                    <button class="btn btn-default navbar-btn" id="TableCheck" title="Контроль таблицы внутриформенный"><i>К</i><small>вф</small></button>
                    <button class="btn btn-default navbar-btn" id="IDTableCheck" title="Контроль таблицы межформенный"><i>К</i><small>мф</small></button>
                    <button class="btn btn-default navbar-btn" id="IPTableCheck" title="Контроль таблицы межпериодный"><i>К</i><small>мп</small></button>
                </div>
                <button class="btn btn-default navbar-btn" id="FormCheck" title="Контроль формы"><i>К</i><small>формы</small></button>
                <button class="btn btn-default navbar-btn" id="tableExcelExport" title="Экспорт данных таблицы в MS Excel">
                    <span class='fa fa-download fa-lg' ></span>
                    <span class='fa fa-file-excel-o fa-lg' ></span>
                </button>
                <button class="btn btn-default navbar-btn" id="tableExcelImport" title="Импорт данных таблицы из MS Excel" style="display: none">
                    <span class='fa fa-upload fa-lg' ></span>
                    <span class='fa fa-file-excel-o fa-lg' ></span>

                </button>
                <div class="btn-group" @if (count($formsections) === 0) style="display: none" @endif>
                    <div id="SectionsManager" class="btn btn-default">
                        <div id="FormSections" style="display: none">
                            <table class="table table-hover">
                                @foreach($formsections as $formsection)
                                    <tr @if(isset($formsection->section_blocks[0]))
                                            title="Раздел {{ $formsection->section_blocks[0]->blocked ? 'принят' : 'отклонен' }} {{ $formsection->section_blocks[0]->updated_at }} пользователем {{ $formsection->section_blocks[0]->worker->description }}"
                                            class=" {{ $formsection->section_blocks[0]->blocked === true ? 'success' : 'danger' }} "
                                        @else
                                            title="Статус раздела не менялся"
                                        @endif
                                            id="{{ $formsection->id }}"
                                    >
                                        <td>{{ $formsection->section_name }}</td>
                                        @if(isset($formsection->section_blocks[0]))
                                            <td>
                                                <button title="Принять" class="btn btn-default blocksection" id="{{ $formsection->id }}" {{ $formsection->section_blocks[0]->blocked ? 'disabled' : '' }}>
                                                    <span class='glyphicon glyphicon-check'></span>
                                                </button>
                                            </td>
                                            <td>
                                                <button title="Отклонить" class="btn btn-default unblocksection" id="{{ $formsection->id }}" {{ $formsection->section_blocks[0]->blocked ? '' : 'disabled' }} >
                                                    <span class='glyphicon glyphicon-remove'></span>
                                                </button>
                                            </td>
                                        @else
                                            <td>
                                                <button title="Принять" class="btn btn-default blocksection" id="{{ $formsection->id }}">
                                                    <span class='glyphicon glyphicon-check'></span>
                                                </button>
                                            </td>
                                            <td>
                                                <button title="Отклонить" class="btn btn-default unblocksection" id="{{ $formsection->id }}" disabled >
                                                    <span class='glyphicon glyphicon-remove'></span>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
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
            <div class="col-lg-12" style="margin-left: 5px"><h4 id="TableTitle"></h4></div>
        </div>
        <div id="DataGrid"></div>
    </div>
    <div>
        <div class="jqx-hideborder jqx-hidescrollbars" id="ControlTabs" style="margin-top: 48px">
            <ul>
                <li style="margin-left: 30px;">Контроль таблицы</li>
                <li>Контроль формы</li>
                <li>Контроль ячейки</li>
                @yield('additionalTabLi')
            </ul>
            <div>
                <div id="TableControlPanel">
                    <div style="padding: 4px; margin: 4px" id="ProtocolToolbar">
                        <div id="extrabuttons">
                            <div id="showallrule" class="extrabutton" style="float: left"><span>Показать только ошибки</span></div>
                            <a id="togglecontrolscreen" style="margin-left: 2px;" target="_blank" title="Рассширить"><span class='glyphicon glyphicon-resize-full'></span></a>
                            <a id='printtableprotocol' style="margin-left: 2px;" target="_blank" title="Распечатать протокол"><span class='glyphicon glyphicon-print'></span></a>
                        </div>
                    </div>
                    <div style="clear: both"></div>
                    <div style="display: none; margin-left: 10px" id="protocolloader"><h5>Выполнение проверки и загрузка протокола контроля <img src="/jqwidgets/styles/images/loader-small.gif" /></h5></div>
                    <div style="display: none" class="inactual-protocol"><span class='text-danger'>Протокол неактуален (в таблице произведены изменения после его формирования)</span></div>
                    <div style="width: 100%; overflow-y: auto; margin-left: 5px" id="tableprotocol"></div>
                </div>
            </div>
            <div>
                <div id="formControlToolbar" style="padding: 4px; margin: 4px">
                    <div id="fc_extrabuttons">
                        <a id='printformprotocol' style="margin-left: 2px;" target="_blank" title="Распечатать протокол"><span class='glyphicon glyphicon-print'></span></a>
                    </div>
                </div>
                <div style="clear: both"></div>
                <div style="display: none" class="inactual-protocol"><span class='text-danger'>Протокол неактуален (в форме произведены изменения после его формирования)</span></div>
                <div style="display: none; margin-left: 10px" id="formprotocolloader"><h5>Выполнение проверки и загрузка протокола контроля <img src='/jqwidgets/styles/images/loader-small.gif' /></h5></div>
                <div style="width: 100%; overflow-y: auto" id="formprotocol"></div>
            </div>
            <div>
                <div style="width: 100%; overflow-y: auto; margin-left: 5px" id="cellprotocol"></div>
            </div>
            @yield('additionalTabDiv')
        </div>
    </div>
</div>