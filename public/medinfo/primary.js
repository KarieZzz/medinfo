/**
 * Created by shameev on 09.09.2016.
 */

let initSplitter = function () {
    $('#formEditLayout').jqxSplitter({
        width: '100%',
        height: '945px',
        theme: theme,
        splitBarSize: 10,
        panels: [
            { size: '60%', min: 100, collapsible: false }, {collapsed:true}
            ]
    });
    $("#ControlTabs").jqxTabs({ theme: theme, height: '100%', width: '100%' });
    $("#TableTitle").html('Таблица ' + data_for_tables[current_table].tablecode + ', "' + data_for_tables[current_table].tablename + '"');

};



/*
let initlayout = function() {
    layout = [{
        type: 'layoutGroup',
        orientation: 'horizontal',
        items: [{
            type: 'layoutGroup',
            orientation: 'vertical',
            width: '40%',
            items: [{
                type: 'tabbedGroup',
                height: '50%',
                allowPin: false,
                items: [{
                    type: 'layoutPanel',
                    title: 'Форма ' + form_code +', таблицы',
                    contentContainer: 'FormPanel',
                    initContent: inittablelist
                }]
            }, {
                type: 'tabbedGroup',
                height: '50%',
                allowPin: false,
                items: [
                    {
                        type: 'layoutPanel',
                        title: 'Контроль формы',
                        contentContainer: 'FormControlPanel',
                        initContent: initcheckformtab
                    },
                    {
                        type: 'layoutPanel',
                        title: 'Журнал изменений в текущем сеансе',
                        contentContainer: 'ValueChangeLogPanel'
                    },
                    {
                        type: 'layoutPanel',
                        title: 'Полный журнал изменений',
                        contentContainer: 'FullValueChangeLogPanel',
                        initContent: function () {
                            $("#openFullChangeLog").click(function () {
                                let dataExportWindow = window.open(valuechangelog_url);
                            });
                        }
                    }]
            }]
        }, {
            type: 'layoutGroup',
            orientation: 'vertical',
            width: '60%',
            items: [{
                type: 'tabbedGroup',
                height: '60%',
                allowPin: false,
                items: [{
                    type: 'layoutPanel',
                    title: 'Таблица ' + data_for_tables[current_table].tablecode + ', "' + data_for_tables[current_table].tablename + '"',
                    contentContainer: 'TableEditPanel',
                    initContent: function () {
                        initfilters();
                        initdatagrid();
                    }
                }]
            }, {
                type: 'tabbedGroup',
                height: '40%',
                allowPin: false,
                alignment: 'bottom',
                items: [{
                    type: 'layoutPanel',
                    title: 'Контроль таблицы',
                    contentContainer: 'TableControlPanel',
                    initContent: initchecktabletab
                },{
                    type: 'layoutPanel',
                    title: 'Контроль ячейки',
                    contentContainer: 'CellControlPanel'
                }]
            }]
        }]
    }];
};*/
