@extends('jqxadmin.app')

@section('title', '<h2>Администрирование: строки и графы</h2>')
@section('headertitle', 'Менеджер строк м граф отчетных форм')

@section('content')
    <div class="row col-sm-offset-1">
            <form class="form-inline">
                <div class="form-group">
                    <div id="form_id"></div>
                </div>
                <div class="form-group">
                    <div id="table_id"></div>
                </div>
        </form>

    </div>
<div id="mainSplitter" >
    <div>
        <div id="rowList" style="margin: 10px"></div>
        <div id="propertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div class="panel-heading"><h3>Редактирование/ввод строки</h3></div>
            <div class="panel-body">
                <form id="form" class="form-horizontal" >
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
                        <label class="control-label col-sm-3" for="medstat_code">Код Медстат:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="medstat_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="medinfo_id">Мединфо Id:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="medinfo_id">
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
    <div id="formContainer">

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
    <script src="{{ asset('/medinfo/admin/rcadmin.js') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var tableDataAdapter;
        var formsDataAdapter;
        var rowsource;
        var rowfetch_url = '/admin/fetchrows/';
        var forms = {!! $forms  !!};
        var tables = {!! $tables !!};

        var current_table = 0;
        initfilterdatasources();
        initsplitter();
        initdatasources();
        initRowList();
        initFormTableFilter();
        initrowactions();
    </script>
@endsection
