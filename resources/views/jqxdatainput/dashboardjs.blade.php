<script type="text/javascript">
let ou_name = "{{ preg_replace('/[\r\n\t]/', '', $current_unit->unit_name) }}";
let ou_code = "{{ $current_unit->unit_code }}";
let doc_id = '{{ $document->id }}';
let doc_type = '{{ $document->dtype }}';
let docstate_id = '{{ $document->state }}';
let doc_statelabel = '{{ $statelabel }}';
let form_name = '{{ $form->form_name }}';
let form_code = '{{ $form->form_code }}';
let default_album = '{{ $album->id }}';
let current_table = '{{ $laststate['currenttable']->id }}';
let current_table_code = '{{ $laststate['currenttable']->table_code }}';
let current_table_index = '{{ $laststate['currenttable']->table_index }}';
let max_table_index = '{!!  $renderingtabledata['max_index'] !!}';
let formsections = {!! $formsections !!};
let splitter = $("#formEditLayout");
let fgrid = $("#FormTables"); // селектор для сетки с перечнем таблиц
let dgrid = $("#DataGrid"); // селектор для сетки с данными таблиц
let controltabs = $("#ControlTabs");
let tdropdown = $('#TableList');
let prevtable = $('#Previous');
let nexttable = $('#Following');
let filterinput = $("#SearchField");
let clearfilter = $("#ClearFilter");
let calculate = $("#Сalculate");
let fullscreen = $("#ToggleFullscreen");
let tcheck = $("#TableCheck");
let idtcheck = $("#IDTableCheck");
let iptcheck = $("#IPTableCheck");
let formcheck = $("#FormCheck");
let excelexport = $("#tableExcelExport");
let excelimport = $("#tableExcelImport");
let fsdropdown = $('#SectionsManager');
let excelUploadWindow = $('#uploadExcelFile');
let flUpload = $('#ExcelFileUpload');
let onlyOneTable = $('#onlyOneTable');
let localizednumber = new Intl.NumberFormat('ru-RU');
let edited_tables = [{!! implode(',', $editedtables) !!}];
let not_editable_cells = {!! json_encode($noteditablecells) !!};
let edit_permission = {{ $editpermission ? 'true' : 'false' }};
let control_disabled = {{ config('app.control_disabled') ? 'true' : 'false' }};
let form_tables_data = [{!! implode(',', $renderingtabledata['tablelist']) !!}];
let data_for_tables = $.parseJSON('{!!  $renderingtabledata['tablecompose'] !!}');
$.each(data_for_tables, function(table, content) {
    $.each(content.columns, function(column, properties) {
        if (typeof properties.cellclassname !== 'undefined' && properties.cellclassname === 'cellclass') {
            properties.cellclassname = cellclass;
        }
        //if (typeof properties.createeditor !== 'undefined') {
          //  properties.createeditor =  eval(properties.createeditor);
        //}
        if (typeof properties.initeditor !== 'undefined') {
            properties.initeditor = eval(properties.initeditor);
        }
        if (typeof properties.validation !== 'undefined') {
            properties.validation = validation;
        }
        if (typeof properties.cellbeginedit !== 'undefined') {
            properties.cellbeginedit = cellbeginedit;
        }
        if (typeof properties.cellsrenderer !== 'undefined') {
            properties.cellsrenderer = cellsrenderer;
        }
    });
    $.each(content.columngroups, function(group, properties) {
        if (typeof properties.rendered !== 'undefined')
            properties.rendered = tooltiprenderer;
    });
});
//console.log(current_table);
//console.log(data_for_tables);
let there_is_calculated = data_for_tables[current_table].calcfields.length > 0;
let current_row_name_datafield = data_for_tables[current_table].columns[1].dataField;
let current_row_number_datafield = data_for_tables[current_table].columns[2].dataField;
let protocol_control_created = false;
let forcereload = 0; // При наличии загружается кэшированный протокол контроля
let invalidTables = [];
let editedCells = [];
let alertedCells = [];
let invalidCells = [];
let comparedCells = [];
let show_table_errors_only = true;
let marking_mode = 'control';
let current_edited_cell = {};
// JSON объект - протокол контроля формы
let current_protocol_source = [];
let source_url = "/datainput/fetchvalues/" + doc_id + "/" + default_album + "/";
let savevalue_url = "/datainput/savevalue/" + doc_id + "/";
//let validate_table_url = "/datainput/tablecontrol/" + doc_id + "/";
let validate_form_url = "/datainput/formcontrol/" + doc_id;
let informTableDataCheck = "/datainput/ifdcheck/table/" + doc_id + "/";
let interFormTableDataCheck = "/datainput/interformdcheck/table/" + doc_id + "/";
let interPeriodTableDataCheck = "/datainput/interperioddcheck/table/" + doc_id + "/";
let formdatacheck_url = "/datainput/dcheck/form/" + doc_id;
let medstat_control_url = "medstat_control_protocol.php?document=" + doc_id;
let valuechangelog_url = "/datainput/valuechangelog/" + doc_id;
let tableexport_url = "/datainput/tableexport/" + doc_id + "/";
let cell_layer_url = "/datainput/fetchcelllayers/" + doc_id + "/";
let calculatedcells_url = "/datainput/calculate/" + doc_id + "/";
let cons_protocol_url = "/datainput/fetchconsprotocol/" + doc_id + "/";
let blocksection_url = "/datainput/blocksection/" + doc_id + "/";
let excelupload_url = '/datainput/excelupload/' + doc_id + '/';
let formlabels =
    {
        compare: 1,
        dependency: 2,
        interannual: 3,
        iadiapazon: 4,
        multiplicity: 5,
        ipdiapazon: 19,
        section: 20
    };
let disabled_states = [{!! $disabled_states or '' !!}];
let initialViewport = $(window).height();
let topOffset1 = 155;
let topOffset2 = 105;
//let topOffset3 = 125;
//let topOffset4 = 105;
//initDgridSize();
//initSplitterSize();
//initProtSize();
//initCellProtSize();
onResizeEventLitener();
initdatasources();
inittoolbarbuttons();
inittablelist();
initSplitter();
initfilters();
initdatagrid();
init_fc_extarbuttons();
initextarbuttons();
initExcelUpload();
@yield('initTableConsolidateAction')
//firefullscreenevent();
</script>