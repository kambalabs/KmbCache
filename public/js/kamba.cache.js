$(window).load(function () {
    var match = document.URL.match(/(\/env\/[0-9]+)/);
    var prefixUri = match ? match[1] : '';

    var refreshExpiredCache = function () {
        $.getJSON(prefixUri + '/cache/refresh-expired', function (data) {
            if (data.refresh) {
                $.gritter.add({
                    title: data.title,
                    text: data.message,
                    class_name: "gritter-light"
                });
            }
            setTimeout(refreshExpiredCache, 5000);
        });
    };

    setTimeout(refreshExpiredCache, 5000);

    $('#clear-cache').click(function () {
        $.getJSON(prefixUri + '/cache/clear');
    });
});
