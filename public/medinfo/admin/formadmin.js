/**
 * Created by shameev on 13.09.2016.
 */
initsplitter = function() {
    $("#mainSplitter").jqxSplitter(
        {
            width: '100%',
            height: '100%',
            theme: theme,
            panels:
                [
                    { size: '50%', min: '10%'},
                    { size: '50%', min: '10%'}
                ]
        }
    );
};
initdatasources = function() {
    let formsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id', type: 'int' },
            { name: 'form_index', type: 'int' },
            { name: 'form_name', type: 'string' },
            { name: 'form_code', type: 'string' },
            { name: 'medstat_code', type: 'string' },
            { name: 'relation', type: 'int' },
            { name: 'inherit_from',  map: 'inherit_from>form_code', type: 'string' },
            { name: 'short_ms_code', type: 'string' },
            { name: 'medstatnsk_id', type: 'string' },
        ],
        id: 'id',
        url: 'fetchforms',
        root: null
    };
    formDataAdapter = new $.jqx.dataAdapter(formsource);
    let realforms_source =
        {
            datatype: "json",
            datafields: [
                { name: 'id' },
                { name: 'form_code' }
            ],
            id: 'id',
            localdata: realforms
        };
    realformsDataAdapter = new $.jqx.dataAdapter(realforms_source);
};
initformlist = function() {
    fl.jqxGrid(
        {
            width: '98%',
            height: '90%',
            theme: theme,
            localization: localize(),
            source: formDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            sortable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '30px' },
                { text: '№ п/п', datafield: 'form_index', width: '70px' },
                { text: 'Код формы', datafield: 'form_code', width: '70px'  },
                { text: 'Имя', datafield: 'form_name' , width: '400px'},
                { text: 'Разрез', datafield: 'inherit_from', width: '70px'  },
                { text: 'Код МС МСК', datafield: 'medstat_code', width: '100px' },
                { text: 'Сокр. код МС МСК', datafield: 'short_ms_code', width: '100px' },
                { text: 'Код МС НСК', datafield: 'medstatnsk_id', width: '100px' },
            ]
        });
    fl.on('rowselect', function (event) {
        let row = event.args.row;
        $("#relation").jqxDropDownList('clearSelection');
        $("#form_name").val(row.form_name);
        $("#form_index").val(row.form_index);
        $("#form_code").val(row.form_code);
        //row.relation === null ? $("#relation").jqxDropDownList('val', '') : $("#relation").jqxDropDownList('val', row.relation); //val(row.relation);
        $("#relation").val(row.relation);
        $("#medstat_code").val(row.medstat_code);
        $("#short_ms_code").val(row.short_ms_code);
        $("#medstatnsk_id").val(row.medstatnsk_id);
    });
};
initbuttons = function () {
    let sel = $("#relation");
    sel.jqxDropDownList({
        theme: theme,
        filterable: true,
        filterPlaceHolder: '',
        source: realformsDataAdapter,
        displayMember: "form_code",
        valueMember: "id",
        placeHolder: "",
        width: '100%',
        height: 35,
        renderer: function (index, label, value) {
            let rec = realforms[index];
            return "(" + rec.form_code + ") " + rec.form_name;
        }
    });
    //sel.jqxDropDownList('addItem', { label: 'Форма не наследуется', value: 0} );

};

initformactions = function() {
    $("#insert").click(function () {
        let data = setQueryString();
        $.ajax({
            dataType: 'json',
            url: '/admin/forms/create',
            method: "POST",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                fl.jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#save").click(function () {
        let row = fl.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let rowid = fl.jqxGrid('getrowid', row);
        let data = setQueryString();
        $.ajax({
            dataType: 'json',
            url: '/admin/forms/update/' + rowid,
            method: "PATCH",
            data: data,
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                }
                $("#formList").jqxGrid('updatebounddata');
            },
            error: function (xhr, status, errorThrown) {
                $.each(xhr.responseJSON, function(field, errorText) {
                    raiseError(errorText);
                });
            }
        });
    });
    $("#delete").click(function () {
        let row = fl.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для удаления");
            return false;
        }
        if (!confirm('Если форма не содержит отчетных документов и данных, то она будет удалена вместе с входящими в нее таблицами, строками, графами')) {
            return false;
        }
        let rowid = fl.jqxGrid('getrowid', row);
        $.ajax({
            dataType: 'json',
            url: '/admin/forms/delete/' + rowid,
            method: "DELETE",
            success: function (data, status, xhr) {
                if (typeof data.error !== 'undefined') {
                    raiseError(data.message);
                } else {
                    raiseInfo(data.message);
                    $("#form")[0].reset();
                }
                fl.jqxGrid('updatebounddata');
                fl.jqxGrid('clearselection');
            },
            error: function (xhr, status, errorThrown) {
                raiseError('Ошибка удаления отчетного периода', xhr);
            }
        });
    });
};

function setQueryString() {
    return "&form_name=" + $("#form_name").val() +
        "&form_index=" + $("#form_index").val() +
        "&form_code=" + $("#form_code").val()  +
        "&relation=" + $("#relation").val()  +
        "&medstat_code=" + $("#medstat_code").val() +
        "&short_ms_code=" + $("#short_ms_code").val() +
        "&medstatnsk_id=" + $("#medstatnsk_id").val();
}