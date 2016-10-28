@extends('jqxadmin.app')

@section('title', 'Методики контроля отчетных форм Мединфо (внутритабличные)')
@section('headertitle', 'Менеджер методик контроля Медифно (старый формат)')

@section('content')
    @include('jqxadmin.table_picker')
    <div class="row">
        <div class="col-sm-6">
            <div class="panel-group">
                <div class="panel panel-default">
                    <div class="panel-heading">Контролируемые строки</div>
                    <div class="panel-body">
                        <div id="controlledList" style="margin: 10px"></div>
                    </div>
                </div>
                <div id="propertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
                    <div class="panel-heading"><h3>Дествия с методиками контроля</h3></div>
                    <div class="panel-body">
                        <form id="form" class="form-horizontal" >
                            <div class="form-group">
                                <label class="control-label col-sm-3" for="comment">Комментарий:</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="comment">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-sm-3" for="blocked">Функция отключена:</label>
                                <div class="col-sm-2">
                                    <div id="blocked"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-7">
                                    <button type="button" id="save" class="btn btn-default">Сохранить изменения</button>
                                    <button type="button" id="insert" class="btn btn-default">Вставить новую запись</button>
                                    <button type="button" id="delete" class="btn btn-danger">Удалить запись</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="panel-group">
                <div class="panel panel-default">
                    <div class="panel-heading">Контролирующие строки</div>
                    <div class="panel-body">
                        <div id="controllingList" style="margin: 10px"></div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Контролирующие/Контролируемые графы</div>
                    <div class="panel-body">
                        <div id="columnList" style="margin: 10px"></div>
                    </div>
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
    <script src="{{ asset('/medinfo/admin/micontrolsadmin.js') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var tableDataAdapter;
        var formsDataAdapter;
        var controlledRowsDataAdapter;
        var controllingRowsDataAdapter;
        var columnsDataAdapter;
        var tablesource;
        var rowsource;
        var columnsource;
        var tablefetch_url = '/admin/rc/fetchtables/';
        var controlscope = 1;
        var fetchcontrolledrows_url = '/admin/micontrols/fetchcontrolledrows/';
        var fetchcolumns_url = '/admin/micontrols/fetchcolumns/';
        var fetchcontrollingrows_url = '/admin/micontrols/vtk/fetchcontrollingrows/';
        var forms = {!! $forms  !!};
        var grid = $("#controlledList"); // список контролируемых строк
        var pgrid = $("#controllingList"); // список контролирующих строк
        var cgrid = $("#columnList"); // список контролирующих строк
        var current_form = 0;
        var current_table = 0;
        var current_relation = 0;
        var current_function = 0;
        var current_firstcol = 0;
        var current_countcol = 0;
        initFilterDatasources();
        initdatasources();
        initControlList();
        initFormTableFilter();
        initButtons();
        initFunctionActions();
    </script>
@endsection
