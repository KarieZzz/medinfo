@extends('jqxadmin.app')

@section('title', 'Формирование справки')
@section('headertitle', 'Быстрая справка - формирование')

@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Ошибка!</strong> Не все необходимые поля заполнены.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div id="formContainer">
        <div id="queryPropertiesForm" class="panel panel-default" >
            <div class="panel-heading"><h3>Формирование справки</h3></div>
            <div class="panel-body">
                <form class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="formList">Выберите период:</label>
                        <div class="col-sm-3">
                            <div id="periodList"></div>
                        </div>
                        <div class="col-sm-6">
                            <div id="periodSelected"><div class="text-bold text-info" style="margin-left: -100px">Текущий период (по умолчанию): "{{ $last_year->name }}" </div></div>
                        </div>
                    </div>
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
                        <label class="control-label col-sm-3" for="groupMode">Группировать по:</label>
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
                        <label class="control-label col-sm-3" for="level">Ограничение по территории/группе:</label>
                        <div class="col-sm-3">
                            <div id="levelListContainer"><div id="levelList"></div></div>
                        </div>
                        <div class="col-sm-6">
                            <div id="levelSelected"><div class="text-bold text-info" style="margin-left: -100px">Все организации</div></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="level">Объединение данных:</label>
                        <div class="col-sm-3">
                            <label class="radio-inline">
                                <input type="radio" id="primary" name="aggregate" value="1" checked="checked" >Нет
                            </label>
                            <label class="radio-inline">
                                <input type="radio" id="legacy" name="aggregate" value="2">Юридические лица
                            </label>
                            <label class="radio-inline">
                                <input type="radio" id="territory" name="aggregate" value="3">Территории
                            </label>
                        </div>
                        <div class="col-sm-6">
                            <div id="aggregateNotice"><div class="text-bold text-info" style="margin-left: -100px">Возможно только при отсутствии ограничений по территориям/группам</div></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="level">Формат вывода:</label>
                        <div class="col-sm-3">
                            <label class="radio-inline">
                                <input type="radio" id="html" name="output" value="1" checked="checked" >html
                            </label>
                            <label class="radio-inline">
                                <input type="radio" id="excel" name="output" value="2">excel
                            </label>
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
    <script src="{{ asset('/medinfo/admin/composequickquery.js?v=012') }}"></script>
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
        var rowfetch_url = '/reports/br/fetchrows/';
        var columnfetch_url = '/reports/br/fetchcolumns/';
        var output_url = '/reports/br/output';
        var periods = {!! $periods !!};
        var forms = {!! $forms !!};
        var levels = {!! $upper_levels  !!};
        var plist = $("#periodList");
        var flist = $("#formList");
        var tlist = $("#tableList");
        var rlist = $("#rowList");
        var clist = $("#columnList");
        var levellist = $("#levelList");
        var modebutton = $("#groupMode");
        var current_period = {{ $last_year->id }};
        var current_form = 0;
        var current_table = 0;
        var current_level = 0;
        var current_type = 1; // по территории - 1, по группе - 2
        var groupmode = 1; // по умолчанию группируем по строке
        var aggregate = 1; // По умолчанию вывод по первичным документам, 2 - по юрлицам, 3 - по территориям
        var output = 1; // По умолчанию вывод в html
        initFilterDatasources();
        initdatasources();
        initRowList();
        initColumnList();
        initFormTableFilter();
        initButtons();
        initActions();
    </script>
@endsection
