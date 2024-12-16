$(document).ready(function () {
    $(".ibCopyThis").each(function (index, element) {
        var tmpID = 'ibCC_' + index;
        $(element).attr('id', tmpID);
        $(element).after('<a class="ibCopyText" data-tooltip data-clipboard-target="#' + tmpID + '"><i title="Text kopieren" class="far fa-copy"></i></a>');
    })
    new ClipboardJS('.ibCopyText');
    $(".ibCopyText").on('click', function () {

        var tmpClip = $(this);
        tmpClip.addClass('showCopied');
        setTimeout(function () {
            tmpClip.removeClass("showCopied");
        }, 800);
    })
}) 