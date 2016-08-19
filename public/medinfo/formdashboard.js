/**
 * Created by shameev on 12.07.2016.
 */
var firefullscreenevent = function() {
    $(document).bind('fscreenchange', function(e, state, elem) {
        var fsel1 =  $('#togglefullscreen');
        if ($.fullscreen.isFullScreen()) {
            fsel1.jqxToggleButton('check');
        } else {
            fsel1.jqxToggleButton('unCheck');
        }
        var fsel2 =  $('#togglecontrolscreen');
        if ($.fullscreen.isFullScreen()) {
            fsel2.jqxToggleButton('check');
        } else {
            fsel2.jqxToggleButton('unCheck');
        }
    });
};
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
            not_editable = 'jqx-grid-cell-pinned jqx-grid-cell-pinned-bootstrap';
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
        $.each(invalidCells[current_table], function(key, value) {
            if (value.r == rowdata.id && value.c == columnfield) {
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
// Пометка/снятие пометки волнистой чертой неверных таблиц
var markTableInvalid = function (id) {
    if ($.inArray(id, invalidTables) == -1) {
        invalidTables.push(id);
    }
    //$("#formTables").jqxDataTable('render');
    //console.log(invalidTables);
};
var markTableValid = function (id) {
    var index = $.inArray(id, invalidTables);
    if (index !== -1) {
        delete invalidTables[index];
    }
    //("#formTables").jqxDataTable('render');
    //console.log(invalidTables);
    //console.log(index);
};
// Вывод в читаемом виде контроля строки/столбца
var renderRowProtocol = function (container, table_id, protocol_by_type, header_text) {
    var header = $("<div class='rule-header'>" + header_text + " " + "</div>");
    var content = $("<div class='rule-content'></div>");
    var r = 0;
    var i = 0;
    var info = $("<div class='rule-comment bg-info'> - правила контроля не заданы</div>");
    //console.log(protocol_by_type);
    if (!protocol_by_type.no_rules) {
        info = $("<table style='margin: 5px;'></table>");
        $.each(protocol_by_type, function(row_index, row_protocol) {
            if (typeof row_protocol.valid !='undefined') {
                $.each(row_protocol, function(column_index, column_protocol ) {
                    if ( typeof column_protocol.valid !='undefined' ) {
                        var row = $("<tr class='control-row'></tr>");
                        var column = $("<td></td>");
                        var valid = '';
                        column_protocol.valid ? valid = 'верно' : valid = 'не верно';
                        var rule = $("<div class='showrule'><span class='text-info'><strong>" + column_protocol.left_part_formula + "</strong></span> <em>" + column_protocol.boolean_readable
                            + "</em> <span class='text-info'>" +column_protocol.right_part_formula +"</span></div>");
                        var t = "<table class='control-result'><tr><td>Значение</td>";
                        t += "<td>Знак сравнения</td><td>Контрольная сумма</td><td>Отклонение</td>";
                        t += "<td>Результат контроля</td></tr>";
                        t += "<tr><td>" + column_protocol.left_part_value + "</td><td>" + column_protocol.boolean_readable + "</td>";
                        t += "<td>" + column_protocol.right_part_value + "</td>";
                        t += "<td>"+column_protocol.deviation + "</td><td class='check'>" + valid + "</td></tr></table>";
                        var explanation = $(t);
                        column.append(rule);
                        column.append(explanation);
                        row.append(column);
                        info.append(row);
                        if (!column_protocol.valid) {
                            invalidCells[table_id].push({r: row_protocol.row_id, c: column_protocol.column_id});
                            explanation.addClass('invalid');
                            i++;
                        } else {
                            row.addClass('control-valid');
                            row.hide();
                            explanation.addClass('bg-success');
                        }
                        r++;
                    }
                });
            }
        });
    }
    var badge = "<span class='badge'>" + r + " / " + i + "</span>";
    //var badge = "<span class='badge'>" + i + "</span>";
    header.append(badge);
    content.append(info);
    container.append(header);
    container.append(content);
    return info;
};
// Вывод в читаемом виде контроля таблицы
var renderTableProtocol = function (table_id, data) {
    var container;
    var protocol_wrapper = $("<div class='tableprotocol-content'></div>");
    container = $("<div></div>");
    renderRowProtocol(container, table_id, data.intable, 'Результаты внутритабличного контроля строк');
    renderRowProtocol(container, table_id, data.inform, 'Результаты внутриформенного контроля строк');
    renderRowProtocol(container, table_id, data.inreport, 'Результаты межформенного контроля строк');
    renderRowProtocol(container, table_id, data.columns, 'Результаты контроля граф');
    protocol_wrapper.append(container);
    return protocol_wrapper;
};
// Инициализация дополнительных кнопок на панели инструментов контроля таблицы
var initextarbuttons = function () {
    $("#extrabuttons").hide();
    //var showall = $("#showallrule") ;
    $("#showallrule").jqxCheckBox({ theme: theme, checked: true });
    $("#showallrule").on('checked', function (event) {
        $(".control-valid").hide();
    });
    $("#showallrule").on('unchecked', function (event) {
        $(".control-row").show();
    });
    $("#togglecontrolscreen").jqxToggleButton({ theme: theme });
    $("#togglecontrolscreen").on('click', function () {
        var toggled = $("#togglecontrolscreen").jqxToggleButton('toggled');
        if (toggled) {
            $("#tableprotocol").fullscreen();
        }
        else $.fullscreen.exit();
        return false;
    });
    $('#printtableprotocol').jqxButton({ theme: theme });
    $("#expandprotocolrow").jqxToggleButton({ theme: theme });
};

// поиск в протоколе контроля по id строки и столбца
function searchprotocol(source, column_id, row_id) {
    var results;
    results = $.map(source, function(value, index) {
        if(typeof value == 'object') {
            if (value.column_id == column_id && value.row_id == row_id) {
                return value;
            } else {
                return search(value, column_id, row_id);
            }
        }
    });
    return results;
}
// Контроль формы - вывод протокола контроля на страницу и для печати
var checkform = function () {
    var data;
    $.ajax({
        dataType: "json",
        url: validate_form_url,
        data: data,
        beforeSend: function( xhr ) {
            $('#formprotocolloader').show();
            $("#formprotocol").html('');
        },
        success: function (data, status, xhr) {
            var formprotocol = $("#formprotocol");
            var protocol_wrapper = $("<div></div>");
            raiseInfo("Протокол контроля формы загружен");
            $('#formprotocolloader').hide();
            invalidCells.length = 0;
            invalidTables.length = 0;
            var now = new Date();
            var timestamp = now.toLocaleString();
            if  (data.nodata) {
                formprotocol.html("<div class='alert alert-info'>"+ timestamp+" Проверяемая форма не содержит данных</div>");
                return true;
            }
            else if (data.no_rules) {
                formprotocol.html("<div class='alert alert-info'>"+ timestamp+" Для данной формы не заданы правила контроля</div>");
                return true;
            }
            else if (data.valid) {
                formprotocol.html("<div class='alert alert-success'>" + timestamp + " При проверке формы ошибок не выявлено</div>");
                return true;
            }
            $.each(data, function(table_id, tablecontrol) {
                if (typeof tablecontrol == 'object') {
                    if (!tablecontrol.valid) {
                        invalidCells[table_id] = [];
                        markTableInvalid(table_id);
                        var header_text = "(" + data_for_tables[table_id].tablecode + ") " + data_for_tables[table_id].tablename;
                        var theader = $("<div class='tableprotocol-header text-info'><span class='glyph glyphicon-plus'></span>" + header_text + " " + "</div>");
                        var tcontent = renderTableProtocol(table_id, tablecontrol);
                        tcontent.hide();
                        //tcontent.append(r.firstChild);
                        protocol_wrapper.append(theader);
                        protocol_wrapper.append(tcontent);
                    }
                }
            });
            formprotocol.append(protocol_wrapper);
            formprotocol.jqxPanel({ autoUpdate: true, width: '99%', height: '90%'});
/*            protocol_wrapper.jqxNavigationBar({
                width: 'auto',
                arrowPosition: 'left',
                expandMode: 'multiple',
                theme: theme
            });*/
            //console.log($("#formprotocol").find(".tableprotocol-content"));
            $("#formprotocol .tableprotocol-header").click(function() {
                $(this).next().toggle();
                var glyph = $(this.firstChild);
                if (glyph.hasClass('glyphicon-plus')) {
                    glyph.removeClass('glyphicon-plus');
                    glyph.addClass('glyphicon-minus');
                } else {
                    glyph.addClass('glyphicon-plus');
                    glyph.removeClass('glyphicon-minus');
                }
            });
            $("#formprotocol .rule-valid").hide();
            $("#formprotocol .tableprotocol-content").each( function() {
                //consol.log(this.firstChild);
                $(this.firstChild).jqxNavigationBar({
                    width: 'auto',
                    arrowPosition: 'left',
                    expandMode: 'multiple',
                    theme: theme
                });
            });



            if (!data.valid) {

                newwindow_link ="<a href='#' id='printformprotocol'>Открыть протокол в новом окне (распечатать)</a>";
                formprotocolheader ="<a href='#' onclick='window.print()'>Распечатать</a>";
                formprotocolheader +="<p>Протокол контроля формы № "+ form_code +": \"" + form_name +"\" </p>";
                formprotocolheader +="<p>Учреждение: " + ou_code + " " + ou_name + "</p>";
                note ="<p style='background-color: #ffa3a8;'> При проверке обнаружены ошибки по следующим таблицам: </p>";
/*                $("#formvalidation").jqxNavigationBar({
                    width: 'auto',
                    arrowPosition: 'left',
                    expandMode: 'multiple',
                    theme: theme
                });*/
                $('#printformprotocol').click(function () {
                    print_style = "<style>.showrule { font-size: 0.8em; } .control_result { border: 1px solid #7f7f7f; width: 400;";
                    print_style += "border-collapse: collapse; margin-bottom: 10px; } .control_result td { border: 1px solid #7f7f7f; } </style>";
                    var pWindow = window.open("", "ProtocolWindow", "width=900, height=600, scrollbars=yes");
                    pWindow.document.write(print_style + formprotocolheader + list);
                });
            }
            $("#formTables").jqxDataTable('render');
            //$('#DataGrid').jqxGrid('render');
        }
    }).fail(function() {
        $('#formprotocol').html('');
        raiseError("Ошибка получения протокола контроля с сервера.");
    });
};

var checktable = function (table_id) {
    var data ="";
    $.ajax({
        dataType: "json",
        url: validate_table_url + table_id,
        data: data,
        beforeSend: function( xhr ) {
            $("#tableprotocol").html('');
            //$("#cellvalidationprotocol").html('');
            $('#protocolloader').show();
            $('#showallrule').jqxCheckBox('check');
            //invalidCells.length = 0; // TODO: Обнулять только текущую таблицу перед заполнением
            marking_mode = 'control';
            $('#DataGrid').jqxGrid('render');
        },
        success: function (data, status, xhr) {
            var protocol_wrapper;
            var header;
            var printable;
            var tableprotocol = $("#tableprotocol");
            var now = new Date();
            var timestamp = now.toLocaleString();
            var cashed = "";
            invalidCells[table_id] = [];
            $('#protocolloader').hide();
            if (data.cashed) {
                cashed = "(сохраненная версия)";
            }
            raiseInfo("Протокол контроля таблицы загружен");
            if  (data.no_data) {
                tableprotocol.html("<div class='alert alert-info'>"+ timestamp+" Проверяемая таблица не содержит данных</div>");
            }
            else if (data.no_rules) {
                tableprotocol.html("<div class='alert alert-info'>"+ timestamp+" Для данной таблицы не заданы правила контроля</div>");
            }
            else if (data.valid) {
                markTableValid(data.table_id);
                tableprotocol.html("<div class='alert alert-success'>" + timestamp + " При проверке таблицы ошибок не выявлено" + " " + cashed + "</div>");
            }
            else {
                header = $("<div class='alert alert-danger'>" + timestamp + " При проверке таблицы выявлены ошибки " + " " + cashed + "</div>");
                markTableInvalid(data.table_id);
                $("#extrabuttons").show();
                protocol_wrapper = renderTableProtocol(table_id, data);
                //console.log(protocol_wrapper[0].firstChild.children);
                printable = protocol_wrapper.clone();
                $(protocol_wrapper[0].firstChild).jqxNavigationBar({
                    width: 'auto',
                    arrowPosition: 'left',
                    expandMode: 'multiple',
                    theme: theme
                });
                protocol_wrapper.jqxPanel({ autoUpdate: true, width: '99%', height: '99%'});
                tableprotocol.append(header);
                tableprotocol.append(protocol_wrapper);
                if ($("#showallrule").jqxCheckBox('checked'))  {
                    $(".control-valid").hide();
                } else {
                    $(".control-valid").show();
                }
                var printprotocolheader ="<a href='#' onclick='window.print()'>Распечатать</a>";
                printprotocolheader +="<h2>Протокол контроля таблицы " + data_for_tables[table_id].tablecode + " \""+ data_for_tables[table_id].tablename;
                printprotocolheader += "\" формы № "+ form_code + "</h2>";
                printprotocolheader +="<h4>Учреждение: " + ou_code + " " + ou_name + "</h4>";
                table_protocol_comment = "<div>Дата и время проведения проверки: " + timestamp + " "+ cashed + "</div>";
                $('#printtableprotocol').click(function () {
                    print_style = "<style>.badge { background-color: #cbcbcb }";
                    print_style += ".rule-comment { text-indent: 20px; font-style: italic }";
                    print_style += ".rule-header { border-bottom: 1px solid; margin-top: 10px}";
                    print_style += ".showrule { font-size: 0.8em; }";
                    print_style += ".control-result { border: 1px solid #7f7f7f; border-collapse: collapse; margin-bottom: 10px; width: 600px; text-align: center; }";
                    print_style += ".control-result td { border: 1px solid #7f7f7f; }";
                    print_style += "</style>";
                    var pWindow = window.open("", "ProtocolWindow", "width=900, height=600, scrollbars=yes");
                    pWindow.document.write(print_style + printprotocolheader + printable.html());
                });
                protocol_control_created = true;
            }
            $("#formTables").jqxDataTable('render');
            $('#DataGrid').jqxGrid('render');
        }
    }).fail(function() {
        $("#tableprotocol").html('');
        raiseError("Ошибка получения протокола контроля с сервера.");
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
        raiseError("Ошибка получения протокола контроля с сервера.");
        //$("#currentError").text("Ошибка получения протокола контроля с сервера.");
        //$("#serverErrorNotification").jqxNotification("open");
    });
};
var rendertoolbar = function(toolbar) {
    var container = $("<div style='margin: 5px;'></div>");
    var input1 = $("<input class='jqx-input jqx-widget-content jqx-rc-all' id='searchField' type='text' style='height: 23px; float: left; width: 150px;' />");
    var input3 = $("<input id='notnullstrings' type='button' value='Непустые строки' />");
    var input4 = $("<input id='clearfilters' type='button' value='Очистить фильтр' />");
    var input5 = $("<input id='savestate' type='button' value='Сохранить настройки таблицы' />");
    var input6 = $("<input id='loadstate' type='button' value='Загрузить настройки таблицы' />");
    var fullscreen = $("<a id='togglefullscreen' style='margin-left: 2px;' target='_blank'><span class='glyphicon glyphicon-fullscreen'></span></a>");
    toolbar.append(container);
    container.append(input1);
    container.append(input3);
    container.append(input4);
    container.append(input5);
    container.append(input6);
    container.append(fullscreen);
    input1.addClass('jqx-widget-content-' + theme);
    input1.addClass('jqx-rc-all-' + theme);
    input1.jqxInput({ width: 200, placeHolder: "Поиск строки" });
    var oldVal = "";
    input1.on('keydown', function (event) {
        if (input1.val().length >= 2) {
            if (this.timer) {
                clearTimeout(this.timer);
            }
            if (oldVal != input1.val()) {
                this.timer = setTimeout(function () {
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
                raiseError("Ошибка сохранения настроек редактирования таблицы. " + xhr.status + ' (' + xhr.statusText + ') - ' + status);
                //$("#currentError").text("Ошибка сохранения настроек редактирования таблицы. " + xhr.status + ' (' + xhr.statusText + ') - ' + status);
                //$("#serverErrorNotification").jqxNotification("open");
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
    fullscreen.jqxToggleButton({ theme: theme });
    fullscreen.on('click', function () {
        var toggled = fullscreen.jqxToggleButton('toggled');
        if (toggled) {
            $("#DataGrid").fullscreen();
        }
        else $.fullscreen.exit();
        return false;
    });
    firefullscreenevent();
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
    dataAdapter = new $.jqx.dataAdapter(tablesource, {
        loadError: function(jqXHR, status, error) {
            if (jqXHR.status == 401) {
                raiseError('Пользователь не авторизован.', jqXHR );
            }
        }
    });
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
            width: 70,
            cellClassName: function (row, column, value, data) {
                var cell_class = '';
                if ($.inArray(data.id, edited_tables) !== -1) {
                    cell_class += " editedRow";
                }
                if ($.inArray(data.id, invalidTables) !== -1) {
                    cell_class += " invalidTable";
                }
                return cell_class;
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
        $("#extrabuttons").hide();
    });
};
var initcheckformtab = function() {
    $("#checkform").jqxButton({ theme: theme, disabled: control_disabled });
    $("#checkform").click(function () { checkform() });
/*    $("#dataexport").jqxButton({ theme: theme });
    $("#dataexport").click(function () {
        var dataExportWindow = window.open(export_data_url);
    });*/
    if (current_user_role == 3 || current_user_role == 4 ) {
        var vfk = $("<input id='medstatcontrol' type='button' value='Контроль МC(ВФ)'/>");
        var mfk = $("<input id='medstatcontrol' type='button' value='Контроль МC(МФ)'/>");
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
    $("#DataGrid").jqxGrid(
        {
            width: '99%',
            height: '99%',
            source: dataAdapter,
            localization: localize(),
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
                    raiseError("Данные не сохранены. Пользователь не авторизован!");
                    //$("#currentError").text("Данные не сохранены. Пользователь не авторизован!");
                    //$("#serverErrorNotification").jqxNotification("open");
                }
                else if (data.error == 1001) {
                    raiseError("Данные не сохранены. Отсутствуют права на изменение данных в этом документе");
                    //$("#currentError").text("Данные не сохранены. Отсутствуют права на изменение данных в этом документе");
                    //$("#serverErrorNotification").jqxNotification("open");
                }
                else {
                    if (data.cell_affected) {
                        timestamp = new Date();
                        log_str = $("#log").html();
                        if (log_str == "Изменений не было") {
                            log_str = "";
                        }
                        $("#log").html(log_str + timestamp.toLocaleString() + " Изменена ячейка т ." + data_for_tables[current_table].tablecode +", с."+ row_number
                            + ", г." + colindex + ". (" + oldvalue +
                            " >> " + value + ").</br>");
                        editedCells.push({ t: current_table, r: rowBoundIndex, c: colid});
/*                        if (data.valid === false) {
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
                        }*/
                        if (protocol_control_created) {
                            $("#tableprotocol").prepend("<span class='text-danger'>Протокол неактуален (в таблице произведены изменения после его формирования)</span>");
                            //$("#protocolcomment").html();
                        }
                    }
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.");
                //$("#currentError").text("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - '
                  //  + status + ". Обратитесь к администратору.");
                //$("#serverErrorNotification").jqxNotification("open");
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
                                var dataExportWindow = window.open(valuechangelog_url);
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
                        $("#checktable").click( function() { checktable(current_table) });
                        $("#compareprevperiod").jqxButton({ theme: theme });
                        $("#compareprevperiod").click(compare_with_prev);

                    }
                },{
                    type: 'layoutPanel',
                    title: 'Контроль ячейки',
                    contentContainer: 'CellControlPanel',
                }]
            }]
        }]
    }];

};
