let addIndex = function() {
    $("#addindexes").click(function () {
        let indexes = $(".index");
        let numberOfIndexes = indexes.length;
        let newindex = indexes.last().clone();
        newindex.find('label').first().text("Показатель " + (numberOfIndexes + 1) + " (наименование)");
        newindex.find('input').first().attr("id", "title" + (numberOfIndexes + 1));
        newindex.find('textarea').first().attr("id", "value" + (numberOfIndexes + 1));
        //newindex.find('label').last().text("Новое значение");
        newindex.appendTo(".indexes");
        removeIndex();
    });
};

let removeIndex = function () {
    $(".rmindex").click(function () {
        //console.log($(this.parent()));
        $(this.closest('div.index')).remove();
    });
};