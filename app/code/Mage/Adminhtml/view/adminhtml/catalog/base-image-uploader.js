/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*global alert:true*/
(function ($) {
    $.widget('mage.baseImage', {
        /**
         * Button creation
         * @protected
         */
        _create: function() {
            var $container = this.element,
                $template = this.element.find('.image-template'),
                $dropPlaceholder = this.element.find('.image-placeholder'),
                $galleryContainer = $('#media_gallery_content'),
                mainClass = 'base-image',
                maximumImageCount = 5;

            var findElement = function(data) {
                return $container.find('.image:not(.image-placeholder)').filter(function() {
                    return $(this).data('image').file == data.file;
                }).first();
            };
            var updateVisibility = function() {
                var elementsList = $container.find('.image:not(.removed-item)');
                elementsList.each(function(index) {
                    $(this)[index < maximumImageCount ? 'show' : 'hide']();
                });
                $dropPlaceholder[elementsList.length > maximumImageCount ? 'hide' : 'show']();
            };

            $galleryContainer.on('setImageType', function(event, data) {
                if (data.type == 'image') {
                    $container.find('.' + mainClass).removeClass(mainClass);
                    if (data.imageData) {
                        findElement(data.imageData).addClass(mainClass);
                    }
                }
            });

            $galleryContainer.on('addItem', function(event, data) {
                var $element = $template.tmpl(data);
                $element.data('image', data).insertBefore($dropPlaceholder);
                updateVisibility();
            });

            $galleryContainer.on('removeItem', function(event, image) {
                findElement(image).addClass('removed-item').hide();
                updateVisibility();
            });

            $galleryContainer.on('moveElement', function(event, data) {
                var $element = findElement(data.imageData);
                if (data.position === 0) {
                    $container.prepend($element);
                } else {
                    var $after = $container.find('.image').eq(data.position);
                    if (!$element.is($after)) {
                        $element.insertAfter($after);
                    }
                }
                updateVisibility();
            });

            $container.on('click', '[data-role="make-main-button"]', function(event) {
                event.preventDefault();
                var data = $(event.target).closest('.image').data('image');
                $galleryContainer.productGallery('setMain', data);
            });

            $container.on('click', '[data-role="delete-button"]', function(event) {
                event.preventDefault();
                $galleryContainer.trigger('removeItem', $(event.target).closest('.image').data('image'));
            });

            $container.sortable({
                axis: 'x',
                items: '.image:not(.image-placeholder)',
                distance: 8,
                tolerance: 'pointer',
                stop: function(event, data) {
                    $galleryContainer.trigger('setPosition', {
                        imageData: data.item.data('image'),
                        position: $container.find('.image').index(data.item)
                    });
                    $galleryContainer.trigger('resort');
                }
            }).disableSelection();

            this.element.find('input[type="file"]').fileupload({
                dataType: 'json',
                dropZone: $dropPlaceholder,
                acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                maxFileSize: this.element.data('maxFileSize'),
                done: function(event, data) {
                    $dropPlaceholder.find('.progress-bar').text('').removeClass('in-progress');
                    if (!data.result) {
                        return;
                    }
                    if (!data.result.error) {
                        $galleryContainer.trigger('addItem', data.result);
                    } else {
                        alert($.mage.__('File extension not known or unsupported type.'));
                    }
                },
                add: function(event, data) {
                    $(this).fileupload('process', data).done(function() {
                        data.submit();
                    });
                },
                progress: function(e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $dropPlaceholder.find('.progress-bar').addClass('in-progress').text(progress + '%');
                }
            });
        }
    });
})(jQuery);
