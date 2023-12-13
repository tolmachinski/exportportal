var widgets = document.getElementsByClassName("ep-widget");

for(var i = 0; i < widgets.length; i++)
EPWidget(widgets[i]);

function EPWidget(divEl) {
    var height = divEl.getAttribute('data-height') || "200px",
    width = divEl.getAttribute('data-width') || "100%",
    iframe = document.createElement('iframe');

    divEl.style.width = width;
    divEl.style.height = height;

    iframe.src = '<?php echo __SITE_URL; ?>dashboard/seller_widget?key=' + divEl.getAttribute("data-key");
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    iframe.style.border = "none";

    divEl.appendChild(iframe);
}
