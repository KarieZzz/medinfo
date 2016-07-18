@extends('jqxdatainput.dashboardlayout')

@section('title', '<h4>Статистические отчетные документы - ввод и корректировка</h4>')

@section('content')
    <div id="mainSplitter">
        <div>
            <div id="filterPanelSplitter">
                <div class="jqx-hideborder">
                    <div id="moExpander">
                        <div class="jqx-hideborder">Медицинские организации</div>
                        <div class="jqx-hideborder  jqx-hidescrollbars">
                            <div class="jqx-hideborder" id="moTree">

                            </div>
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
                            <div id="formecheckboxs">
                                <input id="allForms" type="button" value="Выбрать все" />
                                <input id="noForms" type="button" value="Снять выбор" />
                                <div style="margin-top: 15px">
                                    <div id='f30' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>30</span></div>
                                    <div id='f17' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>17</span></div>
                                    <div id='f12' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>12</span></div>
                                    <div id='f14' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>14</span></div>
                                    <div id='f14дс' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>14дс</span></div>
                                    <div id='f16-вн' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>16-вн</span></div>
                                    <div id='f57' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>57</span></div>
                                    <div id='f1-РБ' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>1-РБ</span></div>
                                    <div id='f15' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>15</span></div>
                                    <div id='f16' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>16</span></div>
                                    <hr style="clear: both" />
                                    <div id='f13' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>13</span></div>
                                    <div id='f31' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>31</span></div>
                                    <div id='f32' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>32</span></div>
                                    <div id='f32_вкл' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>32_вкл</span></div>
                                    <div id='f19' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>19</span></div>
                                    <div id='f1-ДЕТИ' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>1-Дети</span></div>
                                    <hr style="clear: both" />
                                    <div id='f10' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>10</span></div>
                                    <div id='f11' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>11</span></div>
                                    <div id='f36' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>36</span></div>
                                    <div id='f36-ПЛ' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>36-ПЛ</span></div>
                                    <div id='f37' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>37</span></div>
                                    <hr style="clear: both" />
                                    <div id='f9' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>9</span></div>
                                    <div id='f34' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>34</span></div>
                                    <hr style="clear: both" />
                                    <div id='f7-Т' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>7-Т</span></div>
                                    <div id='f39' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>39</span></div>
                                    <div id='f41' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>41</span></div>
                                    <div id='f53' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>53</span></div>
                                    <div id='f55' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>55</span></div>
                                    <div id='f56' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>56</span></div>
                                    <div id='f61' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>61</span></div>
                                    <div id='f70' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>70</span></div>
                                    <hr style="clear: both" />
                                    <div id='f7' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>7</span></div>
                                    <div id='f35' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>35</span></div>
                                    <hr style="clear: both" />
                                    <div id='f8' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>8</span></div>
                                    <div id='f33' class="formbox" style='margin-left: 10px;float: left; font-size: 0.9em'>
                                        <span>33</span></div>
                                    <hr style="clear: both" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div id="statecheckboxs" class="jqx-hideborder  jqx-hidescrollbars">
                            <input id="allStates" type="button" value="Выбрать все" />
                            <input id="noStates" type="button" value="Снять выбор" />
                            <div style="margin-top: 15px">
                                <div id='st2' class="statebox" style='margin-left: 10px'>
                                    <span>Выполняется</span></div>
                                <div id='st4' class="statebox" style='margin-left: 10px'>
                                    <span>Подготовлен к проверке</span></div>
                                <div id='st8' class="statebox" style='margin-left: 10px'>
                                    <span>Принят</span></div>
                                <div id='st16' class="statebox" style='margin-left: 10px'>
                                    <span>Возвращен на доработку</span></div>
                                <div id='st32' class="statebox" style='margin-left: 10px'>
                                    <span>Утвежден</span></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div id="periodcheckboxes" class="jqx-hideborder  jqx-hidescrollbars">
                            <input id="allPeriods" type="button" value="Выбрать все" />
                            <input id="noPeriods" type="button" value="Снять выбор" />
                            <div style="margin-top: 15px">
                                <div id='pl02345j0' class="periodbox" style='margin-left: 10px'>
                                    <span>2013</span></div>
                                <div id='pl02345k0' class="periodbox" style='margin-left: 10px'>
                                    <span>2014</span></div>
                                <div id='pl02345l0' class="periodbox" style='margin-left: 10px'>
                                    <span>2015</span></div>
                            </div>
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
<script src="{{ asset('/medinfo/documentdashboard.js') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var current_top_level_node = '{{ is_null($worker_scope) ? 'null' : $worker_scope }}';
        var current_user_role = '{{ $worker->role }}';
        var current_user_id = '{{ $worker->id }}';
        var audit_permission = {{ $audit_permission ? 'true' : 'false' }};
        var periods = ['{{ $period_id }}'];
        var disabled_states = [{!! $disabled_states !!}];
        var mo_tree_url = 'admin/fetch_mo_tree/';
        var docsource_url = 'datainput/fetchdocuments?';
        var docmessages_url = 'datainput/fetchmessages?';
        var changestate_url = 'datainput/changestate';
        var changeaudition_url = 'datainput/changeaudition';
        var docmessagesend_url = 'datainput/sendmessage';
        var docauditions_url = 'datainput/fetchauditions?';
        var aggrsource_url = 'datainput/fetchaggregates?';
        var edit_form_url = 'datainput/formdashboard';
        var edit_aggregate_url = 'aggregate_dashboard.php?';
        var export_form_url = 'export_form_to_excel.php?';
        var checkedform = ['f30','f17','f12','f14','f14дс','f16','f57','f1-РБ','f15','f16-вн','f13','f31','f32','f32_вкл','f19','f1-ДЕТИ','f10','f11','f36','f36-ПЛ','f37','f9','f34','f7','f35','f8','f33','f7-Т','f39','f41', 'f53','f55','f56','f61','f70'];
        var checkedstates = ['st2', 'st4', 'st8', 'st16', 'st32'];
        var checkedperiods = ['pl02345l0'];
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
        $("#mainSplitter").jqxSplitter(
                {
                    width: '99%',
                    height: '98%',
                    theme: theme,
                    panels:
                            [
                                { size: "30%", min: "10%"},
                                { size: '70%', min: "30%"}
                            ]
                }
        );
        $('#filterPanelSplitter').jqxSplitter({
            width: '100%',
            height: '100%',
            theme: theme,
            orientation: 'horizontal',
            panels: [{ size: '50%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
        });
        $("#moExpander").jqxExpander({toggleMode: 'none', showArrow: false, width: "100%", height: "100%", theme: theme  });
        initfiltertabs();
        $('#DocumentPanelSplitter').jqxSplitter({
            width: '100%',
            height: '95%',
            theme: theme,
            orientation: 'horizontal',
            panels: [{ size: '50%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
        });
        initmotree();
        initdocumentstabs();
        initdocumentproperties();
        initpopupwindows();
        initnotifications();
    </script>
@endsection