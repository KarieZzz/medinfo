@extends('jqxadmin.app')

@section('title', 'Формирование справки')
@section('headertitle', 'Быстрая справка - формирование')

@section('content')
    <div id="formContainer">
        <div id="queryPropertiesForm" class="panel panel-default" >
            <div class="panel-heading"><h3>Формирование справки</h3></div>
            <div class="panel-body">
                <form class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="formList">Выберите форму:</label>
                        <div class="col-sm-3">
                            <div id="formList"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="tableList">Выберите таблицу:</label>
                        <div class="col-sm-3">
                            <div id="tableListContainer"><div id="tableList"></div></div>
                        </div>
                        <div class="col-sm-6">
                            <div id="tableSelected"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="groupMode">Гуппировать по:</label>
                        <div class="col-sm-3">
                            <div id="groupMode"></div>
                        </div>
                        <div class="col-sm-6">
                            <div id="modeSelected"><div class="text-bold text-info" style="margin-left: -100px">Текущий режим группировки "по строке"</div></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="pattern_id">Выбор строк:</label>
                        <div class="col-sm-3">
                            <div id="rowListContainer"><div id="rowList"></div></div>
                        </div>
                        <div class="col-sm-6">
                            <div id="rowSelected"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="medinfo_id">Выбор граф:</label>
                        <div class="col-sm-3">
                            <div id="columnListContainer"><div id="columnList"></div></div>
                        </div>
                        <div class="col-sm-6">
                            <div id="columnSelected"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                            <button type="button" id="make" class="btn btn-default">Сформировать справку</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('loadjsscripts')
    <script src="{{ asset('/jqwidgets/jqxdata.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxpanel.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxscrollbar.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxinput.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxbuttons.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownbutton.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxcheckbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxswitchbutton.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.sort.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/tablepicker.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/composequickquery.js?v=005') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var tableDataAdapter;
        var formsDataAdapter;
        var rowsDataAdapter;
        var columnsDataAdapter;
        var tablesource;
        var rowsource;
        var columnsource;
        var rows = [];
        var columns = [];
        var tablefetch_url = '/admin/rc/fetchtables/';
        var rowfetch_url = '/admin/rc/fetchrows/';
        var columnfetch_url = '/admin/rc/fetchcolumns/';
        var output_url = '/reports/br/output';
        var forms = {!! $forms  !!};
        var flist = $("#formList");
        var tlist = $("#tableList");
        var rlist = $("#rowList");
        var clist = $("#columnList");
        var modebutton = $("#groupMode");
        var current_form = 0;
        var current_table = 0;
        var groupmode = 1; // по умолчанию группируем по строке
        initFilterDatasources();
        initdatasources();
        initRowList();
        initColumnList();
        initFormTableFilter();
        initButtons();
        initRowActions();
    </script>
@endsection
