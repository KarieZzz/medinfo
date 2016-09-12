/**
 * Created by shameev on 09.09.2016.
 */
var initlayout = function() {
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
                            $("#openFullChangeLog").jqxButton({ theme: theme, disabled: control_disabled });
                            $("#openFullChangeLog").click(function () {
                                var dataExportWindow = window.open(valuechangelog_url);
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
                    initContent: function () {
                        $("#checktable").jqxButton({ theme: theme, disabled: control_disabled });
                        $("#checktable").click( function() { checktable(current_table) });
                        $("#compareprevperiod").jqxButton({ theme: theme });
                        $("#compareprevperiod").click(compare_with_prev);

                    }
                },{
                    type: 'layoutPanel',
                    title: 'Контроль ячейки',
                    contentContainer: 'CellControlPanel'
                }]
            }]
        }]
    }];
};