declare namespace TextСounter {
    type Selector = string | HTMLElement | JQuery;
    type Callback = (el: Selector) => void;

    interface Options {
        /**
         * Count characters or words.
         *
         * @default "character"
         */
        type?: "character" | "word";
        /**
         * Minimum number of characters/words
         *
         * @default 0
         */
        min?: number;
        /**
         * Maximum number of characters/words, -1 for unlimited, "auto" to use `maxlength` attribute,
         * "autocustom" to use a custom attribute for the length (must set `autoCustomAttr`)
         *
         * @default 200
         */
        max: number | "auto" | "autocustom";
        /**
         * Custom attribute name with the counter limit if the max is `autocustom`
         *
         * @default "counterlimit"
         */
        autoCustomAttr?: string;
        /**
         * HTML element to wrap the text count in
         *
         * @default "div"
         */
        countContainerElement?: string;
        /**
         * Class applied to the `countContainerElement`
         *
         * @default "text-count-wrapper"
         */
        countContainerClass?: string;
        /**
         * Class applied to the counter message
         *
         * @default "text-count-message"
         */
        textCountMessageClass?: string;
        /**
         * Class applied to the counter length (the count number)
         *
         * @default "text-count"
         */
        textCountClass?: string;
        /**
         * Error class appended to the input element if error occurs
         *
         * @default "error"
         */
        inputErrorClass?: string;
        /**
         * Error class appended to the countContainerElement if error occurs
         *
         * @default "error"
         */
        counterErrorClass?: string;
        /**
         * Counter text
         *
         * @default "Total Count: %d"
         */
        counterText?: string;
        /**
         * Error text element
         *
         * @default "div"
         */
        errorTextElement?: string;
        /**
         * Error message for minimum not met,
         *
         * @default "Minimum not met"
         */
        minimumErrorText?: string;
        /**
         * Error message for maximum range exceeded,
         *
         * @default "Maximum exceeded"
         */
        maximumErrorText?: string;
        /**
         * Display error text messages for minimum/maximum values
         *
         * @default true
         */
        displayErrorText?: boolean;
        /**
         * Stop further text input if maximum reached
         *
         * @default true
         */
        stopInputAtMaximum?: boolean;
        /**
         * Count spaces as character (only for "character" type)
         *
         * @default false
         */
        countSpaces?: boolean;
        /**
         * If the counter should deduct from maximum characters/words rather than counting up
         *
         * @default false
         */
        countDown?: boolean;
        /**
         * Count down text
         *
         * @default "Remaining: %d"
         */
        countDownText?: string;
        /**
         * Count extended UTF-8 characters as 2 bytes (such as Chinese characters)
         *
         * @default false
         */
        countExtendedCharacters?: boolean;
        /**
         * Count carriage returns/newlines as 2 characters
         *
         * @default false
         */
        twoCharCarriageReturn?: boolean;
        /**
         * Display text overflow element
         *
         * @default false
         */
        countOverflow?: boolean;
        /**
         * Count overflow text
         *
         * @default "Maximum %type exceeded by %d"
         */
        countOverflowText?: string;
        /**
         * Class applied to the count overflow wrapper
         *
         * @default "text-count-overflow-wrapper"
         */
        countOverflowContainerClass?: boolean;
        /**
         * Maximum number of characters/words above the minimum to display a count
         *
         * @default -1
         */
        minDisplayCutoff?: number;
        /**
         * Maximum number of characters/words below the maximum to display a count
         *
         * @default -1
         */
        maxDisplayCutoff?: number;

        /**
         * Fires when counter under max limit.
         */
        maxunder?: Callback;
        /**
         * Fires when counter under min limit.
         */
        minunder?: Callback;
        /**
         * Fires when the counter hits the maximum word/character count.
         */
        maxcount?: Callback;
        /**
         * Fires when the counter hits the minimum word/character count.
         */
        mincount?: Callback;
        /**
         * Fires after the counter is initially setup.
         */
        init?: Callback;
    }

    interface Methods {
        init(): void;

        checkLimits(e: JQuery.Event): void;

        textCount(text?: string): number;

        wordCount(text?: string): number;

        characterCount(text?: string): number;

        twoCharCarriageReturnCount(text?: string): number;

        setCount(count: number | string): void;

        setErrors(type: string): void;

        setOverflowMessage(): void;

        removeOverflowMessage(): void;

        showMessage($selector: JQuery<HTMLElement>): void;

        hideMessage($selector: JQuery<HTMLElement>): void;

        clearErrors(type: string): void;
    }

    enum Events {
        /**
         * This event fires immediately key was released in the lement.
         */
        keyup = "keyup.textcounter",

        /**
         * This event is fired when element was clicked.
         */
        click = "click.textcounter",

        /**
         * This event is fired immediately when element lost focus.
         */
        blur = "blur.textcounter",

        /**
         * This event is fired when the element is focused.
         */
        focus = "focus.textcounter",

        /**
         * This event is fired when text is changed in the element.
         */
        change = "change.textcounter",

        /**
         * This event is fired when text is pastedin the element.
         */
        paste = "paste.textcounter",
    }
}

interface JQuery {
    textcounter(options?: TextСounter.Options): this;
}
