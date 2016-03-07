function confirmDialog(title, message, success) {
    var confirmdialog = $('<div></div>').appendTo('body')
        .html('<div><h6>' + message + '</h6></div>')
        .dialog({
            modal: true,
            title: title,
            zIndex: 10000,
            autoOpen: false,
            width: 'auto',
            resizable: false,
            buttons: {
                Yes: function () {
                    success();
                    $(this).dialog("close");
                },
                No: function () {
                    $(this).dialog("close");
                }
            },
            close: function() {
                $(this).remove();
            }
        });

    return confirmdialog.dialog("open");
}

function ModalHandler() {

}

ModalHandler.prototype.initConfirm = function (selector) {

    elements = $(selector);

    elements.each(function () {

        console.log($(this).attr('data-message'));

        $(this).submit(function () {

            var form = this;

            confirmDialog('Confirm', 'Are you sure?', function () {
                form.submit();
            });

            return false;
        });

    });

}