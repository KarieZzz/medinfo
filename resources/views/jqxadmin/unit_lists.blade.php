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
    <div class="form-group row">
        <div class="col-lg-6">
            <button id="RemoveAll" type="button" class="btn">Очистить список</button>
            <button id="RemoveSelected" type="button" class="btn btn-danger">Удалить</button>
        </div>
        <div class="col-lg-6">
            <button id="AddSelected" type="button" class="btn btn-info">Добавить</button>
            <label class="checkbox-inline"><input id="includeSubLegals" type="checkbox" >Включить входящие подразделения при добавлении в список юрлиц</label>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">Состав списка: <span id="ListSlug" class="text text-info"></span> <span id="ListName"></span></div>
                <div class="panel-body" style="height: calc(100vh - 200px)">
                    <div class="col-lg-9">
                        <div class="radio">
                            <label class="radio-inline"><input type="radio" name="filterMember" value="1">Все</label>
                            <label class="radio-inline"><input type="radio" name="filterMember" value="2">Юридические лица</label>
                            <label class="radio-inline"><input type="radio" name="filterMember" value="3">Обособленные подразделения</label>
                            <label class="radio-inline"><input type="radio" name="filterMember" value="4">Образование и соцзащита</label>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <button id="ApplyMemberFilter" type="button" class="btn btn-default btn-sm pull-right">Установить фильтр</button>
                    </div>
                    <div id="ListTerms"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">Перечень медицинских организаций, не включенных в список</div>
                <div class="panel-body" style="height: calc(100vh - 200px)">
                    <div class="col-lg-9">
                        <div class="radio">
                            <label class="radio-inline"><input type="radio" name="filterNonMember" value="1">Все</label>
                            <label class="radio-inline"><input type="radio" name="filterNonMember" value="2" checked>Юридические лица</label>
                            <label class="radio-inline"><input type="radio" name="filterNonMember" value="3">Обособленные подразделения</label>
                            <label class="radio-inline"><input type="radio" name="filterNonMember" value="4">Образование и соцзащита</label>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <button id="ApplyFilter" type="button" class="btn btn-default btn-sm pull-right">Установить фильтр</button>
                    </div>
                    <div id="Units" style="height: calc(100vh - 200px)"></div>
                </div>
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
                    <label class="control-label col-sm-3" for="onFrontend">Отображать список в перечне фильтров МО:</label>
                    <div class="col-sm-9">
                        <div id="onFrontend"></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="button" class="btn btn-primary" id="update">Сохранить</button>
                        <button type="button" class="btn btn-success" id="createCopy">Создать копию</button>
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
    <script src="{{ asset('/medinfo/admin/unitlistadmin.js?v=018') }}"></script>
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
        let createcopy_url = '/admin/units/lists/createcopy/';
        let lists = '/admin/units/fetchlists';
        let member_url = '/admin/units/fetchlistmembers/';
        let addmembers_url = '/admin/units/addlistmembers/';
        let removemembers_url = '/admin/units/removelistmembers/';
        let removeall_url = '/admin/units/removeall/';
        let units_url = '/admin/units/nonmembers/';
        initList();
        initListMembers();
        initUnitsNonmembers();
        initActions();
        initeditlistwindow();

    </script>
@endsection
