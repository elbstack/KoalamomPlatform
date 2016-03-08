function ModalHandler() {
}

ModalHandler.prototype.showConfirmDialog = function (message, callback) {
    $('#confirm').modal({
        position: ["20%",],
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
}

ModalHandler.prototype.initConfirm = function (selector) {

    elements = $(selector);

    var thisClass = this;

    elements.each(function () {

            var element = this;

            if ($(this).attr('data-message') != undefined) {
                var message = $(this).attr('data-message');
            } else {
                var message = "Are you sure?";
            }

            if ($(element).prop('nodeName') == 'FORM') {
                $(element).submit(function () {
                    thisClass.showConfirmDialog(message, function () {
                        element.submit();
                    });
                    return false;
                });
            } else if ($(this).prop('nodeName') == 'A') {
                $(element).click(function () {
                    href = $(element).attr('href');
                    thisClass.showConfirmDialog(message, function () {
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
        span = this;

        if ($(this.parentNode).attr('data-message') != undefined) {
            var message = $(this.parentNode).attr('data-message');
        } else {
            var message = "Are you sure?";
        }
        thisClass.showConfirmDialog(message, function () {
            $(span.parentNode).click();
        });
        return false;
    });
}
