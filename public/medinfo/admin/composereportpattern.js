initActions = function() {
    $("#addindexes").click(function () {
        var numberOfIndexes = $(".index").length;
        var newindex = $(".index").last().clone();
        newindex.find('label').first().text("Показатель " + (numberOfIndexes + 1));
        newindex.find('input').first().attr("id", "title" + (numberOfIndexes + 1));
        newindex.find('textarea').first().attr("id", "value" + (numberOfIndexes + 1));
        //newindex.find('label').last().text("Новое значение");
        newindex.appendTo(".indexes");

    });
};