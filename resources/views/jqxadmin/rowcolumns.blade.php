@extends('jqxadmin.app')

@section('title', 'Строки и графы отчетных форм')
@section('headertitle', 'Менеджер строк м граф отчетных форм')

@section('content')
    @include('jqxadmin.table_picker')
<div id="mainSplitter" >
    <div>
        <div id="rowList" style="margin: 10px"></div>
        <div id="rowPropertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div class="panel-heading"><h3>Редактирование/ввод строки</h3></div>
            <div class="panel-body">
                <form id="rowform" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="row_index">Порядковый номер в таблице:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="row_index">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="row_name">Имя:</label>
                        <div class="col-sm-8">
                            <textarea rows="3" class="form-control" id="row_name"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="row_code">Код:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="row_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="row_medstat_code">Код Медстат:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="row_medstat_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="row_medinfo_id">Мединфо Id:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="row_medinfo_id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="excludedRow">Исключена из текущего альбома:</label>
                        <div class="col-sm-8">
                            <div id="excludedRow"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-7">
                            <button type="button" id="saverow" class="btn btn-default">Сохранить изменения</button>
                            <button type="button" id="insertrow" class="btn btn-default">Вставить новую запись</button>
                            <button type="button" id="deleterow" class="btn btn-danger">Удалить запись</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div>
        <div id="columnList" style="margin: 10px"></div>
        <div id="columnPropertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div class="panel-heading"><h3>Редактирование/ввод графы</h3></div>
            <div class="panel-body">
                <form id="columnform" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="column_index">Порядковый номер в таблице:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="column_index">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="column_name">Имя:</label>
                        <div class="col-sm-8">
                            <textarea rows="2" class="form-control" id="column_name"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="content_type">Тип поля:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="content_type">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="size">Размер поля (*10px):</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="size">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="decimal_count">Знаков после запятой (десятичных):</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="decimal_count">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="column_medstat_code">Код Медстат:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="column_medstat_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="column_medinfo_id">Мединфо Id:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="column_medinfo_id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="excludedColumn">Исключена из текущего альбома:</label>
                        <div class="col-sm-8">
                            <div id="excludedColumn"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-7">
                            <button type="button" id="savecolumn" class="btn btn-default">Сохранить изменения</button>
                            <button type="button" id="insertcolumn" class="btn btn-default">Вставить новую запись</button>
                            <button type="button" id="deletecolumn" class="btn btn-danger">Удалить запись</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
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
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/tablepicker.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/rcadmin.js?v=002') }}"></script>
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
        var tablefetch_url = '/admin/rc/fetchtables/';
        var rowfetch_url = '/admin/rc/fetchrows/';
        var columnfetch_url = '/admin/rc/fetchcolumns/';
        var forms = {!! $forms  !!};
        var rlist = $("#rowList");
        var clist = $("#columnList");
        var current_form = 0;
        var current_table = 0;
        initFilterDatasources();
        initsplitter();
        initdatasources();
        initRowList();
        initColumnList();
        initFormTableFilter();
        initButtons();
        initRowActions();
        initColumnActions();
    </script>
@endsection
