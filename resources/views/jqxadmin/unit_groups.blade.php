@extends('jqxadmin.app')

@section('title', 'Группы медицинских организаций')
@section('headertitle', 'Менеджер групп организационных единиц')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="unitGroupList" style="margin: 10px"></div>
        <div class="panel panel-default">
            <div class="panel-heading"><h4>Состав группы</h4></div>
            <div class="panel-body">
                <div id="memberList"></div>
            </div>
        </div>
    </div>
    <div id="formContainer">
        <div id="propertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div class="panel-heading"><h3>Редактирование/ввод группы</h3></div>
            <div class="panel-body">
                <form id="form" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="group_name">Наименование группы:</label>
                        <div class="col-sm-8">
                            <textarea rows="2" class="form-control" id="group_name"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="group_code">Код группы:</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="group_code" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="slug">Псевдоним:</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="slug" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="parent_id">Входит в состав:</label>
                        <div class="col-sm-2">
                            <div id="parent_id"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-7">
                            <button type="button" id="save" class="btn btn-primary">Сохранить изменения</button>
                            <button type="button" id="insert" class="btn btn-success">Вставить новую запись</button>
                            <button type="button" id="delete" class="btn btn-danger">Удалить запись</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="insertMembersForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div class="panel-heading"><h4>Перечень медицинских организаций, не включенных в список</h4></div>
            <div class="panel-body">
                <div style="height: 380px" id="moTreeContainer">
                    <div id="Units"></div>
                </div>
            </div>
        </div>
        <div class="col-sm-offset-2 col-sm-7">
            <button type="button" id="insertmembers" class="btn btn-success">Добавить учреждения в группу</button>
            <button type="button" id="removemember" class="btn btn-danger">Удалить учреждения из группы</button>
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
    <script src="{{ asset('/medinfo/admin/unitgroupadmin.js?v=009') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let currentgroup = 0;
        let groups = {!! $groups !!};
        let membersource;
        let units_url = '/admin/units/fetchgroupnonmembers/';
        let unitgroup_url ='/admin/units/fetchgroups';
        let motree_url ='/admin/fetch_mo_tree/';
        let member_url ='/admin/units/fetchmembers/';
        let groupcreate_url = '/admin/units/groupcreate';
        let groupupdate_url = '/admin/units/groupupdate/';
        let groupdelete_url = '/admin/units/groupdelete/';
        let addmembers_url = '/admin/units/addmembers/';
        let removemembers_url = '/admin/units/removemember/';
        let unitGroupDataAdapter;
        let memberDataAdapter;
        let mo_dataAdapter;
        let grouplist =  $('#unitGroupList');
        let units = $("#Units");
        let memberlist =  $('#memberList');
        let parentid = $("#parent_id");
        initsplitter();
        initdatasources();
        //initmotree();
        initUnitsNonmembers();
        inittablelist();
        initdropdowns();
        initgroupactions();
        initmemberactions();
    </script>
@endsection
