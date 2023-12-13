/* eslint-disable */
var ProcessEnvelopeModule = (function () {
    "use strict";

    /**
     * Create instance of the renderer.
     *
     * @param {HTMLElement} container
     * @param {string} template
     */
    function RecipientsRenderer(container, template, datepicker) {
        this.container = container;
        this.template = template;
        this.datepicker = datepicker;
    }
    /**
     * Renders one recipient.
     *
     * @param {CreateEnvelope.RecipientOptions} recipient
     * @param {number} index
     */
    RecipientsRenderer.prototype.render = function (recipient, index) {
        this.container.insertAdjacentHTML(
            "beforeend",
            renderTemplate(this.template, {
                index: index,
                position: (index || 0) + 1,
                assigneeName: recipient.assigneeName,
                assigneeGroup: recipient.assigneeGroup,
                assigneeGroupColor: recipient.assigneeGroupColor,
                expiresAt: recipient.expiresAt,
                recipientType: recipient.recipientType,
            })
        );

    };
    /**
     * Renders the recipients list.
     *
     * @param {CreateEnvelope.RecipientOptions[]} list
     */
    RecipientsRenderer.prototype.renderList = function (list, recipientsHandler) {
        while (this.container.firstChild) {
            this.container.removeChild(this.container.firstChild);
        }

        var self = this;
        list.forEach(function (recipient, i) {
            return self.render(recipient, i);
        });

        initializeDueDate(this.datepicker, recipientsHandler, recipientsHandler.maxDays);
    };

    /**
     * Create instance of recipients handler.
     *
     * @param {RecipientsRenderer} renderer
     * @param {HTMLElement} addButton
     * @param {HTMLElement} assigneesList
     * @param {HTMLElement} typesList
     * @param {Array<any>} recipients
     * @param {number} [limit]
     */
    function RecipientsHandler(renderer, addButton, assigneesList, typesList, recipients, limit, defaultDueDateInterval, maxDays) {
        this.assignees = $(assigneesList);
        this.button = $(addButton);
        this.types = $(typesList);
        this.limit = limit || null;
        this.defaultDueDateInterval = defaultDueDateInterval;
        this.maxDays = maxDays;
        this.renderer = renderer;
        this.recipients = recipients || [];
        this.enabledValidation = false;

        this.renderer.renderList(this.recipients, this);
    }

    /**
     * The current amount of the recipients.
     */
    RecipientsHandler.prototype.currentAmount = function () {
        return this.recipients.length;
    };

    /**
     * Validates recipients source nodes.
     */
    RecipientsHandler.prototype.validateNodes = function () {
        this.enableValidation();

        var assigneesAreValid = validateElement(this.assignees);
        var typesAreValid = validateElement(this.types);

        return assigneesAreValid && typesAreValid;
    };

    /**
     * Enables validation for recipients source lists.
     */
    RecipientsHandler.prototype.enableValidation = function () {
        [this.assignees, this.types].forEach(function (node) {
            var dataset = node.data();
            if (dataset.validate) {
                node.addClass(dataset.validate);
            }
        });
    };

    /**
     * Disables validation for recipients source lists.
     */
    RecipientsHandler.prototype.disableValidation = function () {
        [this.assignees, this.types].forEach(function (node) {
            var dataset = node.data();
            if (dataset.validate) {
                node.removeClass(dataset.validate);
                node.removeClass(dataset.border);
            }
        });
    };

    /**
     * Resets the recipients source lists.
     */
    RecipientsHandler.prototype.resetLists = function () {
        this.assignees.val(null);
        this.types.val(null);
    };

    /**
     * Adds one recipient to the list.
     */
    RecipientsHandler.prototype.addRecipient = function () {
        if (this.validateNodes() === false) {
            return;
        }

        var lastDate = (this.recipients.length > 0) ? new Date(this.recipients[this.recipients.length - 1].expiresAt) : new Date();
        lastDate.setDate(lastDate.getDate() + this.defaultDueDateInterval);

        /** @type {CreateEnvelope.RecipientOptions} */
        var recipient = {
            type: this.types.val().toString(),
            assignee: this.assignees.val().toString(),
            assigneeName: this.assignees.find(":selected").data('name'),
            assigneeGroup: this.assignees.find(":selected").data('group'),
            assigneeGroupColor: this.assignees.find(":selected").data('color'),
            recipientType: this.types.find(":selected").text(),
            expiresAt: $.datepicker.formatDate('mm/dd/yy', lastDate),
        };
        this.recipients.push(recipient);
        this.renderer.render(recipient, this.recipients.length - 1);

        initializeDueDate("input[data-index='" + (this.recipients.length - 1) + "']", this, this.maxDays);

        this.resetLists();
        this.disableValidation();
        if (this.limit !== null && this.recipients.length >= this.limit) {
            this.button.addClass("disabled");
        }
    };

    /**
     * Removes recipient from the list.
     *
     * @param {number} [index=null]
     */
    RecipientsHandler.prototype.removeRecipient = function (index) {
        index = typeof index !== "undefined" ? index : null;
        if (index === null) {
            return;
        }

        if (!this.recipients[index]) {
            throw new ReferenceError('The recipients with index "' + index + '" is not found in the list');
        }

        this.recipients.splice(index, 1);
        this.renderer.renderList(this.recipients, this);
        if (this.limit !== null && this.recipients.length < this.limit) {
            this.button.removeClass("disabled");
        }
    };

    /**
     * Offsets the recipient in the list.
     *
     * @param {number} [index=null]
     * @param {string} [direction=null]
     */
    RecipientsHandler.prototype.offsetRecipient = function (index, direction) {
        index = typeof index === 'number' ? index : null;
        direction = typeof direction === 'string' ? direction : null;

        var applyOffset = function (value) {
            switch (direction) {
                case "up":
                    return value > 0 ? value - 1 : 0;
                case "down":
                    return value + 1;
                default:
                    return null;
            }
        };

        if (index === null || direction === null) {
            return;
        }

        this.checkExpiresAt(index, applyOffset(index), direction);

        if (!this.recipients[index]) {
            throw new ReferenceError('The recipient with index "' + index + '" is not found in the list.');
        }
        if (["up", "down"].indexOf(direction) === -1) {
            throw new TypeError('The direction "' + direction + '" is not supported.');
        }

        this.changeRecipientPosition(index, applyOffset(index));
    };

    /**
     * Chages recipient position in the list.
     *
     * @param {number} oldPosition
     * @param {number} newPosition
     */
    RecipientsHandler.prototype.changeRecipientPosition = function (oldPosition, newPosition) {
        if ((oldPosition === newPosition && newPosition === 0) || newPosition === this.recipients.length) {
            return;
        }

        var ref = [this.recipients[newPosition], this.recipients[oldPosition]];
        this.recipients[oldPosition] = ref[0];
        this.recipients[newPosition] = ref[1];
        ref;

        this.renderer.renderList(this.recipients, this);
    };

    /**
     * Check if the recipient's due date is not smaller
     *
     * @param {number} index
     * @param {number} appliedOffset
     * @param {string} direction
     */
    RecipientsHandler.prototype.checkExpiresAt = function (index, appliedOffset, direction) {

        if(typeof this.recipients[appliedOffset] === 'undefined'){
            return true;
        }

        if(!this.recipients[index].expiresAt || !this.recipients[appliedOffset].expiresAt){
            return true;
        }

        var currentRecipientDate = new Date(this.recipients[index].expiresAt);
        var offsetRecipientDate = new Date(this.recipients[appliedOffset].expiresAt);

        if(direction == "up" && currentRecipientDate.getTime() > offsetRecipientDate.getTime()){
            systemMessages(translate_js({ plug: "general_i18n", text: "order_documents_due_date_greater_message" }), "warning");

            return false;
        }
        if(direction == "down" && currentRecipientDate.getTime() < offsetRecipientDate.getTime()){
            systemMessages(translate_js({ plug: "general_i18n", text: "order_documents_due_date_smaller_message" }), "warning");

            return false;
        }

        return true;
    };

    /**
     * Check due date by order
     */
    RecipientsHandler.prototype.checkSortedDueDates = function () {
        var recipientsObject = this.recipients;
        var filteredDates = [];
        Object.keys(recipientsObject).forEach(function(key) {
            if (recipientsObject[key]['expiresAt'] !== "") {
                filteredDates.push(recipientsObject[key]['expiresAt']);
            }
        });

        for (let key = 1; key < filteredDates.length; key++){
            if(new Date(filteredDates[key-1]).getTime() > new Date(filteredDates[key]).getTime()){
                return false;
            }
        }

        return true;
    }

    /**
     * On change date save the recipient's date
     */
    RecipientsHandler.prototype.setDueDate = function (index, date) {
        if(typeof this.recipients[index] === 'undefined'){
            return;
        }

        this.recipients[index].expiresAt = date;

        var self = this;
        setTimeout(function(){
            self.renderer.renderList(self.recipients, self);
        }, 200);

    }

    /**
     * Iterates over recipients.
     */
    RecipientsHandler.prototype.getRecipients = function () {
        var collection = [];
        for (let index = 0; index < this.recipients.length; index += 1) {
            var element = this.recipients[index];

            collection.push(
                { name: "recipients[" + index + "][type]", value: element.type },
                { name: "recipients[" + index + "][order]", value: index + 1 },
                { name: "recipients[" + index + "][assignee]", value: element.assignee },
                { name: "recipients[" + index + "][expiresAt]", value: element.expiresAt }
            );
        }

        return collection;
    };

    /**
     * Send request to create envelope.
     *
     * @param {URL} url
     * @param {number} orderId
     * @param {number} envelopeId
     * @param {RecipientsHandler} recipients
     * @param {HTMLElement} container
     * @param {JQuery} form
     */
    function saveEnvelope(url, orderId, envelopeId, recipients, maxRecipients, container, form) {
        var recipientsList = recipients.getRecipients();

        var entities = [
            { name: "order", value: orderId },
            { name: "envelope", value: envelopeId },
        ].filter(function (f) {
            return f.value;
        });
        if (recipientsList.length < 1) {
            systemMessages(translate_js({ plug: "general_i18n", text: "order_documents_process_form_recipient_empty_list_warning" }), "warning");

            return;
        }
        if (maxRecipients !== null && recipients.currentAmount() > maxRecipients) {
            systemMessages(
                translate_js({
                    plug: "general_i18n",
                    text: "order_documents_process_form_recipient_list_too_much_warning",
                    replaces: { "{{AMOUNT}}": maxRecipients },
                }),
                "warning"
            );

            return;
        }

        if(!recipients.checkSortedDueDates()){
            systemMessages(translate_js({ plug: "general_i18n", text: "order_documents_due_date_wrong_in_list_message" }), "warning");

            return;
        }

        showLoader(container);
        form.find("button[type=submit]").addClass("disabled");

        // @ts-ignore
        return postRequest(url, entities.concat(form.serializeArray()).concat(recipients.getRecipients()), "json")
            .then(function (data) {
                systemMessages(data.message, data.mess_type);
                if (data.mess_type === "success") {
                    closeFancyBox();
                    $(globalThis).trigger("documents:envelope-processed", data.envelope || {});
                }
            })
            .catch(function (e) {
                onRequestError(e);
            })
            .finally(function () {
                form.find("button[type=submit]").removeClass("disabled");
                hideLoader(container);
            });
    }

    /**
     * Collects all <sctipt type="text/template"> from the provided container.
     *
     * @param {HTMLElement} containerElement
     */
    function collectTemplates(containerElement) {
        /** @type {{[x: string]: string}} */
        var templates = {};
        Array.from(containerElement.querySelectorAll('script[type="text/template"]'))
            .filter(function (element) {
                return element instanceof HTMLElement && element.dataset.name;
            })
            .forEach(function (element) {
                templates[element.dataset.name] = element.innerHTML.trim();
                if (Object.prototype.hasOwnProperty.call(element, 'remove') || typeof element.remove === 'function') {
                    element.remove();
                } else {
                    // Make compatible with old and useless IE
                    element.parentNode.removeChild(element);
                }
            });

        return templates;
    }

    /**
     * Initalize datepicker
     *
     * @param {any} datepickerSelector
     * @param {any} recipientsHandler
     * @param {number} maxDays
     */
    function initializeDueDate(datepickerSelector, recipientsHandler, maxDays)
    {
        createDatepicker(datepickerSelector, {
            'minDate': new Date(),
            'maxDate': "+" + maxDays + "d",
            onSelect: function(date){
                var index = $(this).data('index');
                recipientsHandler.setDueDate(index, date);
            }
        });
    };


    /**
     * Module entrypoint.
     *
     * @param {number} envelopeId
     * @param {number} orderId
     * @param {{[x: string]: string }} selectors
     * @param {any} recipients
     * @param {any} maxRecipients
     * @param {any} defaultDueDateInterval
     * @param {number} maxDays
     * @param {string} saveUrl
     */
    function entrypoint(envelopeId, orderId, selectors, recipients, maxRecipients, defaultDueDateInterval, maxDays, saveUrl) {
        var selectorsList = selectors || {};
        var recipientsList = recipients || [];

        // Selectors
        var containerSelector = selectorsList.container;
        var typesListSelector = selectorsList.typesList;
        var descriptionSelector = selectorsList.description;
        var assigneesListSelector = selectorsList.assigneesList;
        var assigneesButtonSelector = selectorsList.assigneesButton;
        var recipientsContainerSelector = selectorsList.recipientsContainer;
        var datepickerSelector = selectorsList.datepicker;

        /** @type {HTMLElement} container */
        var container = document.querySelector(containerSelector);
        var templates = collectTemplates(container);
        var renderer = new RecipientsRenderer(document.querySelector(recipientsContainerSelector), templates.recipientEntry || null, datepickerSelector);
        var formElement = $(container.querySelector('form'));
        var recipientsHandler = new RecipientsHandler(
            renderer,
            document.querySelector(assigneesButtonSelector),
            document.querySelector(assigneesListSelector),
            document.querySelector(typesListSelector),
            recipientsList,
            maxRecipients || null,
            maxDays || 30,
            defaultDueDateInterval
        );

        addCounter(descriptionSelector);
        formElement.on("jqv.form.validating", function () {
            recipientsHandler.disableValidation();
        });

        mix(
            globalThis,
            {
                documentsOrdersProcessFormCallBack: function (form) {
                    saveEnvelope(new URL(saveUrl), orderId, envelopeId, recipientsHandler, maxRecipients || null, container, form);
                },
                addRecipientToEnvelope: function () {
                    recipientsHandler.addRecipient();
                },
                showDatePicker: function (button) {
                    button.parent().find(datepickerSelector).datepicker("show");
                },
                removeRecipientFromEnvelope: function (button) {
                    recipientsHandler.removeRecipient(~~button.data("index"));
                },
                changeRecipientRoutingOrderInEnvelope: function (button) {
                    recipientsHandler.offsetRecipient(~~button.data("index"), button.data("direction") || null);
                },
            },
            false
        );
    }

    return { default: entrypoint };
})();
