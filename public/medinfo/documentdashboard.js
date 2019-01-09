let mon_tree_url = 'datainput/fetch_mon_tree/';
let mo_tree_url = 'datainput/fetch_mo_tree/';
let group_tree_url = 'datainput/fetch_ugroups';
let docsource_url = 'datainput/fetchdocuments?';
let recentdocs_url = 'datainput/fetchrecent?';
let docmessages_url = 'datainput/fetchmessages?';
let docinfo_url = 'datainput/fetchdocinfo/';
let changestate_url = 'datainput/changestate';
let changeaudition_url = 'datainput/changeaudition';
//let docmessagesend_url = 'datainput/sendmessage';
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
let ouarray = [];
let oucount = 0;
let grouptree = $("#groupTree");
let periodTree = $("#periodTree");
let stateList = $("#statesListbox");
let dgrid = $("#Documents"); // сетка для первичных документов
let primary_mo_bc = $("#mo_parents_breadcrumb");
let agrid = $("#Aggregates"); // сетка для сводных документов
let cgrid = $("#Consolidates"); // сетка для консолидированных документов
let rgrid = $("#Recent"); // сетка для последних документов
let mondropdown = $("#monitoringSelector");
let terr = $("#moSelectorByTerritories");
let groups = $('#moSelectorByGroups');
let periodDropDown = $('#periodSelector');
let statusDropDown = $('#statusSelector');
let dataPresenseDDown = $('#dataPresenceSelector');
let docinfoWindow = $('#DocumentInfoWindow');
let doc_id;
let docstate_id;
let current_document_form_code;
let current_document_form_name;
let current_document_ou_name;
let doc_state;
//let currentlet_document_audits = [];
let statelabels =
    {
        performed: 'Выполняется',
        inadvance: 'Подготовлен к предварительной проверке',
        prepared: 'Подготовлен к проверке',
        accepted: 'Принят',
        declined: 'Возвращен на доработку',
        approved: 'Утвержден'
    };
/*let stateIds =
    {
        'Выполняется' : 'performed',
        'Подготовлен к предварительной проверке' : 'inadvance',
        'Подготовлен к проверке' : 'prepared',
        'Принят' : 'accepted',
        'Возвращен на доработку' : 'declined',
        'Утвержден' : 'approved'
    };*/
/*let audit_state_ids =
    {
        noaudit: 1,
        audit_correct: 2,
        audit_incorrect: 3
    };*/
