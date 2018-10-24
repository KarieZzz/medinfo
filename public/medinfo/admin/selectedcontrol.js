initdatasources = function() {
    let levelssource =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'int' },
                { name: 'code', type: 'string' },
                { name: 'type', type: 'int' },
                { name: 'name', type: 'string' }
            ],
            id: 'id',
            localdata: levels
        };
    levelsDataAdapter = new $.jqx.dataAdapter(levelssource);
};

initFilters = function() {
    llc.jqxDropDownButton({ width: '100%', height: 32, theme: theme });
    llc.jqxDropDownButton('setContent', '<div style="margin: 6px">Все организации</div>');
    levellist.jqxGrid(
        {
            width: '100%',
            height: '340px',
            theme: theme,
            localization: localize(),
            source: levelsDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Код', datafield: 'code', width: '10%'  },
                { text: 'Тип', datafield: 'type', width: '10%'  },
                { text: 'Имя', datafield: 'name' , width: '80%'}
            ]
        });

    levellist.on('rowselect', function (event) {
        llc.jqxDropDownButton('close');
        var args = event.args;
        if (args.rowindex === -1) {
            return false;
        }
        let r = args.row;
        current_level = r.id;
        current_type = r.type;
        //console.log(current_level);
        llc.jqxDropDownButton('setContent', '<div style="margin: 6px">Установлено ограничение по: "' + r.code + ' "'+ r.name + '"</div>');
    });
};

setquery = function() {
    return "?script=" + encodeURIComponent($("#script").val()) +
        "&form=" + form +
        "&table=" + table +
        "&monitoring=" + $("#monitoring").val() +
        "&period=" + $("#period").val() +
        "&ou=" + current_level +
        "&type=" + current_type;
};

initActions = function() {
    let result = null;
    $("#performControl").click(function () {
        let data = setquery();
        $.ajax({
            dataType: 'json',
            url: output_url + data,
            method: "GET",
            success: function (data, status, xhr) {
                $("#ptitle").show();
                setProtocol(data.protocol);
            },
            error: xhrErrorNotificationHandler
        });
        $("#checkingProgress").show();
        $("#progress").html(0).css('width', "0%");
        let progres_timer = setTimeout(function get_progress(){
            $.get(progress_url, function(data) {
                if (data.ended) {
                    $("#progress").html('100%').css('width', '100%');
                    $("#ou_intro").html('Проверка закончена');
                    $("#count").html('');
/*                    if (!data.result) {
                        data.result = result;
                    }
                    if (!data.result) {
                        raiseError("Ошибка получения протокола контроля");
                        return false;
                    }*/
                    raiseInfo("Проверка завершена. Загрузка протокола контроля");
                } else {
                    let p = data.progress + "%";
                    $("#progress").html(p).css('width', p);
                    $("#ou_intro").html('Обработано:');
                    $("#count").html(data.managed + ' из ' + data.count_of_docs);
                    progres_timer = setTimeout(get_progress, 2000);
                }
            });
        }, 2000);

    });
    $("#hideValid").click(function () {
        $("tr.valid").toggle();
    });
};

function setProtocol(result) {
    p = $("#protocol");
    p.html('');
    let count = result.length;
    let table = $("<table class='table table-bordered'></table>");
    let theader = $("<th>Код документа</th><th>Медицинская организация</th><th>Результат</th><th>Строки/графы</th>");
    table.append(theader);
    for (let i = 0; i < count; i++) {
        let valid = result[i].valid;
        let correct = (valid ? 'верно' : 'не верно');
        let danger = (valid ? 'valid' : 'bg-danger');
        let nodata = (result[i].no_data ? '(нет данных)' : '');
        let muted = (nodata ? 'bg-warning' : '');
        let checklist = [];
        if (!nodata && !valid) {
            let checks = result[i].iterations;
            for (let j = 0;  j < checks.length; j++) {
                if (!checks[j].valid ) {
                    checklist.push(checks[j].code);
                }
            }
        }
        let row = $('<tr class="' + danger + ' ' + muted + '"></tr>');
        row.append('<td>' + result[i].doc_id + ' ' + nodata +
            '</td><td><a href="/datainput/formdashboard/' + result[i].doc_id + '" target="_blank">' + result[i].unit_name +
            '</a></td><td> ' + correct +
            '</td><td> ' + checklist + '</td>');
        table.append(row);
    }
    p.append(table)
}

