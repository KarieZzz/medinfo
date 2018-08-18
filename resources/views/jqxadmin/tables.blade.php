@extends('jqxadmin.app')

@section('title', 'Таблицы отчетных форм')
@section('headertitle', 'Менеджер таблиц отчетных форм')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="tableList" style="margin: 10px"></div>
    </div>
    <div id="formContainer">
        <div id="propertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div class="panel-heading"><h3>Редактирование/ввод таблицы отчетной формы</h3></div>
            <div class="panel-body">
                <form id="form" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="table_name">Заголовок:</label>
                        <div class="col-sm-8">
                            <textarea rows="3" class="form-control" id="table_name"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="form_id">Входит в форму:</label>
                        <div class="col-sm-2">
                            <div id="form_id"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="table_index">Порядковый номер в форме:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="table_index" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="table_code">Код таблицы:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="table_code">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3" for="transposed">Таблица транспонирована:</label>
                        <div class="col-sm-8">
                            <div id="transposed"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-3" for="medstat_code">Код Медстат:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="medstat_code">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="medinfo_id">Медстат НСК Id:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="medstatnsk_id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="excluded">Исключена из текущего альбома:</label>
                        <div class="col-sm-8">
                            <div id="excluded"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="placebefore">Переместить/вставить перед:</label>
                        <div class="col-sm-3">
                            <input type="number" class="form-control" id="placebefore" placeholder="порядковый номер">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3"></label>
                        <div class="col-sm-8">
                            <button type="button" id="top" class="btn btn-sm btn-default">В начало </button>
                            <button type="button" id="up" class="btn btn-sm btn-default">Вверх</button>
                            <button type="button" id="down" class="btn btn-sm btn-default">Вниз</button>
                            <button type="button" id="bottom" class="btn btn-sm btn-default">В конец</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-7">
                            <button type="button" id="save" class="btn btn-primary">Сохранить/Создать</button>
                            <button type="button" id="create" class="btn btn-success">Очистить форму</button>
                            <button type="button" id="delete" class="btn btn-danger">Удалить таблицу</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
        <div class="well well-lg">
            <div class="row">
                <div class="col-sm-2"><strong>Текущий альбом:</strong></div>
                <div class="col-sm-10">
                    <strong><a href="albums" target="_blank" class="text-primary text-bold album-name" title="Изменить текущий альбом">"{{ $default_album->album_name }}"</a></strong>
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
    <script src="{{ asset('/medinfo/admin/tableadmin.js?v=019') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let rowsDataAdapter;
        let tableDataAdapter;
        let formsDataAdapter;
        let rowid = null;
        let nextAction = 'POST';
        let tlist = $("#tableList");
        let forms = {!! $forms  !!};
        let store_url = '/admin/tables';
        let update_url = '/admin/tables/update/';
        let up_url = 'tables/up/';
        let down_url = 'tables/down/';
        let top_url = 'tables/top/';
        let bottom_url = 'tables/bottom/';
        initfilterdatasources();
        initsplitter();
        initdatasources();
        inittablelist();
        initformactions();
        initOrderControls();
        disableOrderButtons();
    </script>
@endsection
