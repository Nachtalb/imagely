$(function () {
    $(document).on('load keydown', '[contenteditable]', function () {
        var maximumLength = $(this).attr("data-maxlength");
        remain = maximumLength - parseInt($(this).text().length);
        $(this).parent().find('.remaining').text(remain);
    });
});