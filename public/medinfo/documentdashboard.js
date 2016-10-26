/**
 * Created by shameev on 28.06.2016.
 */
// Инициализация источников данных для таблиц
var datasources = function() {
    mo_source =
    {
        dataType: "json",
        dataFields: [
            { name: 'id', type: 'int' },
            { name: 'parent_id', type: 'int' },
            { name: 'unit_code', type: 'string' },
            { name: 'unit_name', type: 'string' }
        ],
        hierarchy:
        {
            keyDataField: { name: 'id' },
            parentDataField: { name: 'parent_id' }
        },
        id: 'id',
        root: '',
        url: mo_tree_url + current_top_level_node
    };
    docsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'unit_code', type: 'string' },
            { name: 'unit_name', type: 'string' },
            { name: 'form_code', type: 'string' },
            { name: 'form_name', type: 'string' },
            { name: 'period', type: 'string' },
            { name: 'state', type: 'string' },
            { name: 'filled', type: 'bool' }
        ],
        id: 'id',
        url: docsource_url+'&ou='+current_top_level_node+'&states='+checkedstates.join()+'&forms='+checkedforms.join()+'&periods='+checkedperiods.join(),
        root: 'data'
    };
    aggregate_source =
    {
        datatype: "json",
        datafields: [
            {name: 'id', type: 'int'},
            {name: 'unit_code', type: 'string'},
            {name: 'unit_name', type: 'string'},
            {name: 'form_code', type: 'string'},
            {name: 'form_name', type: 'string'},
            {name: 'period', type: 'string'},
            {name: 'aggregated_at', type: 'string'},
            { name: 'filled', type: 'bool' }
        ],
        id: 'id',
        url: aggrsource_url + '&ou=' + current_top_level_node + '&forms=' + checkedforms.join()+'&periods='+checkedperiods.join(),
        root: 'data'
    }
    mo_dataAdapter = new $.jqx.dataAdapter(mo_source);
    dataAdapter = new $.jqx.dataAdapter(docsource, {
        loadError: function(jqXHR, status, error) {
            if (jqXHR.status == 401) {
                raiseError('Пользователь не авторизован', jqXHR);
            }
        }
    });
    aggregate_report_table = new $.jqx.dataAdapter(aggregate_source);
};
var checkformfilter = function() {
    checkedforms = [];
    var checkedItems = $("#formsListbox").jqxListBox('getCheckedItems');
    var formcount = checkedItems.length;
    for (i=0; i < formcount; i++) {
        checkedforms.push(checkedItems[i].value);
    }
};
var checkstatefilter = function() {
    checkedstates = [];
    var checkedItems = $("#statesListbox").jqxListBox('getCheckedItems');
    var statecount = checkedItems.length;
    for (i=0; i < statecount; i++) {
        checkedstates.push(checkedItems[i].value);
    }
};
var checkperiodfilter = function() {
    checkedperiods = [];
    var checkedItems = $("#periodsListbox").jqxListBox('getCheckedItems');
    var periodcount = checkedItems.length;
    for (i=0; i < periodcount; i++) {
        checkedperiods.push(checkedItems[i].value);
    }
};
// обновление таблиц первичных и сводных документов в зависимости от выделенных форм, периодов, статусов документов
var updatedocumenttable = function() {
    var old_doc_url = docsource.url;
    var old_aggr_url = aggregate_source.url;
    var states = checkedstates.join();
    var forms = checkedforms.join();
    var periods = checkedperiods.join();
    var new_filter =  '&ou=' +current_top_level_node +'&states='+states+'&forms='+forms+'&periods=' + periods;
    var new_doc_url = docsource_url + new_filter;
    var new_aggr_url = aggrsource_url + new_filter;
    if (new_doc_url != old_doc_url) {
        docsource.url = new_doc_url;
        dgrid.jqxGrid('updatebounddata');
        $("#DocumentMessages").html('');
        $("#DocumentAuditions").html('');
    }
    if (new_aggr_url != old_aggr_url) {
        aggregate_source.url = new_aggr_url;
        agrid.jqxGrid('updatebounddata');
    }
};
// выполнение сведения данных
var aggregatedata = function() {
    var rowindex = agrid.jqxGrid('getselectedrowindex');
    var row_id = agrid.jqxGrid('getrowid', rowindex);
    var rowdata = agrid.jqxGrid('getrowdata', rowindex);
    if (rowindex == -1) {
        return false;
    }
    //var data = "aggregate=" + row_id;
    $.ajax({
        dataType: 'json',
        url: aggregatedata_url + row_id,
        method: "PATCH",
        //data: data,
        success: function (data, status, xhr) {
            if (data.affected_cells) {
                if (data.affected_cells > 0) {
                    raiseInfo("Сведение данных завершено");
                    rowdata.aggregated_at = data.aggregated_at;
                    agrid.jqxGrid('updaterow', row_id, rowdata);
                }
                else {
                    raiseError("Сведение данных не выполнено! Отсутствуют данные в первичных документах");
                }
            }
            else {
                if (data.aggregate_status == 500) {
                    raiseError("Сведение данных не выполнено! " + data.error_message);
                }
            }
        },
        error: function (xhr, status, errorThrown) {
            raiseError("Ошибка сведения данных на сервере.  Обратитесь к администратору", xhr);
        }
    });
};
// Установка класса для обозначения заполненных/пустых документов
var filledFormclass = function (row, columnfield, value, rowdata) {
    if (rowdata.filled) {
        return 'filledForm';
    }
};
// Установка класса для раскрашивания строк в зависимости от статуса документа
var formStatusclass = function (row, columnfield, value, rowdata) {
    if (value == statelabels.performed) {
        return 'editedStatus';
    } else if (value == statelabels.prepared) {
        return 'preparedStatus';
    } else if (value == statelabels.accepted) {
        return 'acceptedStatus';
    }  else if (value == statelabels.approved) {
        return 'approvedStatus';
    } else if (value == statelabels.declined) {
        return 'declinedStatus';
    }
};
// фильтр для быстрого поиска по наименованию учреждения - первичные документы
var mo_name_filter = function (needle) {
    var rowFilterGroup = new $.jqx.filter();
    var filter_or_operator = 1;
    var filtervalue = needle;
    var filtercondition = 'contains';
    var nameRecordFilter = rowFilterGroup.createfilter('stringfilter', filtervalue, filtercondition);
    rowFilterGroup.addfilter(filter_or_operator, nameRecordFilter);
    dgrid.jqxGrid('addfilter', 'unit_name', rowFilterGroup);
    dgrid.jqxGrid('applyfilters');
};
// фильтр для быстрого поиска по наименованию учреждения/территории - сводные документы
var mo_name_aggrfilter = function (needle) {
    var rowFilterGroup = new $.jqx.filter();
    var filter_or_operator = 1;
    var filtervalue = needle;
    var filtercondition = 'contains';
    var nameRecordFilter = rowFilterGroup.createfilter('stringfilter', filtervalue, filtercondition);
    rowFilterGroup.addfilter(filter_or_operator, nameRecordFilter);
    agrid.jqxGrid('addfilter', 'unit_name', rowFilterGroup);
    agrid.jqxGrid('applyfilters');
};
// Рендеринг панели инструментов для таблицы первичных документов
var rendertoolbar = function (toolbar) {
    var me = this;
    var container = $("<div style='margin: 5px;'></div>");
    var input1 = $("<input class='jqx-input jqx-widget-content jqx-rc-all' id='searchField' type='text' style='height: 23px; float: left; width: 150px;' />");
    var input2 = $("<input id='clearfilters' type='button' value='Очистить фильтр'/>");

    if (audit_permission) {
        var input5 = $("<input id='ChangeAudutStatus' type='button' value='Проверка отчета' />");
        input5.click(function () {
            var rowindex = dgrid.jqxGrid('getselectedrowindex');
            if (rowindex == -1) {
                return false;
            }
            var radiostates = $('.auditstateradio');
            radiostates.jqxRadioButton('uncheck');
            //radiostates.each(function() {
                //$(this).jqxRadioButton('disable');
                //$(this).jqxRadioButton('uncheck');
                //$("#SaveAuditState").jqxButton({disabled: true });
            //});
            $.each(current_document_audits, function(key, value) {
                if (value.auditor_id == current_user_id) {
                    //$("#SaveAuditState").jqxButton({disabled: false });
                    //radiostates.jqxRadioButton('enable');
                    switch (value.state_id) {
                        case 1 :
                            $("#noaudit").jqxRadioButton('check');
                            break;
                        case 2:
                            $("#audit_correct").jqxRadioButton('check');
                            break;
                        case 3:
                            $("#audit_incorrect").jqxRadioButton('check');
                            break;
                    }
                }
            });
            var offset = dgrid.offset();
            $("#changeAuditStateWindow").jqxWindow({ position: { x: parseInt(offset.left) + 150, y: parseInt(offset.top) + 100 } });
            $("#changeAuditStateWindow").jqxWindow('open');
        });
    }

    var editform = $("<i style='margin-left: 2px;height: 14px' class='fa fa-edit fa-lg' title='Редактировать форму' />");
    var excel_export = $("<i style='margin-left: 2px;height: 14px' class='fa fa-file-excel-o fa-lg' title='Экспортировать документ в MS Excel'></i>");
    var message_input = $("<i style='margin-left: 2px;height: 14px' class='fa fa-commenting-o fa-lg' title='Сообщение/комментарий к документу'></i>");
    var refresh_list = $("<i style='margin-left: 2px;height: 14px' class='fa fa-refresh fa-lg' title='Обновить список'></i>");
    var changestatus = $("<input id='ChangeStatus' type='button' value='Статус отчета' />");

    toolbar.append(container);
    container.append(input1);
    container.append(input2);

    if (current_user_role != 2) {
        container.append(changestatus);
    }
    if (audit_permission) {
        container.append(input5);
        input5.jqxButton({ theme: theme });
    }
    container.append(editform);
    container.append(message_input);
    container.append(excel_export);
    //container.append(excel_file);
    container.append(refresh_list);
    input1.addClass('jqx-widget-content-' + theme);
    input1.addClass('jqx-rc-all-' + theme);
    input1.jqxInput({ width: 200, placeHolder: "Медицинская организация" });
    input2.jqxButton({ theme: theme });
    editform.jqxButton({ theme: theme });
    changestatus.jqxButton({ theme: theme });
    excel_export.jqxButton({ theme: theme });
    //excel_file.jqxButton({ theme: theme });
    message_input.jqxButton({ theme: theme });
    refresh_list.jqxButton({ theme: theme });
    var oldVal = "";
    input1.on('keydown', function (event) {
        if (input1.val().length >= 2) {
            if (me.timer) {
                clearTimeout(me.timer);
            }
            if (oldVal != input1.val()) {
                me.timer = setTimeout(function () {
                    mo_name_filter(input1.val());
                }, 500);
                oldVal = input1.val();
            }
        }
        else {
            dgrid.jqxGrid('removefilter', '1');
        }
    });
    input2.click(function () { dgrid.jqxGrid('clearfilters'); input1.val('');});
    editform.click(function () {
        var rowindex = dgrid.jqxGrid('getselectedrowindex');
        if (rowindex !== -1 && typeof rowindex !== 'undefined') {
            var document_id = dgrid.jqxGrid('getrowid', rowindex);
            var editWindow = window.open(edit_form_url + '/' + document_id);
        }
    });
    changestatus.click(function () {
        var rowindex = dgrid.jqxGrid('getselectedrowindex');
        var this_document_state ='';
        if (rowindex == -1 && typeof rowindex !== 'undefined') {
            return false;
        }
        var offset = dgrid.offset();
        $("#changeStateWindow").jqxWindow({ position: { x: parseInt(offset.left) + 100, y: parseInt(offset.top) + 100 } });
        var data = dgrid.jqxGrid('getrowdata', rowindex);
        var radiostates = $('.stateradio');
        radiostates.each(function() {
            var state = $(this).attr('id');
            if ($.inArray(state, disabled_states) !== -1) {
                $(this).jqxRadioButton('disable');
            }
            if (statelabels[state] == data.state) {
                $(this).jqxRadioButton('check');
                this_document_state = state;
            }
        });
        if (current_user_role == 1 && this_document_state !== 'performed' && this_document_state !== 'declined') {
            $('#prepared').jqxRadioButton('disable');
        } else if (current_user_role == 1 && (this_document_state == 'performed' || this_document_state == 'declined')) {
            $('#prepared').jqxRadioButton('enable');
        }
        if ((current_user_role == 3 || current_user_role ==4) && this_document_state == 'performed') {
            $('#declined').jqxRadioButton('disable');
        } else if ((current_user_role == 3 || current_user_role ==4) && this_document_state !== 'performed') {
            $('#declined').jqxRadioButton('enable');
        }
        var message = $("#statusChangeMessage").val('');
        $("#changeStateWindow").jqxWindow('open');
    });
    message_input.click(function () {
        var rowindex = dgrid.jqxGrid('getselectedrowindex');
        if (rowindex == -1) {
            return false;
        }
        $("#message").val("");
        var offset = dgrid.offset();
        $("#sendMessageWindow").jqxWindow({ position: { x: parseInt(offset.left) + 100, y: parseInt(offset.top) + 100 } });
        $("#sendMessageWindow").jqxWindow('open');
    });
    excel_export.click(function () {
        var rowindex = dgrid.jqxGrid('getselectedrowindex');
        var document_id = dgrid.jqxGrid('getrowid', rowindex);
        if (rowindex !== -1) {
            window.open(export_form_url + document_id);
        }
    });
    /*                excel_file.click(function () {
     var rowindex = $('#Documents').jqxGrid('getselectedrowindex');
     var document_id = $('#Documents').jqxGrid('getrowid', rowindex);
     if (rowindex !== -1) {
     var editWindow = window.open(export_form_url+'document='+document_id);
     }
     });*/
    refresh_list.click(function () {
        var states = checkedstates.join();
        var forms = checkedforms.join();
        var periods = checkedperiods.join();
        var new_filter =  '&ou=' +current_top_level_node +'&states='+states+'&forms='+forms+'&periods=' + periods;
        var new_doc_url = docsource_url + new_filter;
        docsource.url = new_doc_url;
        dgrid.jqxGrid('updatebounddata');
        $("#DocumentMessages").html('');
        $("#DocumentAuditions").html('');
    });
};
// рендеринг панели инструментов для таблицы сводных документов
var renderaggregatetoolbar = function(toolbar) {
    var me = this;
    var container = $("<div style='margin: 5px;'></div>");
    var input1 = $("<input class='jqx-input jqx-widget-content jqx-rc-all' id='searchField' type='text' style='height: 23px; float: left; width: 150px;' />");
    var filter = $("<i style='margin-left: 2px;height: 14px' class='fa fa-filter fa-lg' title='Очистить фильтр' />");
    var editform = $("<i style='margin-left: 2px;height: 14px' class='fa fa-eye fa-lg' title='Просмотр/редактирование сводного отчета' />");
    var makeaggregation = $("<i style='margin-left: 2px;height: 14px' class='fa fa-database fa-lg' title='Выполнить свод' />");
    if (audit_permission) {
        var change_audit_status = $("<input id='ChangeAudutStatus' type='button' value='Проверка отчета' />");
        change_audit_status.click(function () {
            var rowindex = agrid.jqxGrid('getselectedrowindex');
            if (rowindex == -1) {
                return false;
            }
            var radiostates = $('.auditstateradio');
            radiostates.each(function() {
                $(this).jqxRadioButton('disable');
                $(this).jqxRadioButton('uncheck');
                $("#SaveAuditState").jqxButton({disabled: true });
            });
            $.each(current_document_audits, function(key, value) {
                if (value.auditor_id == current_user_id) {
                    $("#SaveAuditState").jqxButton({disabled: false });
                    radiostates.each(function() {
                        $(this).jqxRadioButton('enable');
                    });
                    switch (value.state_id) {
                        case '1' :
                            $("#noaudit").jqxRadioButton('check');
                            break;
                        case '2':
                            $("#audit_correct").jqxRadioButton('check');
                            break;
                        case '3':
                            $("#audit_incorrect").jqxRadioButton('check');
                            break;
                    }
                }
            });
            var offset = agrid.offset();
            $("#BatchChangeAuditStateWindow").jqxWindow({ position: { x: parseInt(offset.left) + 150, y: parseInt(offset.top) + 100 } });
            $("#BatchChangeAuditStateWindow").jqxWindow('open');
        });
    }
    var excel_export = $("<i style='margin-left: 2px;height: 14px' class='fa fa-file-excel-o fa-lg' title='Экспортировать документ в MS Excel'></i>");
    var refresh_list = $("<i style='margin-left: 2px;height: 14px' class='fa fa-refresh fa-lg' title='Обновить список'></i>");


    toolbar.append(container);
    container.append(input1);
    container.append(filter);
    container.append(editform);
    container.append(makeaggregation);
    if (audit_permission) {
        container.append(change_audit_status);
        change_audit_status.jqxButton({ theme: theme });
    }
    container.append(excel_export);
    container.append(refresh_list);
    input1.addClass('jqx-widget-content-' + theme);
    input1.addClass('jqx-rc-all-' + theme);
    input1.jqxInput({ width: 200, placeHolder: "МО/Территория" });
    filter.jqxButton({ theme: theme });
    editform.jqxButton({ theme: theme });
    makeaggregation.jqxButton({ theme: theme });
    excel_export.jqxButton({ theme: theme });
    refresh_list.jqxButton({ theme: theme });
    var oldVal = "";
    input1.on('keydown', function (event) {
        if (input1.val().length >= 2) {
            if (me.timer) {
                clearTimeout(me.timer);
            }
            if (oldVal != input1.val()) {
                me.timer = setTimeout(function () {
                    mo_name_aggrfilter(input1.val());
                }, 500);
                oldVal = input1.val();
            }
        }
        else {
            agrid.jqxGrid('removefilter', '1');
        }
    });
    filter.click(function () { agrid.jqxGrid('clearfilters'); input1.val('');});
    editform.click(function () {
        var rowindex = agrid.jqxGrid('getselectedrowindex');
        var document_id = agrid.jqxGrid('getrowid', rowindex);
        if (rowindex !== -1) {
            var editWindow = window.open(edit_aggregate_url+'/'+document_id);
        }
    });

    makeaggregation.click( function() {
            aggregatedata();
    });

    excel_export.click(function () {
        var rowindex = agrid.jqxGrid('getselectedrowindex');
        var document_id = agrid.jqxGrid('getrowid', rowindex);
        if (rowindex !== -1) {
            window.open(export_form_url + document_id);
        }
    });
    refresh_list.click(function () {
        var forms = checkedforms.join();
        var periods = checkedperiods.join();
        var new_filter =  '&ou=' +current_top_level_node +'&forms='+forms+'&periods=' + periods;
        aggregate_source.url = aggrsource_url + new_filter;
        agrid.jqxGrid('updatebounddata');
    });
};
// инициализация дерева Территорий/Медицинских организаций
var initmotree = function() {
    $("#moTree").jqxTreeGrid(
        {
            width: '100%',
            height: '100%',
            theme: theme,
            source: mo_dataAdapter,
            selectionMode: "singleRow",
            filterable: true,
            filterMode: "simple",
            localization: localize(),
            columnsResize: true,
            ready: function()
            {
                // expand row with 'EmployeeKey = 32'
                $("#moTree").jqxTreeGrid('expandRow', 0);
            },
            columns: [
                { text: 'Код', dataField: 'unit_code', width: 120 },
                { text: 'Наименование', dataField: 'unit_name', width: 545 }
            ]
        });
    $('#moTree').on('filter',
        function (event)
        {
            var args = event.args;
            var filters = args.filters;
            $('#moTree').jqxTreeGrid('expandAll');
        }
    );
    $('#moTree').on('rowSelect',
        function (event)
        {
            var args = event.args;
            var key = args.key;
            var new_top_level_node = key;
            if (new_top_level_node == current_top_level_node) {
                return false;
            }
            current_top_level_node =  key;
            updatedocumenttable();
            return true;
        }
    );
};
// инициализация вкладок-фильтров с элементами управления
var initfiltertabs = function() {
    $("#filtertabs").jqxTabs({  height: '100%', width: '100%', theme: theme });
    var forms_source =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'form_code' }
        ],
        id: 'id',
        localdata: forms
    };
    formsDataAdapter = new $.jqx.dataAdapter(forms_source);
    $("#formsListbox").jqxListBox({
        theme: theme,
        source: formsDataAdapter,
        displayMember: 'form_code',
        valueMember: 'id',
        checkboxes: true,
        filterable:true,
        filterPlaceHolder: 'Фильтр',
        width: 150,
        height: 370
    });
    $("#formsListbox").jqxListBox('checkAll');
    $("#formsListbox").on('click', function () {
        checkformfilter();
        updatedocumenttable();
    });
    $("#checkAllForms").jqxCheckBox({ width: 170, height: 20, theme: theme, checked: true});
    $('#checkAllForms').on('checked', function (event) {
        $("#formsListbox").jqxListBox('checkAll');
        checkformfilter($("#formsListbox").jqxListBox('getCheckedItems'));
        updatedocumenttable();
    });
    $('#checkAllForms').on('unchecked', function (event) {
        $("#formsListbox").jqxListBox('uncheckAll');
        checkformfilter($("#formsListbox").jqxListBox('getCheckedItems'));
        updatedocumenttable();
    });
    var states_source =
    {
        datatype: "array",
        datafields: [
            { name: 'code' },
            { name: 'name' }
        ],
        id: 'code',
        localdata: states
    };
    statesDataAdapter = new $.jqx.dataAdapter(states_source);
    $("#statesListbox").jqxListBox({
        theme: theme,
        source: statesDataAdapter,
        displayMember: 'name',
        valueMember: 'code',
        checkboxes: true,
        width: 230,
        height: 200
    });
    $("#statesListbox").jqxListBox('checkAll');
    $("#statesListbox").on('click', function () {
        checkstatefilter();
        updatedocumenttable();
    });
    $("#checkAllStates").jqxCheckBox({ width: 170, height: 20, theme: theme, checked: true});
    $('#checkAllStates').on('checked', function (event) {
        $("#statesListbox").jqxListBox('checkAll');
        checkstatefilter();
        updatedocumenttable();
    });
    $('#checkAllStates').on('unchecked', function (event) {
        $("#statesListbox").jqxListBox('uncheckAll');
        checkstatefilter();
        updatedocumenttable();
    });
    $("#formcheckboxesPanel").jqxPanel({ width: '100%', height: '98%'});
    var periods_source =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'name' }
        ],
        id: 'id',
        localdata: periods
    };
    periodsDataAdapter = new $.jqx.dataAdapter(periods_source);
    $("#periodsListbox").jqxListBox({
        theme: theme,
        source: periodsDataAdapter,
        displayMember: 'name',
        valueMember: 'id',
        checkboxes: true,
        width: 230,
        height: 200
    });
    //var item = $("#periodsListbox").jqxListBox('getItem', 0) ;
    for (var i=0; i < checkedperiods.length; i++  ) {
        $("#periodsListbox").jqxListBox('checkItem', checkedperiods[i] );
    }
    $("#periodsListbox").on('click', function () {
        checkperiodfilter();
        updatedocumenttable();
    });

};
// инициализация вкладок с документами
var initdocumentstabs = function() {
    $("#documenttabs").jqxTabs({  height: '100%', width: '100%', theme: theme });
    dgrid.jqxGrid(
        {
            width: '100%',
            height: '100%',
            source: dataAdapter,
            localization: localize(),
            theme: theme,
            columnsresize: true,
            showtoolbar: true,
            rendertoolbar: rendertoolbar,
            columns: [
                { text: '№', datafield: 'id', width: '5%', cellclassname: filledFormclass },
                { text: 'Код МО', datafield: 'unit_code', width: 70 },
                { text: 'Наименование МО', datafield: 'unit_name', width: '25%' },
                { text: 'Код формы', datafield: 'form_code', width: 80 },
                { text: 'Наименование формы', datafield: 'form_name', width: '20%' },
                { text: 'Период', datafield: 'period', width: 120 },
                { text: 'Статус', datafield: 'state', width: 120, cellclassname: formStatusclass },
                { text: 'Данные', datafield: 'filled', columntype: 'checkbox', width: 120 }
            ]
        });
    dgrid.on('rowselect', function (event)
    {
        var row = event.args.row;
        var murl = docmessages_url + 'document=' + row.id;
        current_document_form_code = row.form_code;
        current_document_form_name = row.form_name;
        current_document_ou_name = row.unit_name;
        current_document_state = row.state;
        $.getJSON( murl, function( data ) {
            if (data.responce == 0) {
                $("#DocumentMessages").html("Нет сообщений для данного документа");
            }
            else {
                var items = [];
                $.each( data, function( key, val ) {
                    var m = "<tr>";
                    m += "<td style='width: 20%'>" + val.created_at + "</td>";
                    m += "<td style='width: 20%'>" + val.worker.description + "</td>";
                    m += "<td>" + val.message + "</td>"
                    m +="</tr>"

                    items.push(m);
                });
                $("#DocumentMessages").html("<table class='control_result print_list' style='width: 100%'>" + items.join( "" ) + "</table>");
            }
        });

        var aurl = docauditions_url + 'document=' + row.id;
        current_document_audits = [];
        $.getJSON( aurl, function( data ) {
            if (data.responce == 0) {
                $("#DocumentAuditions").html("Нет результатов проверки данного отчетного документа");
            }
            else {
                var items = [];
                $.each( data, function( key, val ) {
                    current_document_audits.push({ auditor_id: val.worker.id, state_id: val.state_id});
                    var audit_class = '';
                    switch (val.state_id) {
                        case 1:
                            audit_class = 'noaudit';
                            break;
                        case 2:
                            audit_class = 'valid';
                            break;
                        case 3:
                            audit_class = 'invalid';
                            break;
                    }
                    var d = val.created_at ? val.created_at : '';
                    items.push("<tr class='"+ audit_class +"'><td style='width: 50%'>" + val.created_at + "<br /> " + val.worker.description + "</td><td>" + val.dicauditstate.name + "</td></tr>");
                });
                $("#DocumentAuditions").html("<table class='control_result' style='width: 100%'>" + items.join( "" ) + "</table>");
            }
        });
    });
    dgrid.on('rowdoubleclick', function (event)
    {
        var args = event.args;
        var rowindex = args.rowindex;
        var document_id = dgrid.jqxGrid('getrowid', rowindex);
        var editWindow = window.open(edit_form_url + '/' + document_id);
    });
    agrid.jqxGrid(
        {
            width: '100%',
            height: '93%',
            theme: theme,
            source: aggregate_report_table,
            columnsresize: true,
            showtoolbar: true,
            rendertoolbar: renderaggregatetoolbar,
            localization: localize(),
            columns: [
                { text: '№', datafield: 'id', width: '5%' },
                { text: 'Код Территории/МО', datafield: 'unit_code', width: 100 },
                { text: 'Наименование МО', datafield: 'unit_name', width: '20%' },
                { text: 'Код формы', datafield: 'form_code', width: 100 },
                { text: 'Наименование формы', datafield: 'form_name', width: '20%' },
                { text: 'Период', datafield: 'period', width: 150 },
                { text: 'Сведение', datafield: 'aggregated_at', width: 150 },
                { text: 'Данные', datafield: 'filled', columntype: 'checkbox', width: 120 }
            ]
        });
    agrid.on('rowdoubleclick', function (event)
    {
        var args = event.args;
        var rowindex = args.rowindex;
        var document_id = agrid.jqxGrid('getrowid', rowindex);
        var editWindow = window.open(edit_aggregate_url + '/' + document_id);
    });
};
// инициализация вкладок с сообщениями и проверками к документу
var initdocumentproperties = function() {
    $('#DocumentPropertiesSplitter').jqxSplitter({
        width: '100%',
        height: '95%',
        theme: theme,
        orientation: 'vertical',
        panels: [{ size: '70%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
    });
    $("#messagesExpander").jqxExpander({toggleMode: 'none', showArrow: false, width: "100%", height: "100%", theme: theme  });
    $("#openMessagesListWindow").on('click', function(event) {
        var print_style = "<style>.printlist { font-size: 0.8em; } .control_result { border: 1px solid #7f7f7f; width: 400;";
        print_style += "border-collapse: collapse; margin-bottom: 10px; } .control_result td { border: 1px solid #7f7f7f; } </style>";
        var link_to_print ="<a href='#' onclick='window.print()'>Распечатать</a>";
        var header = "<h3>Комментарии к форме №" + current_document_form_code + " " + current_document_form_name;
        header += " по учреждению: " + current_document_ou_name +"</h3>";
        var pWindow = window.open("", "messagesWindow", "width=900, height=600, scrollbars=yes");
        pWindow.document.write(print_style + link_to_print + header + $("#DocumentMessages").html());
    });
    $("#openAuditionListWindow").on('click', function(event) {
        var print_style = "<style>.printlist { font-size: 0.8em; } .control_result { border: 1px solid #7f7f7f; width: 400;";
        print_style += "border-collapse: collapse; margin-bottom: 10px; } .control_result td { border: 1px solid #7f7f7f; } </style>";
        var link_to_print ="<a href='#' onclick='window.print()'>Распечатать</a>";
        var header = "<h3>Перечень проверок формы №" + current_document_form_code + " " + current_document_form_name;
        header += " по учреждению: " + current_document_ou_name +"</h3>";
        var pWindow = window.open("", "messagesWindow", "width=900, height=600, scrollbars=yes");
        pWindow.document.write(print_style + link_to_print + header + $("#DocumentAuditions").html());
    });
    $("#auditExpander").jqxExpander({toggleMode: 'none', showArrow: false, width: "100%", height: "100%", theme: theme  });
};
// инициализация всплывающих окон с формами ввода сообщения и т.д.
var initpopupwindows = function() {
    $("#changeStateWindow").jqxWindow({
        width: 430,
        height: 360,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelStateChanging"),
        theme: theme
    });
    $("#performed").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#prepared").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#accepted").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#declined").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#approved").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $('#statusChangeMessage').jqxTextArea({ placeHolder: 'Оставьте свой комментарий к смене статуса документа', height: 90, width: 400, minLength: 1 });
    $("#CancelStateChanging").jqxButton({ theme: theme });
    $("#SaveState").jqxButton({ theme: theme });
    $("#SaveState").click(function () {
        var rowindex = dgrid.jqxGrid('getselectedrowindex');
        var rowdata = dgrid.jqxGrid('getrowdata', rowindex);
        var row_id = dgrid.jqxGrid('getrowid', rowindex);
        var message = $("#statusChangeMessage").val();
        var radiostates = $('.stateradio');
        var selected_state;
        radiostates.each(function() {
            if ($(this).jqxRadioButton('checked')) {
                selected_state = $(this).attr('id');
            }
        });
        if (statelabels[selected_state] == current_document_state ) {
            return false;
        }
        var data = "&document=" + row_id + "&state=" + selected_state + "&message=" + message;
        $.ajax({
            dataType: 'json',
            url: changestate_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (data.status_changed == 1) {
                    $("#currentInfoMessage").html("Статус документа изменен. <br /> Новый статус: \"" + statelabels[data.new_status] + '"');
                    $("#infoNotification").jqxNotification("open");
                    rowdata.state = statelabels[data.new_status];
                    dgrid.jqxGrid('updaterow', row_id, rowdata);
                    dgrid.jqxGrid('selectrow', rowindex);
                }
                else {
                    $("#currentError").text("Статус не изменен!");
                    $("#serverErrorNotification").jqxNotification("open");
                    // TODO: Обработать ошибку изменения статуса
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
/*                $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                    + status + ". Обратитесь к администратору.");
                $("#serverErrorNotification").jqxNotification("open");*/
            }
        });
        $("#changeStateWindow").jqxWindow('hide');
    });
    $("#changeAuditStateWindow").jqxWindow({
        width: 430,
        height: 300,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelAuditStateChanging"),
        theme: theme
    });
    $("#noaudit").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#audit_incorrect").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#audit_correct").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $('#auditChangeMessage').jqxTextArea({
        placeHolder: 'Оставьте свои замечания по заполнению отчетного документа',
        height: 100,
        width: 400,
        minLength: 1
    });
    $("#SaveAuditState").jqxButton({ theme: theme });
    $("#CancelAuditStateChanging").jqxButton({ theme: theme });
    $("#SaveAuditState").click(function () {
        var rowindex = dgrid.jqxGrid('getselectedrowindex');
        var row_id = dgrid.jqxGrid('getrowid', rowindex);
        var radiostates = $('.auditstateradio');
        var message = $("#auditChangeMessage").val();
        var old_state;
        $.each(current_document_audits, function(key, value) {
            if (value.auditor_id == current_user_id) {
                old_state = value.state_id;
            }
        });
        var selected_state;
        radiostates.each(function() {
            if ($(this).jqxRadioButton('checked')) {
                selected_state = $(this).attr('id');
            }
        });
        var data = "&document=" + row_id + "&auditstate=" + selected_state + "&message=" + message;
        if (audit_state_ids[selected_state] != old_state) {
            $.ajax({
                dataType: 'json',
                url: changeaudition_url,
                method: 'POST',
                data: data,
                success: function (data, status, xhr) {
                    if (data.audit_status_changed == 1) {
                        $("#currentInfoMessage").text("Статус проверки документа изменен");
                        $("#infoNotification").jqxNotification("open");
                        dgrid.jqxGrid('selectrow', rowindex);
                    }
                    else {
                        $("#currentError").text("Статус проверки документа не изменен!");
                        $("#serverErrorNotification").jqxNotification("open");
                    }
                },
                error: function (xhr, status, errorThrown) {
                    $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                        + status + ". Обратитесь к администратору.");
                    $("#serverErrorNotification").jqxNotification("open");
                }
            });
        }
        $("#changeAuditStateWindow").jqxWindow('hide');
    });
    $("#BatchChangeAuditStateWindow").jqxWindow({
        width: 450,
        height: 330,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelBatchAuditStateChanging"),
        theme: theme,
        modalOpacity: 0.01
    });
    $("#nobatchaudit").jqxRadioButton({ width: 250, height: 25, checked: true, theme: theme });
    $("#batch_audit_incorrect").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $("#batch_audit_correct").jqxRadioButton({ width: 250, height: 25, theme: theme });
    $('#AuditBatchChangeMessage').jqxTextArea({ placeHolder: 'Оставьте свой комментарий к смене статуса документов', height: 100, width: 400, minLength: 1 });
    $("#SaveBatchAuditState").jqxButton({ theme: theme });
    $("#CancelBatchAuditStateChanging").jqxButton({ theme: theme });
    $("#SaveBatchAuditState").click(function () {
        var rowindex = agrid.jqxGrid('getselectedrowindex');
        var row_id = agrid.jqxGrid('getrowid', rowindex);
        var radiostates = $('.batchauditstateradio');
        var message = $("#AuditBatchChangeMessage").val();
        var selected_state;
        radiostates.each(function() {
            if ($(this).jqxRadioButton('checked')) {
                selected_state = $(this).attr('id');
            }
        });
        var data = "aggregate=" + row_id + "&batchauditstate=" + selected_state + "&message=" + message;
        $.ajax({
            dataType: 'json',
            url: 'change_batch_audit_state.php',
            data: data,
            success: function (data, status, xhr) {
                var comment = data.responce.comment;
                if (data.responce.audit_status_changed > 0) {
                    var m = "Статус проверки для всех входящих в сводный отчет документов ("+ data.responce.audit_status_changed + ") изменен"
                    $("#currentInfoMessage").text(m);
                    $("#infoNotification").jqxNotification("open");
                    dgrid.jqxGrid('selectrow', rowindex);
                }
                else if (data.responce.audit_status_changed == 0) {
                    $("#currentError").text("Статус проверки документов не изменен! " + comment);
                    $("#serverErrorNotification").jqxNotification("open");
                } else if (data.responce.audit_status_changed == -1) {
                    $("#currentError").text("Ошибка при сохранении данных на сервере. Обратитесь к администратору");
                    $("#serverErrorNotification").jqxNotification("open");
                }
            },
            error: function (xhr, status, errorThrown) {
                $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                    + status + ". Обратитесь к администратору.");
                $("#serverErrorNotification").jqxNotification("open");
            }
        });
        $("#BatchChangeAuditStateWindow").jqxWindow('hide');
    });
