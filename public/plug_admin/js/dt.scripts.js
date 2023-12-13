(function (global, $) {
    var isObject = function (value) {
        return value && typeof value === "object" && value.constructor === Object;
    };
    var isArray = function (value) {
        return typeof value === "object" && Array.isArray(value);
    };
    var eachSlice = function (array, size) {
        var length = array.length;
        if (!length || size < 1) {
            return [];
        }

        var index = 0;
        var resIndex = 0;
        var result = new Array(Math.ceil(length / size));
        while (index < length) {
            result[resIndex++] = array.slice(index, (index += size));
        }

        return result;
    };
    var fromPairs = function (pairs) {
        var length = null !== pairs ? pairs.length : 0;
        if (pairs == null || !length) {
            return {};
        }

        return pairs.reduce(function (accumulator, value) {
            accumulator[value[0]] = value[1];

            return accumulator;
        }, {});
    };
    var toPairs = function (props) {
        return Object.keys(props).map(function (key) {
            return [key, props[key]];
        });
    };
    var flatten = function (array) {
        return array.reduce(function (accumulator, value) {
            return accumulator.concat(value);
        }, []);
    }
    var rewriteHystory = function (url, filter) {
        if (
            typeof window.history !== 'undefined' &&
            typeof window.history.pushState !== 'undefined'
        ) {
            var value = null;
            var segments = {};
            var parts = location.pathname.split('/').filter(function (p) {
                return p;
            });
            for (var i = 0; i < parts.length; i = i + 2) {
                var part = parts[i];
                segments[part] = parts[i + 1] || null;
            }
            if (segments.hasOwnProperty('group')) {
                value = segments.group || null;
            }

            history.replaceState({
                filter: {
                    name: filter.name,
                    value: value
                }
            }, $('title').text(), location.href);
            history.pushState({
                filter: {
                    name: filter.name,
                    value: null
                }
            }, $('title').text(), url);
        } else {
            window.location.href = url;
        }
    };
    var dropPathParams = function (pathname, keys, skip) {
        var parts = fromPairs(eachSlice(pathname.split('/').filter(function (f) {
            return f
        }).slice(skip || 0), 2));
        (Array.isArray(keys) ? keys : [keys]).forEach(function (key) {
            if (parts[key]) {
                delete parts[key];
            }
        });

        return flatten(toPairs(parts)).join('/');
    };
    var updateTable = function (table, refilter) {
        refilter = typeof refilter !== "undefined" ? refilter : true;

        if ($.fn.dataTable) {
            $(table)
                .dataTable()
                .fnDraw(refilter);
        }
    };
    var updateTables = function (refilter) {
        refilter = typeof refilter !== "undefined" ? refilter : true;

        if ($.fn.dataTable) {
            $.fn.dataTable.tables().forEach(function (table) {
                $(table)
                    .dataTable()
                    .fnDraw(refilter);
            });
        }
    };
    var onActionStart = function (table, preservePostion) {
        var wrapper = table.closest('.dataTables_wrapper');
        preservePostion = typeof preservePostion !== 'undefined' ? preservePostion : true;

        table.hide();
        wrapper.addClass('h-450');
        showLoader(wrapper);
        if (preservePostion) {
            table.data('scrollPosition', window.scrollY || window.pageYOffset);
            global.scrollTop(0);
        }
    };
    var onActionEnd = function (table, preservePostion) {
        var wrapper = table.closest('.dataTables_wrapper');
        preservePostion = typeof preservePostion !== 'undefined' ? preservePostion : true;

        table.show();
        wrapper.removeClass('h-450');
        hideLoader(wrapper);
        if (preservePostion) {
            global.scrollTop(table.data('scrollPosition') || 0);
        }
    };
    var onDatagridDraw = function () {
        hideDTbottom(this);
        mobileDataTable($('.main-data-table'));
    };
    var onSendRequest = function (params, source, data, callback) {
        var onRequestSuccess = function (response, textStatus, jqXHR) {
            callback(
                $.extend({
                    aaData: [],
                    iTotalRecords: 0,
                    iTotalDisplayRecrds: 0,
                }, response),
                textStatus,
                jqXHR
            );
        };

        postRequest(source, data.concat(params.filters().getDTFilter()))
            .then(onRequestSuccess)
            .catch(onRequestError);
    };

    /**
     * The Operation object.
     *
     * @param {string} name
     * @param {Function} handler
     * @param {any} taget
     * @param {any} value
     */
    var Operation = function (name, handler, source, taget, value) {
        this.name = name;
        this.value = value || null;
        this.taget = taget || null;
        this.source = source || null;
        this.handler = typeof handler === 'function' ? handler : null;
    };

    /**
     * Handles the filter.
     *
     * @param {any} filter
     */
    Operation.prototype.handle = function (filter) {
        if (null === this.handler) {
            return;
        }

        this.handler.call(
            filter,
            typeof this.source !== 'undefined' && this.source ? $(this.source) : null,
            typeof this.taget !== 'undefined' && this.taget ? $(this.taget) : null,
            this.value
        );
    };
    /**
     * Checks if operation is valid.
     *
     * @param {any} operation
     */
    Operation.isValid = function (operation) {
        return operation instanceof Operation || (isObject(operation) && operation.hasOwnProperty('name'));
    };

    /**
     * The Binding object.
     *
     * @param {string} name
     * @param {any} source
     * @param {Array.<{name: any, target?: any, value?: any}>} operations
     * @param {{[x: string]: Function}} handlers
     */
    var Binding = function (name, source, operations, handlers) {
        var self = this;

        this.name = name;
        this.operations = [];

        (operations || []).forEach(function (operation) {
            if (!Operation.isValid(operation) || !handlers.hasOwnProperty(operation.name)) {
                return;
            }

            self.addOperation(new Operation(
                operation.name,
                handlers[operation.name],
                source || null,
                operation.target || null,
                operation.value || null
            ));
        });
    };

    /**
     * Adds one operation
     *
     * @param {Operation} operation
     */
    Binding.prototype.addOperation = function (operation) {
        if (!operation instanceof Operation) {
            return;
        }

        this.operations.push(operation);
    };

    /**
     * Handles the filter.
     *
     * @param {any} filter
     */
    Binding.prototype.handle = function (filter) {
        this.operations.forEach(function (operation) {
            operation.handle(filter);
        });
    };

    /**
     * Checks if binding is valid.
     *
     * @param {any} binding
     */
    Binding.isValid = function (binding) {
        return binding instanceof Binding ||
            (isObject(binding) && binding.hasOwnProperty('name') && (binding.hasOwnProperty('op') && isArray(binding.op)));
    };

    /**
     * The bindings list object.
     *
     * @param {Array.<{name: string, source?: any, op: Array.<{name: any, target?: any, value?: any}>}>} bindings
     * @param {{[x: string]: Function}} handlers
     */
    var Bindings = function (bindings, handlers) {
        var self = this;

        this.list = [];

        (bindings || []).forEach(function (binding) {
            if (!Binding.isValid(binding)) {
                return;
            }

            self.addBinding(new Binding(
                binding.name,
                binding.source || null,
                binding.op,
                handlers || {}
            ));
        });
    }

    /**
     * Adds one binding
     *
     * @param {Binding} binding
     */
    Bindings.prototype.addBinding = function (binding) {
        if (!binding instanceof Binding) {
            return;
        }

        this.list.push(binding);
    };

    /**
     * Handles the filter.
     *
     * @param {any} filter
     */
    Bindings.prototype.handle = function (filter) {
        this.list
            .filter(function (binding) {
                return filter.name && binding.name === filter.name;
            })
            .forEach(function (binding) {
                binding.handle(filter);
            });
    };

    /**
     * @param {{name: string, [x: string]: any}} filter
     * @param {Array.<{name: string, source?: any, op: Array.<{name: any, target?: any, value?: any}>}>} bindingsList
     * @param {{[x: string]: Function}} handlers
     */
    var adjustFilter = function (filter, bindingsList, handlers) {
        new Bindings(bindingsList || [], handlers || {}).handle(filter);
    };

    $(function () {
        mix(window, {
            DT: mix(global.DT || {}, {
                Util: mix({}, {
                    onActionEnd: onActionEnd,
                    updateTable: updateTable,
                    updateTables: updateTables,
                    onActionStart: onActionStart,
                    onSendRequest: onSendRequest,
                    onDatagridDraw: onDatagridDraw,
                })
            }),
            DTFilters: mix({}, {
                Operation: Operation,
                Bindings: Bindings,
                Binding: Binding,
                Util: mix({}, {
                    adjustFilter: adjustFilter,
                    dropPathParams: dropPathParams,
                    rewriteHystory: rewriteHystory
                })
            })
        });
    });
}(window, jQuery));