@extends('jqxdatainput.dashboardlayout')

@section('title', '<h4>Статистические отчетные документы - ввод и корректировка</h4>')
@section('headertitle', 'Статистические отчетные документы')

@section('content')
    <div id="mainSplitter">
        <div>
            <div id="filterPanelSplitter">
                <div>
                    <div id="monitoringSelector"><div id="monTree"></div></div>
                    <div id="moSelectorByTerritories"><div id="moTree"></div></div>

{{--                        <div class="jqx-hideborder jqx-hidescrollbars" id="motabs">
                            <ul>
                                <li style="margin-left: 30px;"> Медицинские организации по территориям</li>
                                <li>По группам</li>
                            </ul>
                            <div>
                                <div class="jqx-hideborder" id="moTree"></div>
                            </div>
                            <div>
                                <div class="jqx-hideborder" id="groupTree"></div>
                            </div>
                        </div>--}}
                    <div id="moSelectorByGroups"><div class="jqx-hideborder" id="groupTree"></div></div>
                    <div id="periodSelector"><div id="periodTree"></div></div>
                    <div id="statusSelector">
                        <button class="btn btn-primary" id="applyStatuses">Применить</button>
                        <div id="checkAllStates"><span>Выбрать/Убрать все статусы</span></div>
                        <div id="statesListbox"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="ContentPanel">
            <div class="jqx-hideborder jqx-hidescrollbars" id="documenttabs" style="width: 100%; height: 100%">
                <ul>
                    <li style="margin-left: 30px;">Отчеты субъектов</li>
                    <li>Сводные отчеты</li>
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
<script src="{{ asset('/medinfo/documentdashboard.js?v=041') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let current_user_id = '{{ $worker->id }}';
        let audit_permission = {{ $audit_permission ? 'true' : 'false' }};
        let periods = {!! $periods !!};
        let states = {!! $states !!};
        let checkedmf = [{!! $forms->value !!}]; // Выбранные в последнем сеансе мониторинги и формы
        let checkedstates = [{!! $state_ids->value !!}];
        let checkedperiods = [{!! $period_ids->value !!}];
        let disabled_states = [{!! $disabled_states !!}];
        let filter_mode = 1; // 1 - по территориям; 2 - по группам
        let mon_tree_url = 'datainput/fetch_mon_tree/';
        let mo_tree_url = 'datainput/fetch_mo_tree/';
        let group_tree_url = 'datainput/fetch_ugroups';
        let docsource_url = 'datainput/fetchdocuments?';
        let docmessages_url = 'datainput/fetchmessages?';
        let changestate_url = 'datainput/changestate';
        let changeaudition_url = 'datainput/changeaudition';
        let docmessagesend_url = 'datainput/sendmessage';
        let docauditions_url = 'datainput/fetchauditions?';
        let aggrsource_url = 'datainput/fetchaggregates?';
        let edit_form_url = 'datainput/formdashboard';
        let edit_aggregate_url = 'datainput/aggregatedashboard';
        let aggregatedata_url = "/datainput/aggregatedata/";
        let export_form_url = "/datainput/formexport/";
        let montree = $("#monTree");
        let motree = $("#moTree");
        let grouptree = $("#groupTree");
        let periodTree = $("#periodTree");
        let stateList = $("#statesListbox");
        let dgrid = $("#Documents"); // сетка для первичных документов
        let agrid = $("#Aggregates"); // сетка для сводных документов
        let mondropdown = $("#monitoringSelector");
        let terr = $("#moSelectorByTerritories");
        let groups = $('#moSelectorByGroups');
        let periodDropDown = $('#periodSelector');
        let statusDropDown = $('#statusSelector');
        let current_document_form_code;
        let current_document_form_name;
        let current_document_ou_name;
        let current_document_state;
        let currentlet_document_audits = [];
        let current_user_role = '{{ $worker->role }}';
        let current_top_level_node = '{{ is_null($worker_scope) ? 'null' : $worker_scope }}';
        let current_filter = '&filter_mode=' + filter_mode + '&ou=' + current_top_level_node + '&states=' + checkedstates.join() + '&mf=' + checkedmf.join() + '&periods=' + checkedperiods.join();

        let statelabels =
        {
            performed: 'Выполняется',
            prepared: 'Подготовлен к проверке',
            accepted: 'Принят',
            declined: 'Возвращен на доработку',
            approved: 'Утвержден'
        };
        let audit_state_ids =
        {
            noaudit: 1,
            audit_correct: 2,
            audit_incorrect: 3
        };
        datasources();
        //initmotabs();
        $("#mainSplitter").jqxSplitter(
                {
                    width: '99%',
                    height: '96%',
                    theme: theme,
                    panels:
                            [
                                { size: "20%", min: "10%"},
                                { size: '80%', min: "30%"}
                            ]
                }
        );
/*        $('#filterPanelSplitter').jqxSplitter({
            width: '100%',
            height: '99%',
            theme: theme,
            orientation: 'horizontal',
            panels: [{ size: '50%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
        });*/
        $('#DocumentPanelSplitter').jqxSplitter({
            width: '100%',
            height: '93%',
            theme: theme,
            orientation: 'horizontal',
            panels: [{ size: '65%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
        });
        initMonitoringTree();
        initStatusList();
        initDropdowns();
        initmotree();
        initgrouptree();
        initPeriodTree();
        initDocumentSource();
        initdocumentstabs();
        initdocumentproperties();
        initpopupwindows();
        initnotifications();
    </script>
@endsection