/**
 * {license_notice}
 *
 * @category    mage side bar
 * @package     mage
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global confirm:true*/
(function ($) {
    $(document).ready(function () {
        var checkout = {
            // Filled in initialization event
            cartSelector: null,
            isRecursive: undefined,
            url: null,
            button: null,
            confirmMessage: null,
            closeList: null
        };

        $.mage.event.trigger('mage.checkout.initialize', checkout);
        $.mage.decorator.list(checkout.cartSelector, checkout.isRecursive);

        $(checkout.button).on('click', function () {
            $(location).attr('href', checkout.url);
        });
        $(checkout.closeList).on('click', function () {
            return confirm(checkout.confirmMessage);
        });
    });
})(jQuery);
