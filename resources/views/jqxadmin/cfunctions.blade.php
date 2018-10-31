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
                            <textarea rows="7" class="form-control" id="script" spellcheck="false"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="comment">Комментарий:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="comment">
                        </div>
                    </div>
                    <div class="form-inline col-md-offset-2">
                        <div class="form-group  col-md-2 " >
                            <label class="control-label" for="blocked">Функция отключена:</label>
                        </div>
                        <div class="col-md-1" style="margin-right: 50px"><div id="blocked"></div></div>
                        <div class="form-group col-md-6" id="scopeContainer" style="display: none">
                            <label class="control-label" for="scope">Область выполнения функции:</label>
                            <select class="form-control" id="scope">
                                <option value="0">По умолчанию</option>
                                <option value="1">Все разрезы формы</option>
                                <option value="2">Текущий разрез формы</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row" style="margin-top: 50px">
                            <div class="col-sm-offset-1 col-sm-4">
                                <button type="button" id="save" class="btn btn-primary">Сохранить изменения</button>
                                <button type="button" id="insert" class="btn btn-success">Вставить новую запись</button>
                                <button type="button" id="delete" class="btn btn-danger">Удалить запись</button>
                                <button type="button" id="selectedcheck" class="btn btn-warning" title="Выборочный контроль"> >> </button>
                            </div>
                            <div class="col-sm-3"><span>Перекомпилировать все функции: </span>
                                <button type="button" id="recompileTable" class="btn btn-default">В таблице</button>
                                <button type="button" id="recompileForm" class="btn btn-default">В форме</button>
                            </div>
                            <div class="col-sm-3"><span>Экспорт: </span>
                                <button type="button" id="excelExport" class="btn btn-default">Excel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/medinfo/admin/cfunctionadmin.js?v=018') }}"></script>
@endpush

@section('inlinejs')
    @parent
    @include('jqxadmin.table_pickerjs')
    <script type="text/javascript">
        let functionsDataAdapter;
        let rowsource;
        let columnsource;
        let functionfetch_url = '/admin/cfunctions/fetchcf/';
        let recompileTable_url = '/admin/cfunctions/recompiletable/';
        let recompileForm_url = '/admin/cfunctions/recompileform/';
        let excelExport_url = '/admin/cfunctions/excelexport/';
        let errorLevels = {!! $error_levels !!};
        let fgrid = $("#functionList");
        let sc = $("#scopeContainer");
        //let current_form = 0;
        //let current_table = 0;
        let current_function = 0;
        initdatasources();
        initFunctionList();
        initButtons();
        initFunctionActions();
    </script>
@endsection
