<script type="text/javascript">
    let formpickfetch_url = '/fetchforms/';
    let tablepickfetch_url = '/fetchtables/';
    let tpicklist = $("#tableList");
    let tablesource;
    let picked_form = 0;
    let picked_table = 0;
    let isrelated = false;
    let hasrelations = false;
    let initTablePickerDatasources = function() {
        let formssource =
            {
                datatype: "json",
                datafields: [
                    { name: 'id', type: 'int' },
                    { name: 'form_code', type: 'string' },
                    { name: 'form_name', type: 'string' },
                    { name: 'has_relations' },
                    { name: 'inherit_from' }
                ],
                id: 'id',
                url: formpickfetch_url
            };
        formspickDataAdapter = new $.jqx.dataAdapter(formssource);
        tablesource =
            {
                datatype: "json",
                datafields: [
                    { name: 'id', type: 'int' },
                    { name: 'table_code', type: 'string' },
                    { name: 'table_name', type: 'string' }
                ],
                id: 'id',
                url: tablepickfetch_url + picked_form
            };
        tablepickDataAdapter = new $.jqx.dataAdapter(tablesource);
    };

    // Инициализация списков-фильтров форма -> таблица
    let initFormTableFilter = function() {
        let flist = $("#formList");
        let fc = $("#formListContainer");
        let tc = $("#tableListContainer");
        fc.jqxDropDownButton({ width: 300, height: 32, theme: theme });
        fc.jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите форму</div>');
        flist.jqxGrid(
            {
                width: '450px',
                height: '500px',
                theme: theme,
                localization: localize(),
                source: formspickDataAdapter,
                columnsresize: true,
                showfilterrow: true,
                filterable: true,
                sortable: true,
                selectionmode: 'singlerow',
                columns: [
                    { text: 'Код', datafield: 'form_code', width: '50px' },
                    { text: 'Имя', datafield: 'form_name' , width: '380px'}
                ]
            });
        flist.on('rowselect', function (event) {
            fc.jqxDropDownButton('close');
            let row = event.args.row;
            let relations = [];
            isrelated = false;
            hasrelations = false;
            if (event.args.rowindex === -1) {
                return false;
            }
            picked_form = row.id;
            switch (true) {
                case row.inherit_from === null && row.has_relations.length === 0:
                    fc.jqxDropDownButton('setContent', '<div style="margin-top: 9px"><span class="text-info">Форма: ' + row.form_code + '</span></div>');
                    break;
                case row.has_relations.length > 0 :
                    hasrelations = true;
                    for (let i = 0; i < row.has_relations.length; i++) {
                        relations.push(row.has_relations[i].form_code);
                    }
                    fc.jqxDropDownButton('setContent', '<div style="margin-top: 9px"><span class="text-success">Форма: ' + row.form_code +
                        ' (имеет разрезы: ' + relations +')</span></div>');
                    break;
                case row.inherit_from !== null :
                    isrelated = true;
                    fc.jqxDropDownButton('setContent', '<div style="margin-top: 9px"><span class="text-danger">Форма: '+ row.form_code +
                        ' (разрез формы '+ row.inherit_from.form_code +')</span></div>');
                    break;

            }
            updateTableDropdownList(row);
        });

        tc.jqxDropDownButton({ width: 250, height: 32, theme: theme });
        tpicklist.on('bindingcomplete', function() {
            if (picked_form !== 0) {
                tpicklist.jqxGrid('selectrow', 0);
            }
        });
        tpicklist.jqxGrid(
            {
                width: '450px',
                height: '500px',
                theme: theme,
                localization: localize(),
                source: tablepickDataAdapter,
                columnsresize: true,
                showfilterrow: true,
                filterable: true,
                sortable: true,
                selectionmode: 'singlerow',
                columns: [
                    { text: 'Код', datafield: 'table_code', width: '50px' },
                    { text: 'Имя', datafield: 'table_name' , width: '380px'}
                ]
            });
        tpicklist.on('rowselect', function (event) {
            tc.jqxDropDownButton('close');
            let args = event.args;
            if (args.rowindex === -1) {
                return false;
            }
            let r = args.row;
            picked_table = r.id;
            $("#tableProperties").html('<div class="text-bold text-info" style="margin-left: -30px">Таблица: (' + r.table_code + ') ' + r.table_name + '</div>');
            updateRelated();
        });
    };

    // Обновление списка таблиц при выборе формы
    let updateTableDropdownList = function(form) {
        tablesource.url = tablepickfetch_url + form.id;
        $("#tableListContainer").jqxDropDownButton('setContent', '<div style="margin-top: 9px">Выберите таблицу из формы ' + form.form_code + '</div>');
        tpicklist.jqxGrid('updateBoundData');
    };
    initTablePickerDatasources();
    initFormTableFilter();
</script>
