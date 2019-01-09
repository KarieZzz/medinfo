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
        getLatestMessages();
    });
}

function initMessageSentChannel() {
    channel.bind('message-sent-event', function(data) {
        if (String(data.worker_id) !== current_user_id) {
            raiseEventMessage(data.message_header + data.message_body );
        } else {
            console.log("Сообщение скрыто от текущего пользователя - он автор события");
        }
        getLatestMessages();
    });
}

function initMessageFeed() {
    messagefeedtoggle.on('click', function () {
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
        if (messagefeed.is(':visible')) {
            setTimeout(function () {
                if (messagefeed.is(':hidden')) {
                    getLatestMessages();
                }
            }, 4000);
        }
    });
    messagefeedtoggle.on("hidden.bs.dropdown", function(event){
        setTimeout(function () {
            if (messagefeed.is(':hidden')) {
                getLatestMessages();
            }
        }, 2000);
    });
    $("#refreshMessageFeed").on('click', function (e) {
        getLatestMessages();
        e.stopPropagation();
        e.preventDefault();
    });
    $("#markAllAsRead").on('click', function (e) {
        markAllAsRead();
        e.stopPropagation();
        e.preventDefault();
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
            messagefeed.text('');
        },
        success: function (data, status, xhr) {
            $("#formloader").hide();
            let newsection = $('<div></div>');
            let newheader = $('<div class="row" style="margin:0; background-color:#f5f5f5"><div class="col-md-12"><h6 class="text">НОВОЕ</h6></div></div>');
            newsection.append(newheader);
            let oldsection = $('<div></div>');
            let oldheader = $('<div class="row" style="margin:0; background-color:#f5f5f5"><div class="col-md-12"><h6 class="text">РАНЬШЕ</h6></div></div>');
            oldsection.append(oldheader);
            let badge_count = 0;
            let m =  data.messages;
            for(let i=0; i < m.length; i++) {
                let description = '';
                let fn = '';
                let pn = '';
                let ln = '';
                if (m[i].worker !== null) {
                    let pr = m[i].worker.profiles;
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
                    description = m[i].worker.description === '' ? ln + ' ' + fn + ' ' + pn : m[i].worker.description;
                }
                //let mark = 'bg-info';
                let mark = m[i].is_read_count === 1 ? "" : "bg-info";
                let mpanel = $('<div class="row '+ mark +'" style="margin:0"></div>');
                let mcontent = '<div class="col-md-1"><p class="text text-center"><i class="fa fa-comment-o fa-lg"></i></p></div>' +
                    '<div class="col-md-11"><p class="text text-info small" style="margin: 0"><strong>' + description + ': </strong>' + m[i].message +'</p></div>';
                let dpanel = $('<div class="row '+ mark + '" style="margin:0; border-bottom-color:#00a7d0; border-bottom-style:dotted; border-bottom-width: 1px"></div>');
                let dcontent = '<div class="col-md-1"></div>' +
                    '<div class="col-md-7"><p class="text small"><i class="fa fa-map-o"></i> ' +
                    '<a href="/datainput/formdashboard/'+ m[i].document.id + '" target="_blank">Форма ' + m[i].document.form.form_code + ' ' + m[i].document.unit.unit_name +'</a>' +
                    '</p></div>' +
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
            } else if (badge_count === 50) {
                $("#newMessagesBadge").text('50+');
            }
            messagefeed.append(oldsection);
        },
        error: xhrErrorNotificationHandler
    });
}

