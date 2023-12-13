class UrlBuilder {
    /**
     * The URL builder
     *
     * @param {(String|URL)} baseUrl
     * @param {(String|URL)} currentUrl
     */
    constructor(baseUrl, currentUrl, pathMetadata, queryMetadada) {
        this.pathMetadataThis = pathMetadata || [];
        this.queryMetadadaThis = queryMetadada || [];

        this.url = currentUrl instanceof URL ? currentUrl : new URL(currentUrl);
        this.baseUrl = baseUrl instanceof URL ? baseUrl : new URL(baseUrl);
        this.pathMetadata = new Map();
        this.queryMetadada = new Map();

        for (let index = 0; index < this.pathMetadataThis.length; index += 1) {
            const fragment = this.pathMetadataThis[index];
            this.pathMetadata.set(fragment.key, fragment);
        }
        for (let index = 0; index < this.queryMetadadaThis.length; index += 1) {
            const fragment = this.queryMetadadaThis[index];
            this.queryMetadada.set(fragment.key, fragment);
        }
    }

    updateMetaFromFilters(filters) {
        const self = this;

        filters.toArray().forEach(entry => {
            self.updateMetaFragment(entry.name, entry.value);
        });
    }

    updateMetaFragment(name, value) {
        if (typeof name !== "string") {
            throw new TypeError("The name must be a string");
        }

        const inPath = this.pathMetadata.has(name);
        const inQuery = this.queryMetadada.has(name);
        if (!inPath && !inQuery) {
            return;
        }
        if (inPath) {
            this.pathMetadata.get(name).value = value;
        }
        if (inQuery) {
            this.queryMetadada.get(name).value = value;
        }
    }

    /**
     * Builds url.
     *
     * @returns {URL}
     */
    buildUrl() {
        const url = new URL(this.baseUrl.href);
        let fragments = [];
        const formatters = {
            raw(value) {
                return value;
            },
            entity(value) {
                return parseInt(value, 10);
            },
            "scalar:any": function (value) {
                return value;
            },
            "scalar:number": function (value) {
                return parseInt(value, 10);
            },
            "option:boolean": function (value) {
                return value ? "yes" : "no";
            },
            "scalar:number:page": function (value) {
                const page = parseInt(value, 10);

                return page > 1 ? page : null;
            },
        };

        // Update URL path
        Array.from(this.pathMetadata.values()).forEach(meta => {
            if (meta.value === null) {
                fragments[meta.position] = null;
            } else {
                const value = typeof meta.value !== "undefined" ? meta.value : null;
                const formatter = meta.type && Object.prototype.hasOwnProperty.call(formatters, meta.type) ? formatters[meta.type] : formatters.raw;
                const formattedValue = value === null ? null : formatter(meta.value);

                fragments[meta.position] = formattedValue === null ? null : [meta.name, formattedValue].join("/");
            }
        });
        fragments = fragments.filter(f => f);
        if (fragments.length) {
            url.pathname = [url.pathname].concat(fragments).join("/");
        }

        // Clean URL query from all
        url.search = "";
        // Update URL query
        Array.from(this.queryMetadada.values()).forEach(meta => {
            const value = typeof meta.value !== "undefined" ? meta.value : null;
            if (value === null) {
                url.searchParams.delete(meta.name);
            } else {
                const formatter = meta.type && Object.prototype.hasOwnProperty.call(formatters, meta.type) ? formatters[meta.type] : formatters.raw;
                url.searchParams.set(meta.name, formatter(value));
            }
        });

        return url;
    }
}

export default UrlBuilder;
