<script type="text/javascript">
let ou_name = "{{ preg_replace('/[\r\n\t]/', '', $current_unit->unit_name) }}";
let ou_code = "{{ $current_unit->unit_code }}";
let doc_id = '{{ $document->id }}';
let doc_type = '{{ $document->dtype }}';
let form_name = '{{ $form->form_name }}';
let form_code = '{{ $form->form_code }}';
let default_album = '{{ $album->id }}';
let current_user_role = '{{ $worker->role }}';
let current_table = '{{ $laststate['currenttable']->id }}';
let current_table_code = '{{ $laststate['currenttable']->table_code }}';
let fgrid; // селектор для сетки с перечнем таблиц
let dgrid; // селектор для сетки с данными таблиц
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
        if (typeof properties.cellsrenderer !== 'undefined') {
            properties.cellsrenderer = cellsrenderer;
        }
    });
    $.each(content.columngroups, function(group, properties) {
        if (typeof properties.rendered !== 'undefined')
            properties.rendered = tooltiprenderer;
    });
});
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
let validate_table_url = "/datainput/tablecontrol/" + doc_id + "/";
let validate_form_url = "/datainput/formcontrol/" + doc_id;
let tabledatacheck_url = "/datainput/dcheck/table/" + doc_id + "/";
let formdatacheck_url = "/datainput/dcheck/form/" + doc_id;
let medstat_control_url = "medstat_control_protocol.php?document=" + doc_id;
let valuechangelog_url = "/datainput/valuechangelog/" + doc_id;
let tableexport_url = "/datainput/tableexport/" + doc_id + "/";
let cell_layer_url = "/datainput/fetchcelllayers/" + doc_id + "/";
let calculatedcells_url = "/datainput/calculate/" + doc_id + "/";
let formlabels =
    {
        compare: 1,
        dependency: 2,
        interannual: 3,
        multiplicity: 4
    };

initdatasources();
initnotifications();
inittablelist();
initlayout();
$('#formEditLayout').jqxLayout({ theme: theme, width: '99%', height: '98%', layout: layout });
init_fc_extarbuttons();
initextarbuttons();
firefullscreenevent();
</script>