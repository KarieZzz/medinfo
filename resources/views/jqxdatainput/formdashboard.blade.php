@extends('jqxdatainput.dashboardlayout')

@section('title')
<h5>
    Форма №<span class="text-info">{{ $form->form_code  }} </span>
    <span class="glyphicon glyphicon-map-marker"></span> <span class="text-info">{{ $current_unit->unit_name }} </span>
    <span class="glyphicon glyphicon-calendar"></span> <span class="text-info">{{ $period->name }} </span>
    <span class="glyphicon glyphicon-star"></span> <span class="text-info">{{ $statelabel }} </span>
    <span class="glyphicon glyphicon-edit"></span> <span class="text-info">{{ $editmode }} </span>
</h5>
@endsection
@section('headertitle', 'Просмотр/редактирование отчетного документа')

@section('content')
    <div id="formEditLayout">
        <div data-container="FormPanel">
            <div id="formTables" class="no-border">
            </div>
        </div>
        <div data-container="FormControlPanel" id="fcp">
            <div id="form_control_toolbar">
                <input id="checkform" type="button" value="Выполнить проверку формы" />
                <input id="dataexport" type="button" value="Экспорт данных" />
            </div>
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
        <div data-container="TableControlPanel">
            <input id="checktable" type="button" value="Выполнить проверку таблицы" />
            <input id="compareprevperiod" type="button" value="Сравнить с предыдущим периодом" />
            <div id="protocolloader"></div>
            <div id="tableprotocol"></div>
        </div>
        <div data-container="CellControlPanel">
            <div id="cellvalidationprotocol">Изменений в текущем сеансе не было</div>
        </div>
    </div>
@endsection

@push('loadcss')
<link href="{{ asset('/css/medinfoeditform.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('loadjsscripts')
<script src="{{ asset('/jqwidgets/jqxtabs.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxdata.js') }}"></script>
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
<script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxwindow.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxtooltip.js') }}"></script>
<script src="{{ asset('/jqwidgets/jqxnotification.js') }}"></script>
<script src="{{ asset('/jqwidgets/localization.js') }}"></script>
<script src="{{ asset('/medinfo/formdashboard.js') }}"></script>
@endpush

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
        var editedCells = [];
        var invalidCells = [];
        var comparedCells = [];
        var show_table_errors_only = true;
        var marking_mode = 'control';
        var current_edited_cell = {};
        var source_url = "/datainput/fetchvalues/" + doc_id + "/";
        var savevalue_url = "/datainput/savevalue/" + doc_id + "/";
        var medstat_control_url = "medstat_control_protocol.php?document=" + doc_id;
        var valuechangelog_url = "/datainput/valuechangelog/" + doc_id;
        initdatasources()
        //console.log(tablesource);
        initnotifications();
        inittablelist();
        initlayout();
        $('#formEditLayout').jqxLayout({ theme: theme, width: '99%', height: '99%', layout: layout });
    </script>
@endsection