/**
 * {license_notice}
 *
 * @category    mage product view
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
(function ($) {
    $(document).ready(function () {

        var topCartInit = {
            // Default values
            intervalDuration: 4000,
            // Filled in initialization event
            container: null,
            closeButton: null
        };

        $.mage.event.trigger('mage.checkout.initialize', topCartInit);

        topCartInit.container = $(topCartInit.container);
        topCartInit.closeButton = $(topCartInit.closeButton);

        var topCartSettings = {
            element: topCartInit.container.parent(),
            elementHeader: topCartInit.container.prev(),
            interval: null
        };

        topCartInit.closeButton.on('click', function () {
            topCartInit.container.slideUp('slow', function () {
                clearTimeout(topCartInit.interval);
            });
        });

        topCartSettings.element.on('mouseleave',function () {
            topCartInit.interval = setTimeout(function () {
                topCartInit.closeButton.trigger('click');
            }, topCartInit.intervalDuration);
        }).on('mouseenter', function () {
            clearTimeout(topCartSettings.interval);
        });

        topCartSettings.elementHeader.on('click', function () {
            $(topCartInit.container).slideToggle('slow');
        });

    });
})(jQuery);