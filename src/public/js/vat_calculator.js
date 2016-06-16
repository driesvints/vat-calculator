(function () {
    var DATA_PREFIX = 'data-vat';


    /**
     * Private methods
     */
    function bindCalculatorEvents() {
        var form = document.querySelector(VATCalculator.selector);
        if (form !== null) {
            var dropDown = form.querySelector('select[' + DATA_PREFIX + '="country"]');
            if (dropDown !== null) {
                dropDown.addEventListener('change', calculate);
                // Try to preselect based on the user IP
                VATCalculator.getCountryCode({
                    success: function (status, response) {
                        for( var i=0; i<dropDown.options.length; i++ ){
                            if( dropDown.options[i].value == response.country_code ){
                                dropDown.value = response.country_code;
                                fireEvent( dropDown, 'change' );
                            }
                        }
                    }
                });
            }
            var vatNumber = form.querySelector('[' + DATA_PREFIX + '="vat-number"]');
            if (vatNumber !== null) {
                vatNumber.addEventListener('blur', calculate);
            }
            var postalCode = form.querySelector('[' + DATA_PREFIX + '="postal-code"]');
            if (postalCode !== null) {
                postalCode.addEventListener('blur', calculate);
            }
            // Trigger now
            calculate();
        }
    }

    /**
     * Fire an event
     * @param target
     * @param eventName
     */
    function fireEvent( target, eventName )
    {
        if( document.createEvent )
        {
            var evt = document.createEvent('Event');
            evt.initEvent( eventName ,true,true);
            target.dispatchEvent( evt );
        }
    }


    /**
     * Gets the value for a given data attribute
     *
     * @param attribute
     * @returns {string}
     */
    function getValue(attribute) {
        var selector = document.querySelector("[" + DATA_PREFIX + "='" + attribute + "']");
        if (selector === null) {
            return "";
        } else {
            return selector.value;
        }
    }

    /**
     * Set tthe innerHTML for the given attribute
     *
     * @param attribute
     * @param value
     * @returns {boolean}
     */
    function setHTML(attribute, value) {
        var entries = document.querySelectorAll(".vat-" + attribute);
        if (entries.length > 0) {
            for (var i = 0; i < entries.length; i++) {
                entries[i].innerHTML = value;
            }
            return true;
        }
        return false;
    }

    function getJSONResponse(responseText) {
        try {
            return JSON.parse(responseText);
        } catch (e) {
            return false;
        }
    }

    function makeRequest(type, url, callbacks) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                var response = getJSONResponse(xhr.responseText);
                if (xhr.status === 200) {
                    if (callbacks.success) {
                        callbacks.success(xhr.status, response);
                    }
                } else {
                    if (callbacks.error) {
                        callbacks.error(xhr.status, response);
                    }
                }
            }
        };
        xhr.open(type, url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(null);
    }

    /**
     * Calculate the gross price
     * @param successCallback
     */
    function calculate(successCallback) {
        var form = document.querySelector(VATCalculator.selector);
        if (form !== null) {
            var amount = parseInt(form.getAttribute('data-amount')),
                country = getValue('country'),
                postal_code = getValue('postal-code'),
                vat_number = getValue('vat-number');
            makeRequest('GET', '/vatcalculator/calculate?netPrice=' + (amount / 100) + '&country=' + country + '&postal_code=' + postal_code + '&vat_number=' + vat_number, {
                success: function (status, response) {
                    setHTML('total', VATCalculator.formatCurrency(response.gross_price));
                    setHTML('subtotal', VATCalculator.formatCurrency(response.net_price));
                    setHTML('taxes', VATCalculator.formatCurrency(response.tax_value));
                    setHTML('taxrate', (100*response.tax_rate).toFixed(0));
                    if (successCallback && typeof(successCallback) === 'function') {
                        successCallback(response);
                    }
                    VATCalculator.setCalculation(response);
                },
                error: function (status, response) {
                    console.log(response.error);
                }
            });
        }
    }

    var Calculator = function () {
        /**
         * Form selector
         * @type {string}
         */
        this.selector = "#payment-form";

        /**
         * Last calculation results
         * @type {Object}
         */
        this.calculation = {};

        /**
         * Function to use to format calculation
         * results for HTML output.
         * @type {Function}
         */
        this.currencyFormatter = function(value){
            return value.toFixed(2);
        };
    };

    Calculator.prototype = {

        /**
         * Lookup the IP based country code
         * @param callbacks
         */
        getCountryCode: function (callbacks) {
            makeRequest('GET', '/vatcalculator/country-code', callbacks);
        },

        /**
         * Set the last used calculation response
         * @param calculation
         */
        setCalculation: function (calculation) {
            return this.calculation = calculation;
        },

        /**
         * Get the last saved calculation
         * @returns {Calculator.calculation|Function}
         */
        calculation: function () {
            return this.calculation;
        },

        /**
         * Perform the calculation task
         * @param {function} successCallback
         */
        calculate: function (successCallback) {
            calculate(successCallback);
        },

        /**
         * Get the current selector
         * @returns {Calculator.selector|Function}
         */
        selector: function () {
            return this.selector;
        },

        /**
         * Sets the form selector
         * @param {string} selector
         * @returns {*}
         */
        setSelector: function (selector) {
            return this.selector = selector;
        },

        /**
         * Format the calculated values
         *
         * @param value
         * @returns {string}
         */
        formatCurrency: function (value) {
            return this.currencyFormatter(value);
        },

        /**
         * Override the currency formatter function
         *
         * @param {Function} callback
         */
        setCurrencyFormatter: function (callback) {
            this.currencyFormatter = callback;
        },

        /**
         * Initializes the calculator and sets the form
         * selector to use
         * @param {string} selector
         * @returns {boolean}
         */
        init: function (selector) {
            if (document.querySelectorAll(selector).length === 1) {
                this.setSelector(selector);
                bindCalculatorEvents();
                return true;
            }
            return false;
        }
    };

    // Make VAT calculator globally accessible
    window.VATCalculator = new Calculator();

    // Add VAT calculator onload event
    if (window.onload) {
        var onLoad = window.onload;
        window.onload = function () {
            onLoad();
            bindCalculatorEvents();
        };
    } else {
        window.onload = bindCalculatorEvents;
    }
}).call(this);
