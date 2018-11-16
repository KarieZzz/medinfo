raiseError = function(comment, xhr) {
    let add_inf = '';
    if (typeof comment === 'undefined') {
        comment = 'Ошибка получения данных ';
    }
    if (typeof xhr !== 'undefined') {
        add_inf = ' (Код ошибки ' + xhr.status + ')';
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
localize = function() {
    let localizationobj = {};
    localizationobj.thousandsseparator = " ";
    localizationobj.decimalseparator = ',';
    localizationobj.emptydatastring = "Нет данных. Установите условия отбора отчетных документов";
    localizationobj.loadtext = "Загрузка";
    localizationobj.filtershowrowstring = "Показать строки где:";
    localizationobj.filtersearchstring = "Поиск:";
    return localizationobj;
};
initnotifications = function() {
    $("#serverErrorNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 800,  autoClose: true, autoCloseDelay: 8000, template: "error"
    });
    $("#infoNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info"
    });
};
formatDate = function (dateObject) {
    let d = new Date(dateObject);
    let day = d.getDate();
    let month = d.getMonth() + 1;
    let year = d.getFullYear();
    if (day < 10) {
        day = "0" + day;
    }
    if (month < 10) {
        month = "0" + month;
    }
    return day + '.' + month + '.' + year + ' '+ d.getHours() + ':' + d.getMinutes();
};

xhrErrorNotificationHandler = function (xhr, status, errorThrown) {
    if (xhr.status === 401) {
        raiseError('Пользователь не авторизован.', xhr );
        return false;
    }
    $.each(xhr.responseJSON, function(field, errorText) {
        raiseError(errorText);
    });
};

inituserprofilewindow = function() {
    let cl = $("#cancelProfileSaving");
    let up = $("#UserProfileWindow");
    let form = $('#userProfileForm');
    up.jqxWindow({
        width: 850,
        height: 530,
        position: 'center',
        resizable: true,
        isModal: true,
        autoOpen: false,
        cancelButton: cl,
        theme: theme
    });
    $("#saveProfile").click(function () {
        if (form[0].checkValidity()) {
            $.ajax({
                url: '/userprofiles/' + current_user_id,
                data: setUserPfofile(),
                method: 'PATCH',
                success: function (data, status, xhr) {
                    if (data.saved) {
                        raiseInfo('Изменения в профиле пользователя сохранены');
                        up.jqxWindow('close');
                    } else {
                        raiseError('Изменения в профиле пользователя не сохранены');
                    }
                },
                error: xhrErrorNotificationHandler
            })
        } else {
            form[0].reportValidity();
            raiseError("Не все данные заполнены корректно");
        }
    });
    $("#openProfileEditor").click( function () {
        up.jqxWindow('setTitle', 'Профиль пользователя id ' + current_user_id);
        form[0].reset();
        up.jqxWindow('open');
    });
    up.on('open', function (event) {
        getUserProfile();
    });
};

function getUserProfile() {
    $.ajax({
        dataType: 'json',
        url: '/userprofiles/' + current_user_id + '/edit',
        method: "GET",
        beforeSend: function( xhr ) {
            $("#formloader").show();
        },
        success: function (data, status, xhr) {
            $("#formloader").hide();
            $("#lastname").val(data.lastname);
            $("#firstname").val(data.firstname);
            $("#patronym").val(data.patronym);
            $("#wtel").val(data.wtel);
            $("#ctel").val(data.ctel);
            $("#email").val(data.email);
            $("#ou").val(data.ou);
            $("#post").val(data.post);
            $("#description").val(data.description);
        },
        error: xhrErrorNotificationHandler
    });
}

function setUserPfofile() {
    return 'lastname=' + $("#lastname").val() +
        '&firstname=' + $("#firstname").val() +
        '&patronym=' + $("#patronym").val() +
        '&wtel=' + encodeURIComponent($("#wtel").val()) +
        '&ctel=' + encodeURIComponent($("#ctel").val()) +
        '&email=' + $("#email").val() +
        '&ou=' + encodeURIComponent($("#ou").val()) +
        '&post=' + encodeURIComponent($("#post").val()) +
        '&description=' + encodeURIComponent($("#description").val());
}