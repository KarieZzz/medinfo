<script type="text/javascript">
var ou_name = '{{ $current_unit->unit_name }}';
var ou_code = '{{ $current_unit->unit_code }}';
var doc_id = '{{ $document->id }}';
var doc_type = '{{ $document->dtype }}';
var form_name = '{{ $form->form_name }}';
var form_code = '{{ $form->form_code }}';
var current_user_role = '{{ $worker->role }}';
var fgrid; // селектор для сетки с перечнем таблиц
var dgrid; // селектор для сетки с данными таблиц

var edited_tables = [{!! implode(',', $editedtables) !!}];
var not_editable_cells = {!! json_encode($noteditablecells) !!};
//console.log(not_editable_cells);
var edit_permission = {{ $editpermission ? 'true' : 'false' }};
var control_disabled = {{ config('app.control_disabled') ? 'true' : 'false' }};
var form_tables_data = [{!! implode(',', $renderingtabledata['tablelist']) !!}];
var data_for_tables = $.parseJSON('{!!  $renderingtabledata['tablecompose'] !!}');
$.each(data_for_tables, function(table, content) {
    $.each(content.columns, function(column, properties) {
        if (typeof properties.cellclassname !== 'undefined') {
            properties.cellclassname = cellclass;
        }
/*        if (typeof properties.createeditor !== 'undefined') {
            properties.createeditor =  eval(properties.createeditor);
        }*/
        if (typeof properties.initeditor !== 'undefined') {
            properties.initeditor = eval(properties.initeditor);
        }
        if (typeof properties.validation !== 'undefined') {
            properties.validation = validation;
        }
        if (typeof properties.cellbeginedit !== 'undefined') {
            properties.cellbeginedit = cellbeginedit;
        }
    });
    $.each(content.columngroups, function(group, properties) {
        if (typeof properties.rendered !== 'undefined')
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
var alertedCells = [];
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

var tabledatacheck_url = "/datainput/dcheck/table/" + doc_id + "/";
var formdatacheck_url = "/datainput/dcheck/form/" + doc_id;

var medstat_control_url = "medstat_control_protocol.php?document=" + doc_id;
var valuechangelog_url = "/datainput/valuechangelog/" + doc_id;
var tableexport_url = "/datainput/tableexport/" + doc_id + "/";
var cell_layer_url = "/datainput/fetchcelllayers/" + doc_id + "/";

initdatasources();
initnotifications();
inittablelist();
initlayout();
$('#formEditLayout').jqxLayout({ theme: theme, width: '99%', height: '98%', layout: layout });
init_fc_extarbuttons();
initextarbuttons();
firefullscreenevent();
</script>