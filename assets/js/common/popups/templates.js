const IE = navigator.userAgent.match(/msie/i);

export const WRAP = '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>';
export const IMAGE = '<img class="fancybox-image" src="{href}" alt="img" />';
export const IFRAME = '<iframe id="fancybox-frame{rnd}" name="fancybox-frame{rnd}" class="fancybox-iframe bd-none" vspace="0" hspace="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen' + (IE ? ' allowtransparency="true"' : '') + '></iframe>';
export const ERROR = '<p class="fancybox-error">{{ERROR}}</p>';
export const NEXT = '<i class="fancybox-nav fancybox-next"><span title="{{NEXT}}"></span></i>';
export const PREV = '<i class="fancybox-nav fancybox-prev"><span title="{{PREVIOUS}}"></span></i>';
export const LOADING = '<div id="fancybox-loading"><div></div></div>';
export const CLOSE_BTN = '<a title="{{CLOSE}}" class="pull-right js-close-fancybox" data-message="{{CLOSE_MESSAGE}}"><span class="ep-icon ep-icon_remove-stroke"></span></a>';
export const TEMPLATES = {
    wrap: WRAP,
    image: IMAGE,
    iframe: IFRAME,
    error: ERROR,
    next: NEXT,
    prev: PREV,
    loading: LOADING,
    closeBtn: CLOSE_BTN,
};