// Комментирование документа/отправка сообщения к документу
    $('#message').jqxTextArea({ placeHolder: 'Оставьте свой комментарий к выбранному документу', height: 150, width: 400, minLength: 1 });
    $("#CancelMessage").jqxButton({ theme: theme });
    $("#SendMessage").jqxButton({ theme: theme });
    $("#SendMessage").click(function () {
        var rowindex = dgrid.jqxGrid('getselectedrowindex');
        var rowdata = dgrid.jqxGrid('getrowdata', rowindex);
        var row_id = dgrid.jqxGrid('getrowid', rowindex);
        var message = $("#message").val();
        message = message.trim();
        if (message.length == 0) {
            return false;
        }
        var data = "&document=" + row_id + "&message=" + message;
        $.ajax({
            dataType: 'json',
            url: docmessagesend_url,
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                var m = '';
                if (data.message_sent == 1) {
                    $("#currentInfoMessage").text("Сообщение сохранено");
                    $("#infoNotification").jqxNotification("open");
                    dgrid.jqxGrid('selectrow', rowindex);
                }
            },
            error: function (xhr, status, errorThrown) {
                $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                    + status + ". Обратитесь к администратору.");
                $("#serverErrorNotification").jqxNotification("open");
            }
        });
        $("#sendMessageWindow").jqxWindow('hide');
    });
    $("#sendMessageWindow").jqxWindow({
        width: 430,
        height: 260,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelMessage"),
        theme: theme
    });
};
