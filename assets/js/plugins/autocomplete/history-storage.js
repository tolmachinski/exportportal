import { AUTOCOMPLETE_LOCAL_STORAGE_KEY, DEBUG } from "@src/common/constants";
import isSameArray from "@src/util/common/is-same-array";
import escapeRegExp from "@src/util/common/escape-reg-exp";
import htmlEscape from "@src/util/common/html-escape";

const localStorageKey = AUTOCOMPLETE_LOCAL_STORAGE_KEY || "EP_sch.ms";
const typeItems = "items";
export const types = { [typeItems]: 5 };
const postfixes = { [types[typeItems]]: "i" };

export default class Storage {
    constructor(key, entriesMaxAmount = 5, type) {
        if (!key) {
            throw new TypeError("The key is required");
        }

        this.key = key;
        this.entriesMaxAmount = entriesMaxAmount;
        this.type = type;
    }

    /**
     * It returns the local storage key for the given type
     * @param type - The type of the data you want to store.
     * @returns The localStorageKey is being returned with a postfix.
     */
    static getStorageKey(type) {
        const postfix = postfixes[type] || null;

        if (postfix === null) {
            return localStorageKey;
        }

        return `${localStorageKey}.${postfix}`;
    }

    /**
     * Reads the state.
     *
     * @returns {Promise<{read: array, meta: any}>}
     */
    async get(text) {
        const searchText = text || null;
        let state = { read: [], meta: {} };
        const savedValues = global.localStorage.getItem(this.key);

        if (savedValues !== null) {
            try {
                state = JSON.parse(savedValues);
            } catch (error) {
                // Failed to decode. That means that localStorage contains broken data.
                if (DEBUG) {
                    // eslint-disable-next-line no-console
                    console.error(error);
                }
            }
        }

        /* Filtering the records that are being read from localStorage and replacing the text with the bolded text. */
        if (searchText !== null) {
            const searchPattern = new RegExp(`^.*(${escapeRegExp(searchText)})(.*)?`, "gi");
            const replacePattern = new RegExp(`${escapeRegExp(searchText)}`, "gi");

            state.read = state.read
                .filter(record => record[0].match(searchPattern))
                .map(record => {
                    const newRecord = record;
                    newRecord[4] = record[0].replaceAll(replacePattern, match => `<b>${htmlEscape(match)}</b>`);

                    return newRecord;
                });
        }

        return state;
    }

    /**
     * It appends the given records to the list of records that have been read
     * @param {?Array<Array<any>>} records - An array of strings to append to the autocomplete list.
     * @returns The return value is a promise that resolves to the value of the save function.
     */
    async append(records) {
        const state = await this.get();
        const list = state.read || [];

        list.push(...(records || []));

        if (list.length > this.entriesMaxAmount) {
            list.splice(0, 1);
        }

        return this.save({ ...state, read: list });
    }

    /**
     * It prepends the given records to the list of read entries
     * @param {?Array<Array<any>>} records - Array of records to prepend to the list.
     * @returns The return value of the save method.
     */
    async prepend(records) {
        const state = await this.get();
        const list = state.read || [];

        list.unshift(...(records || []));

        if (list.length > this.entriesMaxAmount) {
            list.splice(this.entriesMaxAmount, 1);
        }

        return this.save({ ...state, read: list });
    }

    /**
     * It saves the state to localStorage
     * @param {Object} state - The state to save.
     */
    async save(state) {
        try {
            global.localStorage.setItem(this.key, JSON.stringify(state));
        } catch (error) {
            // Failed to save into localStorage.
            if (DEBUG) {
                // eslint-disable-next-line no-console
                console.error(error);
            }
        }
    }

    /**
     * It removes a record from the read array
     * @param {Array.<any>} record - The record to remove from the read list.
     */
    async remove(record) {
        const state = await this.get();
        const foundIndex = state.read.findIndex(value => isSameArray(value, record) || JSON.stringify(value) === JSON.stringify(record));

        if (foundIndex !== null && foundIndex !== -1) {
            state.read.splice(foundIndex, 1);

            this.save(state);
        }
    }

    /**
     * It changes the type of the storage object
     * @param {string} type - The type of storage you want to use.
     */
    changeType(type) {
        this.type = type || null;
        this.key = Storage.getStorageKey(this.type);
    }

    /**
     * It removes the item from local storage with the key that was passed to the constructor
     */
    async clear() {
        global.localStorage.removeItem(this.key);
    }
}
