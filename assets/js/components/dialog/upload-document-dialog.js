import openRemoteDialog from "@src/components/dialog/remote-dialog";

/**
 * Show document upload dialog.
 *
 * @param {JQuery} button
 */
export default function uploadDocumentDialog(button) {
    const url = button.data("url") ?? null;
    if (!url) {
        throw new ReferenceError("The URL value si required. Add the `data-url` attribute to the node.");
    }

    return openRemoteDialog(
        url,
        {
            type: "type-light",
            size: "size-wide",
            closeIcon: '<i class="ep-icon ep-icon_remove-stroke"></i>',
            closable: true,
            cssClass: "info-bootstrap-dialog inputs-40",
        },
        "Upload the document"
    );
}
