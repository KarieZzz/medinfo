/**
 * Created by shameev on 12.07.2016.
 */
// Обработка состояния кнопочек перевода в полноэкранный режим
// Пометка/снятие пометки волнистой чертой неверных таблиц

// Контроль таблицы - вывод протокола контроля на страницу и для печати
let tabledatacheck = function(table_id, type) {
    let data = "";
    let url = "";
    switch (type) {
        case 'inform' :
            url = informTableDataCheck + table_id + "/" + forcereload;
            break;
        case 'interperiod' :
            url = interPeriodTableDataCheck + table_id + "/" + forcereload;
            break;
    }


    $.ajax({
        dataType: "json",
        url: url,
        data: data,
        beforeSend: beforecheck,
        success: gettableprotocol
    }).fail(function(jqXHR, textStatus, errorThrown) {
        $("#tableprotocol").html('');
        $('#formprotocolloader').hide();
        $('#protocolloader').hide();
        if (jqXHR.status == 401) {
            raiseError('Пользователь не авторизован.', jqXHR );
            return false;
        }
        raiseError("Ошибка получения протокола контроля с сервера.");
    });
};
let beforecheck = function( xhr ) {
    $("#tableprotocol").html('');
    //$("#cellvalidationprotocol").html('');
    $('#protocolloader').show();
    //$('#showallrule').jqxCheckBox('check');
    $("#extrabuttons").hide();
    $(".inactual-protocol").hide();
    if (typeof invalidCells[current_table] !== 'undefined' ) {
        invalidCells[current_table].length = 0;
    }
    if (typeof alertedCells[current_table] !== 'undefined' ) {
        alertedCells[current_table].length = 0;
    }
    marking_mode = 'control';
    dgrid.jqxGrid('refresh');
};
let renderCellProtocol = function(cell_protocol) {
    let row = $("<tr class='control-row'></tr>");
    let column = $("<td></td>");
    cell_protocol.valid ? valid = 'верно' : valid = 'не верно';
    let rule = $("<div class='showrule'><span class='text-info'><strong>" + cell_protocol.left_part_formula + "</strong></span> <em>" + cell_protocol.boolean_readable
        + "</em> <span class='text-info'>" + cell_protocol.right_part_formula +"</span></div>");
    let t = "<table class='control-result'><tr><td>Значение</td>";
    t += "<td>Знак сравнения</td><td>Контрольная сумма</td><td>Отклонение</td>";
    t += "<td>Результат контроля</td></tr>";
    t += "<tr><td>" + cell_protocol.left_part_value + "</td><td>" + cell_protocol.boolean_readable + "</td>";
    t += "<td>" + cell_protocol.right_part_value + "</td>";
    t += "<td>"+cell_protocol.deviation + "</td><td class='check'>" + valid + "</td></tr></table>";
    let explanation = $(t);
    column.append(rule);
    column.append(explanation);
    if (!cell_protocol.valid) {
        explanation.addClass('invalid');
    } else {
        explanation.addClass('bg-success');
    }
    row.append(column);
    return row;
};
// Для контроля из "старого" Мединфо - вывод в читаемом виде контроля строки/столбца
let renderRowProtocol = function (container, table_id, protocol_by_type, header_text) {
    let header = $("<div class='rule-header'>" + header_text + " " + "</div>");
    let content = $("<div class='rule-content'></div>");
    let r = 0;
    let i = 0;
    let info = $("<div class='rule-comment bg-info'> - правила контроля не заданы</div>");
    if (typeof protocol_by_type.no_rules === 'undefined' || !protocol_by_type.no_rules) {
        info = $("<table style='margin: 5px;'></table>");
        $.each(protocol_by_type, function(row_index, row_protocol) {
            if (typeof row_protocol.valid !== 'undefined') {
                $.each(row_protocol, function(column_index, cell_protocol ) {
                    if ( typeof cell_protocol.valid !=='undefined' ) {
                        //var valid = '';
                        let row = renderCellProtocol(cell_protocol);
                        info.append(row);
                        if (!cell_protocol.valid) {
                            invalidCells[table_id].push({r: row_protocol.row_id, c: cell_protocol.column_id});
                            i++;
                        } else {
                            row.addClass('control-valid');
                            //row.hide();
                        }
                        r++;
                    }
                });
            }
        });
    }
    let badge = "<span class='badge'>" + r + " / " + i + "</span>";
    //var badge = "<span class='badge'>" + i + "</span>";
    header.append(badge);
    content.append(info);
    container.append(header);
    container.append(content);
    return info;
};
// Отображение результатов контроля (новый формат) по каждой итерации
let renderCompareControl = function(result, boolean_sign, mode, level) {
    //console.log(result.cells);
    let explanation_intro = mode === 1 ? 'По строке' : 'По графе';
    let error_level_mark = 'invalid';
    switch (level) {
        case 1 :
            error_level_mark = 'invalid';
            break;
        case 2 :
            error_level_mark = 'alerted';
            break;
    }
    let row = $("<div class='control-row'></div>");
    result.valid ? valid = 'верно' : valid = 'не верно';
    //if (typeof result.code !== 'undefined') {
    if (result.code !== null) {
        //console.log(result.code);
        let rule = $("<div class='showrule'><span class='text-info'><strong>" + explanation_intro + "</strong></span> <em>" + result.code + "</em>:</div>");
        row.append(rule);
    }

    let t = "<table class='control-result'><tr><td>Значение</td>";
    t += "<td>Знак сравнения</td><td>Контрольная сумма</td><td>Отклонение</td>";
    t += "<td>Результат контроля</td></tr>";
    t += "<tr><td>" + result.left_part_value + "</td><td>" + boolean_sign + "</td>";
    t += "<td>" + result.right_part_value + "</td>";
    t += "<td>"+result.deviation + "</td><td class='check'>" + valid + "</td></tr></table>";
    let explanation = $(t);

    row.append(explanation);
    if (!result.valid) {
        explanation.addClass(error_level_mark);
    } else {
        explanation.addClass('bg-success');
    }
    return row;
};
let renderDependencyControl = function(result, mode, level) {
    //console.log(result.cells);
    var explanation_intro = mode == 1 ? 'По строке' : 'По графе';
    var error_level_mark = 'invalid';
    switch (level) {
        case 1 :
            error_level_mark = 'invalid';
            break;
        case 2 :
            error_level_mark = 'alerted';
            break;
    }
    var row = $("<div class='control-row'></div>");
    result.valid ? valid = 'верно' : valid = 'не верно';
    if (typeof result.code !== 'undefined') {
        //console.log(result.code);
        var rule = $("<div class='showrule'><span class='text-info'><strong>" + explanation_intro + "</strong></span> <em>" + result.code + "</em>:</div>");
        row.append(rule);
    }

    var t = "<table class='control-result'><tr><td>Значение</td>";
    t += "<td>Контрольная сумма</td><td>Отклонение</td>";
    t += "<td>Результат контроля</td></tr>";
    t += "<tr><td>" + result.left_part_value + "</td>";
    t += "<td>" + result.right_part_value + "</td>";
    t += "<td>"+result.deviation + "</td><td class='check'>" + valid + "</td></tr></table>";
    var explanation = $(t);

    row.append(explanation);
    if (!result.valid) {
        explanation.addClass(error_level_mark);
    } else {
        explanation.addClass('bg-success');
    }
    return row;
};
let renderInDiapazonControl = function(result, level) {
    //console.log(result.cells);
    let error_level_mark = 'invalid';
    switch (level) {
        case 1 :
            error_level_mark = 'invalid';
            break;
        case 2 :
            error_level_mark = 'alerted';
            break;
    }
    let row = $("<div class='control-row'></div>");
    result.valid ? valid = 'верно' : valid = 'не верно';
    if (typeof result.code !== 'undefined') {
        let rule = $("<div class='showrule'><span class='text-info'><strong> По ячейке </strong></span> <em>" + result.code + "</em>:</div>");
        row.append(rule);
    }
    let t = "<table class='control-result'><tr><td>Текущее</td>";
    t += "<td>Прошлогоднее</td><td>Отклонение (%)</td>";
    t += "<td>Результат контроля</td></tr>";
    t += "<tr><td>" + result.left_part_value + "</td>" ;
    t += "<td>" + result.right_part_value + "</td>";
    t += "<td>"+result.deviation + "</td> <td class='check'>" + valid + "</td></tr></table>";
    let explanation = $(t);

    row.append(explanation);
    if (!result.valid) {
        explanation.addClass(error_level_mark);
    } else {
        explanation.addClass('bg-success');
    }
    return row;
};
let renderFoldControl = function(result, level) {
    //console.log(result.cells);
    let error_level_mark = 'invalid';
    switch (level) {
        case 1 :
            error_level_mark = 'invalid';
            break;
        case 2 :
            error_level_mark = 'alerted';
            break;
    }
    let row = $("<div class='control-row'></div>");
    result.valid ? valid = 'верно' : valid = 'не верно';
    if (typeof result.code !== 'undefined') {
        let rule = $("<div class='showrule'><span class='text-info'><strong> По ячейке </strong></span> <em>" + result.code + "</em>:</div>");
        row.append(rule);
    }
    let t = "<table class='control-result'><tr><td>Текущее значение</td>";
    t += "<td>Результат контроля</td></tr>";
    t += "<tr><td>" + result.left_part_value + "</td>" ;
    t += "<td class='check'>" + valid + "</td></tr></table>";
    let explanation = $(t);

    row.append(explanation);
    if (!result.valid) {
        explanation.addClass(error_level_mark);
    } else {
        explanation.addClass('bg-success');
    }
    return row;
};
// Отображение результатов контроля (новый формат) по каждой функции контроля
let renderFunctionProtocol = function (container, table_id, rule) {
    let rule_valid = rule.valid ? 'rule-valid' : 'rule-invalid';
    let rule_wrapper = $("<div class='rule " + rule_valid + "'></div>");
    let header = $("<div class='rule-header " + rule_valid + "'></div>");
    let content = $("<div class='rule-content " + rule_valid + "'></div>");
    let error_level_mark;
    if (rule.comment !== '') {
        content.append("<div class='text-warning small'><strong>^ Пояснения: </strong>" + rule.comment + "</div>");
    }
    if (typeof rule.error !== 'undefined') {
        header.append(rule.error);
        return false;
    }
    switch (rule.level) {
        case 1 :
            error_level_mark = 'text-danger bg-danger';
            break;
        case 2 :
            error_level_mark = 'text-warning bg-warning';
            break;
    }
    header.append("<strong>Правило контроля: </strong><span class='" + error_level_mark +"'>" + rule.formula + "</span> ");
    let r = 0;
    let i = 0;

    $.each(rule.iterations, function(i_index, result) {
        if ( typeof result.valid !=='undefined' ) {
            //let valid = '';
            let row;

            switch (rule.function_id) {
                case formlabels.compare :
                    row = renderCompareControl(result, rule.boolean_sign, rule.iteration_mode, rule.level);
                    break;
                case formlabels.dependency :
                    row = renderDependencyControl(result, rule.iteration_mode, rule.level);
                    break;
                case formlabels.interannual :
                    row = renderInterannualControl(result, rule.level);
                    break;
                case formlabels.iadiapazon :
                    row = renderInDiapazonControl(result, rule.level);
                    break;
                case formlabels.multiplicity :
                    row = renderFoldControl(result, rule.level);
                    break;
            }
            content.append(row);
            if (!result.valid) {
                if (rule.level === 1) {
                    $.each(result.cells, function(c_index, cell) {
                        invalidCells[table_id].push({r: cell.row, c: cell.column});
                    });
                } else {
                    $.each(result.cells, function(c_index, cell) {
                        alertedCells[table_id].push({r: cell.row, c: cell.column});
                    });
                }
                row.show();
                row.addClass('control-invalid');
                i++;
            } else {
                row.addClass('control-valid');
                //row.hide();
            }
            r++;
        }
    });
    let badge = "<span class='badge' title='Всего выполнено / Обнаружены ошибки'> Проверено: " + r + " / Ошибок: " + i + "</span>";
    header.append(badge);
    //content.append(info);

    rule_wrapper.append(header);
    rule_wrapper.append(content);
    container.append(rule_wrapper);
    return content;
};
// Вывод в читаемом виде контроля таблицы
let renderTableProtocol = function (table_id, data) {
    invalidCells[table_id] = [];
    alertedCells[table_id] = [];
    let container = $("<div></div>");
    let protocol_wrapper = $("<div class='tableprotocol-content'></div>");
    if (typeof data.intable !== 'undefined') {
        //renderRowProtocol(container, table_id, data.intable, 'Результаты внутритабличного контроля строк');
        //renderRowProtocol(container, table_id, data.inform, 'Результаты внутриформенного контроля строк');
        //renderRowProtocol(container, table_id, data.inreport, 'Результаты межформенного контроля строк');
        //renderRowProtocol(container, table_id, data.inrow, 'Результаты контроля внутри строки');
        //renderRowProtocol(container, table_id, data.columns, 'Результаты контроля граф');
    } else if(typeof data.rules !== 'undefined') {
        $.each(data.rules, function(rule_index, rule ) {
            renderFunctionProtocol(container, table_id, rule);
        });
    }
    protocol_wrapper.append(container);
    return protocol_wrapper;
};
// Инициализация дополнительных кнопок на панели инструментов контроля формы
let init_fc_extarbuttons = function () {
    $("#fc_extrabuttons").hide();
    $("#showallfcrule").jqxCheckBox({ theme: theme, checked: true });
    $("#showallfcrule").on('checked', function (event) {
        $(".rule-valid ").parent(".jqx-expander-header").hide().next().hide();
    });
    $("#showallfcrule").on('unchecked', function (event) {
        $(".rule-valid ").parent(".jqx-expander-header").show().next().show();
    });
    //$("#toggle_formcontrolscreen").jqxToggleButton({ theme: theme });
    //$("#toggle_formcontrolscreen").on('click', function () {
      //  var toggled = $("#toggle_formcontrolscreen").jqxToggleButton('toggled');
//        if (toggled) {
  //          $("#formprotocol").fullscreen();
    //    }
      //  else $.fullscreen.exit();
        //return false;
    //});
    $('#printformprotocol').jqxButton({ theme: theme });
};
// Инициализация дополнительных кнопок на панели инструментов контроля таблицы
let initextarbuttons = function () {
    $("#extrabuttons").hide();
    $("#showallrule").jqxCheckBox({ theme: theme, checked: true });
    $("#showallrule").on('checked', function (event) {
        $(".rule-valid").hide();
        $(".control-valid").hide();
    });
    $("#showallrule").on('unchecked', function (event) {
        $(".rule-valid").show();
        $(".control-valid").show();
    });
    $("#togglecontrolscreen").jqxToggleButton({ theme: theme });
    $("#togglecontrolscreen").on('click', function () {
        let toggled = $("#togglecontrolscreen").jqxToggleButton('toggled');
        if (toggled) {
            splitter.jqxSplitter({panels: [{ size: 100, collapsible: false }, { size: '50%'}]})
        } else {
            splitter.jqxSplitter({panels: [ { size: '60%', min: 100, collapsible: false }, {collapsed:true} ]});
        };





    });
    $('#printtableprotocol').jqxButton({ theme: theme });
    //$("#expandprotocolrow").jqxToggleButton({ theme: theme });
};
// поиск в протоколе контроля по id строки и столбца (старый формат)
function searchprotocol(source, column_id, row_id) {
    var results;
    results = $.map(source, function(value, index) {
        if(typeof value == 'object') {
            //if (value.column_id == column_id && value.row_id == row_id) {
            if (value.column == column_id && value.row == row_id) {
                return value;
            } else {
                return searchprotocol(value, column_id, row_id);
            }
        }
    });
    return results;
}
function selectedcell_protocol(form_protocol, table_id, table_code, column_id, row_id) {
    let tableprotocol = form_protocol[table_code];
    let cell_protocol = [];
    if (tableprotocol.no_rules) {
        return false;
    } else {
        $.each(tableprotocol.rules, function (rule_idx, rule) {
            if (!rule.no_rules) {
                $.each(rule.iterations, function (iteration_idx, iteration) {
                    //console.log(cellfound(iteration.cells, column_id, row_id));
                    if (cellfound(iteration.cells, column_id, row_id)) {
                        cell_protocol.push({ rule: rule, result: iteration });
                    }
                });
            }
        });
    }
    return cell_protocol;
}

