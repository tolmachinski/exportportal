/**
 * The emitter for JQuery element.
 */
export default class Emitter {
    /**
     * @param {JQuery} element
     */
    constructor(element) {
        this.element = element;
    }

    /**
     * Emit the event.
     *
     * @param {string} name
     * @param {Array<any>} args
     */
    emit(name, args = []) {
        this.element.trigger(`epd-uploader:${name}`, args || []);
    }
}
