class RequestError extends Error {
    constructor(message, type = "error", isGeneric = false) {
        super(message);

        this.isGeneric = isGeneric;
        this.messageType = type;
    }
}

export default RequestError;
