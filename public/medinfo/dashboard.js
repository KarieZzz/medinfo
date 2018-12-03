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
raiseEventMessage = function(comment) {
    if (typeof comment === 'undefined') {
        comment = 'Неизвестное событие';
    }
    $("#eventMessage").html(comment);
    $("#eventNotification").jqxNotification("open");
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
    $("#eventNotification").jqxNotification({
        width: "450px",
        position: "bottom-right",
        opacity: 1,
        autoClose: true,
        autoCloseDelay: 12000,
        template: null });
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

function initPusher() {
    Pusher.logToConsole = true;
    pusher = new Pusher(pkey, {
        cluster: 'eu',
        forceTLS: true
    });
    channel = pusher.subscribe('event-brodcasting-channel');
}

function initStateChangeChannel() {
    channel.bind('state-change-event', function(data) {
        if (String(data.worker_id) !== current_user_id) {
            raiseEventMessage(data.message + data.worker + data.form + data.unit + data.period );
        } else {
            console.log("Сообщение скрыто от текущего пользователя - он автор события");
        }

    });
}

function initMessageSentChannel() {
    channel.bind('message-sent-event', function(data) {
        if (String(data.worker_id) !== current_user_id) {
            raiseEventMessage(data.message_header + data.message_body );
        } else {
            console.log("Сообщение скрыто от текущего пользователя - он автор события");
        }

    });
}

function initMessageFeed() {
    $("#messageFeedToggle").on('click', function () {
        if (messagefeed.is(':hidden')) {
            setTimeout(function () {
                if (messagefeed.is(':visible')) {
                    messagefeed_readts = Date.now()/1000;
                    $.ajax({
                        dataType: 'json',
                        url: '/message/setlastreadtimestamp/' + messagefeed_readts,
                        method: 'POST',
                        success: function (data, status, xhr) {
                            $("#newMessagesBadge").text('');
                        },
                        error: xhrErrorNotificationHandler
                    });
                }
            }, 2000);
        }
    });
    getLatestMessages();
}

function getLatestMessages() {
    $.ajax({
        dataType: 'json',
        url: '/fetchlatestmessages',
        method: "GET",
        beforeSend: function( xhr ) {
            $("#formloader").show();
        },
        success: function (data, status, xhr) {
            let newsection = $('<div></div>');
            let newheader = $('<div class="row" style="margin:0; background-color:#f5f5f5"><div class="col-md-12"><h6 class="text">НОВОЕ</h6></div></div>');
            newsection.append(newheader);
            let oldsection = $('<div></div>');
            let oldheader = $('<div class="row" style="margin:0; background-color:#f5f5f5"><div class="col-md-12"><h6 class="text">РАНЬШЕ</h6></div></div>');
            oldsection.append(oldheader);
            let badge_count = 0;

            let m =  data.messages;
            for(let i=0; i < m.length; i++) {
                let mark = 'bg-info';
                let mpanel = $('<div class="row '+ mark +'" style="margin:0"></div>');
                let mcontent = '<div class="col-md-1"><p class="text text-center"><i class="fa fa-comment-o fa-lg"></i></p></div>' +
                    '<div class="col-md-11"><p class="text"><strong>' + m[i].worker.description + ': </strong>' + m[i].message +'</p></div>';
                let dpanel = $('<div class="row '+ mark + '" style="margin:0; border-bottom-color:#00a7d0; border-bottom-style:dotted; border-bottom-width: 1px"></div>');
                let dcontent = '<div class="col-md-1"></div>' +
                    '<div class="col-md-7"><p class="text small"><i class="fa fa-map-o"></i> Форма ' + m[i].document.form.form_code + ' ' + m[i].document.unit.unit_name +'</p></div>' +
                    '<div class="col-md-4"><p class="text small"><i class="fa fa-clock-o"></i> ' + formatDate(m[i].created_at) + '</p></div>';
                mpanel.append(mcontent);
                dpanel.append(dcontent);
                if (data.ts < m[i].CreatedTS) {
                    newsection.append(mpanel);
                    newsection.append(dpanel);
                    badge_count++;
                } else {
                    oldsection.append(mpanel);
                    oldsection.append(dpanel);
                }
            }
            if (badge_count > 0) {
                messagefeed.append(newsection);
                $("#newMessagesBadge").text(badge_count);
            }
            messagefeed.append(oldsection);

        },
        error: xhrErrorNotificationHandler
    });
}