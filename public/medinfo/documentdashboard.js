/**
 * Created by shameev on 28.06.2016.
 */
let mon_tree_url = 'datainput/fetch_mon_tree/';
let mo_tree_url = 'datainput/fetch_mo_tree/';
let group_tree_url = 'datainput/fetch_ugroups';
let docsource_url = 'datainput/fetchdocuments?';
let recentdocs_url = 'datainput/fetchrecent?';
let docmessages_url = 'datainput/fetchmessages?';
let changestate_url = 'datainput/changestate';
let changeaudition_url = 'datainput/changeaudition';
let docmessagesend_url = 'datainput/sendmessage';
let docauditions_url = 'datainput/fetchauditions?';
let aggrsource_url = 'datainput/fetchaggregates?';
let edit_form_url = 'datainput/formdashboard';
let edit_aggregate_url = 'datainput/aggregatedashboard';
let edit_consolidate_url = 'datainput/consolidatedashboard';
let aggregatedata_url = "/datainput/aggregatedata/";
let export_word_url = "/datainput/wordexport/";
let export_form_url = "/datainput/formexport/";
let consolsource_url = 'datainput/fetchconsolidates?';
let montree = $("#monTree");
let motree = $("#moTree");
let grouptree = $("#groupTree");
let periodTree = $("#periodTree");
let stateList = $("#statesListbox");
let dgrid = $("#Documents"); // сетка для первичных документов
let agrid = $("#Aggregates"); // сетка для сводных документов
let cgrid = $("#Consolidates"); // сетка для консолидированных документов
let rgrid = $("#Recent"); // сетка для последних документов
let mondropdown = $("#monitoringSelector");
let terr = $("#moSelectorByTerritories");
let groups = $('#moSelectorByGroups');
let periodDropDown = $('#periodSelector');
let statusDropDown = $('#statusSelector');
let stateWindow = $('#changeStateWindow');
let current_document_form_code;
let current_document_form_name;
let current_document_ou_name;
let current_document_state;
let currentlet_document_audits = [];
let statelabels =
    {
        performed: 'Выполняется',
        inadvance: 'Подготовлен к предварительной проверке',
        prepared: 'Подготовлен к проверке',
        accepted: 'Принят',
        declined: 'Возвращен на доработку',
        approved: 'Утвержден'
    };
let stateIds =
    {
        'Выполняется' : 'performed',
        'Подготовлен к предварительной проверке' : 'inadvance',
        'Подготовлен к проверке' : 'prepared',
        'Принят' : 'accepted',
        'Возвращен на доработку' : 'declined',
        'Утвержден' : 'approved'
    };

let audit_state_ids =
    {
        noaudit: 1,
        audit_correct: 2,
        audit_incorrect: 3
    };
// Установка разбивки окна на области
initSplitters = function () {
    $("#mainSplitter").jqxSplitter(
        {
            width: '100%',
            height: '95%',
            theme: theme,
            panels:
                [
                    { size: "370px", min: "100px"},
                    { size: '82%', min: "30%"}
                ]
        }
    );
    $('#DocumentPanelSplitter').jqxSplitter({
        width: '100%',
        height: '93%',
        theme: theme,
        orientation: 'horizontal',
        panels: [{ size: '65%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
    });
};
// Инициализация источников данных для таблиц
datasources = function() {
    let mon_source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'parent_id', type: 'int' },
                { name: 'name', type: 'string' }
            ],
            hierarchy:
                {
                    keyDataField: { name: 'id' },
                    parentDataField: { name: 'parent_id' }
                },
            id: 'id',
            root: '',
            url: mon_tree_url
        };
    let mo_source =
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

    let ugroup_source =
    {
        dataType: "json",
        dataFields: [
            { name: 'id', type: 'int' },
            //{ name: 'parent_id', type: 'int' },
            //{ name: 'group_code', type: 'string' },
            { name: 'slug', type: 'string' },
            //{ name: 'group_name', type: 'string' }
            { name: 'name', type: 'string' }
        ],
        hierarchy:
        {
            keyDataField: { name: 'id' },
            parentDataField: { name: 'parent_id' }
        },
        id: 'id',
        root: '',
        url: group_tree_url
    };
    mon_dataAdapter = new $.jqx.dataAdapter(mon_source);
    mo_dataAdapter = new $.jqx.dataAdapter(mo_source);
    ugroup_dataAdapter = new $.jqx.dataAdapter(ugroup_source);

};
// Возвращает выбранные мониторинги и формы для отображения соответствующих отчетов
getCheckedMonsForms = function() {
    let monitorings = [];
    let forms = [];
    let mf = [];
    let checkedRows;
    let uniquemonitorings;
    let uniqueforms;
    checkedRows = montree.jqxTreeGrid('getCheckedRows');
    //console.log(checkedRows);
    if (typeof checkedRows !== 'undefined') {
        for (let i = 0; i < checkedRows.length; i++) {
            mf.push(checkedRows[i].id);
            let r = checkedRows[i].id.toString();
                monitorings.push(r.substr(0,6));
                let form_id = r.substr(6);
                if (form_id) {
                    forms.push(form_id);
                }
        }
    }
    //console.log(forms);
    uniquemonitorings = Array.from(new Set(monitorings));
    uniqueforms = Array.from(new Set(forms));
    if (uniquemonitorings.length > 0 || uniqueforms.length > 0 ) {
        mondropdown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Мониторинги</div>');
    } else {
        mondropdown.jqxDropDownButton('setContent', '<div style="margin: 9px"></i>Мониторинги</div>');
    }
    return {f: uniqueforms, m: uniquemonitorings, mf: mf};
};
// функция удалена
/*checkformfilter = function() {
    checkedforms = [];
    let checkedItems = $("#formsListbox").jqxListBox('getCheckedItems');
    let formcount = checkedItems.length;
    for (i=0; i < formcount; i++) {
        checkedforms.push(checkedItems[i].value);
    }
};*/
checkstatefilter = function() {
    let checkedstates = [];
    let checkedItems = stateList.jqxListBox('getCheckedItems');
    if (typeof checkedItems !== 'undefined') {
        let statecount = checkedItems.length;
        for (i = 0; i < statecount; i++) {
            checkedstates.push(checkedItems[i].value);
        }
    }
    if (checkedstates.length > 0) {
        statusDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Статусы отчетов</div>');
    } else {
        statusDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px"></i>Статусы отчетов</div>');
    }
    return checkedstates.join();
};

