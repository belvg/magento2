/**
 *
 * @license     {}
 */
(function($) {
    $.widget("mage.form", {
        options: {
            actions: {
                saveAndContinueEdit: {
                    template: '${base}{{each(key, value) args}}${key}/${value}/{{/each}}',
                    args: {'back': 'edit'}
                }
            }
        },

        /**
         * Form creation
         * @protected
         */
        _create: function() {
            this.baseUrl = this.element.attr('action');
            $.each(this.options.actions, function(i, v) {
                $.template(i, v.template);
            });
            this._bind();
        },

        /**
         * Bind '_submit' method on 'save' and 'saveAndContinueEdit' events
         * @protected
         */
        _bind: function() {
            this.element.on('save saveAndContinueEdit', $.proxy(this._submit, this));
        },

        /**
         * Get action url for form
         * @param {string} name of action
         * @param {Object} object with parameters for action url
         * @return {string|boolean}
         */
        _getActionUrl: function(action, data) {
            var actions = this.options.actions;
            if (actions[action]) {
                data = data || {};
                return $.tmpl(action, {
                    base: this.baseUrl,
                    args: actions[action].args ? $.extend(actions[action].args, data) : data
                }).text();
            }
            return false;
        },

        /**
         * Submit the form
         * @param {Object} event object
         * @param {Object} event data object
         * @return {string|boolean}
         */
        _submit: function(e, data) {
            this.element.attr('action', this.baseUrl);
            var urlData = {};
            this.element.trigger('beforeSubmit', urlData);
            data = data ? $.extend(data, urlData) : urlData;
            var url = this._getActionUrl(e.type, data);
            if (url) {
                this.element.attr('action', url);
            }
            this.element.triggerHandler('submit');
        }
    });

    $.widget('ui.button', $.ui.button, {
        /**
         * Button creation
         * @protected
         */
        _create: function(){
            var data = this.element.data().widgetButton;
            if ($.type(data) === 'object') {
                this.element.on('click', function() {
                    $(data.related).trigger(data.event);
                });
            }
            this._super("_create");
        }
    });
})(jQuery);

