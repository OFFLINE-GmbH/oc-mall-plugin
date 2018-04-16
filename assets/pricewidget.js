/*
 * Multi currency price widget
 * 
 * Data attributes:
 * - data-control="mall-price" - enables the plugin on an element
 * - data-default-currency="CHF" - default currency code
 * - data-placeholder-field="#placeholderField" - an element that contains the placeholder value
 *
 * JavaScript API:
 * $('a#someElement').mallPrice({ option: 'value' })
 *
 * Dependences:
 * - Nil
 */

+function ($) { "use strict";

    var mallPrice = function(element, options) {
        var self          = this
        this.options      = options
        this.$el          = $(element)

        this.$activeField  = null
        this.$activeButton = $('[data-active-currency]', this.$el)
        this.$dropdown     = $('ul.mall-price-dropdown-menu', this.$el)
        this.$placeholder  = $(this.options.placeholderField)

        this.$dropdown.on('click', '[data-switch-currency]', function(event){
            var selectedCurrency = $(this).data('switch-currency')
            self.setCurrency(selectedCurrency)

            /*
             * If Ctrl/Cmd key is pressed, find other instances and switch
             */
            if (event.ctrlKey || event.metaKey) {
                $('[data-switch-currency="'+selectedCurrency+'"]').click()
            }
        })

        this.$placeholder.on('input', function(){
            self.$activeField.val(this.value)
        })

        /*
         * Init currency
         */
        this.activeCurrency = this.options.defaultCurrency
        this.$activeField = this.getCurrencyElement(this.activeCurrency)
        this.$activeButton.text(this.activeCurrency)
    }

    mallPrice.DEFAULTS = {
        defaultCurrency: 'en',
        defaultField: null,
        placeholderField: null
    }

    mallPrice.prototype.getCurrencyElement = function(currency) {
        var el = this.$el.find('[data-currency-value="'+currency+'"]')
        return el.length ? el : null
    }

    mallPrice.prototype.getCurrencyValue = function(currency) {
        var value = this.getCurrencyElement(currency)
        return value ? value.val() : null
    }

    mallPrice.prototype.setCurrencyValue = function(value, currency) {
        if (currency) {
            this.getCurrencyElement(currency).val(value)
        }
        else {
            this.$activeField.val(value)
        }
    }

    mallPrice.prototype.setCurrency = function(currency) {
        this.activeCurrency = currency
        this.$activeField = this.getCurrencyElement(currency)
        this.$activeButton.text(currency)

        this.$placeholder.val(this.getCurrencyValue(currency))
        this.$el.trigger('setCurrency.oc.mall.price', [currency, this.getCurrencyValue(currency)])
    }

    // MULTILINGUAL PLUGIN DEFINITION
    // ============================

    var old = $.fn.mallPrice

    $.fn.mallPrice = function (option) {
        var args = Array.prototype.slice.call(arguments, 1), result
        this.each(function () {
            var $this   = $(this)
            var data    = $this.data('oc.mall.price')
            var options = $.extend({}, mallPrice.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('oc.mall.price', (data = new mallPrice(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.mallPrice.Constructor = mallPrice

    // MULTILINGUAL NO CONFLICT
    // =================

    $.fn.mallPrice.noConflict = function () {
        $.fn.mallPrice = old
        return this
    }

    // MULTILINGUAL DATA-API
    // ===============
    $(document).render(function () {
        $('[data-control="mall-price"]').mallPrice()
    })

}(window.jQuery);
