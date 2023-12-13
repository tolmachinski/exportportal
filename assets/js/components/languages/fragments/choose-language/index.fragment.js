import EventHub from "@src/event-hub";

export default async () => {
    const { callSocialModal } = await import("@src/components/languages/fragments/choose-language/methods");

    EventHub.on("languages:open-social-modal", (e, button) => {
        callSocialModal(button);
    });
};