// Установка разбивки окна на области
initSplitters = function () {
    $("#mainSplitter").jqxSplitter(
        {
            width: '100%',
            height: '100%',
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
        height: '95%',
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
        url: mo_tree_url + current_user_scope
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
        mondropdown.jqxDropDownButton('setContent', '<div style="margin: 9px">Мониторинги</div>');
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
        statusDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Статусы отчетов</div>');
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
        periodDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Отчетные периоды</div>');
    }
    return checkedperiods.join();
};

checkDataPresenceFilter = function() {
    switch (true) {
        case $("#alldoc").prop("checked") :
            dataPresenseDDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Наличие данных</div>');
            return '-1';
        case $("#filleddoc").prop("checked") :
            dataPresenseDDown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Наличие данных</div>');
            return '1';
        case $("#emptydoc").prop("checked") :
            dataPresenseDDown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Наличие данных</div>');
            return '0';
    }
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
    let searchField = $("<input class='jqx-input jqx-widget-content jqx-rc-all' id='searchField' type='text' style='height: 27px; float: left; width: 150px;' />");
    let clearfilters = $("<input id='clearfilters' type='button' value='Очистить фильтр'/>");
    //let audit = $("<input id='ChangeAudutStatus' type='button' value='Проверка отчета' />");
    //let statewindow = $("#changeAuditStateWindow");
/*    if (audit_permission) {
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
    }*/

    let editform = $("<i style='margin-left: 2px;height: 14px' class='fa fa-edit fa-lg' title='Редактировать форму' />");
    let word_export = $("<i style='margin-left: 2px;height: 14px' class='fa fa-file-word-o fa-lg' title='Экспортировать документ в MS Word'></i>");
    let excel_export = $("<i style='margin-left: 2px;height: 14px' class='fa fa-file-excel-o fa-lg' title='Экспортировать данные документа в MS Excel'></i>");
    let message_input = $("<i style='margin-left: 2px;height: 14px' class='fa fa-commenting-o fa-lg' title='Сообщение/комментарий к документу'></i>");
    let doc_info = $("<i style='margin-left: 2px;height: 14px' class='fa fa-info fa-lg' title='Информация о документе'></i>");
    let refresh_list = $("<i style='margin-left: 2px;height: 14px' class='fa fa-refresh fa-lg' title='Обновить список'></i>");
    let changestatus = $("<input id='openChangeStateWindow' type='button' value='Статус отчета' />");
    let records = $('<span class="text-info pull-right" style="margin: 5px"> документов: <span id="totalrecords">'+ dgridDataAdapter.totalrecords +'</span></span>');

    toolbar.append(container);
    container.append(searchField);
    container.append(clearfilters);
    if (current_user_role !== '2') {
        container.append(changestatus);
    }
/*    if (audit_permission) {
        container.append(audit);
        audit.jqxButton({ theme: theme });
    }*/
    container.append(editform);
    container.append(message_input);
    container.append(word_export);
    container.append(excel_export);
    if (current_user_role === '3' || current_user_role === '4') {
        container.append(doc_info);
    }
    container.append(refresh_list);
    container.append(records);
    searchField.addClass('jqx-widget-content-' + theme);
    searchField.addClass('jqx-rc-all-' + theme);
    searchField.jqxInput({ width: '200px', height: '26px', placeHolder: "Медицинская организация" });
    clearfilters.jqxButton({ theme: theme });
    editform.jqxButton({ theme: theme });
    changestatus.jqxButton({ theme: theme });
    word_export.jqxButton({ theme: theme });
    excel_export.jqxButton({ theme: theme });
    message_input.jqxButton({ theme: theme });
    doc_info.jqxButton({ theme: theme });
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
        let stateWindow = $('#changeStateWindow');
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        //let alert_message;
        let this_document_state = '';
        if (rowindex === -1 && typeof rowindex !== 'undefined') {
            return false;
        }
        //let offset = dgrid.offset();
        //stateWindow.jqxWindow({ position: { x: parseInt(offset.left) + 100, y: parseInt(offset.top) + 100 } });
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
        } else if ((current_user_role === '3' || current_user_role === '4') && this_document_state !== 'performed') {
            $('#declined').jqxRadioButton('enable');
        }
        stateWindow.jqxWindow('open');
    });
    message_input.click(function () {
        let sm = $("#sendMessageWindow");
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        if (rowindex === -1) {
            return false;
        }
        $("#message").val("");
        sm.jqxWindow('open');
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
    doc_info.click(function () {
        docinfoWindow.jqxWindow('open');
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
/*    if (audit_permission) {
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
    }*/
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
    mondropdown.jqxDropDownButton('setContent', '<div style="margin: 9px">Мониторинги</div>');
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
    dataPresenseDDown.jqxDropDownButton({width: 350, height: 32, theme: theme});
    dataPresenseDDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Наличие данных</div>');
    dataPresenseDDown.on('close', function () {
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
    mondropdown.jqxDropDownButton('setContent', '<div style="margin: 9px">Мониторинги</div>');
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
    $("#alldoc").prop('checked', 'checked');
    dataPresenseDDown.jqxDropDownButton('setContent', '<div style="margin: 9px">Наличие данных</div>');
    updatedocumenttable();
};
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
    motree.on('bindingComplete', function (event) {
        let tree = motree.jqxTreeGrid('getRows');
        var traverseTree = function(tree)
        {
            for(var i = 0; i < tree.length; i++)
            {
                ouarray.push({ id: tree[i].id, parent: tree[i].parent ? tree[i].parent['id'] : null, unit: tree[i].unit_name}) ;
                if (tree[i].records) {
                    traverseTree(tree[i].records);
                }
            }
        };
        traverseTree(tree);
        oucount = ouarray.length;
    });
    motree.jqxTreeGrid(
        {
            width: 770,
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
                motree.jqxTreeGrid('expandRow', 0);
            },
            columns: [
                { text: 'Код', dataField: 'unit_code', width: 170 },
                { text: 'Наименование', dataField: 'unit_name', width: 585 }
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
            width: '670px',
            height: '600px',
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
initStateList = function() {
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
initDataPresens = function() {
    if (current_user_role === '3' || current_user_role === '4' ) {
        dataPresenseDDown.show();
    }
    $("#applyDataPresence").click( function (event) {
        dataPresenseDDown.jqxDropDownButton('close');
    });
    switch (checkedfilled) {
        case '-1' :
            $("#alldoc").prop('checked', 'checked');
            break;
        case '1' :
            $("#filleddoc").prop('checked', 'checked');
            break;
        case '0' :
            $("#emptydoc").prop('checked', 'checked');
            break;
    }
};
// инициализация вкладок с документами
initdocumentstabs = function() {
    $("#documenttabs").jqxTabs({  height: '100%', width: '100%', theme: theme });
    let bc = makeMOBreadcrumb(current_top_level_node);
    primary_mo_bc.html(bc);
    dgrid.on("bindingcomplete", function (event) {
        $("#totalrecords").html(dgridDataAdapter.totalrecords);
        dgrid.jqxGrid('selectrow', 0);
    });
    dgrid.jqxGrid(
        {
            width: '100%',
            height: '100%',
            source: dgridDataAdapter,
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
        let bc = '';
        if (typeof row === 'undefined') {
            return false;
        }
        let murl = docmessages_url + 'document=' + row.id;
        doc_id = row.id;
        docstate_id = row.stateid;
        current_document_form_code = row.form_code;
        current_document_form_name = row.form_name;
        current_document_ou_name = row.unit_name;
        doc_state = row.state;
        bc = makeMOBreadcrumb(row.ou_id);
        primary_mo_bc.html(bc);
        $.ajax({
            dataType: 'json',
            url: murl,
            method: 'GET',
            beforeSend: function (xhr) {
                let loadmessage = "<div class='row' style='margin: 0 0 -15px -15px'>" +
                    "   <div class='col-md-12' style='padding: 20px'>" +
                    "       <h5>Загрузка сообщений <img src='/jqwidgets/styles/images/loader-small.gif' /></h5>" +
                    "   </div>" +
                    "</div>";
                $("#DocumentMessages").html(loadmessage);
            },
            success: function (data, status, xhr) {
                if (data.length === 0) {
                    let message = "<div class='row' style='margin: 0 0 -15px -15px'>" +
                        "   <div class='col-md-12' style='padding: 20px'>" +
                        "       <p class='text text-info'>Нет сообщений для данного документа</p>" +
                        "   </div>" +
                        "</div>";
                    $("#DocumentMessages").html(message);
                }
                else {
                    let items = [];
                    $.each( data, function( key, val ) {
                        let worker = 'н/д';
                        let description = '';
                        let wtel = 'н/д';
                        let ctel = 'н/д';
                        let fn = '';
                        let pn = '';
                        let ln = '';
                        if (val.worker !== null) {
                            let pr = val.worker.profiles;
                            for (let i = 0; i < pr.length; i++) {
                                switch (true) {
                                    case (pr[i].tag === 'tel' && pr[i].attribute === 'working') :
                                        wtel = pr[i].value;
                                        break;
                                    case (pr[i].tag === 'tel' && pr[i].attribute === 'cell') :
                                        ctel = pr[i].value;
                                        break;
                                    case (pr[i].tag === 'firstname') :
                                        fn = pr[i].value;
                                        break;
                                    case (pr[i].tag === 'patronym') :
                                        pn = pr[i].value;
                                        break;
                                    case (pr[i].tag === 'lastname') :
                                        ln = pr[i].value;
                                        break;
                                }
                            }
                            description = val.worker.description === '' ? ln + ' ' + fn + ' ' + pn : val.worker.description;
                        }
                        let mark_as_unread = val.is_read_count === 1 ? "" : "info";
                        //let m = "<tr class='"+ mark_as_unread + "'>";
                        let m = "<tr class='"+ "'>";
                        m += "<td style='width: 120px'><p class='text-info'>" + formatDate(val.created_at) + "</p></td>";
                        m += '<td style="width: 20%">' +
                            '<div class="dropdown">' +
                            '  <button class="btn btn-sm btn-link dropdown-toggle" style="padding: 0" type="button" id="menu1" data-toggle="dropdown">' + description + '</button>' +
                            '  <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">' +
                            '    <li role="presentation"><a role="menuitem" href="mailto:' + val.worker.email + '?subject=Вопрос по заполнению формы ' + current_document_form_code +'">' +
                            '       <small>e-mail: ' + val.worker.email + '</small></a></li>' +
                            '    <li role="presentation"><a role="menuitem" href="tel:'+ wtel +'"><small>Рабочий телефон: '+ wtel +'</small></a></li>' +
                            '    <li role="presentation"><a role="menuitem" href="tel:'+ ctel +'"><small>Сотовый телефон: '+ ctel +'</small></a></li>' +
                            '  </ul>' +
                            '</div>' +
                            '</td>';
                        m += "<td><p class='text-info'>" + val.message + "</p></td>";
                        m +="</tr>";
                        items.push(m);
                    });
                    $("#DocumentMessages").html("<table class='table table-bordered table-condensed table-hover table-striped' style='width: 100%'>" + items.join( "" ) + "</table>");
                }
            },
            error: xhrErrorNotificationHandler
        });

/*        $.getJSON( murl, function( data ) {

        });*/
        if (docinfoWindow.jqxWindow('isOpen')) {
            setDocInfo(event.args.rowindex);
        }
/*        let aurl = docauditions_url + 'document=' + row.id;
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
        });*/
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
            height: '94%',
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
    $("#openMessagesListWindow").on('click', function(event) {
        let bootstrap_link = "<link href='/bootstrap/css/bootstrap.css' rel='stylesheet' type='text/css'>";
        let table = $('#DocumentMessages').clone();
        let link_to_print ="<a href='#' onclick='window.print()'>Распечатать</a>";
        let header = "<h4>Комментарии к форме №" + current_document_form_code + " \"" + current_document_form_name + "\"";
        header += " по учреждению: " + current_document_ou_name +"</h4>";
        let pWindow = window.open("", "messagesWindow", "width=1000, height=600, scrollbars=yes");
        table.find('td').addClass('small');
        pWindow.document.write(bootstrap_link + link_to_print + header + table.html());
    });
};
// Аудит документов
initauditionproperties = function() {
    let clAudit = $("#CancelAuditStateChanging");
    let svAudit = $("#SaveAuditState");
        $('#DocumentPropertiesSplitter').jqxSplitter({
        width: '100%',
        height: '95%',
        theme: theme,
        orientation: 'vertical',
        panels: [{ size: '80%', min: 100, collapsible: false }, { min: '100px', collapsible: true}]
    });
    $("#auditExpander").jqxExpander({toggleMode: 'none', showArrow: false, width: "100%", height: "100%", theme: theme  });
    $("#openAuditionListWindow").on('click', function(event) {
        let print_style = "<style>.printlist { font-size: 0.8em; } .control_result { border: 1px solid #7f7f7f; width: 400;";
        print_style += "border-collapse: collapse; margin-bottom: 10px; } .control_result td { border: 1px solid #7f7f7f; } </style>";
        let link_to_print ="<a href='#' onclick='window.print()'>Распечатать</a>";
        let header = "<h3>Перечень проверок формы №" + current_document_form_code + " " + current_document_form_name;
        header += " по учреждению: " + current_document_ou_name +"</h3>";
        let pWindow = window.open("", "messagesWindow", "width=1000, height=600, scrollbars=yes");
        pWindow.document.write(print_style + link_to_print + header + $("#DocumentAuditions").html());
    });
    $("#changeAuditStateWindow").jqxWindow({
        width: 430,
        height: 300,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: clAudit,
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
    svAudit.jqxButton({ theme: theme });
    clAudit.jqxButton({ theme: theme });
    svAudit.click(function () {
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        let row_id = dgrid.jqxGrid('getrowid', rowindex);
        let radiostates = $('.auditstateradio');
        let message = $("#auditChangeMessage").val();
        let old_state;
        $.each(current_document_audits, function(key, value) {
            if (value.auditor_id === current_user_id) {
                old_state = value.state_id;
            }
        });
        let selected_state;
        radiostates.each(function() {
            if ($(this).jqxRadioButton('checked')) {
                selected_state = $(this).attr('id');
            }
        });
        let data = "&document=" + row_id + "&auditstate=" + selected_state + "&message=" + message;
        if (audit_state_ids[selected_state] !== old_state) {
            $.ajax({
                dataType: 'json',
                url: changeaudition_url,
                method: 'POST',
                data: data,
                success: function (data, status, xhr) {
                    if (data.audit_status_changed === 1) {
                        $("#currentInfoMessage").text("Статус проверки документа изменен");
                        $("#infoNotification").jqxNotification("open");
                        dgrid.jqxGrid('selectrow', rowindex);
                    }
                    else {
                        $("#currentError").text("Статус проверки документа не изменен!");
                        $("#serverErrorNotification").jqxNotification("open");
                    }
                },
                error: xhrErrorNotificationHandler
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
        let rowindex = agrid.jqxGrid('getselectedrowindex');
        let row_id = agrid.jqxGrid('getrowid', rowindex);
        let radiostates = $('.batchauditstateradio');
        let message = $("#AuditBatchChangeMessage").val();
        let selected_state;
        radiostates.each(function() {
            if ($(this).jqxRadioButton('checked')) {
                selected_state = $(this).attr('id');
            }
        });
        let data = "aggregate=" + row_id + "&batchauditstate=" + selected_state + "&message=" + message;
        $.ajax({
            dataType: 'json',
            url: 'change_batch_audit_state.php',
            data: data,
            success: function (data, status, xhr) {
                let comment = data.responce.comment;
                if (data.responce.audit_status_changed > 0) {
                    let m = "Статус проверки для всех входящих в сводный отчет документов (" + data.responce.audit_status_changed + ") изменен"
                    $("#currentInfoMessage").text(m);
                    $("#infoNotification").jqxNotification("open");
                    dgrid.jqxGrid('selectrow', rowindex);
                }
                else if (data.responce.audit_status_changed === 0) {
                    $("#currentError").text("Статус проверки документов не изменен! " + comment);
                    $("#serverErrorNotification").jqxNotification("open");
                } else if (data.responce.audit_status_changed === -1) {
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

};

// инициализация всплывающих окон с формами ввода сообщения и т.д.
initpopupwindows = function() {

};
// Инициализация окна с информацией о документе (последние исправления, смены статуса, принятие разделов документа)
initdocinfowindow = function() {
    docinfoWindow.jqxWindow({
        width: 850,
        height: 800,
        position: 'center',
        resizable: true,
        isModal: false,
        autoOpen: false,
        theme: theme
    });
    docinfoWindow.on('open', function () {
        let rowindex = dgrid.jqxGrid('getselectedrowindex');
        setDocInfo(rowindex);
    });
};
function setDocInfo(rowindex) {
    let row_id = dgrid.jqxGrid('getrowid', rowindex);
    let rowdata = dgrid.jqxGrid('getrowdata', rowindex);
    if (rowindex === -1) {
        rec_tbody.html('');
        return false;
    }
    docinfoWindow.jqxWindow('setTitle', 'Сводная информация по документу №' + row_id);
    $.getJSON( docinfo_url + row_id, function( data ) {
        let rec = data.records;
        let st = data.states;
        let sec = data.sections;
        let rec_tbody = $("#valueChangingRecords");
        let st_tbody = $("#stateChangingRecords");
        let sec_tbody = $("#sectionChangingRecords");
        let rec_rows = '';
        let st_rows = '';
        let sec_rows = '';

        if (rec.length === 0) {
            rec_rows = '<tr><td colspan="7"><p class="text-danger text-center">Нет данных</p></td></tr>';
        } else {
            for (let i = 0; i < rec.length; i++ ) {
                rec_rows += '<tr>';
                rec_rows += '<td>'+ rec[i].occured_at +'</td>';
                rec_rows += '<td>'+ rec[i].worker.description +'</td>';
                rec_rows += '<td>'+ rec[i].table.table_code +'</td>';
                rec_rows += '<td>'+ rec[i].row.row_code +'</td>';
                rec_rows += '<td>'+ rec[i].column.column_code +'</td>';
                rec_rows += '<td>'+ rec[i].oldvalue +'</td>';
                rec_rows += '<td>'+ rec[i].newvalue +'</td>' + '</tr>';
                rec_rows += '</tr>';
            }
        }
        rec_tbody.html(rec_rows);
        if (st.length === 0) {
            st_rows = '<tr><td colspan="4"><p class="text-danger text-center">Нет данных</p></td></tr>';
        } else {
            for (let i = 0; i < st.length; i++ ) {
                st_rows += '<tr>';
                st_rows += '<td>'+ st[i].occured_at +'</td>';
                st_rows += '<td>'+ st[i].worker.description +'</td>';
                st_rows += '<td>'+ st[i].oldstate.name +'</td>';
                st_rows += '<td>'+ st[i].newstate.name +'</td>';
                st_rows += '</tr>';
            }
        }
        st_tbody.html(st_rows);
        if (sec.length === 0) {
            sec_rows = '<tr><td colspan="4"><p class="text-danger text-center">Нет данных</p></td></tr>';
        } else {
            for (let i = 0; i < sec.length; i++ ) {
                sec_rows += '<tr>';
                sec_rows += '<td>'+ sec[i].occured_at +'</td>';
                sec_rows += '<td>'+ sec[i].worker.description +'</td>';
                sec_rows += '<td>'+ sec[i].section.section_name +'</td>';
                sec_rows += '<td>'+ (sec[i].blocked === true ? 'Принят' : 'Отклонен') +'</td>';
                sec_rows += '</tr>';
            }
        }
        sec_tbody.html(sec_rows);
    });
}

// Формирование строки запроса к серверу
filtersource = function() {
    let forms;
    let monitorings;
    let states = checkstatefilter();
    let filled = checkDataPresenceFilter();
    let mon_forms = getCheckedMonsForms();
    let mf = mon_forms.mf.join();
    let periods = checkPeriodFilter();
    //let forms = checkedforms.join();
    forms = mon_forms.f.join();
    monitorings = mon_forms.m.join();

    //periods = checkedperiods.join();
    return '&filter_mode=' + filter_mode + '&ou=' +current_top_level_node +'&states='+states+
        '&monitorings='+monitorings+'&forms='+forms +'&periods=' + periods + '&mf=' + mf + '&filled=' + filled;
};
// Источники данных для
initDocumentSource = function () {
    //console.log(current_filter);
    docsource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'ou_id', type: 'int' },
                { name: 'unit_code', type: 'string' },
                { name: 'unit_name', type: 'string' },
                { name: 'form_code', type: 'string' },
                { name: 'form_name', type: 'string' },
                { name: 'monitoring', type: 'string' },
                { name: 'period', type: 'string' },
                { name: 'state', type: 'string' },
                { name: 'stateid', type: 'int' },
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
                {name: 'ou_id', type: 'int'},
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
    dgridDataAdapter = new $.jqx.dataAdapter(docsource, {
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
    if (checkedstates.length > 0) {
        statusDropDown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Статусы отчетов</div>');
    }
    if (checkedfilled !== '-1') {
        dataPresenseDDown.jqxDropDownButton('setContent', '<div style="margin: 9px"><i class="fa fa-filter fa-lg pull-right" style="color: #337ab7;"></i>Наличие данных</div>');
    }
};

function makeMOBreadcrumb(ou_id) {
    let parents = getAncestors(ou_id);
    if (parents === null) {
        return '...';
    }
    let bc = '/ ';
    let all = parents.pop();
    for (i = parents.length-1; i >= 0; i--) {
        bc += parents[i] + " / ";
    }
    return bc;
}

function searchMOById(id) {
    if (id === null) {
        return null;
    }
    for (i = 0; i < oucount; i++ ) {
        if (ouarray[i].id === id) {
            return ouarray[i];
        } 
    }
    return null;
}

function getAncestors(id) {
    let ancestors = [];
    let current = searchMOById(id);
    if (current === null) {
        return null;
    }
    var traversAncestors = function(parent)
    {
        let found = searchMOById(parent);
        if (found) {
            ancestors.push(found.unit) ;
            traversAncestors(found.parent);
        }
    };
    traversAncestors(current.parent);
    return ancestors;
}

function postMessageSendActions(document) {
    //let rowindex = agrid.jqxGrid('getselectedrowindex');
    let rowindex = dgrid.jqxGrid('getrowboundindexbyid', document);
    dgrid.jqxGrid('selectrow', rowindex);
}

function postChangeStateActions(selected_state) {
    let rowdata = dgrid.jqxGrid('getrowdatabyid', doc_id);
    rowdata.state = selected_state;
    //console.log(rowdata);
    dgrid.jqxGrid('updaterow', rowdata.id, rowdata);
    dgrid.jqxGrid('selectrow', rowdata.boundindex);
}