@extends('jqxadmin.app')

@section('title', 'Альбомы отчетных форм')
@section('headertitle', 'Менеджер альбомов отчетных форм')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="AlbumList" style="margin: 10px"></div>
        <div class="panel panel-default">
            <div class="panel-heading"><h4>Формы, входящие в альбом:</h4></div>
            <div class="panel-body" style="height: 100%">
                <div id="MemberList"></div>
                <div class="col-sm-7">
                    <button type="button" id="removemember" class="btn btn-danger">Удалить формы из альбома</button>
                </div>
            </div>
        </div>
    </div>
    <div id="formContainer">
        <div id="propertiesForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div class="panel-heading"><h3>Редактирование/ввод альбома</h3></div>
            <div class="panel-body">
                <form id="form" class="form-horizontal" >
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="album_name">Наименование альбома:</label>
                        <div class="col-sm-8">
                            <textarea rows="3" class="form-control" id="album_name"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="default">По умолчанию:</label>
                        <div class="col-sm-8">
                            <div id="default"></div>
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
        <div id="insertMembersForm" class="panel panel-default" style="padding: 3px; width: 100%">
            <div class="panel-heading"><h4>Добавление форм в выбранный альбом</h4></div>
            <div class="panel-body">
                <div class="form-group">
                    <div style="height: 200px" id="FormContainer">
                        <div id="Forms"></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-7">
                        <button type="button" id="insertmembers" class="btn btn-default">Добавить формы в альбом</button>
                    </div>
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
    <script src="{{ asset('/medinfo/admin/albumadmin.js?v=002') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let currentalbum = 0;
        let membersource;
        let album_url ='/admin/fetchalbums';
        let form_url ='/admin/fetchforms/';
        let member_url ='/admin/albums/fetchformset/';
        let albumcreate_url = '/admin/albums/create';
        let albumupdate_url = '/admin/albums/update/';
        let albumdelete_url = '/admin/albums/delete/';
        let addmembers_url = '/admin/albums/addmembers/';
        let removemember_url = '/admin/albums/removemember/';
        let AlbumDataAdapter;
        let FormDataAdapter;
        let memberDataAdapter;
        let agrid = $("#AlbumList");
        let mlist = $("#MemberList");

        //var mo_dataAdapter;
        initsplitter();
        initdatasources();
        initButtons();
        initformlist();
        inittablelist();
        initalbumactions();
        initmemberactions();
    </script>
@endsection
