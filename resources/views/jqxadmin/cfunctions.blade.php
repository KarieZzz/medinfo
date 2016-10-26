@extends('jqxadmin.app')

@section('title', 'Функции контроля отчетных форм')
@section('headertitle', 'Менеджер функций контроля')

@section('content')
    @include('jqxadmin.table_picker')
    <div>
        <div id="functionList" style="margin: 10px"></div>
        <div id="propertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div class="panel-heading"><h3>Редактирование/ввод функции контроля</h3></div>
            <div class="panel-body">
                <form id="form" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="level">Уровень:</label>
                        <div class="col-sm-2">
                            <div id="level" style="padding-left: 12px"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="script">Текст функции:</label>
                        <div class="col-sm-8">
                            <textarea rows="5" class="form-control" id="script"></textarea>
                        </div>
                    </div>
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
    <script src="{{ asset('/medinfo/admin/cfunctionadmin.js') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var tableDataAdapter;
        var formsDataAdapter;
        var functionsDataAdapter;
        var tablesource;
        var rowsource;
        var columnsource;
        var tablefetch_url = '/admin/rc/fetchtables/';
        var functionfetch_url = '/admin/cfunctions/fetchcf/';
        var forms = {!! $forms  !!};
        var errorLevels = {!! $error_levels !!};
        var fgrid = $("#functionList");
        var current_form = 0;
        var current_table = 0;
        var current_function = 0;
        initFilterDatasources();
        initdatasources();
        initFunctionList();
        initFormTableFilter();
        initButtons();
        initFunctionActions();
    </script>
@endsection
