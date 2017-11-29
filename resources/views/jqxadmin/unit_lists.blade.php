@extends('jqxadmin.app')

@section('title', 'Списки медицинских организаций для расчетных таблиц')
@section('headertitle', 'Списки медицинских организаций для расчетных таблиц')

@section('content')
    <form style="margin-top: 3px" >
        <div class="form-group row">
            <div class="col-lg-2">
                <h4 class="text-right">Выберите список:</h4>
            </div>
            <div class="col-lg-4">
                <div id="ListContainer"><div id="List"></div></div>
            </div>
            <div class="col-lg-6">
                <button id="edit" type="button" class="btn btn-primary">Редактировать</button>
                <button id="delete" type="button" class="btn btn-danger">Удалить</button>
                <button id="create" type="button" class="btn btn-success">Создать</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-lg-6">
            <button id="RemoveSelected" type="button" class="btn btn-default pull-right">Удалить</button>
            <button id="RemoveAll" type="button" class="btn btn-default pull-right">Удалить все</button>
        </div>
        <div class="col-lg-6">
            <button id="AddSelected" type="button" class="btn btn-default">Добавить</button>
            <button id="AddAll" type="button" class="btn btn-default">Добавить все</button>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">Состав списка: <span id="ListName"></span></div>
                <div class="panel-body"><div id="ListTerms"></div></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">Перечень медицинских организаций, не включенных в список</div>
                <div class="panel-body"><div id="Units"></div></div>
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
    <script src="{{ asset('/medinfo/admin/unitlistadmin.js?v=000') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let lists = {!! $lists !!};
        let list = $("#List");
        let listterms = $("#ListTerms");
        let units = $("#Units");
        let currentlist = 0;
        let list_url = 'admin/units/lists';
        let member_url = '/admin/units/fetchlistmembers/';
        let units_url = '/admin/units/nonmembers/';
        initList();
        initListMembers();
        initUnitsNonmembers();
    </script>
@endsection
