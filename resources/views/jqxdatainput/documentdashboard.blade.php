@extends('jqxdatainput.dashboardlayout')

@section('title', '<h4>Статистические отчетные документы - ввод и корректировка</h4>')
@section('headertitle', 'Статистические отчетные документы')

@section('content')
    <div id="mainSplitter">
        <div>
            <div id="filterPanelSplitter">
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
                                    <button class="btn btn-default btn-sm" id="checkAllPeriods">Выбрать все</button>
                                    <button class="btn btn-default btn-sm" id="clearAllPeriods">Очистить</button>
                                    <button class="btn btn-primary btn-sm" id="applyPeriods">Применить</button>
                                    <div id="periodTree"></div>
                                </div>
                                <div id="statusSelector">
                                    <button class="btn btn-default btn-sm" id="checkAllStates">Выбрать все</button>
                                    <button class="btn btn-default btn-sm" id="clearAllStates">Очистить</button>
                                    <button class="btn btn-primary btn-sm" id="applyStatuses">Применить</button>
                                    <div id="statesListbox"></div>
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
            <div class="jqx-hideborder jqx-hidescrollbars" id="documenttabs" style="width: 100%; height: 100%">
                <ul>
                    <li style="margin-left: 30px;">Отчеты субъектов</li>
                    <li>Сводные отчеты</li>
                    <li>Последние документы</li>
                </ul>
                <div class="jqx-hideborder jqx-hidescrollbars" style="width: 100%; height: 100%">
                    <h3 style="margin-left: 30px">Первичные отчеты</h3>
                    <div id="DocumentPanelSplitter">
                        <div >
                            <div id="Documents"></div>
                        </div>
                        <div class="jqx-hideborder">
                            <div id="DocumentPropertiesSplitter">
                                <div class="jqx-hideborder">
                                    <div id="messagesExpander">
                                        <div id="messagesTitle">Сообщения и комментарии <a href="#" id="openMessagesListWindow"><...></a></div>
                                        <div id="DocumentMessages"></div>
                                    </div>
                                </div>
                                <div class="jqx-hideborder" >
                                    <div id="auditExpander">
                                        <div>Статус проверки документа <a href="#" id="openAuditionListWindow"><...></a></div>
                                        <div id="DocumentAuditions"></div>
                                    </div>
                                </div>
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
<script src="{{ asset('/jqwidgets/jqxtabs.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxdata.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxpanel.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxexpander.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxscrollbar.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxbuttons.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxdropdownbutton.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxinput.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxtextarea.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxcheckbox.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxradiobutton.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxtree.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxwindow.js') }}"></script>
<script src="{{ asset('/jqwidgets/localization.js') }}"></script>
<script src="{{ asset('/medinfo/documentdashboard.js?v=051') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let current_user_id = '{{ $worker->id }}';
        let audit_permission = {{ $audit_permission ? 'true' : 'false' }};
        let periods = {!! $periods !!};
        let states = {!! $states !!};
        let checkedmf = [{!! $mf->value or '' !!}]; // Выбранные в последнем сеансе мониторинги и формы
        let lasstscope = {!! $last_scope->value or $worker_scope !!};
        let checkedmonitorings = [{!! $mon_ids->value or '' !!}];
        let checkedforms = [{!! $form_ids->value or '' !!}];
        let checkedstates = [{!! $state_ids->value or '' !!}];
        let checkedperiods = [{!! $period_ids->value or '' !!}];
        let disabled_states = [{!! $disabled_states or '' !!}];
        let filter_mode = {!! $filter_mode->value or 1 !!}; // 1 - по территориям; 2 - по группам
        //let current_top_level_node = '{{ is_null($worker_scope) ? 'null' : $worker_scope }}';
        let current_top_level_node = {{ is_null($worker_scope) ? 'null' : $worker_scope }};
        let current_filter = '&filter_mode=' + filter_mode + '&ou=' + lasstscope + '&states='
            + checkedstates.join() + '&mf=' + checkedmf.join() + '&monitorings=' + checkedmonitorings.join()
            + '&forms=' + checkedforms.join() + '&periods=' + checkedperiods.join();
        datasources();
        initSplitters();
        initMonitoringTree();
        initStatusList();
        initDropdowns();
        initFilterIcons();
        initmotree();
        initgrouptree();
        initPeriodTree();
        initDocumentSource();
        initdocumentstabs();
        initdocumentproperties();
        initRecentDocuments();
        initpopupwindows();
        initnotifications();
    </script>
@endsection