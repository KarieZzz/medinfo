/**
 * Created by shameev on 12.07.2016.
 */
var cellbeginedit = function (row, datafield, columntype, value) {
    var rowid = $('#DataGrid').jqxGrid('getrowid', row);
    for (var i = 0; i < not_editable_cells.length; i++) {
        if (not_editable_cells[i].t == current_table &&  not_editable_cells[i].r == rowid && not_editable_cells[i].c == datafield ) {
            return false;
        }
    }
};
var cellclass = function (row, columnfield, value, rowdata) {
    var not_editable = '';
    for (var i = 0; i < not_editable_cells.length; i++) {
        if (not_editable_cells[i].t == current_table &&  not_editable_cells[i].r == rowdata.id && not_editable_cells[i].c == columnfield) {
            not_editable = 'not_editable';
        }
    }
    if (marking_mode == 'control') {
        var class_by_value = '';
        var class_by_edited_row = '';
        for (var i = 0; i < editedCells.length; i++) {
            if (editedCells[i].t == current_table && editedCells[i].r == row && editedCells[i].c == columnfield ) {
                class_by_edited_row = "editedRow";
            }
        }
        $.each(invalidCells, function(key, value) {
            if (value.t == current_table && value.r == rowdata.id && value.c == columnfield) {
                class_by_value = 'invalid';
            }
        });
        if (current_edited_cell.t == current_table && current_edited_cell.r == row && current_edited_cell.c == columnfield) {
            if (!current_edited_cell.valid) {
                class_by_value = 'invalid';
            }
            else {
                class_by_value = '';
            }
        }
        return class_by_value + ' ' + class_by_edited_row + ' ' + not_editable;
    }
    else if (marking_mode == 'compareperiods') {
        var class_compare = '';
        for (var i = 0; i < comparedCells.length; i++) {
            if (comparedCells[i].t == current_table && comparedCells[i].r == rowdata.id && comparedCells[i].c == columnfield ) {
                class_compare = comparedCells[i].degree;
            }
        }
        return class_compare + ' ' + not_editable;
    }
};
var tooltiprenderer = function (element) {
    $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
};
var checkform = function () {
    var data ="";
    $.ajax({
        dataType: "json",
        url: validate_form_url,
        data: data,
        beforeSend: function( xhr ) {
            loader = "Выполнение проверки и загрузка протокола контроля <img src='plugins/jqwidgets/styles/images/loader-small.gif' />";
            $('#formprotocol').html(loader);
        },
        success: function (data, status, xhr) {
            $("#currentInfoMessage").text("Протокол контроля формы загружен");
            $("#infoNotification").jqxNotification("open");
            var list = '<div id="formvalidation">';
            var invalidtables = 0;
            $.each(data.protocol, function(table_id, tablecontrol) {
                if (!tablecontrol.tablecorrect) {
                    invalidtables++;
                    list += '<div>Таблица ' + tablecontrol.tablecode + '</div>';
                    list += '<div>';
                    $.each(tablecontrol.tableprotocol, function(row_id, rowcontrols) {
                        if (!rowcontrols.row_correct) {
                            list += "<div id='" + rowcontrols.row_id + "'>Строка №" + rowcontrols.row_number + '</div><hr />';
                            $.each(rowcontrols.columns, function(col_id, columncontrol) {
                                if (!columncontrol.column_correct) {
                                    list += '<div><b>Графа №'+ columncontrol.column_index +'.</b></div>';
                                    $.each(columncontrol.column_protocols, function(ctype, controlcontent) {
                                        if (controlcontent.result !== true) {
                                            list += "<span class='showrule' style='white-space: normal'>" + controlcontent.rule + "</span>";
                                            list += "<table class='control_result invalid'><tr><td>Значение</td>";
                                            list += "<td>Знак сравнения</td><td>Контрольная сумма</td><td>Отклонение</td></tr>";
                                            list += "<tr style='text-align: center'><td>"+controlcontent.left_part+"</td><td>"+controlcontent.boolean_readable+"</td>";
                                            list += "<td>"+controlcontent.right_part_value+"</td>";
                                            list += "<td>"+controlcontent.deviation+"</td></tr></table>";
                                        }
                                    });
                                }
                            });
                        }
                    });
                    list += '</div>';
                }
            });
            list += '</div>';
            if (invalidtables == 0) {
                list ="<p style='background-color: #b0ffaf;'> Ошибок не обнаружено </p>";
                $('#formprotocol').html(list);
            } else {
                newwindow_link ="<a href='#' id='printformprotocol'>Открыть протокол в новом окне (распечатать)</a>";
                formprotocolheader ="<a href='#' onclick='window.print()'>Распечатать</a>";
                formprotocolheader +="<p>Протокол контроля формы № "+ form_code +": \"" + form_name +"\" </p>";
                formprotocolheader +="<p>Учреждение: " + ou_code + " " + ou_name + "</p>";
                note ="<p style='background-color: #ffa3a8;'> При проверке обнаружены ошибки по следующим таблицам: </p>";
                $('#formprotocol').html(newwindow_link + note + list);
                $("#formvalidation").jqxNavigationBar({
                    width: 'auto',
                    arrowPosition: 'left',
                    expandMode: 'multiple',
                    theme: theme
                });
                $('#printformprotocol').click(function () {
                    print_style = "<style>.showrule { font-size: 0.8em; } .control_result { border: 1px solid #7f7f7f; width: 400;";
                    print_style += "border-collapse: collapse; margin-bottom: 10px; } .control_result td { border: 1px solid #7f7f7f; } </style>";
                    var pWindow = window.open("", "ProtocolWindow", "width=900, height=600, scrollbars=yes");
                    pWindow.document.write(print_style + formprotocolheader + list);
                });
            }
        }
    }).fail(function() {
        $('#formprotocol').html('');
        $("#currentError").text("Ошибка получения протокола контроля с сервера.");
        $("#serverErrorNotification").jqxNotification("open");
    });
};
var checktable = function () {
    var data ="";
    $.ajax({
        dataType: "json",
        url: validate_table_url + '&table=' + current_table,
        data: data,
        beforeSend: function( xhr ) {
            $("#tableprotocol").html('');
            $("#cellvalidationprotocol").html('');
            loader = "Выполнение проверки и загрузка протокола контроля <img src='plugins/jqwidgets/styles/images/loader-small.gif' />";
            $('#tableprotocol').html(loader);
            invalidCells.length = 0; // TODO: Обнулять только текущую таблицу перед заполнением
            marking_mode = 'control';
            $('#DataGrid').jqxGrid('render');
        },
        success: function (data, status, xhr) {
            cashed = "";
            if (data.cashed) {
                cashed = "(сохраненная версия)";
            }
            $("#currentInfoMessage").text("Протокол контроля таблицы загружен");
            $("#infoNotification").jqxNotification("open");
            check_timestamp = new Date();
            if (data.protocol == 0) {
                $("#tableprotocol").html("<div class='valid'>"+ check_timestamp.toLocaleString()+" При проверке таблицы ошибок не выявлено" + " " + cashed + "</div>");
            }
            else if (data.protocol == -1) {
                $("#tableprotocol").html("<div class='valid'>"+ check_timestamp.toLocaleString()+" Таблица не содержит данных</div>");
            }
            else {
                protocolheader ="<a href='#' onclick='window.print()'>Распечатать</a>";
                protocolheader +="<p>Протокол контроля таблицы " + data_for_tables[current_table].tablecode + " \""+ data_for_tables[current_table].tablename;
                protocolheader += "\" формы № "+ form_code + "</p>";
                protocolheader +="<p>Учреждение: " + ou_code + " " + ou_name + "</p>";
                table_protocol_comment = "<div>Дата и время проведения проверки: " + check_timestamp.toLocaleString() + " "+ cashed + "</div>";
                var list = '';
                var incorrect_rows = new Array();
                var rules = new Array();
                $.each(data.protocol, function(row, result) {
                    if (!result.row_correct) {
                        incorrect_rows.push(result.row_number);
                    }
                    row_protocol = "<div>Строка "+result.row_number + "</div><div>";
                    $.each(result.columns, function(col, content) {
                        if (!content.column_correct) {
                            col_protocol = new Array();
                            $.each(content.column_protocols, function(rule, rule_result) {
                                if (!rule_result.result) {
                                    control_table_class = 'invalid';
                                    invalidCells.push({t: current_table, r: result.row_id, c: col});
                                    result_string = 'Не верно';
                                } else {
                                    control_table_class = 'valid';
                                    result_string = 'Верно';
                                };
                                col_text = "<div class='showrule'>" + rule_result.rule + "</div>";
                                col_text += "<table class='control_result "+ control_table_class + "'><tr><td>Значение</td>";
                                col_text += "<td>Знак сравнения</td><td>Контрольная сумма</td><td>Отклонение</td>";
                                col_text += "<td>Результат контроля</td></tr>";
                                col_text += "<tr style='text-align: center'><td>"+rule_result.left_part+"</td><td>"+rule_result.boolean_readable+"</td>";
                                col_text += "<td>"+rule_result.right_part_value+"</td>";
                                col_text += "<td>"+rule_result.deviation+"</td><td>"+ result_string +"</td></tr></table>";
                                if (show_table_errors_only) {
                                    if (!rule_result.result) {
                                        col_protocol.push(col_text);
                                    }
                                }
                                else {
                                    col_protocol.push(col_text);
                                }
                            });
                            if (col_protocol.length > 0) {
                                row_protocol += '<b>Графа ' + content.column_index + '</b></br>' + col_protocol.join("");
                            }
                        }
                    });
                    row_protocol +='</div>';
                    if (show_table_errors_only) {
                        if (!result.row_correct) {
                            rules.push(row_protocol);
                        }
                    }
                    else {
                        rules.push(row_protocol);
                    }
                });
                note ="<p class='valid'> При проверке таблицы ошибок не обнаружено </p>";
                if (rules.length > 0 ) {
                    if (incorrect_rows.length > 0) {
                        note = "<a href='#' id='printtableprotocol'>Открыть в новом окне (распечатать)</a>";
                        note +="<p style='background-color: #ffa3a8;'> При проверке обнаружены ошибки по следующим строкам: " + incorrect_rows.join(", ") + "</p>";
                    }
                    list = "<div id=\"tablevalidation\">";
                    list += rules.join('');
                    list += '</div>';
                    $("#tableprotocol").html(table_protocol_comment + note + list);
                    $("#tablevalidation").jqxNavigationBar({
                        width: 'auto',
                        arrowPosition: 'left',
                        expandMode: 'multiple',
                        theme: theme
                    });
                    $('#tablevalidation').jqxNavigationBar('render');
                }
                else {
                    $("#tableprotocol").html(table_protocol_comment + note);
                }
            }
            $('#DataGrid').jqxGrid('render');
            $('#printtableprotocol').click(function () {
                print_style = "<style>.showrule { font-size: 0.8em; } .control_result { border: 1px solid #7f7f7f; ";
                print_style += "border-collapse: collapse; margin-bottom: 10px; } .control_result td { border: 1px solid #7f7f7f; }</style>";
                var pWindow = window.open("", "ProtocolWindow", "width=900, height=600, scrollbars=yes");
                pWindow.document.write(print_style + protocolheader + list);
            });
            protocol_control_created = true;
        }
    }).fail(function() {
        $("#tableprotocol").html('');
        $("#currentError").text("Ошибка получения протокола контроля с сервера.");
        $("#serverErrorNotification").jqxNotification("open");
    });
};
var compare_with_prev = function () {
    var data ="";
    $.ajax({
        dataType: "json",
        url: compare_period_url + '&table=' + current_table,
        data: data,
        beforeSend: function( xhr ) {
            $("#tableprotocol").html('');
            $("#cellvalidationprotocol").html('');
            loader = "Выполнение проверки и загрузка данных сравнения периодов <img src='plugins/jqwidgets/styles/images/loader-small.gif' />";
            $('#tableprotocol').html(loader);
            comparedCells.length = 0;
            $('#DataGrid').jqxGrid('render');
        },
        success: function (data, status, xhr) {
            $("#currentInfoMessage").text("Протокол сравнения периодов загружен");
            $("#infoNotification").jqxNotification("open");
            if (data.responce.error == 2002) {
                var legend = "<div>Отсутстует данные в соответствующей таблице прошлого периода</div>";
                $("#tableprotocol").html(legend);
            }
            else {
                var legend = "<div>Отклонения от данных предыдущего периода</div>";
                legend += "<div class='equality'>Данные совпадают</div>";
                legend += "<div class='approximate'>Отклонение в пределах 10%</div>";
                legend += "<div class='moderate'>Отклонение в пределах 10-30%</div>";
                legend += "<div class='significant'>Отклонение более 30%</div>";
                $("#tableprotocol").html(legend);
                marking_mode = 'compareperiods';
                $.each(data.responce, function(cellindex, cellcontent) {
                    comparedCells.push({t: current_table, r: cellcontent.row_id, c: cellcontent.col_id, degree: cellcontent.validation});
                });
                $('#DataGrid').jqxGrid('render');
            }
        }
    }).fail(function() {
        $("#tableprotocol").html('');
        $("#currentError").text("Ошибка получения протокола контроля с сервера.");
        $("#serverErrorNotification").jqxNotification("open");
    });
};
var rendertoolbar = function(toolbar) {
    var me = this;
    var container = $("<div style='margin: 5px;'></div>");
    var input1 = $("<input class='jqx-input jqx-widget-content jqx-rc-all' id='searchField' type='text' style='height: 23px; float: left; width: 150px;' />");
    var input3 = $("<input id='notnullstrings' type='button' value='Непустые строки' />");
    var input4 = $("<input id='clearfilters' type='button' value='Очистить фильтр' />");
    var input5 = $("<input id='savestate' type='button' value='Сохранить настройки таблицы' />");
    var input6 = $("<input id='loadstate' type='button' value='Загрузить настройки таблицы' />");
    toolbar.append(container);
    container.append(input1);
    container.append(input3);
    container.append(input4);
    container.append(input5);
    container.append(input6);
    input1.addClass('jqx-widget-content-' + theme);
    input1.addClass('jqx-rc-all-' + theme);
    input1.jqxInput({ width: 200, placeHolder: "Поиск строки" });
    var oldVal = "";
    input1.on('keydown', function (event) {
        if (input1.val().length >= 2) {
            if (me.timer) {
                clearTimeout(me.timer);
            }
            if (oldVal != input1.val()) {
                me.timer = setTimeout(function () {
                    row_name_filter(input1.val());
                }, 500);
                oldVal = input1.val();
            }
        }
        else {
            $("#DataGrid").jqxGrid('removefilter', '1');
        }
    });
    input3.jqxButton({ theme: theme });
    input3.click(function () { not_null_filter(); });
    input4.jqxButton({ theme: theme });
    input4.click(function () { $("#DataGrid").jqxGrid('clearfilters'); input1.val(''); });
    input5.jqxButton({ theme: theme });
    input5.click(function () {
        var tablestate = $("#DataGrid").jqxGrid('savestate');
        var data = "state=" + JSON.stringify(tablestate);
        $.ajax({
            dataType: 'json',
            url: table_state_url + '&table='+ current_table + '&action=save' ,
            data: data,
            success: function (data, status, xhr) {
                //console.log(data.responce);
            },
            error: function (xhr, status, errorThrown) {
                $("#currentError").text("Ошибка сохранения настроек редактирования таблицы. " + xhr.status + ' (' + xhr.statusText + ') - ' + status);
                $("#serverErrorNotification").jqxNotification("open");
            }
        });
    });
    input6.jqxButton({ theme: theme });
    input6.click(function () {
        get_state_url = table_state_url + '&table='+ current_table + '&action=get';
        $.getJSON( get_state_url, function( data ) {
            if (data.responce != 0) {
                $("#DataGrid").jqxGrid('loadstate', data.responce);
            }
            else {
                $("#currentInfoMessage").text("Для данной таблицы нет сохраненных настроек редактирования");
                $("#infoNotification").jqxNotification("open");
            }
        });
    });
};
var initnotifications = function() {
    $("#serverErrorNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 800, autoClose: false, template: "error"
    });
    $("#infoNotification").jqxNotification({
        width: 250, position: "top-right", opacity: 0.9,
        autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info"
    });
};
var initdatasources = function() {
    form_table_source = {
        dataType: "json",
        dataFields: [{
            name: 'id',
            type: 'int'
        }, {
            name: 'code',
            type: 'string'
        }, {
            name: 'name',
            type: 'string'
        }],
        id: 'id',
        localdata: form_tables_data
    };
    tableListDataAdapter = new $.jqx.dataAdapter(form_table_source);
    tablesource =
    {
        datatype: "json",
        datafields: data_for_tables[current_table].datafields,
        id: 'id',
        url: source_url + current_table,
        root: null
    };
    dataAdapter = new $.jqx.dataAdapter(tablesource);
};
var inittablelist = function() {
    $("#formTables").jqxDataTable({
        width: '99%',
        height: '99%',
        theme: theme,
        source: tableListDataAdapter,
        ready: function () {
            $("#formTables").jqxDataTable('selectRow', 0);
        },
        columns: [{
            text: 'Код',
            dataField: 'code',
            width: 120,
            cellClassName: function (row, column, value, data) {
                if ($.inArray(data.id, edited_tables) !== -1) {
                    return "editedRow";
                }
            }
        }, {
            text: 'Наименование',
            dataField: 'name',
            cellClassName: function (row, column, value, data) {
                if ($.inArray(data.id, edited_tables) !== -1) {
                    return "editedRow";
                }
            }
        }]
    });
    $('#formTables').on('rowSelect', function (event) {
        if (event.args.row.id == current_table) {
            return false;
        }
        $("#DataGrid").jqxGrid('clearfilters');
        current_table = event.args.row.id;
        current_row_name_datafield = data_for_tables[current_table].columns[1].dataField;
        current_row_number_datafield = data_for_tables[current_table].columns[2].dataField;
        //$('#DataGrid').jqxGrid('clearselection');
        $("#DataGrid").jqxGrid('beginupdate');
        tablesource.datafields = data_for_tables[current_table].datafields;
        tablesource.url = source_url + current_table;
        $("#DataGrid").jqxGrid( { columns: data_for_tables[current_table].columns } );
        $("#DataGrid").jqxGrid( { columngroups: data_for_tables[current_table].columngroups } );
        $('#DataGrid').jqxGrid('updatebounddata');
        $("#DataGrid").jqxGrid('endupdate');
        layout[0].items[1].items[0].items[0].title = "Таблица " + data_for_tables[current_table].tablecode + ', "' + data_for_tables[current_table].tablename + '"';
        $('#formEditLayout').jqxLayout('refresh');
        $("#tableprotocol").html('');
    });
};
var initcheckformtab = function() {
    $("#checkform").jqxButton({ theme: theme, disabled: control_disabled });
    $("#checkform").click(function () { checkform() });
    $("#dataexport").jqxButton({ theme: theme });
    $("#dataexport").click(function () {
        var dataExportWindow = window.open(export_data_url);
    });
    if (current_user_role == 3 || current_user_role == 4 ) {
        var vfk = $("<input id='medstatcontrol' type='button' value='Контроль Медстат (ВФ)'/>");
        var mfk = $("<input id='medstatcontrol' type='button' value='Контроль Медстат (МФ)'/>");
        $("#form_control_toolbar").append(vfk);
        vfk.jqxButton({ theme: theme });
        vfk.click(function () {
            var ms_cntrl = window.open(medstat_control_url + '&type=vfk');
        });
        $("#form_control_toolbar").append(mfk);
        mfk.jqxButton({ theme: theme });
        mfk.click(function () {
            var ms_cntrl = window.open(medstat_control_url + '&type=mfk');
        });
    }
};
var initfilters = function() {
    row_name_filter = function (needle) {
        var rowFilterGroup = new $.jqx.filter();
        var filter_or_operator = 1;
        // create a string filter with 'contains' condition.
        //var filtervalue = 'всего';
        var filtervalue = needle;
        var filtercondition = 'contains';
        var nameRecordFilter = rowFilterGroup.createfilter('stringfilter', filtervalue, filtercondition);
        rowFilterGroup.addfilter(filter_or_operator, nameRecordFilter);
        //$("#DataGrid").jqxGrid('addfilter', '1', rowFilterGroup);
        $("#DataGrid").jqxGrid('addfilter', current_row_name_datafield, rowFilterGroup);
        $("#DataGrid").jqxGrid('applyfilters');

    }
    not_null_filter = function () {
        var notnullFilterGroup = new $.jqx.filter();
        // create a filter.
        var filter_or_operator = 1;
        var filtervalue = 0;
        var filtercondition = 'NOT_NULL';
        var notnullFilterGroup1 = notnullFilterGroup.createfilter('numericfilter', filtervalue, filtercondition);
        notnullFilterGroup.addfilter(filter_or_operator, notnullFilterGroup1);
        // TODO: Здесь нужно получить итоговый столбец из описания таблицы
        $("#DataGrid").jqxGrid('addfilter', '3', notnullFilterGroup);
        $("#DataGrid").jqxGrid('applyfilters');
    }
};
var initdatagrid = function() {
    $("#DataGrid").bind('bindingcomplete', function () {
        var localizationobj = {};
        thousandsseparator = " ";
        filtershowrowstring = "Показать строки где:";
        emptydatastring = "Нет данных";
        loadtext = "Загрузка..";
        localizationobj.thousandsseparator = thousandsseparator;
        localizationobj.emptydatastring = emptydatastring;
        localizationobj.loadtext = loadtext;
        localizationobj.filtershowrowstring = filtershowrowstring;
        $("#DataGrid").jqxGrid('localizestrings', localizationobj);
    });
    $("#DataGrid").jqxGrid(
        {
            width: '99%',
            height: '99%',
            source: dataAdapter,
            selectionmode: 'singlecell',
            theme: theme,
            editable: edit_permission,
            editmode: 'selectedcell',
            clipboard: true,
            columnsresize: true,
            //showfilterrow: false,
            showtoolbar: true,
            rendertoolbar: rendertoolbar,
            filterable: false,
            columns: data_for_tables[current_table].columns,
            columngroups: data_for_tables[current_table].columngroups
        });
    $('#DataGrid').on('cellvaluechanged', function (event) {
        var rowBoundIndex = args.rowindex;
        //var rowdata = $('#DataGrid').jqxGrid('getrowdata', rowBoundIndex);
        var rowid = $('#DataGrid').jqxGrid('getrowid', rowBoundIndex);
        //var rowid = rowdata.id;
        var colid = event.args.datafield;

        var value = args.newvalue;
        if (typeof args.oldvalue !== 'undefined') {
            var oldvalue = args.oldvalue;
        } else {
            var oldvalue = null;
        }
        var row_number = $('#DataGrid').jqxGrid('getcellvaluebyid', rowid, current_row_number_datafield);
        var colindex = $('#DataGrid').jqxGrid('getcolumnproperty', colid, 'text');
        current_edited_cell.t = current_table;
        current_edited_cell.r = rowBoundIndex;
        current_edited_cell.c = colid;
        current_edited_cell.valid = true;
        var data = "row=" + rowid + "&column=" + colid + "&value=" + value+ "&oldvalue=" + oldvalue;
        $.ajax({
            dataType: 'json',
            url: savevalue_url + current_table ,
            //timeout: 1000,
            data: data,
            method: 'POST',
            success: function (data, status, xhr) {
                if (data.error == 401) {
                    $("#currentError").text("Данные не сохранены. Пользователь не авторизован!");
                    $("#serverErrorNotification").jqxNotification("open");
                }
                else if (data.error == 1001) {
                    $("#currentError").text("Данные не сохранены. Отсутствуют права на изменение данных в этом документе");
                    $("#serverErrorNotification").jqxNotification("open");
                }
                else {
                    if (data.cell_affected) {
                        timestamp = new Date();
                        log_str = $("#log").html();
                        if (log_str == "Изменений не было") {
                            log_str = "";
                        };
                        $("#log").html(log_str + timestamp.toLocaleString() + " Изменена ячейка т ." + data_for_tables[current_table].tablecode +", с."+ row_number
                            + ", г." + colindex + ". (" + oldvalue +
                            " >> " + value + ").</br>");
                        editedCells.push({ t: current_table, r: rowBoundIndex, c: colid});
                        if (data.valid === false) {
                            current_edited_cell.valid = false;
                        }
                        else {
                            //console.log(invalidCells);
                            for (var i = 0; i < invalidCells.length; i++) {
                                if (invalidCells[i].r == rowid && invalidCells[i].c == colid && invalidCells[i].t == current_table ) {
                                    invalidCells.splice(i,1);
                                }
                            }
                        }
                        var protocol = '<div>Строка №' + row_number + ', графа №' + colid + '</div>';
                        i = 1;
                        if (typeof data.protocol !== 'undefined') {
                            $.each(data.protocol, function(key, value) {
                                if (value.result) {
                                    b_color = "#9ddc97";
                                    result = "Верно";
                                }
                                else {
                                    b_color = "#ffa3a8";
                                    result = "Ошибка";
                                }
                                rule = value.rule.trim();
                                protocol += "<div id='showrule"+ i +"'><div class='showrule'>" + i + ". " + rule + "</div></div>";
                                protocol += "<table class='control_result' style='border-color: "+ b_color +"; background-color:"+ b_color +";'><tr><td>Значение</td>";
                                protocol += "<td>Знак сравнения</td><td>Контрольная сумма</td><td>Отклонение</td><th rowspan='2'>"+result+"</th></tr>";
                                protocol += "<tr style='text-align: center'><td>"+value.left_part+"</td><td>"+value.boolean_readable+"</td><td>"+value.right_part_value+"</td>";
                                protocol += "<td>"+value.deviation+"</td></tr></table>";

                                i++;
                            });
                        }
                        else {
                            protocol += "Для данной ячейки правила контроля не определены</br>";
                        }
                        $("#cellvalidationprotocol").html(protocol);
                        for (var j = 1; j < i; j++) {
                            if ($("#showrule"+j).text().length > 300) {
                                $("#showrule"+j).jqxPanel({ width: '100%', height: 50, theme: theme});
                            }
                        }
                        if (protocol_control_created) {
                            $("#protocolcomment").html("<span class='invalid'>Неактуален (в таблице произведены изменения после его формирования)</span>");
                        }
                    }
                }
            },
            error: function (xhr, status, errorThrown) {
                $("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                    + status + ". Обратитесь к администратору.");
                $("#serverErrorNotification").jqxNotification("open");
            }
        });
    });

};
var initlayout = function() {
    layout = [{
        type: 'layoutGroup',
        orientation: 'horizontal',
        items: [{
            type: 'layoutGroup',
            orientation: 'vertical',
            width: '30%',
            items: [{
                type: 'tabbedGroup',
                height: '70%',
                allowPin: false,
                items: [{
                    type: 'layoutPanel',
                    title: 'Форма ' + form_code +', таблицы',
                    contentContainer: 'FormPanel',
                    initContent: inittablelist
                },
                    {
                        type: 'layoutPanel',
                        title: 'Контроль формы',
                        contentContainer: 'FormControlPanel',
                        initContent: initcheckformtab
                    }]
            }, {
                type: 'tabbedGroup',
                height: '30%',
                allowPin: false,
                items: [{
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
                                var dataExportWindow = window.open(full_log_url);
                            });
                        }
                    }]
            }]
        }, {
            type: 'layoutGroup',
            orientation: 'vertical',
            width: '70%',
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
                        $("#checktable").click(checktable);
                        $("#compareprevperiod").jqxButton({ theme: theme });
                        $("#compareprevperiod").click(compare_with_prev);
                    }
                },{
                    type: 'layoutPanel',
                    title: 'Контроль последней изменной ячейки',
                    contentContainer: 'CellControlPanel',
                }]
            }]
        }]
    }];

};
