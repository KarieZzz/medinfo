// инициализация источников данных для предустановленных фильтров
initdatasources = function() {
    let forms_source =
        {
            datatype: "json",
            datafields: [
                { name: 'id' },
                { name: 'form_code' }
            ],
            id: 'id',
            localdata: forms
        };
    formsDataAdapter = new $.jqx.dataAdapter(forms_source);
};

// Инициализация окна ввода нового документа
initcontrols = function () {
    let sel = $("#selectForm");
    let formsids = $("#formids");

    let checkedItems = [];
    sel.jqxDropDownList({
        theme: theme,
        checkboxes: true,
        filterable: true,
        filterPlaceHolder: '',
        source: formsDataAdapter,
        displayMember: "form_code",
        valueMember: "id",
        placeHolder: "Выберите формы:",
        width: '100%',
        height: 35,
        renderer: function (index, label, value) {
            let rec = forms[index];
            return rec.form_code + " " + rec.form_name;
        }
    });

    sel.on('select', function (event) {
        let items = sel.jqxDropDownList('getCheckedItems');
        let allitems = sel.jqxDropDownList('getItems');
        checkedItems = [];
        $.each(items, function (index) {
            checkedItems.push(this.value);
        });
        formids.value = "";
        formids.value = checkedItems;

        if (items.length === allitems.length) {
            allforms.val('1');
        } else {
            allforms.val('0');
        }
    });

    $("#checkAllForm").on('click', function () {
        sel.jqxDropDownList('checkAll');
        checkedItems = [];
        let items = sel.jqxDropDownList('getCheckedItems');
        $.each(items, function (index) {
            checkedItems.push(this.value);
        });
        formids.value = "";
        formids.value = checkedItems;
        allforms.val('1');
    });

    $("#uncheckAllForm").on('click', function () {
        sel.jqxDropDownList('uncheckAll');
        checkedItems = [];
        formids.value = "";
        allforms.val('0');
    });
};

