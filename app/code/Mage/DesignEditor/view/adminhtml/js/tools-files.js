/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */
MediabrowserUtility = {
    getMaxZIndex: function() {
        var max = 0, i;
        var cn = document.body.childNodes;
        for (i = 0; i < cn.length; i++) {
            var el = cn[i];
            var zIndex = el.nodeType == 1 ? parseInt(el.style.zIndex, 10) || 0 : 0;
            if (zIndex < 10000) {
                max = Math.max(max, zIndex);
            }
        }
        return max + 10;
    },
    openDialog: function(url, width, height, title, options) {
        var windowId = 'modal_dialog_message';
        jQuery('body').append('<div class="popup-window magento_message" id="' + windowId + '"></div>');
        jQuery('#' + windowId).dialog({
            autoOpen:   false,
            title:      title || 'Insert File...',
            modal:      true,
            resizable:  false,
            width:      width || 950,
            zIndex:     this.getMaxZIndex(),
            close:      function(event, ui) {
                jQuery(this).dialog('destroy');
                jQuery('#' + windowId).remove();
            }
        });

        jQuery('#' + windowId).dialog('open');

        new Ajax.Updater('modal_dialog_message', url, {evalScripts: true});
    },
    closeDialog: function(window) {
        if (!window) {
            window = this.dialogWindow;
        }
        if (window) {
            // IE fix - hidden form select fields after closing dialog
            WindowUtilities._showSelect();
            jQuery('#modal_dialog_message').dialog('close');
        }
    }
};

