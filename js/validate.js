var Validate = {
    formId: '',
    caller: null,
    callback: function() {},
    validRequired: {},
    init: function(options) {
        jQuery.extend(this, options);
        this._loadValidations();
        this._listen();
    },
    _loadValidations: function () {
        var self = this;
        this.validRequired = {};

        // Set all required fields to false to begin with
        jQuery(this.formId + ' *').filter(':input').each(function () {
            self._setValid(jQuery(this), false);
        });
    },
    _setValid: function ($this, truth) {
        var value = $this.val();
        var fieldName = $this.attr('name');
        if ($this.hasClass('required')) {
            this.validRequired[fieldName] = truth;
        }

        return truth;
    },
    _toggleValid: function ($this, isValid) {
        $this.toggleClass('valid', isValid);
    },
    _listen: function() {
        var self = this;

        jQuery(this.formId + ' *').filter(':input').off()
            .on('keyup change', function (evt) {
            var $this = jQuery(this);
            var isValid = true;

            var valid = self._setValid(jQuery(this), ($this.val() !== ''));
            self._toggleValid($this, valid);

            for  (var field in self.validRequired) {
                if (self.validRequired.hasOwnProperty(field)) {
                    if (!self.validRequired[field]) {
                        isValid = false;
                    }
                }
            }

            self.callback(self.caller, isValid);
        });
    }

};