checkPeriodFilter = function() {
    let checkedperiods = [];
    let checkedRows = periodTree.jqxTreeGrid('getCheckedRows');
    //console.log(checkedRows);
    if (typeof checkedRows !== 'undefined') {
        for (let i = 0; i < checkedRows.length; i++) {
            checkedperiods.push(checkedRows[i].id);
        }
    }
    if (checkedperiods.length > 0) {
        periodDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Отчетные периоды</div>');
    } else {
        periodDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px"></i>Отчетные периоды</div>');
    }
    return checkedperiods.join();

};
// обновление таблиц первичных и сводных документов в зависимости от выделенных форм, периодов, статусов документов
updatedocumenttable = function() {
    let old_doc_url = docsource.url;
    let old_aggr_url = aggregate_source.url;
    let old_cons_url = consolsource.url;
    //let states = checkedstates.join();
    //let forms = checkedforms.join();
    //let periods = checkedperiods.join();
    let new_filter =  filtersource();
    let new_doc_url = docsource_url + new_filter;
    let new_aggr_url = aggrsource_url + new_filter;
    let new_cons_url = consolsource_url + new_filter;
    if (new_doc_url !== old_doc_url) {
        docsource.url = new_doc_url;
        dgrid.jqxGrid('updatebounddata');
        $("#DocumentMessages").html('');
        $("#DocumentAuditions").html('');
    }
    if (new_aggr_url !== old_aggr_url) {
        aggregate_source.url = new_aggr_url;
        agrid.jqxGrid('updatebounddata');
    }
    if (new_cons_url !== old_cons_url) {
        consolsource.url = new_cons_url;
        cgrid.jqxGrid('updatebounddata');
    }

};
// выполнение сведения данных
aggregatedata = function() {
    let rowindex = agrid.jqxGrid('getselectedrowindex');
    let row_id = agrid.jqxGrid('getrowid', rowindex);
    let rowdata = agrid.jqxGrid('getrowdata', rowindex);
    if (rowindex === -1) {
        return false;
    }
    //var data = "aggregate=" + row_id;
    $.ajax({
        dataType: 'json',
        url: aggregatedata_url + row_id + '/' + filter_mode,
        method: "GET",
        //data: data,
        success: function (data, status, xhr) {
            if (typeof data.affected_cells !== 'undefined') {
                if (data.affected_cells > 0) {
                    raiseInfo("Сведение данных завершено");
                    rowdata.aggregated_at = data.aggregated_at;
                    agrid.jqxGrid('updaterow', row_id, rowdata);
                }
                else {
                    raiseError("Отсутствуют данные в первичных документах");
                }
            }
            else {
                if (data.aggregate_status === 500) {
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
filledFormclass = function (row, columnfield, value, rowdata) {
    if (rowdata.filled) {
        return 'filledForm';
    }
};
// Установка класса для раскрашивания строк в зависимости от статуса документа
formStatusclass = function (row, columnfield, value, rowdata) {
    switch (value) {
        case statelabels.performed :
        case statelabels.inadvance :
            return 'editedStatus';
        case statelabels.prepared :
            return 'preparedStatus';
        case statelabels.accepted :
            return 'acceptedStatus';
        case statelabels.approved :
            return 'approvedStatus';
        case statelabels.declined :
            return 'declinedStatus';
        default:
            return '';
    }
};
// фильтр для быстрого поиска по наименованию учреждения - первичные документы
mo_name_filter = function (needle) {
    let rowFilterGroup = new $.jqx.filter();
    let filter_or_operator = 1;
    let filtervalue = needle;
    let filtercondition = 'contains';
    let nameRecordFilter = rowFilterGroup.createfilter('stringfilter', filtervalue, filtercondition);
    rowFilterGroup.addfilter(filter_or_operator, nameRecordFilter);
    dgrid.jqxGrid('addfilter', 'unit_name', rowFilterGroup);
    dgrid.jqxGrid('applyfilters');
};
// фильтр для быстрого поиска по наименованию учреждения/территории - сводные документы
mo_name_aggrfilter = function (needle) {
    let rowFilterGroup = new $.jqx.filter();
    let filter_or_operator = 1;
    let filtervalue = needle;
    let filtercondition = 'contains';
    let nameRecordFilter = rowFilterGroup.createfilter('stringfilter', filtervalue, filtercondition);
    rowFilterGroup.addfilter(filter_or_operator, nameRecordFilter);
    agrid.jqxGrid('addfilter', 'unit_name', rowFilterGroup);
    agrid.jqxGrid('applyfilters');
};
// Рендеринг панели инструментов для таблицы первичных документов
renderdoctoolbar = function (toolbar) {
    let me = this;
    let container = $("<div style='margin: 5px;'></div>");
    let searchField = $("<input class='jqx-input jqx-widget-content jqx-rc-all' id='searchField' type='text' style='height: 23px; float: left; width: 150px;' />");
    let clearfilters = $("<input id='clearfilters' type='button' value='Очистить фильтр'/>");
    let audit = $("<input id='ChangeAudutStatus' type='button' value='Проверка отчета' />");
    let statewindow = $("#changeAuditStateWindow");
    if (audit_permission) {
        audit.click(function () {
            let rowindex = dgrid.jqxGrid('getselectedrowindex');
            if (rowindex === -1) {
                return false;
            }
            let radiostates = $('.auditstateradio');
            radiostates.jqxRadioButton('uncheck');
            //radiostates.each(function() {
                //$(this).jqxRadioButton('disable');
                //$(this).jqxRadioButton('uncheck');
                //$("#SaveAuditState").jqxButton({disabled: true });
            //});
            $.each(current_document_audits, function(key, value) {
                if (value.auditor_id === current_user_id) {
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
            let offset = dgrid.offset();
            statewindow.jqxWindow({ position: { x: parseInt(offset.left) + 150, y: parseInt(offset.top) + 100 } });
            statewindow.jqxWindow('open');
        });
    }

    let editform = $("<i style='margin-left: 2px;height: 14px' class='fa fa-edit fa-lg' title='Редактировать форму' />");
    let word_export = $("<i style='margin-left: 2px;height: 14px' class='fa fa-file-word-o fa-lg' title='Экспортировать документ в MS Word'></i>");
    let excel_export = $("<i style='margin-left: 2px;height: 14px' class='fa fa-file-excel-o fa-lg' title='Экспортировать данные документа в MS Excel'></i>");
    let message_input = $("<i style='margin-left: 2px;height: 14px' class='fa fa-commenting-o fa-lg' title='Сообщение/комментарий к документу'></i>");
    let refresh_list = $("<i style='margin-left: 2px;height: 14px' class='fa fa-refresh fa-lg' title='Обновить список'></i>");
    let changestatus = $("<input id='ChangeStatus' type='button' value='Статус отчета' />");

    toolbar.append(container);
    container.append(searchField);
    container.append(clearfilters);
    if (current_user_role !== 2) {
        container.append(changestatus);
    }
    if (audit_permission) {
        container.append(audit);
        audit.jqxButton({ theme: theme });
    }
    container.append(editform);
    container.append(message_input);
    container.append(word_export);
    container.append(excel_export);
    container.append(refresh_list);
    searchField.addClass('jqx-widget-content-' + theme);
    searchField.addClass('jqx-rc-all-' + theme);
    searchField.jqxInput({ width: 200, placeHolder: "Медицинская организация" });
    clearfilters.jqxButton({ theme: theme });
    editform.jqxButton({ theme: theme });
    changestatus.jqxButton({ theme: theme });
    word_export.jqxButton({ theme: theme });
    excel_export.jqxButton({ theme: theme });
    message_input.jqxButton({ theme: theme });
    refresh_list.jqxButton({ theme: theme });
    let oldVal = "";
    searchField.on('keydown', function (event) {
        if (searchField.val().length >= 2) {
            if (me.timer) {
                clearTimeout(me.timer);
            }
            if (oldVal !== searchField.val()) {
                me.timer = setTimeout(function () {
                    mo_name_filter(searchField.val());
                }, 500);
                oldVal = searchField.val();
            }
        }
        else {
            dgrid.jqxGrid('removefilter', '1');
        }
    });
    clearfilters.click(function () { dgrid.jqxGrid('clearfilters'); searchField.val('');});
    editform.click(function () {
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        if (rowindex !== -1 && typeof rowindex !== 'undefined') {
            let document_id = dgrid.jqxGrid('getrowid', rowindex);
            let editWindow = window.open(edit_form_url + '/' + document_id);
        }
    });
    changestatus.click(function () {
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        //let alert_message;
        let this_document_state = '';
        if (rowindex === -1 && typeof rowindex !== 'undefined') {
            return false;
        }
        let offset = dgrid.offset();
        stateWindow.jqxWindow({ position: { x: parseInt(offset.left) + 100, y: parseInt(offset.top) + 100 } });
        let data = dgrid.jqxGrid('getrowdata', rowindex);
        if (!data.filled && current_user_role === '1') {
            raiseError('Внимание! Документ не содержит данные. Необходимо, В ОБЯЗАТЕЛЬНОМ ПОРЯДКЕ, пояснить в сообщении по какой причине!');
            $("#statusChangeMessage").val('Документ не заполнен по причине: ');
        } else {
            $("#statusChangeMessage").val('');
        }
        let radiostates = $('.stateradio');
        radiostates.each(function() {
            let state = $(this).attr('id');
            if ($.inArray(state, disabled_states) !== -1) {
                $(this).jqxRadioButton('disable');
            }
            if (statelabels[state] === data.state) {
                $(this).jqxRadioButton('check');
                this_document_state = state;
            }
        });
        //console.log(current_user_role === '1' && this_document_state === 'inadvance');
        //console.log(data.state);
        $('#changeStateFormCode').html(data.form_code);
        $('#changeStateMOCode').html(data.unit_code);
        if (current_user_role === '1' && this_document_state !== 'performed' && this_document_state !== 'inadvance' && this_document_state !== 'declined') {
            //$('#inadvance').jqxRadioButton('disable');
            $('#prepared').jqxRadioButton('disable');
        } else if (current_user_role === '1' && this_document_state === 'performed') {
            //$('#inadvance').jqxRadioButton('enable');
            $('#prepared').jqxRadioButton('enable');
        } else if (current_user_role === '1' && this_document_state === 'declined') {
            //$('#inadvance').jqxRadioButton('enable');
            $('#prepared').jqxRadioButton('enable');
        } else if (current_user_role === '1' && this_document_state === 'inadvance') {
            //console.log("Переход с предварительной проверки");
            //$('#inadvance').jqxRadioButton('disable');
            $('#prepared').jqxRadioButton('enable');
            $('#performed').jqxRadioButton('enable');
        }
        if ((current_user_role === '3' || current_user_role === '4') && this_document_state === 'performed') {
            $('#declined').jqxRadioButton('disable');
        } else if ((current_user_role === 3 || current_user_role === 4) && this_document_state !== 'performed') {
            $('#declined').jqxRadioButton('enable');
        }
        stateWindow.jqxWindow('open');
    });
    message_input.click(function () {
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        if (rowindex === -1) {
            return false;
        }
        $("#message").val("");
        let offset = dgrid.offset();
        $("#sendMessageWindow").jqxWindow({ position: { x: parseInt(offset.left) + 100, y: parseInt(offset.top) + 100 } });
        $("#sendMessageWindow").jqxWindow('open');
    });
    word_export.click(function () {
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        let document_id = dgrid.jqxGrid('getrowid', rowindex);
        if (rowindex !== -1) {
            location.replace(export_word_url + document_id);
        }
    });
    excel_export.click(function () {
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        let document_id = dgrid.jqxGrid('getrowid', rowindex);
        if (rowindex !== -1) {
            location.replace(export_form_url + document_id);
        }
    });
    refresh_list.click(function () {
        docsource.url = docsource_url + filtersource();
        dgrid.jqxGrid('updatebounddata');
        $("#DocumentMessages").html('');
        $("#DocumentAuditions").html('');
    });
};
// рендеринг панели инструментов для таблицы сводных документов
renderaggregatetoolbar = function(toolbar) {
    let me = this;
    let container = $("<div style='margin: 5px;'></div>");
    let input1 = $("<input class='jqx-input jqx-widget-content jqx-rc-all' id='searchField' type='text' style='height: 23px; float: left; width: 150px;' />");
    let filter = $("<i style='margin-left: 2px;height: 14px' class='fa fa-filter fa-lg' title='Очистить фильтр' />");
    let editform = $("<i style='margin-left: 2px;height: 14px' class='fa fa-eye fa-lg' title='Просмотр/редактирование сводного отчета' />");
    let makeaggregation = $("<i style='margin-left: 2px;height: 14px' class='fa fa-database fa-lg' title='Выполнить свод' />");
    if (audit_permission) {
        let change_audit_status = $("<input id='ChangeAudutStatus' type='button' value='Проверка отчета' />");
        change_audit_status.click(function () {
            let rowindex = agrid.jqxGrid('getselectedrowindex');
            if (rowindex === -1) {
                return false;
            }
            let radiostates = $('.auditstateradio');
            radiostates.each(function() {
                $(this).jqxRadioButton('disable');
                $(this).jqxRadioButton('uncheck');
                $("#SaveAuditState").jqxButton({disabled: true });
            });
            $.each(current_document_audits, function(key, value) {
                if (value.auditor_id === current_user_id) {
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
            let offset = agrid.offset();
            $("#BatchChangeAuditStateWindow").jqxWindow({ position: { x: parseInt(offset.left) + 150, y: parseInt(offset.top) + 100 } });
            $("#BatchChangeAuditStateWindow").jqxWindow('open');
        });
    }
    let word_export = $("<i style='margin-left: 2px;height: 14px' class='fa fa-file-word-o fa-lg' title='Экспортировать документ в MS Word'></i>");
    let excel_export = $("<i style='margin-left: 2px;height: 14px' class='fa fa-file-excel-o fa-lg' title='Экспортировать данные документа в MS Excel'></i>");
    let refresh_list = $("<i style='margin-left: 2px;height: 14px' class='fa fa-refresh fa-lg' title='Обновить список'></i>");
    toolbar.append(container);
    container.append(input1);
    container.append(filter);
    container.append(editform);
    container.append(makeaggregation);
/*    if (audit_permission) {
        container.append(change_audit_status);
        change_audit_status.jqxButton({ theme: theme });
    }*/
    container.append(word_export);
    container.append(excel_export);
    container.append(refresh_list);
    input1.addClass('jqx-widget-content-' + theme);
    input1.addClass('jqx-rc-all-' + theme);
    input1.jqxInput({ width: 200, placeHolder: "МО/Территория" });
    filter.jqxButton({ theme: theme });
    editform.jqxButton({ theme: theme });
    makeaggregation.jqxButton({ theme: theme });
    word_export.jqxButton({ theme: theme });
    excel_export.jqxButton({ theme: theme });
    refresh_list.jqxButton({ theme: theme });
    let oldVal = "";
    input1.on('keydown', function (event) {
        if (input1.val().length >= 2) {
            if (me.timer) {
                clearTimeout(me.timer);
            }
            if (oldVal !== input1.val()) {
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
        let rowindex = agrid.jqxGrid('getselectedrowindex');
        let document_id = agrid.jqxGrid('getrowid', rowindex);
        if (rowindex !== -1) {
            let editWindow = window.open(edit_aggregate_url+'/'+document_id);
        }
    });

    makeaggregation.click( function() {
            aggregatedata();
    });

    word_export.click(function () {
        let rowindex = agrid.jqxGrid('getselectedrowindex');
        let document_id = agrid.jqxGrid('getrowid', rowindex);
        if (rowindex !== -1) {
            location.replace(export_word_url + document_id);
        }
    });

    excel_export.click(function () {
        let rowindex = agrid.jqxGrid('getselectedrowindex');
        let document_id = agrid.jqxGrid('getrowid', rowindex);
        if (rowindex !== -1) {
            location.replace(export_form_url + document_id);
        }
    });
    refresh_list.click(function () {
        aggregate_source.url = aggrsource_url + filtersource();
        agrid.jqxGrid('updatebounddata');
    });
};
// Инициализация элементов управления с выпадающими списками
initDropdowns = function () {
    terr.jqxDropDownButton({width: 350, height: 32, theme: theme});
    terr.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по территориям)</div>');
    groups.jqxDropDownButton({width: 350, height: 32, theme: theme});
    groups.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по группам)</div>');
    if (current_user_role === '1') {
        groups.jqxDropDownButton({disabled:true});
    }
    mondropdown.jqxDropDownButton({width: 350, height: 32, theme: theme});
    mondropdown.jqxDropDownButton('setContent', '<div style="margin: 9px"></i>Мониторинги</div>');
    mondropdown.on('close', function () {
        updatedocumenttable()
    });
    periodDropDown.jqxDropDownButton({width: 350, height: 32, theme: theme});
    periodDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Отчетные периоды</div>');
    periodDropDown.on('close', function () {
        updatedocumenttable()
    });
    statusDropDown.jqxDropDownButton({width: 350, height: 32, theme: theme});
    statusDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Статусы отчетов</div>');
    statusDropDown.on('close', function () {
        updatedocumenttable()
    });
    $("#clearAllFilters").click( clearAllFilters );
};

clearAllFilters = function (event) {
    let checkedMonitorings = montree.jqxTreeGrid('getCheckedRows');
    if (typeof checkedMonitorings !== 'undefined') {
        for (let i = 0; i < checkedMonitorings.length; i++) {
            montree.jqxTreeGrid('uncheckRow' , checkedMonitorings[i].id);
        }
    }
    mondropdown.jqxDropDownButton('setContent', '<div style="margin: 9px"></i>Мониторинги</div>');
    terr.jqxTreeGrid('clearSelection');
    terr.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по территориям)</div>');
    groups.jqxTreeGrid('clearSelection');
    groups.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по группам)</div>');
    let checkedPeriods = periodTree.jqxTreeGrid('getCheckedRows');
    if (typeof checkedPeriods !== 'undefined') {
        for (let i = 0; i < checkedPeriods.length; i++) {
            periodTree.jqxTreeGrid('uncheckRow' , checkedPeriods[i].id);
        }
    }
    periodDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Отчетные периоды</div>');
    stateList.jqxListBox('uncheckAll');
    statusDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Статусы отчетов</div>');
    updatedocumenttable();
};
// функция удалена
/*initmotabs = function() {
    $("#motabs").jqxTabs({  height: 500, width: 670, theme: theme });
};*/
initMonitoringTree = function () {
    montree.jqxTreeGrid(
        {
            width: 900,
            height: 600,
            theme: theme,
            source: mon_dataAdapter,
            selectionMode: "singleRow",
            showToolbar: true,
            renderToolbar: montreeToolbar,
            filterable: true,
            filterMode: "simple",
            localization: localize(),
            checkboxes: true,
            hierarchicalCheckboxes: true,
            columnsResize: true,
            autoRowHeight: false,
            ready: function()
            {
                montree.jqxTreeGrid('expandRow', 100000);
                for (let i = 0; i < checkedmf.length; i++) {
                    montree.jqxTreeGrid('checkRow', checkedmf[i]);
                }
            },
            columns: [
                { text: 'Наименование мониторинга/отчетной формы', dataField: 'name', width: 900 }
            ]
        });
    montree.on('filter',
        function (event)
        {
            let args = event.args;
            let filters = args.filters;
            montree.jqxTreeGrid('expandAll');
        }
    );

/*    montree.on('rowCheck', function (event) {
        console.log(getCheckedMonsForms());
    });*/
};
montreeToolbar = function (toolbar) {
    toolbar.append("<button type='button' id='moncollapseAll' class='btn btn-default btn-sm'>Свернуть все</button>");
    toolbar.append("<button type='button' id='monexpandAll' class='btn btn-default btn-sm'>Развернуть все</button>");
    toolbar.append("<button type='button' id='monfilterApply' class='btn btn-primary btn-sm'>Применить фильтр</button>");
    $('#monexpandAll').click(function (event) {
        montree.jqxTreeGrid('expandAll');
    });
    $('#moncollapseAll').click(function (event) {
        montree.jqxTreeGrid('collapseAll');
        montree.jqxTreeGrid('expandRow', 0);
    });
    $('#monfilterApply').click(function (event) {
        mondropdown.jqxDropDownButton('close');
        return true;
    });
};
// инициализация дерева Территорий/Медицинских организаций
initmotree = function() {
    motree.jqxTreeGrid(
        {
            width: 670,
            height: 600,
            theme: theme,
            source: mo_dataAdapter,
            selectionMode: "singleRow",
            showToolbar: true,
            renderToolbar: motreeToolbar,
            hierarchicalCheckboxes: false,
            checkboxes: false,
            filterable: true,
            filterMode: "simple",
            localization: localize(),
            columnsResize: true,
            ready: function()
            {
                // expand row with 'EmployeeKey = 32'
                motree.jqxTreeGrid('expandRow', 0);
            },
            columns: [
                { text: 'Код', dataField: 'unit_code', width: 120 },
                { text: 'Наименование', dataField: 'unit_name', width: 545 }
            ]
        });
    motree.on('filter',
        function (event)
        {
            let args = event.args;
            let filters = args.filters;
            motree.jqxTreeGrid('expandAll');
        }
    );
    motree.on('rowSelect',
        function (event)
        {
            let args = event.args;
            let new_top_level_node = args.key;
            if (new_top_level_node === current_top_level_node && filter_mode === 1) {
                return false;
            }
            current_top_level_node =  new_top_level_node;
            filter_mode = 1; // режим отбора документов по территориям
            updatedocumenttable();
            terr.jqxDropDownButton('close');
            if (current_top_level_node == 0) {
                terr.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по территориям)</div>');
            } else {
                terr.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Медицинские организации (по территориям)</div>');
            }
            groups.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по группам)</div>');
            return true;
        }
    );
};
motreeToolbar = function (toolbar) {
    toolbar.append("<button type='button' id='collapseAll' class='btn btn-default btn-sm'>Свернуть все</button>");
    toolbar.append("<button type='button' id='expandAll' class='btn btn-default btn-sm'>Развернуть все</button>");
    $('#expandAll').click(function (event) {
        motree.jqxTreeGrid('expandAll');
    });
    $('#collapseAll').click(function (event) {
        motree.jqxTreeGrid('collapseAll');
        motree.jqxTreeGrid('expandRow', 0);
    });
};
// инициализация выбора отчетов по группе учреждений
initgrouptree = function() {
    grouptree.jqxTreeGrid(
        {
            width: 670,
            height: 600,
            theme: theme,
            source: ugroup_dataAdapter,
            selectionMode: "singleRow",
            filterable: true,
            filterMode: "simple",
            localization: localize(),
            columnsResize: true,
            ready: function()
            {
                grouptree.jqxTreeGrid('expandRow', 0);
            },
            columns: [
                //{ text: 'Код', dataField: 'group_code', width: 120 },
                { text: 'Сокр', dataField: 'slug', width: 120 },
                { text: 'Наименование', dataField: 'name', width: 545 }
            ]
        });
    if (current_user_role === '1') {
        grouptree.jqxTreeGrid({ disabled:true });
    }
    grouptree.on('filter',
        function (event)
        {
            let args = event.args;
            let filters = args.filters;
            grouptree.jqxTreeGrid('expandAll');
        }
    );
    grouptree.on('rowSelect',
        function (event)
        {
            let args = event.args;
            let new_top_level_node = args.key;
            if (new_top_level_node === current_top_level_node && filter_mode === 2) {
                return false;
            }
            filter_mode = 2; // режим отбора документов по группам
            current_top_level_node =  new_top_level_node;
            updatedocumenttable();
            groups.jqxDropDownButton('close');
            groups.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Медицинские организации (по группам)</div>');
            terr.jqxDropDownButton('setContent', '<div style="margin: 9px">Медицинские организации (по территориям)</div>');
            return true;
        }
    );
};
// Ининциализация списка отчетных периодов
initPeriodTree = function () {
    let uncheckAll = $("#clearAllPeriods");
    let periods_source =
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
    periodTree.jqxTreeGrid(
        {
            width: 345,
            height: 500,
            theme: theme,
            source: periodsDataAdapter,
            selectionMode: "singleRow",
            filterable: true,
            filterMode: "simple",
            checkboxes: true,
            localization: localize(),
            columnsResize: true,
            ready: function()
            {
                periodTree.jqxTreeGrid('expandRow', 0);
                for (let i = 0; i < checkedperiods.length; i++) {
                    periodTree.jqxTreeGrid('checkRow', checkedperiods[i]);
                }
            },
            columns: [
                { text: 'Наименование', dataField: 'name', width: 345 }
            ]
        });
    uncheckAll.click( function (event) {
        let checkedRows = periodTree.jqxTreeGrid('getCheckedRows');
        if (typeof checkedRows !== 'undefined') {
            for (let i = 0; i < checkedRows.length; i++) {
                periodTree.jqxTreeGrid('uncheckRow', checkedRows[i].id);
            }
        }
    });
    $("#applyPeriods").click( function (event) {
        periodDropDown.jqxDropDownButton('close');
    });
};
// инициализация списка статусов отчетного документа
initStatusList = function() {
    let checkAll = $("#checkAllStates");
    let uncheckAll = $("#clearAllStates");
    let states_source =
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
    stateList.jqxListBox({
        theme: theme,
        source: statesDataAdapter,
        displayMember: 'name',
        valueMember: 'code',
        checkboxes: true,
        width: 290,
        height: 200
    });
    for(let i = 0; i < checkedstates.length; i++) {
        stateList.jqxListBox('checkItem', checkedstates[i]);
    }
    checkAll.click( function (event) {
            stateList.jqxListBox('checkAll');
    });
    uncheckAll.click( function (event) {
        stateList.jqxListBox('uncheckAll');
    });
    $("#applyStatuses").click( function (event) {
        statusDropDown.jqxDropDownButton('close');
    });

};
// инициализация вкладок с документами
initdocumentstabs = function() {
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
            rendertoolbar: renderdoctoolbar,
            columns: [
                { text: '№', datafield: 'id', width: '5%', cellclassname: filledFormclass },
                { text: 'Код МО', datafield: 'unit_code', width: 70 },
                { text: 'Наименование МО', datafield: 'unit_name', width: '25%' },
                { text: 'Мониторинг', datafield: 'monitoring', width: 320 },
                { text: 'Код формы', datafield: 'form_code', width: 80 },
                //{ text: 'Наименование формы', datafield: 'form_name', width: '20%' },
                { text: 'Период', datafield: 'period', width: 120 },
                { text: 'Статус', datafield: 'state', width: 170, cellclassname: formStatusclass },
                { text: 'Данные', datafield: 'filled', columntype: 'checkbox', width: 120 }
            ]
        });
    dgrid.on('rowselect', function (event)
    {
        let row = event.args.row;
        let murl = docmessages_url + 'document=' + row.id;
        current_document_form_code = row.form_code;
        current_document_form_name = row.form_name;
        current_document_ou_name = row.unit_name;
        current_document_state = row.state;
        $.getJSON( murl, function( data ) {
            if (data.responce === 0) {
                $("#DocumentMessages").html("Нет сообщений для данного документа");
            }
            else {
                let items = [];
                $.each( data, function( key, val ) {
                    let worker = 'н/д';
                    if (val.worker !== null) {
                        worker = val.worker.description;
                    }
                    let m = "<tr>";
                    m += "<td style='width: 120px'>" + formatDate(val.created_at) + "</td>";
                    m += "<td style='width: 20%'>" + worker + "</td>";
                    m += "<td>" + val.message + "</td>";
                    m +="</tr>";
                    items.push(m);
                });
                $("#DocumentMessages").html("<table class='table table-bordered table-condensed table-hover table-striped' style='width: 100%'>" + items.join( "" ) + "</table>");
            }
        });

        let aurl = docauditions_url + 'document=' + row.id;
        current_document_audits = [];
        $.getJSON( aurl, function( data ) {
            if (data.responce === 0) {
                $("#DocumentAuditions").html("Нет результатов проверки данного отчетного документа");
            }
            else {
                let items = [];
                $.each( data, function( key, val ) {
                    current_document_audits.push({ auditor_id: val.worker.id, state_id: val.state_id});
                    let audit_class = '';
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
                    let d = val.created_at ? val.created_at : '';
                    items.push("<tr class='"+ audit_class +"'><td style='width: 50%'>" + val.created_at + "<br /> " + val.worker.description + "</td><td>" + val.dicauditstate.name + "</td></tr>");
                });
                $("#DocumentAuditions").html("<table class='control_result' style='width: 100%'>" + items.join( "" ) + "</table>");
            }
        });
    });
    dgrid.on('rowdoubleclick', function (event)
    {
        let args = event.args;
        let rowindex = args.rowindex;
        let document_id = dgrid.jqxGrid('getrowid', rowindex);
        let editWindow = window.open(edit_form_url + '/' + document_id);
    });
    agrid.jqxGrid(
        {
            width: '100%',
            height: '85%',
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
                { text: 'Мониторинг', datafield: 'monitoring', width: 320 },
                { text: 'Код формы', datafield: 'form_code', width: 100 },
                //{ text: 'Наименование формы', datafield: 'form_name', width: '20%' },
                { text: 'Период', datafield: 'period', width: 150 },
                { text: 'Сведение', datafield: 'aggregated_at', width: 150 },
                { text: 'Данные', datafield: 'filled', columntype: 'checkbox', width: 120 }
            ]
        });
    agrid.on('rowdoubleclick', function (event)
    {
        let args = event.args;
        let rowindex = args.rowindex;
        let document_id = agrid.jqxGrid('getrowid', rowindex);
        let editWindow = window.open(edit_aggregate_url + '/' + document_id);
    });
};
// Инициализация вкладки консолидированных отчетов
initConsolidates = function () {
    consolsource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'unit_code', type: 'string' },
                { name: 'unit_name', type: 'string' },
                { name: 'form_code', type: 'string' },
                { name: 'monitoring', type: 'string' },
                { name: 'period', type: 'string' }
            ],
            id: 'id',
            ///url: docsource_url + filtersource(),
            url: consolsource_url + current_filter,
            root: 'data'
        };
    consolTableDA = new $.jqx.dataAdapter(consolsource);
    cgrid.jqxGrid(
        {
            width: '100%',
            height: '93%',
            theme: theme,
            source: consolTableDA,
            columnsresize: true,
            localization: localize(),
            columns: [
                { text: '№', datafield: 'id', width: '5%' },
                { text: 'Код Территории/МО', datafield: 'unit_code', width: 100 },
                { text: 'Наименование МО', datafield: 'unit_name', width: '20%' },
                { text: 'Мониторинг', datafield: 'monitoring', width: 320 },
                { text: 'Код формы', datafield: 'form_code', width: 100 },
                { text: 'Период', datafield: 'period', width: 150 }
            ]
        });
    cgrid.on('rowdoubleclick', function (event)
    {
        let args = event.args;
        let rowindex = args.rowindex;
        let document_id = cgrid.jqxGrid('getrowid', rowindex);
        let editWindow = window.open(edit_consolidate_url + '/' + document_id);
    });
};
// Инициализация вкладки последних документов
initRecentDocuments = function () {
    recentsource =
        {
            datatype: "json",
            datafields: [
                { name: 'document_id', type: 'int' },
                { name: 'unit_code', map: 'document>unitsview>code', type: 'string' },
                { name: 'unit_name', map: 'document>unitsview>name', type: 'string' },
                { name: 'form_code', map: 'document>form>form_code', type: 'string' },
                { name: 'monitoring', map: 'document>monitoring>name', type: 'string' },
                { name: 'period', map: 'document>period>name', type: 'string' },
                { name: 'state', map: 'document>state>name', type: 'string' }
            ],
            id: 'document_id',
            ///url: docsource_url + filtersource(),
            url: recentdocs_url,
            root: 'data'
        };
    recentTableDA = new $.jqx.dataAdapter(recentsource);
    rgrid.jqxGrid(
        {
            width: '100%',
            height: '93%',
            theme: theme,
            source: recentTableDA,
            columnsresize: true,
            localization: localize(),
            columns: [
                { text: '№', datafield: 'document_id', width: '5%' },
                { text: 'Код Территории/МО', datafield: 'unit_code', width: 100 },
                { text: 'Наименование МО', datafield: 'unit_name', width: '20%' },
                { text: 'Мониторинг', datafield: 'monitoring', width: 320 },
                { text: 'Код формы', datafield: 'form_code', width: 100 },
                { text: 'Период', datafield: 'period', width: 150 },
                { text: 'Статус', datafield: 'state', width: 120, cellclassname: formStatusclass }
            ]
        });
    rgrid.on('rowdoubleclick', function (event)
    {
        let args = event.args;
        let rowindex = args.rowindex;
        let document_id = rgrid.jqxGrid('getrowid', rowindex);
        let editWindow = window.open(edit_aggregate_url + '/' + document_id);
    });
};
// инициализация вкладок с сообщениями и проверками к документу
initdocumentproperties = function() {
    $('#DocumentPropertiesSplitter').jqxSplitter({
        width: '100%',
        height: '95%',
        theme: theme,
        orientation: 'vertical',
        panels: [{ size: '80%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
    });
    $("#openMessagesListWindow").on('click', function(event) {
        let bootstrap_link = "<link href='http://homestead.app/bootstrap/css/bootstrap.min.css' rel='stylesheet' type='text/css'>";
        let table = $('#DocumentMessages').clone();
        //var print_style = "<style>.printlist { font-size: 0.8em; } .control_result { border: 1px solid #7f7f7f; width: 400;";
        //print_style += "border-collapse: collapse; margin-bottom: 10px; } .control_result td { border: 1px solid #7f7f7f; } </style>";
        let link_to_print ="<a href='#' onclick='window.print()'>Распечатать</a>";
        let header = "<h3>Комментарии к форме №" + current_document_form_code + " \"" + current_document_form_name + "\"";
        header += " по учреждению: " + current_document_ou_name +"</h3>";
        let pWindow = window.open("", "messagesWindow", "width=1000, height=600, scrollbars=yes");

        table.find('td').addClass('small');
        pWindow.document.write(bootstrap_link + link_to_print + header + table.html());
    });
    $("#openAuditionListWindow").on('click', function(event) {
        let print_style = "<style>.printlist { font-size: 0.8em; } .control_result { border: 1px solid #7f7f7f; width: 400;";
        print_style += "border-collapse: collapse; margin-bottom: 10px; } .control_result td { border: 1px solid #7f7f7f; } </style>";
        let link_to_print ="<a href='#' onclick='window.print()'>Распечатать</a>";
        let header = "<h3>Перечень проверок формы №" + current_document_form_code + " " + current_document_form_name;
        header += " по учреждению: " + current_document_ou_name +"</h3>";
        let pWindow = window.open("", "messagesWindow", "width=1000, height=600, scrollbars=yes");
        pWindow.document.write(print_style + link_to_print + header + $("#DocumentAuditions").html());
    });
    $("#auditExpander").jqxExpander({toggleMode: 'none', showArrow: false, width: "100%", height: "100%", theme: theme  });
};
// инициализация всплывающих окон с формами ввода сообщения и т.д.
initpopupwindows = function() {
    stateWindow.jqxWindow({
        width: 530,
        height: 480,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelStateChanging"),
        theme: theme
    });
    stateWindow.on('close', function (event) { $('#changeStateAlertMessage').html('').hide(); });
    $("#performed").jqxRadioButton({ width: 450, height: 25, theme: theme });
    //$("#inadvance").jqxRadioButton({ width: 450, height: 25, theme: theme });
    $("#prepared").jqxRadioButton({ width: 450, height: 25, theme: theme });
    $("#accepted").jqxRadioButton({ width: 450, height: 25, theme: theme });
    $("#declined").jqxRadioButton({ width: 450, height: 25, theme: theme });
    $("#approved").jqxRadioButton({ width: 450, height: 25, theme: theme });
    $('#statusChangeMessage').jqxTextArea({ placeHolder: 'Оставьте свой комментарий к смене статуса документа', height: 90, width: 450, minLength: 1 });
    $("#CancelStateChanging").jqxButton({ theme: theme });
    $("#SaveState").jqxButton({ theme: theme });
    $(".stateradio").on('checked', function (event) {
        let alert_message = '';
        if (current_user_role === '1') {
            if ($(this).attr('id') === 'prepared') {
                alert_message = '<div class="alert alert-danger"><strong>Внимание!</strong> Выбор статуса документа "Подготовлен к проверке" допускается только в то случае если ВСЕ правки документа выполнены! <br />';
                alert_message += 'Если Вы не уверены, что закончили редактирование - отмените действие!</div>';
                $('#changeStateAlertMessage').html(alert_message).show();
            } else if ($(this).attr('id') === 'inadvance') {
                alert_message = '<div class="alert alert-info"><strong>Внимание!</strong> Данный статус предназначен для проверки некоторых данных в сроки до ОФИЦИАЛЬНОЙ сдачи отчетной формы! <br />';
                alert_message += 'Если имеется необходимость внесения/коррекции данных, измените статус на "Выполняется"';
                $('#changeStateAlertMessage').html(alert_message).show();
            } else {
                $('#changeStateAlertMessage').html('').hide();
            }
        }

    });
    $("#SaveState").click(function () {
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        let rowdata = dgrid.jqxGrid('getrowdata', rowindex);
        let oldstate = rowdata.state;
        let row_id = dgrid.jqxGrid('getrowid', rowindex);
        let message = $("#statusChangeMessage").val();
        let radiostates = $('.stateradio');
        let selected_state;
        radiostates.each(function() {
            if ($(this).jqxRadioButton('checked')) {
                selected_state = $(this).attr('id');
            }
        });
        if (statelabels[selected_state] === current_document_state ) {
            return false;
        }
        let data = "&document=" + row_id + "&state=" + selected_state + "&oldstate=" + stateIds[oldstate] + "&message=" + message;
        $.ajax({
            dataType: 'json',
            url: changestate_url,
            method: "POST",
            beforeSend: function (xhr) {
                if (selected_state === 'prepared') {
                    $("#changeStateAlertMessage").html('<div class="alert alert-warning"><h5>Выполнение проверки документа перед сменой статуса <img src="/jqwidgets/styles/images/loader-small.gif" /></h5></div>')
                    $("#SaveState").jqxButton({disabled: true });
                    $("#CancelStateChanging").jqxButton({disabled: true });
                }
            },
            data: data,
            success: function (data, status, xhr) {
                if (data.status_changed == 1) {
                    raiseInfo("Статус документа изменен. Новый статус: \"" + statelabels[data.new_status] + '\""');
                    rowdata.state = statelabels[data.new_status];
                    dgrid.jqxGrid('updaterow', row_id, rowdata);
                    dgrid.jqxGrid('selectrow', rowindex);
                }
                else if(data.status_changed == 0) {
                    raiseError("Статус не изменен! " + data.comment);
                }
                $("#changeStateAlertMessage").html('')
                $("#SaveState").jqxButton({disabled: false });
                $("#CancelStateChanging").jqxButton({disabled: false });
                stateWindow.jqxWindow('hide');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
                $("#changeStateAlertMessage").html('')
                $("#SaveState").jqxButton({disabled: false });
                $("#CancelStateChanging").jqxButton({disabled: false });
                stateWindow.jqxWindow('hide');
            }
        });

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
// Формирование строки запроса к серверу
filtersource = function() {
    let forms;
    let monitorings;
    //let states = checkedstates.join();
    //let states = checkedstates.join();
    let states = checkstatefilter();
    let mon_forms = getCheckedMonsForms();
    let mf = mon_forms.mf.join();
    let periods = checkPeriodFilter();
    //let forms = checkedforms.join();
    forms = mon_forms.f.join();
    monitorings = mon_forms.m.join();

    //periods = checkedperiods.join();
    return '&filter_mode=' + filter_mode + '&ou=' +current_top_level_node +'&states='+states+
        '&monitorings='+monitorings+'&forms='+forms +'&periods=' + periods + '&mf=' + mf;
};
// Источники данных для
initDocumentSource = function () {
    //console.log(current_filter);
    docsource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'unit_code', type: 'string' },
                { name: 'unit_name', type: 'string' },
                { name: 'form_code', type: 'string' },
                { name: 'form_name', type: 'string' },
                { name: 'monitoring', type: 'string' },
                { name: 'period', type: 'string' },
                { name: 'state', type: 'string' },
                { name: 'filled', type: 'bool' }
            ],
            id: 'id',
            ///url: docsource_url + filtersource(),
            url: docsource_url + current_filter,
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
                { name: 'monitoring', type: 'string' },
                {name: 'period', type: 'string'},
                {name: 'aggregated_at', type: 'string'},
                { name: 'filled', type: 'bool' }
            ],
            id: 'id',
            //url: aggrsource_url + '&filter_mode='+ filter_mode + '&ou=' + current_top_level_node + '&forms=' + checkedforms.join()+'&periods='+checkedperiods.join(),
            url: aggrsource_url + current_filter,
            root: 'data'
        };
    dataAdapter = new $.jqx.dataAdapter(docsource, {
        loadError: function(jqXHR, status, error) {
            if (jqXHR.status === 401) {
                raiseError('Пользователь не авторизован', jqXHR);
            }
        }
    });
    aggregate_report_table = new $.jqx.dataAdapter(aggregate_source);
};
// Показываем иконки фильтров при установленных ограничениях
initFilterIcons = function () {
    if (current_top_level_node !== lasstscope) {
        if (filter_mode === 1) {
            terr.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Медицинские организации (по территориям)</div>');
        } else if (filter_mode === 2) {
            groups.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Медицинские организации (по группам)</div>');
        }
    }
    if (checkedmf.length > 0) {
        mondropdown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Мониторинги</div>');
    }
    if (checkedperiods.length > 0) {
        periodDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Отчетные периоды</div>');
    }
    if (checkedperiods.length > 0) {
        statusDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Статусы отчетов</div>');
    }
};