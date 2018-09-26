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
                        <label class="control-label col-md-3" for="row_index">Порядковый номер в таблице:</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control input-sm" id="row_index">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="row_name">Имя:</label>
                        <div class="col-md-8">
                            <textarea rows="3" class="form-control input-sm" id="row_name"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="row_code">Код:</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control input-sm" id="row_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="row_medstat_code">Код Медстат:</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control input-sm" id="row_medstat_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="row_medstatnsk_id">Медстат (НСК) Id:</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control input-sm" id="row_medstatnsk_id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="excludedRow">
                            Исключена из текущего альбома <a href="albums" target="_blank" class="text-primary album-name" title="Изменить текущий альбом">("{{ $album->album_name }}")</a>:
                        </label>
                        <div class="col-md-2">
                            <div id="excludedRow"></div>
                        </div>
                        <div class="col-md-7 text-primary"> </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-7">
                            <button type="button" id="saverow" class="btn btn-primary">Сохранить изменения</button>
                            <button type="button" id="insertrow" class="btn btn-success">Вставить новую запись</button>
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
                        <label class="control-label col-md-3" for="column_index">Порядковый номер в таблице:</label>
                        <div class="col-md-2">
                            <input type="number" class="form-control input-sm" id="column_index">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="column_name">Имя:</label>
                        <div class="col-md-8">
                            <textarea rows="2" class="form-control input-sm" id="column_name"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="column_code">Код:</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control input-sm" id="column_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="column_type">Тип поля:</label>
                        <div class="col-md-3">
                            <div id="column_type"></div>
                        </div>
                        <div class="col-md-4">
                            <button id="editFormula" type="button" class="btn btn-primary btn-sm" style="display: none">Добавить/изменить формулу расчета</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="size">Размер поля (px):</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control input-sm" id="field_size" name="field_size">
                        </div>
                        <label class="control-label col-md-3" for="decimal_count">Знаков после запятой (десятичных):</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control input-sm" id="decimal_count">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3" for="column_medstat_code">Код Медстат:</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control input-sm" id="column_medstat_code">
                        </div>
                        <label class="control-label col-md-3" for="column_medstatnsk_id">Медстат (НСК) Id:</label>
                        <div class="col-md-2">
                            <input type="text" class="form-control input-sm" id="column_medstatnsk_id">
                        </div>
                    </div>
                {{--<div class="form-group">

                                            <div class="col-md-7">
                                                <button type="button" id="top" class="btn btn-sm btn-default">В начало </button>
                                                <button type="button" id="up" class="btn btn-sm btn-default">Вверх</button>
                                                <button type="button" id="down" class="btn btn-sm btn-default">Вниз</button>
                                                <button type="button" id="bottom" class="btn btn-sm btn-default">В конец</button>
                                            </div>
                    </div>--}}
                    <div class="form-group">
                        <label class="control-label col-md-3" for="excludedColumn">
                            Исключена из текущего альбома <a href="albums" target="_blank" class="text-primary album-name" title="Изменить текущий альбом">("{{ $album->album_name }}")</a>:
                        </label>
                        <div class="col-md-8">
                            <div id="excludedColumn"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-7">
                            <button type="button" id="savecolumn" class="btn btn-primary">Сохранить изменения</button>
                            <button type="button" id="insertcolumn" class="btn btn-success">Вставить новую запись</button>
                            <button type="button" id="deletecolumn" class="btn btn-danger">Удалить запись</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="formulaWindow">
    <div id="FormHeader">
        <span id="headerContainer" style="float: left">Введите/измените формулу для вычисляемой графы</span>
    </div>
    <div>
        <div style="padding: 15px" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-md-3" for="columnName">Графа:</label>
                <div class="col-md-8">
                    <div id="columnNameId"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3" for="formula">Формула расчета</label>
                <div class="col-md-8">
                    <textarea rows="2" class="form-control" id="formula" placeholder="Введите формулу расчета"></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-offset-3 col-md-6">
                    <button type="button" id="saveFormula" class="btn btn-primary">Сохранить</button>
                    <button type="button" id="cancelButton" class="btn btn-danger">Отменить</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('loadjsscripts')
{{--    <script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
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
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>--}}
    <script src="{{ asset('/medinfo/admin/tablepicker.js?v=014') }}"></script>
    <script src="{{ asset('/medinfo/admin/rcadmin.js?v=037') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let tableDataAdapter;
        let formsDataAdapter;
        let rowsDataAdapter;
        let columnsDataAdapter;
        let tablesource;
        let rowsource;
        let columnsource;
        let rowfetch_url = '/admin/rc/fetchrows/';
        let columnfetch_url = '/admin/rc/fetchcolumns/';
        let showcolumnformula_url = '/admin/rc/columnformula/show/';
        let updatecolumnformula_url = '/admin/rc/columnformula/update/';
        let storecolumnformula_url = '/admin/rc/columnformula/store/';
        let forms = {!! $forms  !!};
        let columnTypes = {!! $columnTypes !!};
        let rlist = $("#rowList");
        let clist = $("#columnList");
        let current_form = 0;
        let current_table = 0;
        initFilterDatasources();
        initsplitter();
        initdatasources();
        initRowList();
        initColumnList();
        initFormTableFilter();
        initButtons();
        initRowActions();
        initColumnActions();
        initColumnFormulaWindow();
    </script>
@endsection
