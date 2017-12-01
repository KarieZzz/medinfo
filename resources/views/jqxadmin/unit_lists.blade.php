@extends('jqxadmin.app')

@section('title', 'Списки медицинских организаций для расчетных таблиц')
@section('headertitle', 'Списки медицинских организаций для расчетных таблиц')

@section('content')
    <form style="margin-top: 3px" >
        <div class="form-group row">
            <div class="col-lg-2">
                <h4 class="text-right">Выберите список:</h4>
            </div>
            <div class="col-lg-2">
                <div id="ListContainer"><div id="List"></div></div>
            </div>
            <div class="col-lg-8">
                <button id="edit" type="button" class="btn btn-primary">Редактировать список</button>
                <button id="delete" type="button" class="btn btn-danger">Удалить</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-lg-6">
            <button id="RemoveSelected" type="button" class="btn btn-danger pull-right">Удалить</button>
            <button id="RemoveAll" type="button" class="btn btn-default pull-right">Удалить все</button>
        </div>
        <div class="col-lg-6">
            <button id="AddSelected" type="button" class="btn btn-info">Добавить</button>
            <button id="AddAll" type="button" class="btn btn-default">Добавить все</button>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">Состав списка: <span id="ListName"></span></div>
                <div class="panel-body" style="height: calc(100vh - 200px)"><div id="ListTerms"></div></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">Перечень медицинских организаций, не включенных в список</div>
                <div class="panel-body" style="height: calc(100vh - 200px)"><div id="Units"></div></div>
            </div>
        </div>
    </div>
    <div id="ListEdit">
        <div id="ListEditHeader">
            <span id="headerContainer" style="float: left">Редактирование/ввод списка учреждений</span>
        </div>
        <div>
            <div style="padding: 15px" class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-sm-3" for="name">Наименование</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="name">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3" for="slug">Псевдоним</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="slug">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="button" class="btn btn-primary" id="update">Сохранить</button>
                        <button type="button" class="btn btn-success" id="create">Создать</button>
                        <button type="button" class="btn" id="cancel">Отменить</button>
                    </div>
                </div>
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
    <script src="{{ asset('/jqwidgets/jqxgrid.edit.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    {{--<script src="{{ asset('/medinfo/admin/tablepicker.js?v=008') }}"></script>--}}
    <script src="{{ asset('/medinfo/admin/unitlistadmin.js?v=003') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let list = $("#List");
        let listterms = $("#ListTerms");
        let units = $("#Units");
        let listeditform = $('#ListEdit');
        let listbutton =$("#ListContainer");
        let currentlist = 0;
        let list_url = '/admin/units/lists';
        let lists = '/admin/units/fetchlists';
        let member_url = '/admin/units/fetchlistmembers/';
        let addmembers_url = '/admin/units/addlistmembers/';
        let removemembers_url = '/admin/units/removelistmembers/';
        let units_url = '/admin/units/nonmembers/';
        initList();
        initListMembers();
        initUnitsNonmembers();
        initActions();
        initeditlistwindow();
    </script>
@endsection
