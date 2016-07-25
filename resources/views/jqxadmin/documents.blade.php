@extends('jqxadmin.app')

@section('title', '<h2>Менеджер документов</h2>')
@section('headertitle', 'Менеджер документов')


@section('content')
<div id="mainSplitter" >
    <div>
        <div id="leftPanel" style="margin: 10px">
            <div>
                <h4>Территории/Медицинские организации</h4>
                <div id="moTreeContainer">
                    <div id="moTree"></div>
                </div>
            </div>
            <div>
                <h4>Периоды</h4>
                <div id="periodList" style="margin: 10px"></div>
            </div>
        </div>
    </div>
    <div>
        <div id="rightPanel">
            <div id="documentContainer">
                <h4>Документы</h4>
                <div id="documentList" class="box" style="padding-bottom: 3px">  </div>
            </div>
            <div id="actionPanel">
                <h4>Действия с выделенными документами</h4>
                <input class='jqx-input jqx-widget-content jqx-rc-all' id='deleteDocuments' type='text' value='Удалить' style='height: 23px; float: left; width: 150px;' />
                <input class='jqx-input jqx-widget-content jqx-rc-all' id='changeStates' type='text' value='Сменить статус' style='height: 23px; float: left; width: 150px;' />
                Скопировать данные (?)
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
    <script src="{{ asset('/jqwidgets/jqxlistbox.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdropdownlist.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.filter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.columnsresize.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxgrid.selection.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxdatatable.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxtreegrid.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/documentadmin.js') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var docsource_url = '/datainput/fetchdocuments?';
        var deletedocuments_url = '/admin/deletedocuments?';
        var current_top_level_node = 0;
        var checkedform = ['f30','f17','f12','f14','f14дс','f16','f57','f1-РБ','f15','f16-вн','f13','f31','f32','f32_вкл','f19','f1-ДЕТИ','f10','f11','f36','f36-ПЛ','f37','f9','f34','f7','f35','f8','f33','f7-Т','f39','f41', 'f53','f55','f56','f61','f70'];
        var checkedstates = ['st2', 'st4', 'st8', 'st16', 'st32'];
        var checkedperiods = ['pl02345l0'];
        datasources();
        initmotree();
        initdocumentslist();
        $("#mainSplitter").jqxSplitter(
            {
                width: '100%',
                height: '100%',
                theme: theme,
                panels:
                [
                    { size: '40%', min: '10%'},
                    { size: '60%', min: '10%'}
                ]
            }
        );
        $('#leftPanel').jqxSplitter({
            width: '100%',
            height: '100%',
            theme: theme,
            orientation: 'horizontal',
            panels: [{ size: '50%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
        });
        $("#deleteDocuments").jqxButton({ theme: theme });
        $("#deleteDocuments").click(function () {
            var rowindexes = $('#documentList').jqxGrid('getselectedrowindexes');
            indexes_length =  rowindexes.length;
            var row_ids = [];
            for (i = 0; i < indexes_length; i++) {
                row_ids.push($('#documentList').jqxGrid('getrowid', rowindexes[i]));
            }
            //console.log(row_ids);
            var data = "document=" + row_ids;
            $.ajax({
                dataType: 'json',
                url: deletedocuments_url,
                method: "POST",
                data: data,
                success: function (data, status, xhr) {
                    var m = '';
                    if (data.message_sent == 1) {
                        $("#currentInfoMessage").text("Сообщение сохранено");
                        $("#infoNotification").jqxNotification("open");
                        $('#Documents').jqxGrid('selectrow', rowindex);
                    }
                },
                error: function (xhr, status, errorThrown) {
                    $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                            + status + ". Обратитесь к администратору.");
                    $("#serverErrorNotification").jqxNotification("open");
                }
            });
        });
    </script>
@endsection
