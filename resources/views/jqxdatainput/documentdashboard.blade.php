@extends('jqxdatainput.dashboardlayout')

@section('title', 'Статистические отчетные документы')
@section('headertitle', 'Статистические отчетные документы')

@section('content')
    <div id="mainSplitter">
        <div>
            <div id="filterPanelSplitter" style="padding-top: 10px; margin-bottom: 20px">
                <div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="text-center">Выбор мониторингов/отчетных документов:</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="well well-sm">
                                <div id="monitoringSelector">
                                    <div id="monTree"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="well well-sm">
                                <div id="moSelectorByTerritories"><div id="moTree"></div></div>
                                <div id="moSelectorByGroups"><div class="jqx-hideborder" id="groupTree"></div></div>
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
                                    <button class="btn btn-primary btn-sm" id="applyStatuses">Применить</button>
                                    <div id="statesListbox"></div>
                                    <div class="row">
                                        <div class="col-md-offset-1 col-md-11">
                                            <p class="text-info">Только для первичных документов</p>
                                        </div>
                                    </div>
                                </div>
                                <div id="dataPresenceSelector" style="display: none">
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
                    </div>
                    <div class="row">
                        <div class="col-sm-offset-1 col-sm-12">
                            <button class="btn btn-primary" id="clearAllFilters">Очистить фильтры</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="ContentPanel">
            <div class="jqx-hideborder jqx-hidescrollbars" id="documenttabs">
                <ul>
                    <li style="margin-left: 30px;">Отчеты субъектов</li>
                    <li>Сводные отчеты</li>
                    <li>Консолидированные отчеты</li>
                    <li>Последние документы</li>
                </ul>
                <div>
                    <div class="jqx-hideborder jqx-hidescrollbars" style="width: 100%; height: 100%">
                        <div class="row">
                            <div class="col-md-3">
                                <h3 style="margin-left: 30px">Первичные отчеты</h3>
                            </div>
                            <div class="col-md-9">
                                <h4 class="pull-right" style="padding-right: 10px" title="Выделенная организационная единица входит в состав">
                                    <small class="text-info" id="mo_parents_breadcrumb">...</small>
                                </h4>
                            </div>
                        </div>

                    <div id="DocumentPanelSplitter">
                        <div >
                            <div id="Documents"></div>
                        </div>
                        <div class="jqx-hideborder">
                            {{--<div id="DocumentPropertiesSplitter">--}}
                                <div id="messagesExpander" class="panel panel-default panel" style="height: 95%">
                                    <div id="messagesTitle" class="panel-heading">Сообщения и комментарии <a href="#" id="openMessagesListWindow"><...></a></div>
                                    <div id="DocumentMessages" class="panel-body" style="height: 85%; padding: 0; overflow-y: auto"></div>
                                </div>
{{--                                <div class="jqx-hideborder" >
                                    <div id="auditExpander">
                                        <div>Статус проверки документа <a href="#" id="openAuditionListWindow"><...></a></div>
                                        <div id="DocumentAuditions"></div>
                                    </div>
                                </div>
                            </div>--}}
                        </div>
                    </div>

                    </div>
                </div>
                <div>
                    <div class="jqx-hideborder jqx-hidescrollbars" style="width: 100%; height: 100%">
                        <h3 style="margin-left: 30px">Сводные отчеты</h3>
                        <div id="Aggregates"></div>
                    </div>
                </div>
                <div>
                    <div class="jqx-hideborder jqx-hidescrollbars" style="width: 100%; height: 100%">
                        <h3 style="margin-left: 30px">Консолидированные отчеты</h3>
                        <div id="Consolidates"></div>
                    </div>
                </div>
                <div>
                    <div class="jqx-hideborder jqx-hidescrollbars" style="width: 100%; height: 100%">
                        <h3 style="margin-left: 30px">Последние документы</h3>
                        <div id="Recent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('jqxdatainput.windows')
@endsection

@push('loadcss')
<link href="{{ asset('/css/medinfodocuments.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('loadjsscripts')
<script src="{{ asset('/medinfo/documentdashboard.js?v=175') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let current_user_scope = '{{ $worker_scope }}';
        let audit_permission = {{ $audit_permission ? 'true' : 'false' }};
        let periods = {!! $periods !!};
        let states = {!! $states !!};
        let checkedmf = [{!! $mf->value or '' !!}]; // Выбранные в последнем сеансе мониторинги и формы
        let lasstscope = '{{ is_null($last_scope) ? $worker_scope : $last_scope }}';
        let checkedmonitorings = [{!! $mon_ids->value or '' !!}];
        let checkedforms = [{!! $form_ids->value or '' !!}];
        let checkedstates = [{!! $state_ids->value or '' !!}];
        let checkedperiods = [{!! $period_ids->value or '' !!}];
        let checkedfilled = '{{ $filleddocs->value or '-1' }}';
        let disabled_states = [{!! $disabled_states or '' !!}];
        let filter_mode = {!! $filter_mode->value or 1 !!}; // 1 - по территориям; 2 - по группам
        //let current_top_level_node = '{{ is_null($worker_scope) ? 'null' : $worker_scope }}';
        //let current_top_level_node = {{ is_null($worker_scope) ? 0 : $worker_scope }};
        let current_top_level_node = {{ is_null($last_scope) ? $worker_scope : $last_scope }};
        let current_filter = '&filter_mode=' + filter_mode + '&ou=' + lasstscope + '&states='
            + checkedstates.join() + '&mf=' + checkedmf.join() + '&monitorings=' + checkedmonitorings.join()
            + '&forms=' + checkedforms.join() + '&periods=' + checkedperiods.join() + '&filled=' + checkedfilled;
        let dgridDataAdapter;
        datasources();
        initSplitters();
        initMonitoringTree();
        initmotree();
        initgrouptree();
        initPeriodTree();
        initStateList();
        initDataPresens();
        initDropdowns();
        initFilterIcons();
        initDocumentSource();
        initdocumentstabs();
        initdocumentproperties();
        //initauditionproperties
        initConsolidates();
        initRecentDocuments();
        initpopupwindows();
        initdocinfowindow();
    </script>
@endsection