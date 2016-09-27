/**
 * Created by shameev on 31.08.2016.
 */
var raiseError = function(comment, xhr) {
    if (typeof comment == 'undefined') {
        var comment = 'Ошибка получения данных ';
    }
    if (typeof xhr != 'undefined') {
        var add_inf = ' (Код ошибки ' + xhr.status + ')';
    } else {
        var add_inf = '';
    }
    $("#currentError").text(comment + add_inf);
    $("#serverErrorNotification").jqxNotification("open");
};
var raiseInfo = function(comment) {
    if (typeof comment == 'undefined') {
        comment = 'Текст информационного сообщения по умолчанию ';
    }
    $("#currentInfoMessage").text(comment);
    $("#infoNotification").jqxNotification("open");
};

raiseConfirm = function(confirmMessage, event) {
    //event.stopImmediatePropagation();;
    $("#okButton").off( "click" );
    $("#confirmMessage").html(confirmMessage);
    $('#confirmPopup').jqxWindow('open');
};
hideConfirm = function() {
    $('#confirmPopup').jqxWindow('close');
};

var localize = function() {
    var localizationobj = {};
    localizationobj.thousandsseparator = " ";
    localizationobj.emptydatastring = "Нет данных";
    localizationobj.loadtext = "Загрузка..";
    localizationobj.filtershowrowstring = "Показать строки где:";
    localizationobj.filtersearchstring = "Поиск:";
    return localizationobj;
};
var initnotifications = function() {
    $("#serverErrorNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 6000, template: "error"
    });
    $("#infoNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info"
    });
}

var initConfirmWindow = function() {
    var confirm = $('#confirmPopup').jqxWindow({
        width: 400,
        height: 250,
        resizable: false,
        autoOpen: false,
        isModal: true,
        okButton: $('#okButton'),
        cancelButton: $('#cancelButton')
    });
    $("#cancelButton").click( function() { return false } );
    $('#confirmPopup').on('close', function (event) {
        confirm_action = event.args.dialogResult.OK;
    });

}