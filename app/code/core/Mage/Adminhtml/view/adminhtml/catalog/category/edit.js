(function($){
    $.widget("mage.categoryForm", $.mage.form, {
        options: {
            categoryIdSelector : 'input[name="general[id]"]',
            categoryPathSelector : 'input[name="general[path]"]'
        },
        /**
         * Form creation
         * @protected
         */
        _create: function() {
            this._super('_create');
            $('body').on('categoryMove.tree', $.proxy(this.refreshPath, this));
        },
        /**
         * Sending ajax to server to refresh field 'general[path]'
         * @protected
         */
        refreshPath: function() {
            if (!this.element.find(this.options.categoryIdSelector).prop('value')) {
                return false;
            }
            new Ajax.Request(
                this.options.refreshUrl,
                {
                    method:     'POST',
                    evalScripts: true,
                    onSuccess: this._refreshPathSuccess.bind(this)
                }
            );
        },
        /**
         * Refresh field 'general[path]' on ajax success
         * @param {Object} The XMLHttpRequest object returned by ajax
         * @protected
         */
        _refreshPathSuccess: function(transport) {
            if (transport.responseText.isJSON()) {
                var response = transport.responseText.evalJSON();
                if (response.error) {
                    alert(response.message);
                } else {
                    if (this.element.find(this.options.categoryIdSelector).prop('value') === response.id) {
                        this.element.find(this.options.categoryPathSelector)
                            .prop('value', response.path);
                    }
                }
            }
        }
    });
})(jQuery);

/**
 * Create/edit some category
 */
function categorySubmit(url, useAjax) {
    var activeTab = $('active_tab_id');
    if (activeTab) {
        if (activeTab.tabsJsObject && activeTab.tabsJsObject.activeTab) {
            activeTab.value = activeTab.tabsJsObject.activeTab.id;
        }
    }

    var params = {};
    var fields = $('category_edit_form').getElementsBySelector('input', 'select');
    for(var i=0;i<fields.length;i++){
        if (!fields[i].name) {
            continue;
        }
        params[fields[i].name] = fields[i].getValue();
    }

    // Get info about what we're submitting - to properly update tree nodes
    var categoryId = params['general[id]'] ? params['general[id]'] : 0;
    var isCreating = categoryId == 0; // Separate variable is needed because '0' in javascript converts to TRUE
    var path = params['general[path]'].split('/');
    var parentId = path.pop();
    if (parentId == categoryId) { // Maybe path includes category id itself
        parentId = path.pop();
    }

    // Make operations with category tree
    if (isCreating) {
        /* Some specific tasks for creating category */
        if (!tree.currentNodeId) {
            // First submit of form - select some node to be current
            tree.currentNodeId = parentId;
        }
        tree.addNodeTo = parentId;
    } else {
        /* Some specific tasks for editing category */
        // Maybe change category enabled/disabled style
        if (tree && tree.storeId==0) {
            var currentNode = tree.getNodeById(categoryId);

            if (currentNode) {
                if (parseInt(params['general[is_active]'])) {
                    var oldClass = 'no-active-category';
                    var newClass = 'active-category';
                } else {
                    var oldClass = 'active-category';
                    var newClass = 'no-active-category';
                }

                Element.removeClassName(currentNode.ui.wrap.firstChild, oldClass);
                Element.addClassName(currentNode.ui.wrap.firstChild, newClass);
            }
        }
    }

    // Submit form
    jQuery('#category_edit_form').trigger('submit');
}