Mediabrowser = Class.create();
Mediabrowser.prototype = {
    targetElementId: null,
    contentsUrl: null,
    onInsertUrl: null,
    newFolderUrl: null,
    deleteFolderUrl: null,
    deleteFilesUrl: null,
    headerText: null,
    tree: null,
    currentNode: null,
    storeId: null,
    showBreadcrumbs: null,
    initialize: function (setup) {
        this.newFolderPrompt = setup.newFolderPrompt;
        this.deleteFolderConfirmationMessage = setup.deleteFolderConfirmationMessage;
        this.deleteFileConfirmationMessage = setup.deleteFileConfirmationMessage;
        this.targetElementId = setup.targetElementId;
        this.contentsUrl = setup.contentsUrl;
        this.onInsertUrl = setup.onInsertUrl;
        this.newFolderUrl = setup.newFolderUrl;
        this.deleteFolderUrl = setup.deleteFolderUrl;
        this.deleteFilesUrl = setup.deleteFilesUrl;
        this.headerText = setup.headerText;
        this.showBreadcrumbs = setup.showBreadcrumbs;
    },
    setTree: function (tree) {
        this.tree = tree;
        this.currentNode = tree.getRootNode();
    },

    getTree: function (tree) {
        return this.tree;
    },

    selectFolder: function (node, event) {
        this.currentNode = node;
        this.activateBlock('contents');

        // Activate/deactivate trash can icon
        var dataFolderDelete = '[data-folder="delete"]';
        if (jQuery(dataFolderDelete) != undefined) {
            var deleteFolderTitle = '';
            if(node.id == 'root') {
                jQuery(dataFolderDelete).removeClass('activate');
            }
            else {
                deleteFolderTitle = jQuery.mage.__('Delete') + ' ' + node.text + ' ' + jQuery.mage.__('Folder');
                jQuery(dataFolderDelete).addClass('activate')
                    .on('click', function() {
                        MediabrowserInstance.deleteFolder();
                    });
            }
            jQuery(dataFolderDelete).on('mouseover', function() {
                jQuery(this).attr('title', deleteFolderTitle);
            });
        }

        this.updateHeader(this.currentNode);
        if (this.showBreadcrumbs)
            this.drawBreadcrumbs(this.currentNode);

        jQuery('#contents').loadingPopup({ timeout: false });
        new Ajax.Request(this.contentsUrl, {
            parameters: {node: this.currentNode.id},
            evalJS: true,
            onSuccess: function(transport) {
                try {
                    this.currentNode.select();
                    this.onAjaxSuccess(transport);
                    jQuery('#contents').trigger('hideLoadingPopup');
                    if ($('contents') != undefined) {
                        $('contents').update(transport.responseText);
                        $$('div.filecnt').each(function(s) {
                            // Bind to the Insert File button.
                            var dataInsertFile = "[data-insert-file='" + s.id + "']";
                            jQuery(dataInsertFile).on('click', function() {
                                MediabrowserInstance.insert(s.id);
                            });
                            // Bind to the trash can.
                            var dataDeleteFile = "[data-delete-file='" + s.id + "']";
                            jQuery(dataDeleteFile).on('click', function() {
                                MediabrowserInstance.deleteFiles(s.id);
                            });
                        }.bind(this));
                    }
                } catch(e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    selectFolderById: function (nodeId) {
        var node = this.tree.getNodeById(nodeId);
        if (node.id) {
            this.selectFolder(node);
        }
    },

    handleUploadComplete: function(files) {
        $$('div[class*="file-row complete"]').each(function(e) {
            $(e.id).remove();
        });
        this.selectFolder(this.currentNode);
    },

    insert: function(id) {
        var targetEl = this.getTargetElement();
        if (! targetEl) {
            alert("Target element not found for content update");
            jQuery('#modal_dialog_message').dialog('close');
            return;
        }

        var params = {filename:id, node:this.currentNode.id, store:this.storeId};

        if (targetEl.tagName.toLowerCase() == 'textarea') {
            params.as_is = 1;
        }

        new Ajax.Request(this.onInsertUrl, {
            parameters: params,
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    if (this.getMediaBrowserOpener()) {
                        self.blur();
                    }
                    jQuery('#modal_dialog_message').dialog('close');
                    if (targetEl.tagName.toLowerCase() == 'input') {
                        targetEl.value = transport.responseText;
                    } else {
                        updateElementAtCursor(targetEl, transport.responseText);
                        if (varienGlobalEvents) {
                            varienGlobalEvents.fireEvent('tinymceChange');
                        }
                    }
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    /**
     * Find document target element in next order:
     *  in active file browser opener:
     *  - input field with ID: "src" in opener window
     *  - input field with ID: "href" in opener window
     *  in document:
     *  - element with target ID
     *
     * return HTMLelement | null
     */
    getTargetElement: function() {
        if (typeof(tinyMCE) != 'undefined' && tinyMCE.get(this.targetElementId)) {
            var opener = this.getMediaBrowserOpener();
            if ((opener)) {
                var targetElementId = tinyMceEditors.get(this.targetElementId).getMediaBrowserTargetElementId();
                return opener.document.getElementById(targetElementId);
            } else {
                return null;
            }
        } else {
            return document.getElementById(this.targetElementId);
        }
    },

    /**
     * Return opener Window object if it exists, not closed and editor is active
     *
     * return object | null
     */
    getMediaBrowserOpener: function() {
        if (typeof(tinyMCE) != 'undefined'
            && tinyMCE.get(this.targetElementId)
            && typeof(tinyMceEditors) != 'undefined'
            && ! tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener().closed) {
            return tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener();
        } else {
            return null;
        }
    },

    newFolder: function() {
        var folderName = prompt(this.newFolderPrompt);
        if (!folderName) {
            return false;
        }
        new Ajax.Request(this.newFolderUrl, {
            parameters: {name: folderName},
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON()
                        var newNode = new Ext.tree.AsyncTreeNode({
                            text: response.short_name,
                            draggable:false,
                            id:response.id,
                            expanded: true
                        });
                        var child = this.currentNode.appendChild(newNode);
                        this.tree.expandPath(child.getPath(), '', function(success, node) {
                            this.selectFolder(node);
                        }.bind(this));
                    }
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        })
    },

    deleteFolder: function() {
        if (!confirm(this.deleteFolderConfirmationMessage)) {
            return false;
        }
        new Ajax.Request(this.deleteFolderUrl, {
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    var parent = this.currentNode.parentNode;
                    parent.removeChild(this.currentNode);
                    this.selectFolder(parent);
                }
                catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        })
    },

    deleteFiles: function(value) {
        if (!confirm(this.deleteFileConfirmationMessage)) {
            return false;
        }
        var ids = [];
        ids[0] = value;
        new Ajax.Request(this.deleteFilesUrl, {
            parameters: {files: Object.toJSON(ids)},
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    this.selectFolder(this.currentNode);
                } catch(e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    drawBreadcrumbs: function(node) {
        if ($('breadcrumbs') != undefined) {
            $('breadcrumbs').remove();
        }
        if (node.id == 'root') {
            return;
        }
        var path = node.getPath().split('/');
        var breadcrumbs = '';
        for(var i = 0, length = path.length; i < length; i++) {
            if (path[i] == '') {
                continue;
            }
            var currNode = this.tree.getNodeById(path[i]);
            if (currNode.id) {
                breadcrumbs += '<li>';
                breadcrumbs += '<a href="#" onclick="MediabrowserInstance.selectFolderById(\'' + currNode.id + '\');">' + currNode.text + '</a>';
                if(i < (length - 1)) {
                    breadcrumbs += ' <span>/</span>';
                }
                breadcrumbs += '</li>';
            }
        }

        if (breadcrumbs != '') {
            breadcrumbs = '<ul class="breadcrumbs" id="breadcrumbs">' + breadcrumbs + '</ul>';
            $('content_header').insert({after: breadcrumbs});
        }
    },

    updateHeader: function(node) {
        var header = (node.id == 'root' ? this.headerText : node.text);
        if ($('content_header_text') != undefined) {
            $('content_header_text').innerHTML = header;
        }
    },

    activateBlock: function(id) {
        //$$('div [id^=contents]').each(this.hideElement);
        this.showElement(id);
    },

    hideElement: function(id) {
        if ($(id) != undefined) {
            $(id).addClassName('no-display');
            $(id).hide();
        }
    },

    showElement: function(id) {
        if ($(id) != undefined) {
            $(id).removeClassName('no-display');
            $(id).show();
        }
    },

    onAjaxSuccess: function(transport) {
        if (transport.responseText.isJSON()) {
            var response = transport.responseText.evalJSON()
            if (response.error) {
                throw response;
            } else if (response.ajaxExpired && response.ajaxRedirect) {
                setLocation(response.ajaxRedirect);
            }
        }
    }
};
