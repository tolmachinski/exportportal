const closeBtn = `
    <button title="{{CLOSE}}" class="fancybox-close-icon js-close-modal-btn" data-message="{{CLOSE_MESSAGE}}">
        <span class="ep-icon ep-icon_remove-stroke"></span>
    </button>
`;

const baseTpl = `
    <div class="fancybox-container" role="dialog" tabindex="-1">
        <div class="fancybox-bg"></div>
        <div class="fancybox-inner">
            <div class="fancybox-stage"></div>
        </div>
    </div>
`;

const dialogContentTpl = `
    <div class="fancybox-dialog__header">
        <div class="fancybox-dialog__header-wr">
            <div class="fancybox-dialog__icon">{{ICON}}</div>
            <h2 class="fancybox-dialog__title">{{TITLE}}</h2>
            {{SUBTITLE}}
        </div>
    </div>
    {{BODY_WRAPPER}}
    {{FOOTER_WRAPPER}}
`;

const errorTpl = `<div class="fancybox-error"><p>{{ERROR}}</p></div>`;

const modalHeaderTpl = `<div class="fancybox-header">{{CONTENT}}</div>`;

const modalContentBodyTpl = `<div class="fancybox-body">{{CONTENT}}</div>`;

// eslint-disable-next-line import/prefer-default-export
export const TEMPLATES = {
    baseTpl,
    modalHeaderTpl,
    modalContentBodyTpl,
    dialogContentTpl,
    errorTpl,
    btnTpl: {
        closeBtn,
    },
};
