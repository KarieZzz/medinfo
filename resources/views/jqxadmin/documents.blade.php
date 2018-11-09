@extends('jqxadmin.app')

@section('title', 'Отчетные документы')
@section('headertitle', 'Менеджер документов')
@section('local_actions')
{{--<li><a href="#" id="newdocument">Новый документ</a></li>--}}

@endsection

@section('content')
<div id="mainSplitter" class="jqx-widget">
    <div>
        <div id="leftPanel">
            <div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="well well-sm">
                            <div id="monitoringSelector">
                                <button type="button" id="moncollapseAll" class="btn btn-default btn-sm">Свернуть все</button>
                                <button type="button" id="monexpandAll" class="btn btn-default btn-sm">Развернуть все</button>
                                <button type="button" id="monfilterApply" class="btn btn-primary btn-sm">Применить фильтр</button>
                                <div id="monTree"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="well well-sm">
                            <div id="moSelectorByTerritories"><div id="moTree"></div></div>
                            <div id="moSelectorByGroups"><div id="groupTree" style="height: 300px"></div></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="well well-sm">
                            <div id="periodSelector">
                                <button class="btn btn-default btn-sm" id="clearAllPeriods">Очистить</button>
                                <button class="btn btn-primary btn-sm" id="applyPeriods">Применить</button>
                                <div id="periodTree"></div>
                            </div>
                            <div id="statusSelector">
                                <button class="btn btn-default btn-sm" id="checkAllStates">Выбрать все</button>
                                <button class="btn btn-default btn-sm" id="clearAllStates">Очистить</button>
                                <button class="btn btn-primary btn-sm" id="applyStates">Применить</button>
                                <div id="statesListbox"></div>
                                <div class="row">
                                    <div class="col-md-offset-1 col-md-11">
                                        <p class="text-info">Только для первичных документов</p>
                                    </div>
                                </div>
                            </div>
                            <div id="dtypeSelector">
                                <button class="btn btn-default btn-sm" id="checkAllTypes">Выбрать все</button>
                                <button class="btn btn-default btn-sm" id="clearAllTypes">Очистить</button>
                                <button class="btn btn-primary btn-sm" id="applyTypes">Применить</button>
                                <div id="dtypesListbox"></div>
                            </div>
                            <div id="dataPresenceSelector">
                                <div id="presence" style="width: 300px">
                                    <button class="btn btn-primary btn-sm" id="applyDataPresence">Применить</button>
                                    <div class="row">
                                        <div class="col-md-12" style="margin-left: 15px">
                                            <div class="radio">
                                                <label><input type="radio" name="optfilled" id="alldoc">Все документы</label>
                                            </div>
                                            <div class="radio">
                                                <label><input type="radio" name="optfilled" id="filleddoc">Данные имеются</label>
                                            </div>
                                            <div class="radio">
                                                <label><input type="radio" name="optfilled" id="emptydoc">Данные отсутствуют</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-offset-1 col-md-11">
                                            <p class="text-info">Только для первичных документов</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-sm-offset-1 col-sm-12">
                            <button class="btn btn-primary" id="clearAllFilters">Очистить фильтры</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div id="rightPanel" style="height: 100%">
            <div class="row">
                <div class="col-sm-4"><h4 style="margin-left: 10px">Документы</h4></div>
                <div style="padding-top: 7px" class="col-sm-6" id="checkedDocumentsToolbar">
                    <div style='float: left; margin-right: 4px;' id='statesDropdownList'></div>
                    <i style='height: 14px' class="fa fa-eraser fa-lg" id='eraseData' title="Очистить данные"></i>
                    <i style='height: 14px' class="fa fa-trash-o fa-lg" id='deleteDocuments' title="Удалить документы"></i>
                    <i style='height: 14px' class="fa fa-product-hunt fa-lg" id='protectAggregates' title="Защитить сводный документ"></i>
                    <i style='height: 14px' class="fa fa-calculator fa-lg" id='Сalculate' title="Расчет (консолидация) данных"></i>
                    <i style='height: 14px' class="fa fa-leaf fa-lg" id='ValueEditingLog' title="Журнал изменения данных"></i>
                    <i style='height: 14px' class="fa fa-clone fa-lg" id='CloneDocuments' title="Клонирование документов в новый отчетный период"></i>
                </div>
            </div>
            <div class="row" id="documentList"></div>
        </div>
    </div>
</div>
<div id="newForm">
    <div id="newFormHeader">
        <span id="headerContainer" style="float: left">Новые документы для отмеченных территорий/учреждений</span>
    </div>
    <div>
        <div style="padding: 15px" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-sm-3" for="selectMonitoring">Мониторинг</label>
                <div class="col-sm-6">
                    <div id="selectMonitoring"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="selectAlbum">Альбом форм</label>
                <div class="col-sm-6">
                    <div id="selectAlbum"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="selectForm">Формы</label>
                <div class="col-sm-6">
                    <div id="selectForm"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="period">Период</label>
                <div class="col-sm-6">
                    <div id="selectPeriod"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="period">Исходный статус</label>
                <div class="col-sm-6">
                    <div id="selectState"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-8">
                    <div class="checkbox">
                        <label><input type="checkbox" id="selectPrimary"> Первичные</label>
                        <label><input type="checkbox" id="selectAggregate"> Сводные</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="button" id="saveButton" class="btn btn-default">Создать</button>
                    <button type="button" id="cancelButton" class="btn btn-default">Отменить</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="cloneDocuments">
    <div id="cloneDocumentsHeader">
        <span id="headerContainer" style="float: left">Клонирование документов в новый отчетный период</span>
    </div>
    <div>
        <div style="padding: 15px" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-sm-3" for="selectClonePeriod">Выберите период:</label>
                <div class="col-sm-6">
                    <div id="selectClonePeriod"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="selectCloneMonitoring">Выберите мониторинг:</label>
                <div class="col-sm-6">
                    <div id="selectCloneMonitoring"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="selectCloneAlbum">Выберите альбом форм:</label>
                <div class="col-sm-6">
                    <div id="selectCloneAlbum"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3" for="selectCloneState">Исходный статус:</label>
                <div class="col-sm-6">
                    <div id="selectCloneState"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="button" id="doClone" class="btn btn-primary">Клонировать</button>
                    <button type="button" id="cancelClone" class="btn btn-default">Отменить</button>
                </div>
            </div>
            <div class="row">
                <p class="text-info">В новом периоде будут созданы документы в соответствии с выбранными в текущем периоде с теми же основными параметрами:
                    учреждение, тип документа, мониторинг
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/documentadmin.js?v=043') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let checkeddtypes = {!! $dtype_ids !!};
        let albums = {!! $albums  !!};
        let forms = {!! $forms  !!};
        let states = {!! $states !!};
        let periods = {!! $periods !!};
        let dtypes = {!! $dtypes !!};
        let checkedstates = {!! $state_ids !!};
        let checkedperiods = [{!! $period_ids !!}];
        let checkedfilled = '{{ $filleddocs->value or '-1' }}';
        datasources();
        initfilterdatasources();
        initDropdowns();
        initsplitters();
        initMonitoringTree();
        initPeriodTree();
        initStatusList();
        initDTypesList();
        initDataPresens();
        initMoTree();
        initGroupTree();
        initdocumentslist();
        initdocumentactions();
        initnewdocumentwindow();
    </script>
@endsection
