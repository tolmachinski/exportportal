import EventHub from "@src/event-hub";
import { downloadDocuments } from "@src/documents/common";

/**
 * Fragment entrypoint.
 *
 * @param {number} envelopeId
 * @param {string} downloadUrl
 */
export default async (envelopeId, downloadDocumentUrl) => {
    EventHub.off("documents:details:download-documents");
    EventHub.on("documents:details:download-documents", (e, button) => downloadDocuments(envelopeId, button.data("document" || null), downloadDocumentUrl));
};
