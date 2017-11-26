@extends('jqxadmin.app')

@section('title', 'Управление мониторингами')
@section('headertitle', 'Менеджер мониторингов')

@section('content')
<div id="mainSplitter" >
    <div>
        <div id="monitoringList" style="margin: 10px"></div>
    </div>
    <div id="formContainer">
        <div id="PropertiesForm" class="panel panel-default">
            <div class="panel-heading"><h3>Данные мониторинга</h3></div>
            <div class="panel-body">
                <form id="form" class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="name">Наименование мониторинга:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="periodicity">Периодичность:</label>
                        <div class="col-sm-3">
                            <div id="periodicity" style="padding-left: 12px"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="accumulation">Накопление данных:</label>
                        <div class="col-sm-3">
                            <div id="accumulation"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="album">Использует альбом форм:</label>
                        <div class="col-sm-3">
                            <div id="album" style="padding-left: 12px"></div>
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
    <script src="{{ asset('/jqwidgets/jqxswitchbutton.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxcheckbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/monitoringadmin.js?v=010') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        let preiodicityDataAdapter;
        let albumDataAdapter;
        let periodicities = {!! $periodicities !!};
        let albums = {!! $albums !!};
        let monitoringinsert_url = '/admin/monitorings';
        let monitoringupdate_url = '/admin/monitorings/';
        let fetchmonitoring_url = '/admin/monitorings/fetchlist/';
        let mlist = $('#monitoringList');
        initsplitter();
        initMonitoringList();
        initbuttons();
        initactions();
    </script>
@endsection
