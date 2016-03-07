function IntegrationForm() {

}

IntegrationForm.prototype.initMultiInputs = function (multiInputs) {
    multiInputs.each(function () {
        initOptions($(this));
        $(this).html($(this).html() + '<button onclick="addOption(this);return false;">add option</button>');
    });
};