function cellfound(cells, column_id, row_id) {
    let found = false;
    $.each(cells, function(cell_idx, cell) {
        //console.log(cell.column == column_id && cell.row == row_id);
        if (cell.column == column_id && cell.row == row_id) {
            found = true;
        }
    });
    return found;
}

let checkform = function () {
    let data;
    $.ajax({
        dataType: "json",
        //url: validate_form_url,
        url: formdatacheck_url +"/" + forcereload,
        data: data,
        beforeSend: function( xhr ) {
            $('#formprotocolloader').show();
            $("#formprotocol").html('');
            $(".inactual-protocol").hide();
            $("#fc_extrabuttons").hide();
            current_protocol_source = null;
        },
        success: function (data, status, xhr) {
            let formprotocol = $("#formprotocol");
            let protocol_wrapper = $("<div></div>");
            let header;
            let printable;
            let formprotocolheader;
            current_protocol_source = data;
            raiseInfo("Протокол контроля формы загружен");
            $('#formprotocolloader').hide();
            invalidCells.length = 0;
            alertedCells.length = 0;
            invalidTables.length = 0;
            let now = new Date();
            let timestamp = now.toLocaleString();
            if  (data.nodata) {
                formprotocol.html("<div class='alert alert-info'>"+ timestamp+" Проверяемая форма не содержит данных</div>");
                protocol_control_created = true;
                return true;
            }
            else if (data.no_rules) {
                formprotocol.html("<div class='alert alert-info'>"+ timestamp+" Для данной формы не заданы правила контроля</div>");
                protocol_control_created = false;
                return true;
            }
            else if (data.valid && data.no_alerts) {
                formprotocol.html("<div class='alert alert-success'>" + timestamp + " При проверке формы ошибок/замечаний не выявлено</div>");
                protocol_control_created = true;
                return true;
            }
            $("#fc_extrabuttons").show();
            $.each(data, function(tablecode, tablecontrol) {
                if (typeof tablecontrol === 'object') {
                    if (!tablecontrol.valid || !tablecontrol.no_alerts) {
                        invalidCells[tablecontrol.table_id] = [];
                        alertedCells[tablecontrol.table_id] = [];
                        markTableInvalid(tablecontrol.table_id);
                        let header_text = "(" + tablecode + ") " + data_for_tables[tablecontrol.table_id].tablename;
                        let theader = $("<div class='tableprotocol-header text-info'><span class='glyph glyphicon-plus'></span>" + header_text + " " + "</div>");
                        let tcontent = renderTableProtocol(tablecontrol.table_id, tablecontrol);
                        //tcontent.hide();
                        //tcontent.append(r.firstChild);
                        protocol_wrapper.append(theader);
                        protocol_wrapper.append(tcontent);
                    }
                }
            });
            header = $("<div class='alert'></div>");
            header.html(timestamp + " При проверке формы выявлены ошибки/замечания в следующих таблицах: ");
            if (!data.valid) {
                header.addClass('alert-danger');
            } else {
                header.addClass('alert-warning');
            }
            formprotocol.append(header);
            //header = $("<div class='alert alert-danger'>" + timestamp + " При проверке формы выявлены ошибки/замечания в следующих таблицах:</div>");
            formprotocol.append(protocol_wrapper);
            printable = formprotocol.clone();

            formprotocol.jqxPanel({ autoUpdate: true, width: '97%', height: '80%'});
            $("#formprotocol .tableprotocol-header").click(function() {
                $(this).next().toggle();
                let glyph = $(this.firstChild);
                if (glyph.hasClass('glyphicon-plus')) {
                    glyph.removeClass('glyphicon-plus');
                    glyph.addClass('glyphicon-minus');
                } else {
                    glyph.addClass('glyphicon-plus');
                    glyph.removeClass('glyphicon-minus');
                }
            });


            //$("#formprotocol .rule-valid").hide();
            $("#formprotocol .tableprotocol-content").hide();
            $("#formprotocol .tableprotocol-content").each( function() {
                //consol.log(this.firstChild);
                $(this.firstChild).jqxNavigationBar({
                    width: 'auto',
                    arrowPosition: 'left',
                    expandMode: 'multiple',
                    theme: theme
                });
            });
            $(".rule-valid ").parent(".jqx-expander-header").hide().next().hide();
            $(".control-valid ").hide();
            formprotocolheader ="<a href='#' onclick='window.print()'>Распечатать</a>";
            formprotocolheader +="<h2>Протокол контроля формы № "+ form_code +": \"" + form_name +"\" </h2>";
            formprotocolheader +="<h3>Учреждение: " + ou_code + " " + ou_name + "</h3>";
            let print_style = "<style>.tableprotocol-header { margin-top: 20px; font-size: 1.1em; font-weight: bold }";
            //print_style += ".badge { background-color: #cbcbcb }";
            print_style += ".badge { display:none }";
            print_style += ".rule-valid { display:none }";
            print_style += ".control-valid { display:none }";
            print_style += ".rule-comment { text-indent: 20px; font-style: italic }";
            print_style += ".rule-header { border-bottom: 1px solid; margin-top: 10px}";
            print_style += ".showrule { font-size: 0.8em; }";
            print_style += ".control-result { border: 1px solid #7f7f7f; border-collapse: collapse; margin-bottom: 10px; width: 600px; text-align: center; }";
            print_style += ".control-result td { border: 1px solid #7f7f7f; }";
            print_style += "</style>";
            $('#printformprotocol').click(function () {
                var pWindow = window.open("", "ProtocolWindow", "width=900, height=600, scrollbars=yes");
                pWindow.document.body.innerHTML = " ";
                pWindow.document.write(print_style + formprotocolheader + printable.html());
            });

            protocol_control_created = true;
            $("#formTables").jqxDataTable('refresh');
            $('#DataGrid').jqxGrid('refresh');
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        $('#formprotocol').html('');
        $('#formprotocolloader').hide();
        if (jqXHR.status === 401) {
            raiseError('Пользователь не авторизован.', jqXHR );
            return false;
        }
        raiseError("Ошибка получения протокола контроля с сервера.");
    });
};
let markTableInvalid = function (id) {
    if ($.inArray(id, invalidTables) === -1) {
        invalidTables.push(id);
    }
};
let markTableValid = function (id) {
    let index = $.inArray(id, invalidTables);
    if (index !== -1) {
        delete invalidTables[index];
    }
    //("#formTables").jqxDataTable('render');
    //console.log(invalidTables);
    //console.log(index);
};
/*
// Не используется в текущей версии
let checktable = function (table_id) {
    invalidCells[table_id] = [];
    alertedCells[table_id] = [];
    let data = "";
    $.ajax({
        dataType: "json",
        url: validate_table_url + table_id + "/" + forcereload,
        data: data,
        beforeSend: beforecheck,
        success: gettableprotocol
    }).fail(function(jqXHR, textStatus, errorThrown) {
        $("#tableprotocol").html('');
        $('#formprotocolloader').hide();
        $('#protocolloader').hide();
        if (jqXHR.status == 401) {
            raiseError('Пользователь не авторизован.', jqXHR );
            return false;
        }
        raiseError("Ошибка получения протокола контроля с сервера.");
    });
};
// Не используется в текущей версии
let compare_with_prev = function () {
    let data ="";
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
*/

let gettableprotocol = function (data, status, xhr) {
    let protocol_wrapper;
    let header;
    let printable;
    let scripterrors;
    let tableprotocol = $("#tableprotocol");
    let now = new Date();
    let timestamp = now.toLocaleString();
    let cashed = "";
    $('#protocolloader').hide();
    if (data.cashed) {
        cashed = "(сохраненная версия)";
    }
    raiseInfo("Протокол контроля таблицы загружен");
/*    if  (data.no_data) {
        tableprotocol.html("<div class='alert alert-info'>"+ timestamp+" Проверяемая таблица не содержит данных</div>");
        protocol_control_created = true;
    }
    else*/
    if (typeof data.no_rules !== 'undefined' && data.no_rules) {
        tableprotocol.html("<div class='alert alert-info'>"+ timestamp+" Для данной таблицы не заданы правила контроля</div>");
        protocol_control_created = false;
    }
    else if (data.valid && data.no_alerts) {
        markTableValid(data.table_id);
        tableprotocol.append("<div class='alert alert-success'>" + timestamp + " При проверке таблицы ошибок/замечаний не выявлено" + " " + cashed + "</div>");
        protocol_control_created = true;
    } else {
        header = $("<div class='alert'></div>");
        header.html(timestamp + " При проверке таблицы выявлены ошибки/замечания " + " " + cashed);
        if (!data.valid) {
            header.addClass('alert-danger');
        } else {
            header.addClass('alert-warning');
        }
        markTableInvalid(data.table_id);
        $("#extrabuttons").show();
        protocol_wrapper = renderTableProtocol(data.table_id, data);
        printable = protocol_wrapper.clone();
/*        $(protocol_wrapper[0].firstChild).jqxNavigationBar({
            width: 'auto',
            arrowPosition: 'left',
            expandMode: 'multiple',
            theme: theme
        });*/
        //protocol_wrapper.jqxPanel({ autoUpdate: true, width: '98%', height: '75%'});
        tableprotocol.append(header);
        tableprotocol.append(protocol_wrapper);
        if ($("#showallrule").jqxCheckBox('checked'))  {
            $(".rule-valid ").hide();
            $(".control-valid ").hide();
            //$(".rule-valid ").parent(".jqx-expander-header").hide().next().hide();
            //$(".control-valid ").hide();
        } else {
            //$(".rule-valid").parent(".jqx-expander-header").show().next().hide();
            $(".rule-valid").show();
            $(".control-valid ").show();
        }
        let printprotocolheader ="<a href='#' onclick='window.print()'>Распечатать</a>";
        printprotocolheader += "<h2>Протокол контроля таблицы " + current_table_code + " \""+ data_for_tables[data.table_id].tablename;
        printprotocolheader += "\" формы № "+ form_code + "</h2>";
        printprotocolheader +="<h4>Учреждение: " + ou_code + " " + ou_name + "</h4>";
        let print_style = "<style>.badge { background-color: #cbcbcb }";
        print_style += ".badge { display:none }";
        print_style += ".rule-valid { display:none }";
        print_style += ".control-valid { display:none }";
        print_style += ".rule-comment { text-indent: 20px; font-style: italic }";
        print_style += ".rule-header { border-bottom: 1px solid; margin-top: 10px}";
        print_style += ".showrule { font-size: 0.8em; }";
        print_style += ".control-result { border: 1px solid #7f7f7f; border-collapse: collapse; margin-bottom: 10px; width: 600px; text-align: center; }";
        print_style += ".control-result td { border: 1px solid #7f7f7f; }";
        print_style += "</style>";
        table_protocol_comment = "<div>Дата и время проведения проверки: " + timestamp + " "+ cashed + "</div>";
        $('#printtableprotocol').click(function () {
            let pWindow = window.open("", "ProtocolWindow", "width=900, height=600, scrollbars=yes");
            // почистить окошко от предыдущего протокола
            pWindow.document.body.innerHTML = " ";
            pWindow.document.write(print_style + printprotocolheader + printable.html());
        });
        protocol_control_created = true;
    }
    if (typeof data.errors !== 'undefined' && data.errors.length > 0 && (current_user_role === 3 || current_user_role === 4 )) {
        scripterrors = $("<div class='alert alert-danger'></div>");
        scripterrors.append("<p><strong>Ошибка выполнения!</strong> При выполнения контроля по данной таблицы выявлен ряд ошибок в функциях:</p>");
        $.each(data.errors, function(error_inx, error ) {
            scripterrors.append("<p><strong>Код ошибки: " + error.code + "</strong> " + error.message + "</p>");
        });
        tableprotocol.append(scripterrors);
    }
    current_protocol_source[current_table_code] = data;
    fgrid.jqxDataTable('refresh');
    dgrid.jqxGrid('refresh');
};
// Экспорт данных текущей таблицы в эксель
let tabledataexport = function(table_id) {
    window.open(tableexport_url + table_id);
};
let initdatasources = function() {
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

/*
    let form_table_source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'parent_id', type: 'int' },
                { name: 'code', type: 'string' },
                { name: 'name', type: 'string' }
            ],
            hierarchy:
                {
                    keyDataField: { name: 'id' },
                    parentDataField: { name: 'parent_id' }
                },
            id: 'id',
            root: '',
            localdata: form_tables_data
        };
*/


    tableListDataAdapter = new $.jqx.dataAdapter(form_table_source);
    tablesource =
    {
        datatype: "json",
        datafields: data_for_tables[current_table].datafields,
        autoBind: true,
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
// Получение читабельных координат ячейки - код строки, индекс графы
let getreadablecelladress = function(row, column) {
    let row_code = dgrid.jqxGrid('getcellvaluebyid', row, current_row_number_datafield);
    let column_index = dgrid.jqxGrid('getcolumnproperty', column, 'text');
    return { row: row_code, column: column_index};
};
let fetchcelllayer = function(row, column) {
    let layer_container = $("<table class='table table-condensed table-striped table-bordered'></table>");
    let period_container = $("<table class='table table-condensed table-striped table-bordered'></table>");
    let fetch_url = cell_layer_url + row + '/' + column;
    $.getJSON( fetch_url, function( data ) {
        $.each(data.layers, function (i, layer) {
            let row = $("<tr class='rowdocument' id='"+ layer.doc_id +"'><td>" + layer.unit_code
                + "</td><td><a href='/datainput/formdashboard/" + layer.doc_id +"' target='_blank' title='Открыть для редактирования'>" + layer.unit_name + "</a>"
                + "</td><td style='min-width: 40px' class='text-primary text-right'>" + layer.value
                + "</td></tr>");
            layer_container.append(row);
        });
        $.each(data.periods, function (i, period) {
            let row = $("<tr><td>" + period.period
                + "</td><td style='min-width: 40px' class='text-primary text-right'>" + period.value
                + "</td></tr>");
            period_container.append(row);
        });
    });
    return { layers: layer_container, periods: period_container} ;
};

// Инициализация перечня таблиц текущей формы
let inittablelist = function() {
    fgrid.jqxDataTable({
        width: 700,
        height: 400,
        filterable: true,
        filterMode: 'simple',
        theme: theme,
        source: tableListDataAdapter,
        localization: localize(),
        ready: function () {
            fgrid.jqxDataTable('selectRow', 0);
        },
        columns: [{
            text: 'Код',
            dataField: 'code',
            width: 70,
            cellClassName: function (row, column, value, data) {
                let cell_class = '';
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
    fgrid.on('rowSelect', function (event) {
        if (event.args.row.id === current_table) {
            return false;
        }
        //$("#formTables").jqxDataTable('render');
        dgrid.jqxGrid('endcelledit', current_edited_cell.r, current_edited_cell.c, false);
        dgrid.jqxGrid('clearselection');
        dgrid.jqxGrid('clearfilters');
        current_table = event.args.row.id;
        current_table_code = data_for_tables[current_table].tablecode;
        current_row_name_datafield = data_for_tables[current_table].columns[1].dataField;
        current_row_number_datafield = data_for_tables[current_table].columns[2].dataField;
        dgrid.jqxGrid('beginupdate');
        tablesource.datafields = data_for_tables[current_table].datafields;
        tablesource.url = source_url + current_table;
        data_for_tables[current_table].calcfields.length > 0 ? there_is_calculated = true : there_is_calculated = false;
        there_is_calculated ? calculate.prop('disabled', false ) : calculate.attr('disabled', true );
        dgrid.jqxGrid( { columns: data_for_tables[current_table].columns } );
        dgrid.jqxGrid( { columngroups: data_for_tables[current_table].columngroups } );
        dgrid.jqxGrid('updatebounddata');
        dgrid.jqxGrid('endupdate');
        //layout[0].items[1].items[0].items[0].title = "Таблица " + data_for_tables[current_table].tablecode + ', "' + data_for_tables[current_table].tablename + '"';
        $("#TableTitle").html("Таблица " + data_for_tables[current_table].tablecode + ', "' + data_for_tables[current_table].tablename + '"');
        //$('#formEditLayout').jqxLayout('refresh');
        $("#tableprotocol").html('');
        $("#extrabuttons").hide();
        tdropdown.jqxDropDownButton('close');
        splitter.jqxSplitter('collapse');
        //$("#formTables").jqxDataTable('focus');
    });

};
// Инициализация вкладки протокола контроля формы
let initcheckformtab = function() {
    //$("#checkform").jqxButton({ theme: theme, disabled: control_disabled });
    $("#checkform").click(function () { checkform() });
    var refresh_protocol = $("<i style='margin-left: 2px;height: 14px; float: left' class='fa  fa-lg fa-circle-o' title='Обновить/пересоздать протокол контроля'></i>");
    refresh_protocol.jqxToggleButton({ theme: theme, toggled: false });
    refresh_protocol.on('click', function () {
        var toggled = $(this).jqxToggleButton('toggled');
        if (toggled) {
            forcereload = 1;
            $(this).removeClass('fa-circle-o');
            $(this).addClass('fa-circle');
            raiseInfo("При следующием запуске контроля формы/таблицы протоколы будут обновлены");
        } else {
            forcereload = 0;
            $(this).removeClass('fa-circle');
            $(this).addClass('fa-circle-o');
        }


    });
    $("#fc_extrabuttons").append(refresh_protocol);

/*    $("#dataexport").jqxButton({ theme: theme });
    $("#dataexport").click(function () {
        var dataExportWindow = window.open(export_data_url);
    });*/
    /*if (current_user_role == 3 || current_user_role == 4 ) {
        var vfk = $("<input id='medstatcontrol' style='float: left' type='button' value='Контроль МC(ВФ)'/>");
        var mfk = $("<input id='medstatcontrol' style='float: left' type='button' value='Контроль МC(МФ)'/>");
        $("#formControlToolbar").prepend(vfk);
        vfk.jqxButton({ theme: theme });
        vfk.click(function () {
            var ms_cntrl = window.open(medstat_control_url + '&type=vfk');
        });
        $("#formControlToolbar").prepend(mfk);
        mfk.jqxButton({ theme: theme });
        mfk.click(function () {
            var ms_cntrl = window.open(medstat_control_url + '&type=mfk');
        });

    }*/
};
let initfilters = function() {
    row_name_filter = function (needle) {
        let rowFilterGroup = new $.jqx.filter();
        let filter_or_operator = 1;
        // create a string filter with 'contains' condition.
        //var filtervalue = 'всего';
        let filtervalue = needle;
        let filtercondition = 'contains';
        let nameRecordFilter = rowFilterGroup.createfilter('stringfilter', filtervalue, filtercondition);
        rowFilterGroup.addfilter(filter_or_operator, nameRecordFilter);
        //$("#DataGrid").jqxGrid('addfilter', '1', rowFilterGroup);
        dgrid.jqxGrid('addfilter', current_row_name_datafield, rowFilterGroup);
        dgrid.jqxGrid('applyfilters');
    };
    not_null_filter = function () {
        let notnullFilterGroup = new $.jqx.filter();
        // create a filter.
        let filter_or_operator = 1;
        let filtervalue = 0;
        let filtercondition = 'NOT_NULL';
        let notnullFilterGroup1 = notnullFilterGroup.createfilter('numericfilter', filtervalue, filtercondition);
        notnullFilterGroup.addfilter(filter_or_operator, notnullFilterGroup1);
        // TODO: Здесь нужно получить итоговый столбец из описания таблицы
        dgrid.jqxGrid('addfilter', '3', notnullFilterGroup);
        dgrid.jqxGrid('applyfilters');
    }
};
let initdatagrid = function() {
    dgrid.jqxGrid(
        {
            width: '100%',
            height: '855px',
            source: dataAdapter,
            localization: localize(),
            selectionmode: 'singlecell',
            theme: theme,
            editable: edit_permission,
            editmode: 'selectedcell',
            clipboard: true,
            columnsresize: true,
            //showfilterrow: false,
            //showtoolbar: true,
            //rendertoolbar: rendertoolbar,
            filterable: false,
            columns: data_for_tables[current_table].columns,
            columngroups: data_for_tables[current_table].columngroups
        });
    dgrid.on('cellvaluechanged', function (event) {
        let rowBoundIndex = args.rowindex;
        let rowid = $('#DataGrid').jqxGrid('getrowid', rowBoundIndex);
        let colid = event.args.datafield;
        let value = args.newvalue;
        let oldvalue;
        if (typeof args.oldvalue !== 'undefined') {
            oldvalue = args.oldvalue;
        } else {
            oldvalue = null;
        }
        let readable_coordinates = getreadablecelladress(rowid, colid);

        current_edited_cell.t = current_table;
        current_edited_cell.r = rowBoundIndex;
        current_edited_cell.c = colid;
        current_edited_cell.valid = true;
        current_edited_cell.rowid = rowid;

        let data = "row=" + rowid + "&column=" + colid + "&value=" + value+ "&oldvalue=" + oldvalue;
        $.ajax({
            dataType: 'json',
            url: savevalue_url + current_table ,
            //timeout: 1000,
            data: data,
            method: 'POST',
            success: function (data, status, xhr) {
                if (data.error == 401) {
                    raiseError("Данные не сохранены. Пользователь не авторизован!");
                }
                else if (data.error == 1001) {
                    raiseError("Данные не сохранены. Отсутствуют права на изменение данных в этом документе");

                }
                else {
                    if (data.cell_affected) {
                        timestamp = new Date();
                        log_str = $("#log").html();
                        if (log_str == "Изменений не было") {
                            log_str = "";
                        }
                        $("#log").html(log_str + timestamp.toLocaleString() + " Изменена ячейка т ." + data_for_tables[current_table].tablecode +", с."+ readable_coordinates.row
                            + ", г." + readable_coordinates.column + ". (" + oldvalue +
                            " >> " + value + ").</br>");
                        editedCells.push({ t: current_table, r: rowBoundIndex, c: colid});
                        if (protocol_control_created) {
                            $(".inactual-protocol").show();
                            //$("#protocolcomment").html();
                        }
                    }
                }
            },
            error: function (xhr, status, errorThrown) {
                raiseError("Ошибка сохранения данных на сервере. " + xhr.status + ' (' + xhr.statusText + ') - ' + status + ". Обратитесь к администратору.");
            }
        });
    });

    dgrid.on('cellselect', function (event)
    {
        let cell_protocol_panel = $("#cellprotocol");
        let header;
        let args = event.args;
        let column_id = args.datafield;
        let rowindex = event.args.rowindex;
        let row_id = dgrid.jqxGrid('getrowid', rowindex);
        let row_code = dgrid.jqxGrid('getcellvaluebyid', row_id, current_row_number_datafield);
        let colindex = dgrid.jqxGrid('getcolumnproperty', column_id, 'text');
        let analitic_header = "<b>Строка " + row_code + ", Графа " + colindex +  ": </b><br/>";
        cell_protocol_panel.html('');
        if (current_protocol_source.length === 0) {
            cell_protocol_panel.html("<div class='alert alert-danger'><p>Протокол контроля формы не найден. Выполните контроль формы или контроль текущей таблицы</p></div>");
        } else if (typeof current_protocol_source[current_table_code] === 'undefined') {
            cell_protocol_panel.html("<div class='alert alert-danger'><p>Протокол контроля текущей таблицы не найден. Выполните контроль формы или контроль текущей таблицы</p></div>");
        } else {
            let cellprotocol = selectedcell_protocol(current_protocol_source, current_table, current_table_code, column_id, row_id);
            let count_of_rules  = cellprotocol.length > 0 ? cellprotocol.length : " не определены ";
            if ( cellprotocol.length > 0) {
                header = $("<div class='alert alert-info'><p>Количество заданых правил контроля для данной ячейки - " + count_of_rules + " </p></div>");
            } else {
                header = $("<div class='alert alert-warning'><p>Нет заданых правил контроля для данной ячейки</p></div>");
            }
            cell_protocol_panel.append(header);
            for (i = 0; i < count_of_rules ; i++) {
                cell_protocol_panel.append("<strong>Правило контроля: </strong><span>" + cellprotocol[i].rule.formula + "</span>");
                let row;
                let result = cellprotocol[i].result;
                let rule = cellprotocol[i].rule;
                switch (cellprotocol[i].rule.function_id) {
                    case formlabels.compare :
                        row = renderCompareControl(result, rule.boolean_sign, rule.iteration_mode, rule.level);
                        break;
                    case formlabels.dependency :
                        row = renderDependencyControl(result, rule.iteration_mode, rule.level);
                        break;
                    case formlabels.interannual :
                        row = renderInterannualControl(result, rule.level);
                        break;
                    case formlabels.iadiapazon :
                        row = renderInDiapazonControl(result, rule.level);
                        break;
                    case formlabels.multiplicity :
                        row = renderFoldControl(result, rule.level);
                        break;
                }
                cell_protocol_panel.append(row);
                if (cellprotocol[i].rule.comment !== '') {
                    cell_protocol_panel.append("<div style='margin-bottom: 5px'><strong>^ </strong><small>" + cellprotocol[i].rule.comment + "</small></div>");
                }
            }
        }

        if (doc_type === '2') {
            let returned = fetchcelllayer(row_id, column_id);
            $("#CellAnalysisTable").html(analitic_header).append(returned.layers);
            //$("#CellAnalysisTable").append(returned.layers);
            $("#CellPeriodsTable").html(analitic_header).append(returned.periods);
            //$("#CellPeriodsTable").append(returned.periods);
        }
    });
};
// Панель инструментов для редактируемой таблицы


let inittoolbarbuttons = function () {
    tdropdown.jqxDropDownButton({width: 120, height:20, theme: theme});
    tdropdown.jqxDropDownButton('setContent', '<div style="margin-top: 3px">Таблицы</div>');
    let oldVal = "";
    filterinput.on('keydown', function (event) {
        if (filterinput.val().length >= 2) {
            if (this.timer) {
                clearTimeout(this.timer);
            }
            if (oldVal !== filterinput.val()) {
                this.timer = setTimeout(function () {
                    row_name_filter(filterinput.val());
                }, 500);
                oldVal = filterinput.val();
            }
        }else {
            dgrid.jqxGrid('removefilter', '1');
        }
    });
    clearfilter.click(function () { dgrid.jqxGrid('clearfilters'); filterinput.val(''); });
    if (!there_is_calculated) {
        calculate.attr('disabled', true );
    }
    calculate.click(fillCalculatedFields);
    fullscreen.click(function() {
        dgrid.fullscreen();
    });
    tcheck.click( function() {
        tabledatacheck(current_table, 'inform');
        splitter.jqxSplitter('expand');
    });
    iptcheck.click( function() {
        tabledatacheck(current_table, 'interperiod');
        splitter.jqxSplitter('expand');
    });
};

/*let firefullscreenevent = function() {
    $(document).bind('fscreenchange', function(e, state, elem) {
        let fsel1 =  $('#togglefullscreen');
        let fsel2 =  $('#togglecontrolscreen');
        let fsel3 =  $('#toggle_formcontrolscreen');
        if ($.fullscreen.isFullScreen()) {
            fsel1.jqxToggleButton('check');
            fsel2.jqxToggleButton('check');
            fsel3.jqxToggleButton('check');
        } else {
            fsel1.jqxToggleButton('unCheck');
            fsel2.jqxToggleButton('unCheck');
            fsel3.jqxToggleButton('unCheck');
        }
    });
};*/

// проверяем ли находится ли данная ячейка в списке запрещенных к редактированию ячеек
let cellbeginedit = function (row, datafield, columntype, value) {
    let rowid = dgrid.jqxGrid('getrowid', row);
    let necell_count = not_editable_cells.length;
    for (let i = 0; i < necell_count; i++) {
        if (not_editable_cells[i].t == current_table &&  not_editable_cells[i].r == rowid && not_editable_cells[i].c == datafield ) {
            return false;
        }
    }
};
let defaultEditor = function (row, cellvalue, editor, celltext, pressedChar) {
    editor.jqxNumberInput({ decimalDigits: 0, digits: 12 });
};
let initDecimal2Editor = function (row, cellvalue, editor, celltext, pressedChar) {
    editor.jqxNumberInput({ decimalDigits: 2, digits: 12, decimalSeparator: ',' });
};
let initDecimal3Editor = function (row, cellvalue, editor, celltext, pressedChar) {
    editor.jqxNumberInput({ decimalDigits: 3, digits: 12, decimalSeparator: ',' });
};
let cellsrenderer = function (row, column, value, defaulthtml, columnproperties) {
    if (!value) { return defaulthtml  }
    let formated = $(defaulthtml).html(localizednumber.format(value));
    return formated[0].outerHTML;
};
let cellclass = function (row, columnfield, value, rowdata) {
    let invalid_cell = '';
    let alerted_cell = '';
    let class_by_edited_row = '';
    let not_editable = '';
    for (let i = 0; i < not_editable_cells.length; i++) {
        if (not_editable_cells[i].t === current_table && not_editable_cells[i].r === rowdata.id && not_editable_cells[i].c === columnfield) {
            not_editable = 'jqx-grid-cell-pinned jqx-grid-cell-pinned-bootstrap';
        }
    }
    if (marking_mode === 'control') {
        $.each(invalidCells[current_table], function(key, value) {
            if (value.r == rowdata.id && value.c == columnfield) {
                invalid_cell = 'invalid';
            }
        });
        $.each(alertedCells[current_table], function(key, value) {
            if (value.r === rowdata.id && value.c === columnfield) {
                alerted_cell = 'alerted';
            }
        });
        for (let i = 0; i < editedCells.length; i++) {
            if (editedCells[i].t == current_table && editedCells[i].r == row && editedCells[i].c == columnfield ) {
                class_by_edited_row = "editedRow";
                invalid_cell = '';
                alerted_cell = '';
            }
        }
        /*        if (current_edited_cell.t == current_table && current_edited_cell.r == row && current_edited_cell.c == columnfield) {
                    if (!current_edited_cell.valid) {
                        invalid_cell = 'invalid';
                    }
                    else {
                        invalid_cell = '';
                    }
                }*/
        return  alerted_cell + ' ' + invalid_cell +' ' + class_by_edited_row + ' ' + not_editable;
    }
    else if (marking_mode === 'compareperiods') {
        let class_compare = '';
        for (let i = 0; i < comparedCells.length; i++) {
            if (comparedCells[i].t == current_table && comparedCells[i].r == rowdata.id && comparedCells[i].c == columnfield ) {
                class_compare = comparedCells[i].degree;
            }
        }
        return class_compare + ' ' + not_editable;
    }
};
let validation = function(cell, value) {
    if (value < 0) {
        return { result: false, message: 'Допускаются только положительные значения' };
    }
    return true;
};
// Пояснялки названий столбцов
let tooltiprenderer = function (element) {
    $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
};

// Инициализация вкладки протокола контроля текущей таблицы
let initchecktabletab = function() {
    //$("#checktable").jqxButton({ theme: theme, disabled: control_disabled });
    //$("#checktable").click( function() { checktable(current_table) });
    //$("#datacheck").jqxButton({ theme: theme, disabled: control_disabled });
    $("#datacheck").click( function() { tabledatacheck(current_table) });
    //$("#compareprevperiod").jqxButton({ theme: theme });
    //$("#compareprevperiod").click(compare_with_prev);

    /*    if (current_user_role == 3 || current_user_role == 4 ) {
            var tk = $("<input id='medstatcontrol' style='float: left' type='button' value='Контроль таблицы (Старый формат)'/>");
            $("#ProtocolToolbar").prepend(tk);
            tk.jqxButton({ theme: theme });
            tk.click(function () {
                checktable(current_table);
            });
        }*/

};
let fillCalculatedFields = function () {
    $.get(calculatedcells_url + current_table, function( data ) {
        if (typeof data.errors !== 'undefined') {
            for (i = 0; data.errors.length > i; i++ ) {
                raiseError(data.errors[i]);
            }
        }
        dgrid.jqxGrid('updatebounddata');
        raiseInfo("Заполнение рассчитываемых ячеек выполнено");
        /*        if (typeof data.calculations !== 'undefined') {
                    for (i = 0; data.calculations.length > i; i++ ) {
                        let row_id = data.calculations[i].r;
                        let datafield = data.calculations[i].c;
                        let v = data.calculations[i].v;
                        dgrid.jqxGrid('setcellvaluebyid', row_id, datafield, v);
                    }
                }*/
    });
};