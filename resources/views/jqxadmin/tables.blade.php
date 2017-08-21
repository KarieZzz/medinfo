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
                            <input type="text" class="form-control" id="table_index">
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
                        <div class="col-sm-2">
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
                        <label class="control-label col-sm-3" for="medinfo_id">Мединфо Id:</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" id="medinfo_id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="excluded">Исключена из текущего альбома:</label>
                        <div class="col-sm-8">
                            <div id="excluded"></div>
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
    <script src="{{ asset('/medinfo/admin/tableadmin.js?v=003') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let rowsDataAdapter;
        let tableDataAdapter;
        let formsDataAdapter;
        let tlist = $("#tableList");
        let forms = {!! $forms  !!};
        initfilterdatasources();
        initsplitter();
        initdatasources();
        inittablelist();
        initformactions();
    </script>
@endsection
