export module "./index" {
    /**
     * Initialize validator.
     */
    export function initialize(): Promise<any>;

    /**
     * Validates the element.
     */
    export function validateElement(selector: HTMLElement | JQuery | JQuery[], options?: any): Promise<boolean>;

    /**
     * Enables provided form validation.
     */
    export function enableFormValidation(selector: HTMLElement | JQuery, options?: any, button?: HTMLElement | JQuery): Promise<JQuery>;

    /**
     * Disables the form validation.
     */
    export function disableFormValidation(selector: HTMLElement | JQuery): Promise<JQuery>;
}
