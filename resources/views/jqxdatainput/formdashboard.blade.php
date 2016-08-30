@extends('jqxdatainput.dashboardlayout')

@section('title')
<h5>
    Форма №<span class="text-info">{{ $form->form_code  }} </span>
    <i class="fa fa-hospital-o fa-lg"></i> <span class="text-info">{{ $current_unit->unit_name }} </span>
    <i class="fa fa-calendar-o fa-lg"></i> <span class="text-info">{{ $period->name }} </span>
    <i class="fa fa-star fa-lg"></i> <span class="text-info">{{ $statelabel }} </span>
    <i class="fa fa-edit fa-lg"></i> <span class="text-info">{{ $editmode }} </span>
</h5>
@endsection
@section('headertitle', 'Просмотр/редактирование отчетного документа')

@section('content')
    <div id="formEditLayout">
        <div data-container="FormPanel">
            <div id="flist" style="width: 100%; height: 100%">
                <div id="formTables" class="no-border"></div>
            </div>
        </div>
        <div data-container="FormControlPanel" id="fcp">
            <div id="formControlToolbar" style="padding: 4px;">
                {{--<input id="dataexport" type="button" value="Экспорт данных" />--}}
                <input id="checkform" style="float: left" type="button" value="Контроль МИ" />
                <div style="padding: 4px; display: none" id="fc_extrabuttons">
                    <div id="showallfcrule" class="extrabutton" style="float: left"><span>Показать только ошибки</span></div>
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
                <input style="float: left" id="checktable" type="button" value="Контроль таблицы" />
                <input style="float: left" id="compareprevperiod" type="button" value="Сравнить с предыдущим периодом" />
                {{--<a href="#" onClick="DoFullScrene()">Full Screen Mode</a>--}}
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
            <div style='width: 100%;height: 90%' id="tableprotocol"></div>
        </div>
        <div data-container="CellControlPanel">
            <div id="cellprotocol"></div>
        </div>
    </div>
@endsection

@push('loadcss')
<link href="{{ asset('/css/medinfoeditform.css') }}" rel="stylesheet" type="text/css" />
@endpush('loadcss')

@push('loadjsscripts')
<script src="{{ asset('/jqwidgets/jqxtabs.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxdata.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxdata.export.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxlayout.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxribbon.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxpanel.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxexpander.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxscrollbar.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxbuttons.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxinput.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxtextarea.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxcheckbox.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxradiobutton.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxnumberinput.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.edit.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxgrid.export.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxwindow.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxtooltip.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxnotification.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxnavigationbar.js') }}"></script>
<script src="{{ asset('/jqwidgets/localization.js') }}"></script>
<script src="{{ asset('/plugins/fullscreen/jquery.fullscreen.js') }}"></script>
<script src="{{ asset('/medinfo/formdashboard.js') }}"></script>
@endpush('loadjsscripts')

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var ou_name = '{{ $current_unit->unit_name }}';
        var ou_code = '{{ $current_unit->unit_code }}';
        var doc_id = '{{ $document->id }}';
        var form_name = '{{ $form->form_name }}';
        var form_code = '{{ $form->form_code }}';
        var current_user_role = '{{ $worker->role }}';
        var edited_tables = [{!! implode(',', $editedtables) !!}];
        var not_editable_cells = {!! json_encode($noteditablecells) !!};
        //console.log(not_editable_cells);
        var edit_permission = {{ $editpermission ? 'true' : 'false' }};
        var control_disabled = {{ config('app.control_disabled') ? 'true' : 'false' }};
        var form_tables_data = [{!! implode(',', $renderingtabledata['tablelist']) !!}];
        var data_for_tables = $.parseJSON('{!!  $renderingtabledata['tablecompose'] !!}');
        $.each(data_for_tables, function(table, content) {
            $.each(content.columns, function(column, properties) {
                if (typeof properties.cellclassname !== 'undefined' )
                    properties.cellclassname = cellclass;
                //var row, cellvalue, editor;
                properties.createeditor =  eval(properties.createeditor);
                properties.validation =  eval(properties.validation);
                properties.cellbeginedit = cellbeginedit;
            });
            $.each(content.columngroups, function(group, properties) {
                if (typeof properties.rendered !== 'undefined' )
                    properties.rendered = tooltiprenderer;
            });
        });
        //console.log(data_for_tables);
        var current_table = '{{ $laststate['currenttable'] }}';
        var current_row_name_datafield = data_for_tables[current_table].columns[1].dataField;
        var current_row_number_datafield = data_for_tables[current_table].columns[2].dataField;
        var protocol_control_created = false;
        var invalidTables = [];
        var editedCells = [];
        var invalidCells = [];
        var comparedCells = [];
        var show_table_errors_only = true;
        var marking_mode = 'control';
        var current_edited_cell = {};
        // JSON объект - протокол контроля формы
        var current_protocol_source;
        var source_url = "/datainput/fetchvalues/" + doc_id + "/";
        var savevalue_url = "/datainput/savevalue/" + doc_id + "/";
        var validate_table_url = "/datainput/tablecontrol/" + doc_id + "/";
        var validate_form_url = "/datainput/formcontrol/" + doc_id;
        var medstat_control_url = "medstat_control_protocol.php?document=" + doc_id;
        var valuechangelog_url = "/datainput/valuechangelog/" + doc_id;
        var tableexport_url = "/datainput/tableexport/" + doc_id + "/";
        initdatasources();
        initnotifications();
        inittablelist();
        initlayout();
        $('#formEditLayout').jqxLayout({ theme: theme, width: '99%', height: '98%', layout: layout });
        init_fc_extarbuttons();
        initextarbuttons();
        firefullscreenevent();
    </script>
@endsection