@extends('jqxadmin.app')

@section('title', '<h2>Администрирование: отчетные документы</h2>')
@section('headertitle', 'Менеджер документов')
@section('local_actions')
{{--<li><a href="#" id="newdocument">Новый документ</a></li>--}}

@endsection

@section('content')
<div id="mainSplitter" class="jqx-widget">
    <div>
        <div id="leftPanel">
            <div>
                <h4>Территории/Медицинские организации</h4>
                <div id="moTreeContainer">
                    <div id="moTree"></div>
                </div>
            </div>
            <div id="filtertabs">
                <ul>
                    <li style="margin-left: 30px;" class="header-name">Формы</li>
                    <li>Статусы</li>
                    <li>Периоды</li>
                    <li>Типы</li>
                </ul>
                <div>
                    <h4>Формы</h4>
                    <div id="formsListbox" style="float: left; margin-right: 30px"></div>
                    <div id="selectedFormBox">
                        <div id="checkAllForms"><span>Выбрать все формы</span></div>
                    </div>
                </div>
                <div>
                    <h4>Статусы</h4>
                    <div id="statesListbox" style="float: left; margin-right: 30px"></div>
                    <div id="checkAllStates"><span>Выбрать все статусы</span></div>
                </div>
                <div>
                    <h4>Периоды</h4>
                    <div id="periodsListbox" style="margin: 10px"></div>
                </div>
                <div>
                    <h4>Типы документов</h4>
                    <div id="dtypesListbox" style="margin: 10px"></div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div id="rightPanel">
            <div id="documentContainer">
                <h4 style="margin-left: 10px">Документы</h4>
                <div id="documentList" class="box" style="padding-bottom: 3px">  </div>
            </div>
            <div id="actionPanel">
                <h4 style="margin-left: 10px">Действия с выделенными документами</h4>
                <div class="row">
                    <div style='float: left; padding-left: 20px; margin-left: 20px;' id='statesDropdownList'></div>
                    <input class='jqx-input jqx-widget-content jqx-rc-all' id='changeStates' type='text' value='Сменить статус' style='height: 25px; float: left; width: 150px; margin-left: 10px;' />
                </div>
                <div class="row">
                    <input class='jqx-input jqx-widget-content jqx-rc-all' id='deleteDocuments' type='text' value='Удалить' style='height: 25px; float: left; width: 150px;' />
                    <input class='jqx-input jqx-widget-content jqx-rc-all' id='eraseData' type='text' value='Очистить данные' style='height: 25px; float: left; width: 150px;' />
                </div>
            </div>
        </div>
    </div>
    <div id="newForm">
        <div id="newFormHeader">
            <span id="headerContainer" style="float: left">Новые документы для отмеченных территорий/учреждений</span>
        </div>

        <div>
            <form class="form-horizontal">
{{--                <div class="form-group">
                    <label class="control-label col-sm-3" for="ou">Территория/учреждение</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="ou" placeholder="Выберите ОЕ">
                    </div>
                </div>--}}
                <div class="form-group">
                    <label class="control-label col-sm-3" for="selectForm">Код формы</label>
                    <div class="col-sm-6">
                        <div id="selectForm"></div>
                       {{-- <input type="text" class="form-control" id="form_code" placeholder="Введите код формы">--}}
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3" for="period">Период</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="period" placeholder="Введите отчетный период">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-3" for="period">Исходный статус</label>
                    <div class="col-sm-6">
                        <div id="selectState"></div>
                        {{--<input type="text" class="form-control" id="status" placeholder="Статус">--}}
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-8">
                        <div class="checkbox">
                            <label><input type="checkbox"> Первичные</label>
                            <label><input type="checkbox"> Сводные</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-7">
                        <button id="saveButton" class="btn btn-default">Создать</button>
                        <button id="cancelButton" class="btn btn-default">Отменить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('loadjsscripts')
    <script src="{{ asset('/jqwidgets/jqxsplitter.js') }}"></script>
    <script src="{{ asset('/jqwidgets/jqxtabs.js') }}"></script>
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
    <script src="{{ asset('/jqwidgets/jqxwindow.js') }}"></script>
    <script src="{{ asset('/jqwidgets/localization.js') }}"></script>
    <script src="{{ asset('/medinfo/admin/documentadmin.js') }}"></script>
@endpush

@section('inlinejs')
    @parent
    <script type="text/javascript">
        var docsource_url = '/admin/fetchdocuments?';
        var deletedocuments_url = '/admin/deletedocuments';
        var erasedocuments_url = '/admin/erasedocuments';
        var changestate_url = '/admin/documentstatechange';
        var current_top_level_node = 0;
        var checkeddtypes = {!! $dtype_ids !!};
        var forms = {!! $forms  !!};
        var states = {!! $states !!};
        var periods = {!! $periods !!};
        var dtypes = {!! $dtypes !!};
        var checkedforms = {!! $form_ids !!};
        var checkedstates = {!! $state_ids !!};
        var checkedperiods = [{!! $period_ids !!}];
        var checkeddtypes = {!! $dtype_ids !!};

        datasources();
        initfilterdatasources();
        initnewdocumentwindow();
        initsplitters();
        initmotree();

        initfiltertabs();
        initdocumentslist();
        initnotifications();
        initdocumentactions();

        $("#statesDropdownList").jqxDropDownList({
            theme: theme,
            source: changestateDA,
            displayMember: "name",
            valueMember: "code",
            placeHolder: "Изменение статуса документов:",
            //selectedIndex: 2,
            width: 250,
            height: 25
        });

    </script>
@endsection
