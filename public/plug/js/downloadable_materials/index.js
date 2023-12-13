// Show share popup
var showDMShareModal = function(element){
    open_result_modal({
        title: "Share this with a friend",
        subTitle: "Share the downloadable material with a friend",
        content: $(element).data("link"),
        isAjax: true,
        closable: true,
        validate: true,
        classes: "bootstrap-dialog--unset-scroll",
        type: "info",
        buttons: [],
    });

    setTimeout(function(){
        enableFormValidation($(".validengine"))
    }, 1000)
};

// Submit share popup request
var submitDMShareModal = function(form) {
    const element = $(form);

    element.find("button[type=submit]").addClass("disabled");

    postRequest(globalThis.__site_url + "downloadable_materials/ajaxShareAdministration/create", element.serialize())
        .then(function(response) {
            if (response.mess_type === "success") {
                systemMessages(response.message, response.mess_type);

                bootstrapDialogCloseAll();
            }
        })
        .catch(onRequestError)
        .finally(function(){
            element.find("button[type=submit]").removeClass("disabled");
        });
};

// Adaptive function to change position of cover image if content height > cover height
var callAdaptivePosition = function() {
    var cover = $("#js-dwn-materials-cover");
    if (globalThis.matchMedia("(min-width: 601px)").matches && $("#js-dwn-materials-info").outerHeight() > cover.outerHeight()) {
        if (!cover.hasClass("animated")) {
            cover.addClass("animated");
            cover.css("top", cover.css("top"));
            setTimeout(function() {
                cover.css("top", "85px");
            }, 100);
        }
    } else {
        cover.removeClass("animated");
    }
};

var downloadMaterial = function(btn) {
    CustomFileSaver.saveAs(globalThis.__site_url + "downloadable_materials/download/" + $(btn).data("id"));
};

$(function(){
    callAdaptivePosition();

    var resizeTimout;
    $(window).on("resize", function () {
        clearTimeout(resizeTimout);
        resizeTimout = setTimeout(function() {
            callAdaptivePosition();
        }, 300);
    });

    var checkFileSaver;
    checkFileSaver = setInterval(function(){
        if(typeof CustomFileSaver !== "undefined"){
            clearInterval(checkFileSaver);
            const needDownload = $("#needDownload");
            if (needDownload.length) {
                globalThis.history.pushState({}, globalThis.document.title || "", $("#js-dwn-download").data("href"));
                downloadMaterial($("#js-dwn-download"));
                needDownload.remove();
            }
        }
    }, 500)
});
