var raiseError = function(xhr, comment) {
    if (typeof comment == 'undefined') {
        comment = 'Ошибка получения данных ';
    }
    $("#currentError").text(comment +  ' (' + xhr.status + ' ' + xhr.statusText + ')');
    $("#serverErrorNotification").jqxNotification("open");
};
var raiseInfo = function(comment) {
    if (typeof comment == 'undefined') {
        comment = 'Текст информационного сообщения по умолчанию ';
    }
    $("#currentInfoMessage").text(comment);
    $("#infoNotification").jqxNotification("open");
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
        autoOpen: false, animationOpenDelay: 800, autoClose: false, template: "error"
    });
    $("#infoNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info"
    });
}