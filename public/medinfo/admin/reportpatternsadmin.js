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
    let patternsource =
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
    let periodsource =
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
            height: '93%',
            theme: theme,
            localization: localize(),
            source: patternDataAdapter,
            columnsresize: true,
            showfilterrow: true,
            filterable: true,
            columns: [
                { text: 'Id', datafield: 'id', width: '40px' },
                { text: 'Имя', datafield: 'name' , width: '885px'}
            ]
        });
    ilist.on('rowselect', function (event) {
        let row = event.args.row;
        let indexes_url = url + row.id + "/fetchindexes";
        $.getJSON( indexes_url, function( data) {
            let container = $('<div></div>');
            let i = 1;
            let html = '';
            $.each(data, function(key, index) {
                html += i + ". <span class='text-primary' >" + index.title + "</span> : <span class='text-info'>" + index.value + "</span><br>";
                i++;
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
        let args = event.args;
        current_period = args.item.value;
        $("#periodSelected").html('<div class="text-bold text-info" style="margin-left: -80px; margin-top: 10px">Выбран период: "'+ args.item.label +'"</div>');
    });
};

initformactions = function() {
    $("#edit").click(function () {
        let row = ilist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        let rowid = ilist.jqxGrid('getrowid', row);
        let url = '/reports/patterns/' + rowid + '/edit';
        //console.log(url);
        //window.open(url);
        location.replace(url);
    });
    $("#perform").click(function () {
        let row = ilist.jqxGrid('getselectedrowindex');
        if (row === -1) {
            raiseError("Выберите запись для изменения/сохранения данных");
            return false;
        }
        $("#progress").html(0);
        let progres_timer = setInterval(function(){
            $.get('/reports/patterns/progress', function(data) {
                $("#progress").html(data + "%").css('width', data + "%");
            });
        }, 3000);
        let rowid = ilist.jqxGrid('getrowid', row);
        let route = url + rowid + '/' + current_period + '/' + sortorder + '/perform';
        //console.log(url);
        //let report_window = window.open(route, );
        //report_window.opener.focus();
        //report_window.blur();
        //$( report_window ).load(function() {
          //  clearInterval(progres_timer);
        //});
        location.assign(route);
        //$( window ).load(function() {
          //  clearInterval(progres_timer);
        //});

    });
    $("#fordigest").on('click', function() {
        sortorder = 2;
    });
    $("#byname").on('click', function() {
        sortorder = 1;
    });
/*    $("#bycode").on('click', function() {
        sortorder = 2;
    });*/
};