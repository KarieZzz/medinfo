initsplitter = function() {
    $("#mainSplitter").jqxSplitter({
        width: '100%',
        height: '100%',
        theme: theme,
        panels:
            [
                { size: '50%', min: '10%'},
                { size: '50%', min: '10%'}
            ]
    });
};
initMonitoringList = function() {
    let monSource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'name', type: 'string' },
            { name: 'periodicity', type: 'int' },
            { name: 'periodicities', map: 'periodicities>name', type: 'string' },
            { name: 'accumulation', type: 'bool' },
            { name: 'album_id', type: 'int' },
            { name: 'album', map: 'album>album_name', type: 'string' },
        ],
        id: 'id',
        url: fetchmonitoring_url,
        root: null
    };
    let dataAdapter = new $.jqx.dataAdapter(monSource);
    mlist.jqxGrid(
        {
            width: '98%',
            height: '90%',
            theme: theme,
            localization: localize(),
            source: dataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: 'Наименование мониторинга', datafield: 'name' , width: '380px'},
                { text: 'Периодичность', datafield: 'periodicities', width: '100px' },
                { text: 'Накопление данных', datafield: 'accumulation', columntype: 'checkbox', width: '140px' },
                { text: 'Альбом форм', datafield: 'album', width: '170px' }
            ]
        });
    mlist.on('rowselect', function (event) {
        let row = event.args.row;
        $("#name").val(row.name);
        $("#periodicity").val(row.periodicity);
        $("#accumulation").val(row.accumulation === true);
        $("#album").val(row.album_id);
    });
};

initbuttons = function () {
    $('#accumulation').jqxSwitchButton({
        height: 31,
        width: 81,
        onLabel: 'Да',
        offLabel: 'Нет',
        checked: false
    });
    let preiodicitysource =
        {
            datatype: "json",
            datafields: [
                { name: 'code' },
                { name: 'name' }
            ],
            id: 'code',
            localdata: periodicities
        };
    preiodicityDataAdapter = new $.jqx.dataAdapter(preiodicitysource);
    $("#periodicity").jqxDropDownList({
        theme: theme,
        source: preiodicityDataAdapter,
        displayMember: "name",
        valueMember: "code",
        placeHolder: "Выберите периодичность отчетов:",
        width: 300,
        height: 34
    });

    let albumsource =
        {
            datatype: "json",
            datafields: [
                { name: 'id' },
                { name: 'album_name' }
            ],
            id: 'id',
            localdata: albums
        };
    albumDataAdapter = new $.jqx.dataAdapter(albumsource);
    $("#album").jqxDropDownList({
        theme: theme,
        source: albumDataAdapter,
        displayMember: "album_name",
        valueMember: "id",
        placeHolder: "Выберите альбом отчетных форм:",
        width: 300,
        height: 34
    });
};

setquerystring = function() {
    return "&name=" + $("#name").val() +
        "&periodicity=" + $("#periodicity").val() +
        "&accumulation=" + ($("#accumulation").val() ? 1 :0) +
        "&album=" + $("#album").val();
};

initactions = function() {
    $("#insert").click(function () {
        $.ajax({
            dataType: 'json',
            url: monitoringinsert_url,
            method: "POST",
            data: setquerystring(),
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    mlist.jqxGrid('updatebounddata', 'data');
                    mlist.on("bindingcomplete", function (event) {
                        let newindex = mlist.jqxGrid('getrowboundindexbyid', data.id);
                        mlist.jqxGrid('selectrow', newindex);
                    });
                    raiseInfo(data.message);
                }
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        let row = mlist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let rowid = mlist.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: monitoringupdate_url + rowid,
            method: "PUT",
            data: setquerystring(),
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                mlist.jqxGrid('updatebounddata', 'data');
                mlist.on("bindingcomplete", function (event) {
                    let newindex = mlist.jqxGrid('getrowboundindexbyid', rowid);
                    mlist.jqxGrid('selectrow', newindex);
                });
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#delete").click(function () {
        let row = mlist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        let rowid = mlist.jqxGrid('getrowid', row);
        let confirm_text = 'Подтвердите удаление мониторинга Id' + rowid ;
        if (!confirm(confirm_text)) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            url: monitoringupdate_url + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    mlist.jqxGrid('clearselection');
                    mlist.jqxGrid('updatebounddata')
                    raiseInfo(data.message);
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка сохранения данных на сервере', xhr);
            }
        });
    });
};