$(function () {
    $('img').lazyload();

    $(document).on('load keydown', '[contenteditable]', function () {
        var maximumLength = $(this).attr("data-maxlength");
        remain = maximumLength - parseInt($(this).text().length);
        $(this).parent().find('.remaining').text(remain);
    });

    $(document).on('click', 'a.delete', function (e) {
        if (!confirm($('.deleteText').text()))
            e.preventDefault();
    });

    $('#image-gallery').magnificPopup({
        delegate: 'a.gallery-image-link',
        type: 'image',
        closeOnContentClick: false,
        closeBtnInside: false,
        image: {
            verticalFit: true
        },
        gallery: {
            enabled: true
        }
    });

});