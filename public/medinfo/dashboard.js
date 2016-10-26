var raiseError = function(comment, xhr) {
    var add_inf = '';
    if (typeof comment == 'undefined') {
        comment = 'Ошибка получения данных ';
    }
    if (typeof xhr !== 'undefined') {
        add_inf = ' (Код ошибки ' + xhr.status + ')';
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
        autoOpen: false, animationOpenDelay: 800,  autoClose: true, autoCloseDelay: 8000, template: "error"
    });
    $("#infoNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info"
    });
}