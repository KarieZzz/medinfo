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
    var patternsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'name' }
        ],
        id: 'id',
        localdata: patterns
    };
    patternDataAdapter = new $.jqx.dataAdapter(patternsource);
    var periodsource =
    {
        datatype: "json",
        datafields: [
            { name: 'id' },
            { name: 'name' }
        ],
        id: 'id',
        localdata: periods
    };
    periodsDataAdapter = new $.jqx.dataAdapter(periodsource);

};

initpatternlist = function() {
    ilist.jqxGrid(
        {
            width: '98%',
            height: '98%',
            theme: theme,
            localization: localize(),
            source: patternDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '40px' },
                { text: 'Имя', datafield: 'name' , width: '897px'}
            ]
        });
    ilist.on('rowselect', function (event) {
        var row = event.args.row;
        var indexes_url = url + row.id + "/fetchindexes";
        $.getJSON( indexes_url, function( data) {
            var container = $('<div></div>');
            var i = 1;
            var html = '';
            $.each(data, function(key, index) {
                html += i + ". <span class='text-primary' >" + index.title + "</span> : <span class='text-info'>" + index.value + "</span><br>";
                i++;
                //console.log(index.title);
            });
            container.html(html);
            $('#indexes').html(container);
        });

    });
};

initformcontrols = function() {
    plist.jqxDropDownList({
        theme: theme,
        source: periodsDataAdapter,
        displayMember: "name",
        valueMember: "id",
        placeHolder: "Выберите период:",
        //selectedIndex: 2,
        width: 250,
        height: 32
    });
    plist.on('select', function (event) {
        var args = event.args;
        current_period = args.item.value;
        $("#periodSelected").html('<div class="text-bold text-info" style="margin-left: -80px; margin-top: 10px">Выбран период: "'+ args.item.label +'"</div>');
    });
};

initformactions = function() {
    $("#edit").click(function () {
        var row = ilist.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = ilist.jqxGrid('getrowid', row);
        var url = '/reports/patterns/' + rowid + '/edit';
        //console.log(url);
        //window.open(url);
        location.replace(url);
    });
    $("#perform").click(function () {
        var row = ilist.jqxGrid('getselectedrowindex');
        if (row == -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        var rowid = ilist.jqxGrid('getrowid', row);
        var route = url + rowid + '/' + current_period + '/' + sortorder + '/perform';
        //console.log(url);
        //window.open(url);
        location.replace(route);
    });
    $("#fordigest").on('click', function() {
        sortorder = 1;
    });
    $("#byname").on('click', function() {
        sortorder = 2;
    });
    $("#bycode").on('click', function() {
        sortorder = 2;
    });
};