<script>
    window.PaymentModal = ({
        token: null,
        stripeKey: null,
        currentStep: 1,
        selectedMethod: null,
        PAYPAL_METHOD: 5,
        CREDIT_CARD_METHOD: 2,
        init: function(params) {
            params = params || {};
            this.token = params.token;
            this.stripeKey = params.stripeKey;

            var self = this;
            var form = $('#payment-form');
            var nextButton = $('.js-btns-pay .next-btn');
            var prevButton = $('.js-btns-pay .prev-btn');
            var payButton = $('.js-btns-pay .pay-btn');
            var additionalPayments = form.find('.js-enable-additional-payment');
            var onCheckPayment = function() {
                var self = $(this);
                var totalSpan = $('#total-amount');
                var totalValue = parseFloat(totalSpan.data('total') || 0);
                var totalNew;

                if (!self.is(':checked')) {
                    totalNew = (totalValue - parseFloat(self.data('value') || 0));
                } else {
                    totalNew = (totalValue + parseFloat(self.data('value') || 0));
                }

                totalSpan.html(get_price(totalNew, false));
                totalSpan.data('total', totalNew);
            };
            var onMethodSelect = function(e) {
                var button = $(this);

                button.addClass('active').siblings().removeClass('active');
                nextButton.prop('disabled', false);
                self.selectedMethod = parseInt(button.data('method') || 0);

            };
            var onPreviousStep = function(e) {
                e.preventDefault();
                self.renderModal(self.getPrevStep()).then(function() {
                    self.decrementStep();
                });
            };
            var onNextStep = function(e) {
                e.preventDefault();
                self.requestStep(self.getNextStep()).then(function(data) {
                    if ('success' === data.mess_type) {
                        self.incrementStep();
                    }
                    if (2 === self.currentStep) {
                        $('.methods-pay__item').on('click', onMethodSelect);
                    }
                })
            };
            var onPay = function(e) {
                e.preventDefault();
                self.executePayment();
            };

            nextButton.on('click', onNextStep);
            prevButton.on('click', onPreviousStep);
            payButton.on('click', onPay);
            if (additionalPayments.length) {
                additionalPayments.on('change', onCheckPayment);
            }

            return this;
        },
        requestStep: function(step) {
            step = step || 1;
            var self = this;
            var $wrapper = $('.js-order-payment-form').closest('.js-modal-flex');
            var additionalPay = [];
            var sendRequest = function(url, data) {
                onRequestStart();

                return $.post(url, data, null, 'json');
            }
            var onRequestEnd = function() {
                hideLoader($wrapper);
            };
            var onRequestStart = function() {
                showLoader($wrapper);
            };
            var onRequestSuccess = function(data) {
                var stepContainer = $('#step-b-' + step);
                if (data.mess_type === 'success') {
                    stepContainer.html(data.content);
                    self.renderModal(step);
                } else {
                    systemMessages(data.message, 'error');
                }
            };

            if (step >= 2) {
                $('.additional-item-pay').each(function() {
                    var payment = $(this).find('input[type="checkbox"]');
                    if (payment.is(':checked')) {
                        additionalPay.push(payment.val());
                    }
                });
            }

            return sendRequest(
                    __group_site_url + 'payments/ajax_payment_method', {
                        step: step,
                        token: self.token,
                        method: self.selectedMethod || null,
                        additionalPay: additionalPay
                    }
                ).done(onRequestSuccess)
                .fail(onRequestError)
                .always(onRequestEnd);
        },
        renderModal: function(step) {
            step = step || 1;
            var stepContainer = $('#step-b-' + step);
            var stepContainersList = $('[id^=step]');
            var invoiceLink = $('#payment-form--action--download-invoice');
            var nextButton = $('.js-btns-pay .next-btn');
            var prevButton = $('.js-btns-pay .prev-btn');
            var payButton = $('.js-btns-pay .pay-btn');
            stepContainersList.hide();
            stepContainer.show();
            prevButton[step > 1 ? 'show' : 'hide']();
            nextButton[step < 3 ? 'show' : 'hide']();
            payButton[step === 3 ? 'show' : 'hide']();

            if (1 === step) {
                this.selectedMethod = null;
                invoiceLink.attr('href', invoiceLink.data('base-href'));
                nextButton.prop('disabled', false);
            }

            if (2 === step && null === this.selectedMethod) {
                nextButton.prop('disabled', true);
            }

            if (3 === step) {
                enableFormValidation($('.validateModal'));
                if (this.selectedMethod === this.CREDIT_CARD_METHOD) {
                    this.creditCardForm.init({
                        stripeKey: this.stripeKey
                    });
                }
                if (this.selectedMethod === this.PAYPAL_METHOD) {
                    payButton.hide();
                    this.paypalButton.init({
                        token: this.token,
                        container: stepContainer,
                    });
                }
            }

            $.fancybox.update();

            return $.Deferred().resolve();
        },
        executePayment: function() {
            var form = $('#method-form');
            if (form.validationEngine('validate') !== true) {
                return form.validationEngine();
            }

            this.paymentSubmitForm(form);
        },
        paymentSubmitForm: function(form) {
            var data = form.serializeArray();
            var $wrapper = form.closest('.js-modal-flex');
            var sendRequest = function(url, data) {
                return $.post(url, data, null, 'json');
            }
            var onRequestEnd = function() {
                hideLoader($wrapper);
            };
            var onRequestStart = function() {
                showLoader($wrapper);
            };
            var onRequestSuccess = function(data) {
                systemMessages(data.message, data.mess_type);
                if ('success' === data.mess_type) {
                    callFunction('payment_callback', data);
                    closeFancyBox();
                }
            };
            var onCardPaymentFail = function (error) {
                if (error.code && error.message) {
                    systemMessages(error.message, 'error');
                } else {
                    onRequestError(error);
                }
            };
            var payWithCreditCard = function(token) {
                data.push({ name: 'stripe_token', value: token.id || null });

                return sendRequest(__group_site_url + 'payments/process_credit_card', data)
                    .done(onRequestSuccess)
                    .fail(onRequestError)
                    .always(onRequestEnd);
            };

            onRequestStart(form);
            if (this.selectedMethod === this.CREDIT_CARD_METHOD) {
                return this.creditCardForm.createStripeToken(payWithCreditCard, onCardPaymentFail, onRequestEnd);
            } else {
                return sendRequest(__group_site_url + 'payments/ajax_payment_info', data)
                    .done(onRequestSuccess)
                    .fail(onRequestError)
                    .always(onRequestEnd);
            }
        },
        getNextStep: function() {
            return this.currentStep < 3 ? this.currentStep + 1 : this.currentStep;
        },
        getPrevStep: function () {
            return this.currentStep > 0 ? this.currentStep - 1 : 0;
        },
        decrementStep: function() {
            this.currentStep = this.currentStep > 0 ? this.currentStep - 1 : 0;
        },
        incrementStep: function() {
            this.currentStep = this.currentStep < 3 ? this.currentStep + 1 : this.currentStep;
        },
        paypalButton: {
            init: function (params) {
                if (!window.paypal || !window.paypal.Buttons) {
                    return;
                }

                var container = params.container || null;
                var sendRequest = function (url, data) {
                    return new Promise(function(resolve, reject) {
                        $.post(url, data, null, 'json')
                            .fail(function (xhr, status, error) { reject(error); })
                            .done(function (response) {
                                if ('error' === response.status || 'error' === response.mess_type) {
                                    reject({ message: response.message, data: response.data || null, isCustom: true });

                                    return;
                                }

                                resolve(response);
                            });
                    });
                };
                var handleError = function (actions, error) {
                    if (!error.isCustom || !error.message) {
                        onRequestError(error);
                    } else {
                        systemMessages(error.message, 'error');
                        if (__debug_mode) {
                            console.error(error);
                        }
                    }

                    if (error.data && error.data.is_declined && actions && actions.restart) {
                        return actions.restart();
                    }
                };

                paypal.Buttons({
                    createOrder: function(details, actions) {
                        var url = __group_site_url + 'payments/ajax_paypal_checkout/purchase';
                        var data = { token: params.token || null };

                        return sendRequest(url, data)
                            .then(function (response) { return response.data || {}; })
                            .then(function (data) { return data.id || null; })
                            .catch(handleError.bind(this, actions));
                    },
                    onApprove: function(details, actions) {
                        var url = __group_site_url + 'payments/ajax_paypal_checkout/complete_purchase';
                        var data = {
                            token: params.token || null,
                            order_id: details.orderID || null,
                            payer_id: details.payerID || null,
                            payment_id: details.paymentID || null
                        };
                        if (null !== container) {
                            showLoader(container);
                            container.find('.wr-ajax-loader').css({ 'z-index': 1000000 });
                        }

                        return sendRequest(url, data)
                            .catch(handleError.bind(this, actions))
                            .then(function (response) {
                                systemMessages(response.message, response.mess_type);
                                if ('success' === response.mess_type) {
                                    callFunction('payment_callback', response);
                                    closeFancyBox();
                                }
                            });
                    },
                    onError: function() {
                        if (null !== container) {
                            hideLoader(container);
                        }
                    },
                }).render('#payment-form--method--paypal-container');
            },
        },
        creditCardForm: {
            stripe: null,
            stripeKey: null,
            elements: {},
            init: function(params) {
                params = params || {};
                this.stripeKey = params.stripeKey;
                this.stripe = Stripe(this.stripeKey);

                var elements = this.stripe.elements(),
                    elementStyles = {
                        base: {
                            color: '#32325D',
                            fontWeight: 500,
                            lineHeight: '35px',
                            fontFamily: 'Source Code Pro, Consolas, Menlo, monospace',
                            fontSize: '16px',
                            fontSmoothing: 'antialiased',
                            '::placeholder': {
                                color: '#CFD7DF'
                            },
                            ':-webkit-autofill': {
                                color: '#e39f48'
                            }
                        },
                        invalid: {
                            color: '#E25950',
                            '::placeholder': {
                                color: '#FFCCA5'
                            }
                        }
                    },
                    elementClasses = {
                        focus: 'focused',
                        empty: 'empty',
                        invalid: 'invalid'
                    };


                this.elements.cardNumber = elements.create('cardNumber', {
                    style: elementStyles,
                    classes: elementClasses
                });
                this.elements.cardNumber.mount('#card-number');


                this.elements.cardExpiry = elements.create('cardExpiry', {
                    style: elementStyles,
                    classes: elementClasses
                });
                this.elements.cardExpiry.mount('#card-expiry');


                this.elements.cardCvc = elements.create('cardCvc', {
                    style: elementStyles,
                    classes: elementClasses
                });
                this.elements.cardCvc.mount('#card-cvc');

                [this.elements.cardNumber, this.elements.cardExpiry, this.elements.cardCvc].forEach(function(t) {
                    t.addEventListener('change', function(event) {
                        if (event.error) {
                            var error = event.error;
                            if (error.code && error.message) {
                                systemMessages(translate_js ({ plug: 'stripe', text: error.code }) || error.message, 'error');
                            }
                        }
                    });
                });
            },
            createStripeToken: function(successCallback, errorCallback, completeCallback) {
                return this.stripe.createToken(this.elements.cardNumber, {
                    name: $('.card-holder-first-name').val() + ' ' + $('.card-holder-last-name').val()
                }).then(function(result) {
                    if (result.error) {
                        errorCallback && errorCallback(result.error);
                        completeCallback && completeCallback();
                    } else {
                        successCallback(result.token);
                    }
                }).catch(function(err) {
                    errorCallback && errorCallback(err);
                    completeCallback && completeCallback();
                });
            }
        }
    }).init({ token: '<?php echo $token; ?>', stripeKey: '<?php echo $stripe_key; ?>' });

    mix(window, { modalFormCallBack: null }, false);
</script>
