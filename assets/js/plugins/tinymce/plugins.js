import tinymce from "tinymce";

tinymce.PluginManager.add("charactercount", function (editor) {
    const self = this;
    let textcount = 0;
    const maxTextcount = editor.targetElm.dataset.max;
    let statusbar;
    let toReplaceText;
    let charactercouterReplacedtext;

    function update() {
        textcount = self.getCount();
        if (typeof maxTextcount !== "undefined") {
            charactercouterReplacedtext = [toReplaceText, textcount, maxTextcount];
            const textElement = statusbar.find(".charactercount")[0].$el[0];
            if (typeof textElement !== "undefined") {
                if (maxTextcount < textcount) {
                    textElement.classList.add("txt-red");
                } else {
                    textElement.classList.remove("txt-red");
                }
            }
        } else {
            charactercouterReplacedtext = [toReplaceText, textcount];
        }

        editor.theme.panel.find("#charactercount").text(charactercouterReplacedtext);
    }

    editor.on("init", () => {
        textcount = self.getCount();

        if (typeof maxTextcount !== "undefined") {
            toReplaceText = "Characters: {0}, max: {1} allowed";
            charactercouterReplacedtext = [toReplaceText, textcount, maxTextcount];
        } else {
            toReplaceText = "Characters: {0}";
            charactercouterReplacedtext = [toReplaceText, textcount];
        }

        statusbar = editor.theme.panel && editor.theme.panel.find("#statusbar")[0];
        if (statusbar) {
            window.setTimeout(() => {
                statusbar.insert(
                    {
                        type: "label",
                        name: "charactercount",
                        text: charactercouterReplacedtext,
                        classes: "charactercount",
                        disabled: editor.settings.readonly,
                    },
                    0
                );

                editor.on("setcontent beforeaddundo", update);

                editor.on("keyup", () => {
                    update();
                });
            }, 0);
        }
    });

    self.getCount = () => {
        const tx = editor.getContent({ format: "text" });
        let decodedStripped = tx;
        if (tx.length === 1 && tx.charCodeAt() === 10) {
            return 0;
        }

        // remove control characters
        // replace with empty space
        decodedStripped = decodedStripped.replace(/\xA0/g, " ");
        // replace with empty space
        decodedStripped = decodedStripped.replace(/\r/g, " ");
        // replace with space
        decodedStripped = decodedStripped.replace(/\n/g, " ");
        // replace with space
        decodedStripped = decodedStripped.replace(/\t/g, " ");
        // remove multiple spaces
        // decodedStripped = decodedStripped.replace(/ {2,}/g,' ');
        // decodedStripped = decodedStripped.trim();

        // var extended = decodedStripped.match(/[^\x00-\xff]/gi);

        // if (extended == null) {
        //     tc = decodedStripped.length;
        // } else {
        //     tc = decodedStripped.length + extended.length;
        // }

        return decodedStripped.length;
    };
});