function markAllAsRead() {
    $.ajax({
        url: '/message/setlastreadtimestamp',
        method: 'PATCH',
        beforeSend: function( xhr ) {
            $("#formloader").show();
            messagefeed.text('');
        },
        success: function (data, status, xhr) {
            getLatestMessages();
        },
        error: xhrErrorNotificationHandler
    });
}
// Комментирование документа/отправка сообщения к документу
function initSendMessage() {
    let sm = $("#sendMessageWindow");
    if (sm.length) {
        let clMessage = $("#CancelMessage");
        let sendMessage = $("#SendMessage");
        let message_input = $("#openSendMessageWindow");
        let docmessagesend_url = '/datainput/sendmessage';
        $('#message').jqxTextArea({ placeHolder: 'Оставьте свой комментарий к выбранному документу', height: 150, width: 400, minLength: 1 });
        clMessage.jqxButton({ theme: theme });
        sendMessage.jqxButton({ theme: theme });
        sendMessage.click(function () {
            //let rowindex = dgrid.jqxGrid('getselectedrowindex');
            //let rowdata = dgrid.jqxGrid('getrowdata', rowindex);
            //let row_id = dgrid.jqxGrid('getrowid', rowindex);
            let message = encodeURIComponent($("#message").val());
            message = message.trim();
            if (message.length === 0) {
                return false;
            }
            let data = "&document=" + doc_id + "&message=" + message;
            $.ajax({
                dataType: 'json',
                url: docmessagesend_url,
                method: "POST",
                data: data,
                success: function (data, status, xhr) {
                    let m = '';
                    if (data.message_sent === true) {
                        raiseInfo("Сообщение отправлено");
                        if (typeof (postMessageSendActions) === 'function') {
                            postMessageSendActions(doc_id);
                        }
                    }
                },
                error: xhrErrorNotificationHandler
            });
            sm.jqxWindow('hide');
        });
        sm.jqxWindow({
            width: 430,
            height: 260,
            resizable: false,
            isModal: true,
            autoOpen: false,
            cancelButton: clMessage,
            theme: theme
        });
        message_input.click(function () {
            $("#message").val("");
            sm.jqxWindow('open');
        });
    }
}
// инициализация всплывающего окна с формой смены статуса документа
function initChangeState() {
    let stateWindow = $('#changeStateWindow');
    if (stateWindow.length) {
        let changestate_url = '/datainput/changestate';
        let changestate_input = $('#openChangeStateWindow');
        let clState = $("#CancelStateChanging");
        let svState = $("#SaveState");
        let stateCodeIds =
            {
                2:  'performed',
                3:  'inadvance',
                4:  'prepared',
                8:  'accepted',
                16: 'declined',
                32: 'approved'
            };
        let stateIdCodes =
            {
                performed : 2,
                inadvance : 3,
                prepared  : 4,
                accepted  : 8,
                declined  : 16,
                approved  : 32
            };
        let stateIdLabels =
            {
                performed: 'Выполняется',
                inadvance: 'Подготовлен к предварительной проверке',
                prepared: 'Подготовлен к проверке',
                accepted: 'Принят',
                declined: 'Возвращен на доработку',
                approved: 'Утвержден'
            };
        stateWindow.jqxWindow({
            width: 530,
            height: 480,
            resizable: false,
            isModal: true,
            autoOpen: false,
            cancelButton: clState,
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
        clState.jqxButton({ theme: theme });
        svState.jqxButton({ theme: theme });
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
        svState.click(function () {
            let oldstate = stateCodeIds[docstate_id];
            let message = encodeURIComponent($("#statusChangeMessage").val());
            let radiostates = $('.stateradio');
            let selected_state;
            radiostates.each(function() {
                if ($(this).jqxRadioButton('checked')) {
                    selected_state = $(this).attr('id');
                }
            });
            if (selected_state === stateCodeIds[docstate_id] ) {
                return false;
            }
            let data = "&document=" + doc_id + "&state=" + selected_state + "&oldstate=" + oldstate + "&message=" + message;
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
                    if (data.status_changed === 1) {
                        raiseInfo("Статус документа изменен. Новый статус: < " + stateIdLabels[data.new_status] + " >");
                        if (typeof (postChangeStateActions) === 'function') {
                            postChangeStateActions(stateIdLabels[data.new_status]);
                        }
                        if ($("#StateInfo").length) {
                            $("#StateInfo").html(stateIdLabels[data.new_status]);
                        }
                        docstate_id = stateIdCodes[data.new_status];
                    }
                    else if(data.status_changed === 0) {
                        raiseError("Статус не изменен! " + data.comment);
                    }
                    $("#changeStateAlertMessage").html('');
                    $("#SaveState").jqxButton({disabled: false });
                    $("#CancelStateChanging").jqxButton({disabled: false });
                    stateWindow.jqxWindow('hide');
                },
                error: xhrErrorNotificationHandler
            });
        });
        changestate_input.click(function () {
            let radiostates = $('.stateradio');
            radiostates.each(function() {
                let state = $(this).attr('id');
                if ($.inArray(state, disabled_states) !== -1) {
                    $(this).jqxRadioButton('disable');
                }
                if (state === stateCodeIds[docstate_id]) {
                    $(this).jqxRadioButton('check');
                }
            });
            let doc_state = stateCodeIds[docstate_id];
            $('#changeStateFormCode').html(form_code);
            $('#changeStateMOCode').html(ou_code);
            if (current_user_role === '1' && doc_state !== 'performed' && doc_state !== 'inadvance' && doc_state !== 'declined') {
                //$('#inadvance').jqxRadioButton('disable');
                $('#prepared').jqxRadioButton('disable');
            } else if (current_user_role === '1' && doc_state === 'performed') {
                //$('#inadvance').jqxRadioButton('enable');
                $('#prepared').jqxRadioButton('enable');
            } else if (current_user_role === '1' && doc_state === 'declined') {
                //$('#inadvance').jqxRadioButton('enable');
                $('#prepared').jqxRadioButton('enable');
            } else if (current_user_role === '1' && doc_state === 'inadvance') {
                //console.log("Переход с предварительной проверки");
                //$('#inadvance').jqxRadioButton('disable');
                $('#prepared').jqxRadioButton('enable');
                $('#performed').jqxRadioButton('enable');
            }
            if ((current_user_role === '3' || current_user_role === '4') && doc_state === 'performed') {
                $('#declined').jqxRadioButton('disable');
            } else if ((current_user_role === '3' || current_user_role === '4') && doc_state !== 'performed') {
                $('#declined').jqxRadioButton('enable');
            }
            stateWindow.jqxWindow('open');
        })
    }

}

