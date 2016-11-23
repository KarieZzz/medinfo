@extends('jqxdatainput.dashboardlayout')

@section('title', '<h4>Статистические отчетные документы - ввод и корректировка</h4>')
@section('headertitle', 'Статистические отчетные документы')

@section('content')
    <div id="mainSplitter">
        <div>
            <div id="filterPanelSplitter">
                <div>
                    <div class="jqx-hideborder jqx-hidescrollbars" id="motabs">
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
                    </div>
                </div>
                <div id="filtertabs" class="jqx-hideborder jqx-hidescrollbars">
                    <ul>
                        <li style="margin-left: 30px;">Формы</li>
                        <li>Статусы отчетов</li>
                        <li>Периоды</li>
                    </ul>
                    <div>
                        <div id="formcheckboxesPanel">
                            <div id="formsListbox" style="float: left; margin-right: 30px"></div>
                            <div id="selectedFormBox">
                                <div id="checkAllForms"><span>Выбрать все формы</span></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div id="statecheckboxs" class="jqx-hideborder  jqx-hidescrollbars">
                            <div id="statesListbox" style="float: left; margin-right: 30px"></div>
                            <div id="checkAllStates"><span>Выбрать все статусы</span></div>
                        </div>
                    </div>
                    <div>
                        <div id="periodsListbox" style="margin: 10px"></div>
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
<script src="{{ asset('/jqwidgets/jqxnotification.js') }}"></script>
<script src="{{ asset('/jqwidgets/localization.js') }}"></script>
<script src="{{ asset('/medinfo/documentdashboard.js?v=003') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var current_top_level_node = '{{ is_null($worker_scope) ? 'null' : $worker_scope }}';
        var current_user_role = '{{ $worker->role }}';
        var current_user_id = '{{ $worker->id }}';
        var audit_permission = {{ $audit_permission ? 'true' : 'false' }};
        var periods = {!! $periods !!};
        var forms = {!! $forms  !!};
        var states = {!! $states !!};
        var checkedforms = {!! $form_ids !!};
        var checkedstates = {!! $state_ids !!};
        var checkedperiods = [{{ $period_ids }}];
        var disabled_states = [{!! $disabled_states !!}];
        var filter_mode = 1; // 1 - по территориям; 2 - по группам
        var mo_tree_url = 'datainput/fetch_mo_tree/';
        var group_tree_url = 'datainput/fetch_ugroups';
        var docsource_url = 'datainput/fetchdocuments?';
        var docmessages_url = 'datainput/fetchmessages?';
        var changestate_url = 'datainput/changestate';
        var changeaudition_url = 'datainput/changeaudition';
        var docmessagesend_url = 'datainput/sendmessage';
        var docauditions_url = 'datainput/fetchauditions?';
        var aggrsource_url = 'datainput/fetchaggregates?';
        var edit_form_url = 'datainput/formdashboard';
        var edit_aggregate_url = 'datainput/aggregatedashboard';
        var aggregatedata_url = "/datainput/aggregatedata/";
        var export_form_url = "/datainput/formexport/";
        //var checkedform = ['f30','f17','f12','f14','f14дс','f16','f57','f1-РБ','f15','f16-вн','f13','f31','f32','f32_вкл','f19','f1-ДЕТИ','f10','f11','f36','f36-ПЛ','f37','f9','f34','f7','f35','f8','f33','f7-Т','f39','f41', 'f53','f55','f56','f61','f70'];
        //var checkedstates = ['st2', 'st4', 'st8', 'st16', 'st32'];
        var motree = $("#moTree");
        var grouptree = $("#groupTree");
        var dgrid = $("#Documents"); // сетка для первичных документов
        var agrid = $("#Aggregates"); // сетка для сводных документов
        var current_document_form_code;
        var current_document_form_name;
        var current_document_ou_name;
        var current_document_state;
        var current_document_audits = [];
        var statelabels =
        {
            performed: 'Выполняется',
            prepared: 'Подготовлен к проверке',
            accepted: 'Принят',
            declined: 'Возвращен на доработку',
            approved: 'Утвержден'
        };
        var audit_state_ids =
        {
            noaudit: 1,
            audit_correct: 2,
            audit_incorrect: 3
        };
        datasources();
        initmotabs();
        $("#mainSplitter").jqxSplitter(
                {
                    width: '99%',
                    height: '96%',
                    theme: theme,
                    panels:
                            [
                                { size: "35%", min: "10%"},
                                { size: '65%', min: "30%"}
                            ]
                }
        );
        $('#filterPanelSplitter').jqxSplitter({
            width: '100%',
            height: '99%',
            theme: theme,
            orientation: 'horizontal',
            panels: [{ size: '50%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
        });
        initfiltertabs();
        $('#DocumentPanelSplitter').jqxSplitter({
            width: '100%',
            height: '93%',
            theme: theme,
            orientation: 'horizontal',
            panels: [{ size: '50%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
        });
        initmotree();
        initgrouptree();
        initdocumentstabs();
        initdocumentproperties();
        initpopupwindows();
        initnotifications();
    </script>
@endsection