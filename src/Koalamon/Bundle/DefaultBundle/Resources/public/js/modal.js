function ModalHandler() {
}

ModalHandler.prototype.showConfirmDialog = function (message, callback) {
    $('#confirm').modal({
        position: ["20%"],
        overlayId: 'confirm-overlay',
        containerId: 'confirm-container',
        onShow: function (dialog) {
            var modal = this;

            $('.message', dialog.data[0]).append(message);

            // if the user clicks "yes"
            $('.yes', dialog.data[0]).click(function () {
                // call the callback
                if ($.isFunction(callback)) {
                    callback.apply();
                }
                // close the dialog
                modal.close(); // or $.modal.close();
            });
        }
    });
};

ModalHandler.prototype.initConfirm = function (selector) {

    var elements = $(selector);

    var self = this;
    var messageQuestion = "Are you sure?";

    elements.each(function () {

            var element = this;
            var message = messageQuestion;
            if ($(this).attr('data-message') != undefined) {
                message = $(this).attr('data-message');
            }

            if ($(element).prop('nodeName').toUpperCase() == 'FORM') {
                $(element).submit(function () {
                    self.showConfirmDialog(message, function () {
                        element.submit();
                    });
                    return false;
                });
            } else if ($(this).prop('nodeName').toUpperCase() == 'A') {
                $(element).click(function () {
                    var href = $(element).attr('href');
                    self.showConfirmDialog(message, function () {
                        location.href = href;
                    });
                    return false;
                });
            } else {
                $(element).html('<span class="confirm-dialog-span">' + $(element).html() + '</span>');
            }
        }
    );

    $(".confirm-dialog-span").on('click', function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var span = this;

        var message = messageQuestion;
        if ($(span.parentNode).attr('data-message') != undefined) {
            message = $(span.parentNode).attr('data-message');
        }

        self.showConfirmDialog(message, function () {
            // shouldn't it be childNode?
            $(span.parentNode).click();
        });
        return false;
    });
};
