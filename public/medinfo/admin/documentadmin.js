/**
 * Created by shameev on 28.06.2016.
 */
// Инициализация источников данных для таблиц
var datasources = function() {
    var mo_source =
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
        url: 'fetch_mo_tree/0'
    };
    docsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'doctype', type: 'string' },
            { name: 'unit_name', type: 'string' },
            { name: 'period', type: 'string' },
            { name: 'form_code', type: 'string' },
            { name: 'state', type: 'string' },
            { name: 'filled', type: 'bool' }
        ],
        id: 'id',
        url: docsource_url+'&ou='+current_top_level_node+'&states='+checkedstates.join()+'&forms='+checkedform.join()+'&periods='+checkedperiods.join(),
        root: null
    };
    mo_dataAdapter = new $.jqx.dataAdapter(mo_source);
    dataAdapter = new $.jqx.dataAdapter(docsource, {
        loadError: function(jqXHR, status, error) {
            if (jqXHR.status == 401) {
                raiseError(jqXHR, 'Пользователь не авторизован');
            }
        }
    });
};
var checkformfilter = function() {
    var checkformboxes = $('.formbox');
    checkedform = [];
    checkformboxes.each(function() {
        if ($(this).jqxCheckBox('checked')) {
            checkedform.push($(this).attr('id'));
        }
    });
};
var checkstatefilter = function() {
    var checkboxes = $('.statebox');
    checkedstates = [];
    var i = 0;
    checkboxes.each(function() {
        if ($(this).jqxCheckBox('checked')) {
            checkedstates.push($(this).attr('id'));
        }
        i++;
    });
};
var checkperiodfilter = function() {
    var checkboxes = $('.periodbox');
    checkedperiods = [];
    var i = 0;
    checkboxes.each(function() {
        if ($(this).jqxCheckBox('checked')) {
            checkedperiods.push($(this).attr('id'));
        }
        i++;
    });
};
// обновление таблиц первичных и сводных документов в зависимости от выделенных форм, периодов, статусов документов
var updatedocumenttable = function() {
    var old_doc_url = docsource.url;
    var states = checkedstates.join();
    var forms = checkedform.join();
    var periods = checkedperiods.join();
    var new_filter =  '&ou=' +current_top_level_node +'&states='+states+'&forms='+forms+'&periods=' + periods;
    var new_doc_url = docsource_url + new_filter;
    if (new_doc_url != old_doc_url) {
        docsource.url = new_doc_url;
        $('#documentList').jqxGrid('clearselection');
        $('#documentList').jqxGrid('updatebounddata');
    }
};
// Рендеринг панели инструментов для таблицы первичных документов
var initmotree = function() {
    $("#moTreeContainer").jqxPanel({width: '100%', height: '100%'});
    $("#moTree").jqxTreeGrid(
        {
            width: '98%',
            height: '99%',
            theme: theme,
            source: mo_dataAdapter,
            selectionMode: "singleRow",
            filterable: true,
            filterMode: "simple",
            columnsResize: true,
            checkboxes: true,
            hierarchicalCheckboxes: true,
            ready: function () {
                $("#moTree").jqxTreeGrid('expandRow', 0);
            },
            columns: [
                {text: 'Код', dataField: 'unit_code', width: 150},
                {text: 'Наименование', dataField: 'unit_name'}
            ]
        });
    $('#moTree').on('filter',
        function (event) {
            var args = event.args;
            var filters = args.filters;
            $('#moTree').jqxTreeGrid('expandAll');
        }
    );
    $('#moTree').on('rowSelect',
        function (event) {
            var args = event.args;
            var key = args.key;
            var new_top_level_node = key;
            if (new_top_level_node == current_top_level_node) {
                return false;
            }
            current_top_level_node = key;
            updatedocumenttable();
            return true;
        });
};
var initfiltertabs = function() {
    $("#filtertabs").jqxTabs({  height: '100%', width: '100%', theme: theme });
    $("#allForms").jqxButton({theme: theme});
    $('#allForms').click(function () {
        var checkboxes = $('.formbox');
        checkboxes.each(function() {
            $(this).jqxCheckBox('check');
        });
        checkformfilter();
        updatedocumenttable();
    });
    $("#noForms").jqxButton({theme: theme});
    $('#noForms').click(function () {
        var checkboxes = $('.formbox');
        checkboxes.each(function() {
            $(this).jqxCheckBox('uncheck');
        });
        checkformfilter();
        updatedocumenttable();
    });
    $(".formbox").jqxCheckBox({ width: 70, height: 20, enableContainerClick: false, theme: theme, checked: true});
    $('.formbox').on('click', function (event) {
        checkformfilter();
        updatedocumenttable();
    });
    $("#allStates").jqxButton({theme: theme});
    $('#allStates').click(function () {
        var checkboxes = $('.statebox');
        checkboxes.each(function() {
            $(this).jqxCheckBox('check');
        });
        checkstatefilter();
        updatedocumenttable();
    });
    $("#noStates").jqxButton({theme: theme});
    $('#noStates').click(function () {
        var checkboxes = $('.statebox');
        checkboxes.each(function() {
            $(this).jqxCheckBox('uncheck');
        });
        checkstatefilter();
        updatedocumenttable();
    });
    $("#formcheckboxesPanel").jqxPanel({ width: '100%', height: '98%'});

    $(".statebox").jqxCheckBox({ width: 120, height: 25, theme: theme, enableContainerClick: false, checked: true});
    $('.statebox').on('click', function (event) {
        checkstatefilter();
        updatedocumenttable();
    });
    $(".periodbox").jqxCheckBox({ width: 120, height: 25, enableContainerClick: false, theme: theme });
    $("#pl02345l0").jqxCheckBox('checked', true);
/*    $("#pl02345j0").jqxCheckBox({ width: 120, height: 25, enableContainerClick: false, theme: theme });
    $("#pl02345k0").jqxCheckBox({ width: 190, height: 25, enableContainerClick: false, theme: theme});
    $("#pl02345l0").jqxCheckBox({ width: 120, height: 25, enableContainerClick: false, theme: theme, checked: true});*/
    $('.periodbox').on('click', function (event) {
        checkperiodfilter();
        updatedocumenttable();
    });
    $("#allPeriods").jqxButton({theme: theme});
    $('#allPeriods').click(function () {
        var checkboxes = $('.periodbox');
        checkboxes.each(function() {
            $(this).jqxCheckBox('check');
        });
        checkperiodfilter();
        updatedocumenttable();
    });
    $("#noPeriods").jqxButton({theme: theme});
    $('#noPeriods').click(function () {
        var checkboxes = $('.periodbox');
        checkboxes.each(function() {
            $(this).jqxCheckBox('uncheck');
        });
        checkperiodfilter();
        updatedocumenttable();
    });
}
var initdocumentslist = function() {
    $("#documentList").jqxGrid(
        {
            width: '98%',
            height: '98%',
            theme: theme,
            source: dataAdapter,
            columnsresize: true,
            selectionmode: 'checkbox',
            columns: [
                { text: '№', datafield: 'id', width: '60px' },
                { text: 'Тип', datafield: 'doctype' , width: '100px'},
                { text: 'МО', datafield: 'unit_name', width: '400px' },
                { text: 'Период', datafield: 'period', width: '100px' },
                { text: 'Форма', datafield: 'form_code', width: '100px'  },
                { text: 'Статус', datafield: 'state' },
                { text: 'Данные', datafield: 'filled', columntype: 'checkbox', width: 100 }
            ]
        });
};
var initpopupwindows = function() {
    $("#changeStateWindow").jqxWindow({
        width: 430,
        height: 360,
        resizable: false,
        isModal: true,
        autoOpen: false,
        cancelButton: $("#CancelStateChanging"),
        theme: theme,
        modalOpacity: 0.01
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
        var rowindex = $('#Documents').jqxGrid('getselectedrowindex');
        var rowdata = $('#Documents').jqxGrid('getrowdata', rowindex);
        var row_id = $('#Documents').jqxGrid('getrowid', rowindex);
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
                    $('#Documents').jqxGrid('updaterow', row_id, rowdata);
                    $('#Documents').jqxGrid('selectrow', rowindex);
                }
                else {
                    $("#currentError").text("Статус не изменен!");
                    $("#serverErrorNotification").jqxNotification("open");
                    // TODO: Обработать ошибку изменения статуса
                }
            },
            error: function (xhr, status, errorThrown) {
                $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                    + status + ". Обратитесь к администратору.");
                $("#serverErrorNotification").jqxNotification("open");
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
        theme: theme,
        modalOpacity: 0.01
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
        var rowindex = $('#Documents').jqxGrid('getselectedrowindex');
        var row_id = $('#Documents').jqxGrid('getrowid', rowindex);
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
                        $('#Documents').jqxGrid('selectrow', rowindex);
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
        var rowindex = $('#Aggregates').jqxGrid('getselectedrowindex');
        var row_id = $('#Aggregates').jqxGrid('getrowid', rowindex);
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
                    $('#Documents').jqxGrid('selectrow', rowindex);
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
        var rowindex = $('#Documents').jqxGrid('getselectedrowindex');
        var rowdata = $('#Documents').jqxGrid('getrowdata', rowindex);
        var row_id = $('#Documents').jqxGrid('getrowid', rowindex);
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
                    $('#Documents').jqxGrid('selectrow', rowindex);
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
        theme: theme,
        modalOpacity: 0.01
    });
}
