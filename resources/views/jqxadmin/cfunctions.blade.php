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
                            <textarea rows="7" class="form-control" id="script"></textarea>
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
                        <div class="row">
                            <div class="col-sm-offset-2 col-sm-4">
                                <button type="button" id="save" class="btn btn-primary">Сохранить изменения</button>
                                <button type="button" id="insert" class="btn btn-success">Вставить новую запись</button>
                                <button type="button" id="delete" class="btn btn-danger">Удалить запись</button>
                            </div>
                            <div class="col-sm-offset-1 col-sm-3"><span>Перекомпилировать все функции: </span>
                                <button type="button" id="recompileTable" class="btn btn-default">В таблице</button>
                                <button type="button" id="recompileForm" class="btn btn-default">В форме</button>
                            </div>
                        </div>
                    </div>
                </form>
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
    <script src="{{ asset('/medinfo/admin/tablepicker.js?v=010') }}"></script>
    <script src="{{ asset('/medinfo/admin/cfunctionadmin.js?v=004') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let tableDataAdapter;
        let formsDataAdapter;
        let functionsDataAdapter;
        let tablesource;
        let rowsource;
        let columnsource;
        let functionfetch_url = '/admin/cfunctions/fetchcf/';
        let recompileTable_url = '/admin/cfunctions/recompiletable/';
        let recompileForm_url = '/admin/cfunctions/recompileform/';
        let forms = {!! $forms  !!};
        let errorLevels = {!! $error_levels !!};
        let fgrid = $("#functionList");
        let current_form = 0;
        let current_table = 0;
        let current_function = 0;
        initFilterDatasources();
        initdatasources();
        initFunctionList();
        initFormTableFilter();
        initButtons();
        initFunctionActions();
    </script>
@endsection
