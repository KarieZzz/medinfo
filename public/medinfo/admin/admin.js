/**
 * Created by shameev on 31.08.2016.
 */
raiseError = function(comment, xhr) {
    let add_inf;
    if (typeof comment === 'undefined') {
        comment = 'Ошибка получения данных ';
    }
    if (typeof xhr !== 'undefined') {
        add_inf = ' (Код ошибки ' + xhr.status + ')';
    } else {
        add_inf = '';
    }
    $("#currentError").text(comment + add_inf);
    $("#serverErrorNotification").jqxNotification("open");
};

raiseInfo = function(comment) {
    if (typeof comment === 'undefined') {
        comment = 'Текст информационного сообщения по умолчанию ';
    }
    $("#currentInfoMessage").text(comment);
    $("#infoNotification").jqxNotification("open");
};

raiseConfirm = function(confirmMessage) {
    $("#confirmMessage").html(confirmMessage);
    confirmpopup.jqxWindow('open');
};

hideConfirm = function() {
    $('#confirmPopup').jqxWindow('close');
};

localize = function() {
    let localizationobj = {};
    localizationobj.thousandsseparator = " ";
    localizationobj.emptydatastring = "Нет данных";
    localizationobj.loadtext = "Загрузка";
    localizationobj.filtershowrowstring = "Показать строки где:";
    localizationobj.filtersearchstring = "Поиск:";
    return localizationobj;
};

initnotifications = function() {
    $("#serverErrorNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 600, autoClose: true, autoCloseDelay: 8000, template: "error"
    });
    $("#infoNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 600, autoClose: true, autoCloseDelay: 5000, template: "info"
    });
};

initConfirmWindow = function() {
    var cbutton = $('#cancelButton');
    var okbutton = $('#okButton');
    confirmpopup.jqxWindow({
        width: 400,
        height: 250,
        resizable: false,
        autoOpen: false,
        isModal: true,
        okButton: okbutton,
        cancelButton: cbutton
    });
    okbutton.click( function() { performAction() } );
    confirmpopup.on('close', function (event) {
        confirm_action = event.args.dialogResult.OK;
    });

    xhrErrorNotificationHandler = function (xhr, status, errorThrown) {
    if (xhr.status === 401) {
        raiseError('Пользователь не авторизован.', xhr );
        return false;
    }
    $.each(xhr.responseJSON, function(field, errorText) {
        raiseError(errorText);
    });

}

};