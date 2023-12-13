/* eslint-disable no-undef */

const { parallel, dest, src, watch, series } = require("gulp");
const autoprefixer = require("gulp-autoprefixer");
const sourcemaps = require("gulp-sourcemaps");
const sassLoader = require("gulp-sass")(require("sass"));
const cleanCSS = require("gulp-clean-css");
const uglify = require("gulp-uglify-es").default;
const concat = require("gulp-concat");
const colors = require("ansi-colors");
const log = require("fancy-log");

const scssOutput = "./public/css";
const scssSrcEP = ["./public/sass/**/*.scss", "./public/sass_other/**/*.scss"];
const scssSrcAll = [
    "./public/sass/**/*.scss",
    "./public/sass_other/**/*.scss",

    "./public/sass_admin/**/*.scss",
    "./public/sass_bloggers/**/*.scss",

    "./public/plug/jquery-validation-engine-2-6-2/scss/*.scss",
    "./public/plug/croppie-master/*.scss",
    "./public/plug/jquery-fancybox-2-1-7/scss/*.scss",
];
const jsAdminSrc = [
    "./public/plug_admin/js/lang_new.js",
    "./public/plug_admin/jquery-1-12-0/jquery-1.12.0.min.js",
    "./public/plug_admin/jquery-validation-engine-2-6-1/js/jquery.validationEngine.js",
    "./public/plug_admin/jquery-datatables-1-10-12/jquery.dataTables.min.js",
    "./public/plug_admin/bootstrap-rating-1-3-1/bootstrap-rating.min.js",
    "./public/plug_admin/icheck-1-0-2/js/icheck.min__pc.js",
    "./public/plug_admin/bootstrap-3-3-7/js/bootstrap.min.js",
    "./public/plug_admin/select2-4-0-3/js/select2.min.js",
    "./public/plug_admin/jquery-ui-1-12-1-custom/jquery-ui.min.js",
    "./public/plug_admin/jquery-dtfilters/jquery.dtFilters.js",
    "./public/plug_admin/js/js.cookie.js",
    "./public/plug_admin/bootstrap-dialog/js/bootstrap-dialog.js",
    "./public/plug_admin/jquery-fancybox-2-1-5/js/jquery.fancybox.js",
    "./public/plug_admin/jquery-qtip-2-2-0/jquery.qtip.min.js",
    "./public/plug_admin/clipboard-1-5-9/clipboard.min.js",
    "./public/plug_admin/tooltipster/tooltipster.bundle.min.js",
    "./public/plug_admin/textcounter-0-3-6/textcounter.js",
    "./public/plug_admin/js/scripts_general.js",
    "./public/plug_admin/js/admin.scripts.js",
];
const jsEPLDashboardSrc = [
    "./public/plug/js/lang_new.js",
    "./public/plug/jquery-1-12-0/jquery-1.12.0.min.js",
    "./public/plug/jquery-mousewheel-3-1-12/jquery.mousewheel.min.js",
    "./public/plug/popper-1-11-0/popper.min.js",
    "./public/plug/bootstrap-4-1-1/js/src/util.js",
    "./public/plug/bootstrap-4-1-1/js/src/tooltip.js",
    "./public/plug/bootstrap-4-1-1/js/src/popover.js",
    "./public/plug/bootstrap-4-1-1/js/src/modal.js",
    "./public/plug/bootstrap-4-1-1/js/src/tab.js",
    "./public/plug/bootstrap-4-1-1/js/src/carousel.js",
    "./public/plug/ofi/ofi.min.js",
    "./public/plug/jquery-fancybox-2-1-7/js/jquery.fancybox.js",
    "./public/plug/jquery-validation-engine-2-6-2/js/jquery.validationEngine.js",
    "./public/plug/textcounter-0-3-6/textcounter.js",
    "./public/plug/jquery-jscrollpane-2-0-20/jquery.jscrollpane.min-mod.js",
    "./public/plug/js/js.cookie.js",
    "./public/plug/select2-4-0-3/js/select2.min.js",
    "./public/plug/bootstrap-dialog-1-35-4/js/bootstrap-dialog.js",
    "./public/plug/bootstrap-rating-1-3-1/bootstrap-rating.min.js",
    "./public/plug/jquery-ui-1-12-1-custom/jquery-ui.min.js",
    "./public/plug/resizestop-master/jquery.resizestop.min.js",
    "./public/plug/lazyloading/index.js",
    "./public/plug/js/scripts_general.js",
    "./public/plug/js/scripts_new.js",
];
const jsUserNewSrc = [
    "./public/plug/js/lang_new.js",
    "./public/plug/jquery-1-12-0/jquery-1.12.0.min.js",
    "./public/plug/jquery-mousewheel-3-1-12/jquery.mousewheel.min.js",
    "./public/plug/popper-1-11-0/popper.min.js",
    "./public/plug/bootstrap-4-1-1/js/src/util.js",
    "./public/plug/bootstrap-4-1-1/js/src/tooltip.js",
    "./public/plug/bootstrap-4-1-1/js/src/popover.js",
    "./public/plug/bootstrap-4-1-1/js/src/modal.js",
    "./public/plug/bootstrap-4-1-1/js/src/tab.js",
    "./public/plug/bootstrap-4-1-1/js/src/carousel.js",
    "./public/plug/ofi/ofi.min.js",
    "./public/plug/bootstrap-tabdrop-master/js/bootstrap-tabdrop.js",
    "./public/plug/jquery-fancybox-2-1-7/js/jquery.fancybox.js",
    "./public/plug/jquery-validation-engine-2-6-2/js/jquery.validationEngine.js",
    "./public/plug/textcounter-0-3-6/textcounter.js",
    "./public/plug/jquery-jscrollpane-2-0-20/jquery.jscrollpane.min-mod.js",
    "./public/plug/js/js.cookie.js",
    "./public/plug/select2-4-0-3/js/select2.min.js",
    "./public/plug/bootstrap-dialog-1-35-4/js/bootstrap-dialog.js",
    "./public/plug/bootstrap-rating-1-3-1/bootstrap-rating.min.js",
    "./public/plug/jquery-ui-1-12-1-custom/jquery-ui.min.js",
    "./public/plug/jquery-bxslider-4-2-12/jquery.bxslider.js",
    "./public/plug/resizestop-master/jquery.resizestop.min.js",
    "./public/plug/jquery-multiple-select-1-1-0/js/jquery.multiple.select.js",
    "./public/plug/jquery-tags-input-master/jquery.tagsinput.min.js",
    "./public/plug/i-observers/i-observers.js",
    "./public/plug/lazyloading/index.js",
    "./public/plug/slick-1-8-1/js/slick.min.js",
    "./public/plug/swiper-6-7-1/js/swiper-bundle.min.js",
    "./public/plug/js/scripts_general.js",
    "./public/plug/js/scripts_new.js",
];
const jsBloggersSrc = [
    "./public/plug_bloggers/jquery-1-12-0/jquery-1.12.0.min.js",
    "./public/plug_bloggers/js/js.cookie.js",
    "./public/plug_bloggers/jquery-validation-engine-2-6-3/js/jquery.validationEngine.js",
    "./public/plug_bloggers/jquery-fancybox-2-1-5/js/jquery.fancybox.js",
    "./public/plug_bloggers/select2-4-0-3/js/select2.min.js",
    "./public/plug_bloggers/noty-3.2.0-beta/noty.min.js",
    "./public/plug_bloggers/tinymce-4-8-3/tinymce.min.js",
    "./public/plug_bloggers/jquery-fileupload-5-42-3/jquery.ui.widget.js",
    "./public/plug_bloggers/jquery-fileupload-5-42-3/jquery.iframe-transport.js",
    "./public/plug_bloggers/jquery-fileupload-5-42-3/jquery.fileupload.js",
    "./public/plug_bloggers/jquery-fileupload-5-42-3/jquery.fileupload-process.js",
    "./public/plug_bloggers/jquery-fileupload-5-42-3/jquery.fileupload-validate.js",
    "./public/plug_bloggers/icheck-1-0-2/js/icheck.min.js",
    "./public/plug_bloggers/js/lang_new.js",
    "./public/plug_bloggers/js/bloggers.js",
];

const minifyAdminScripts = function () {
    return src(jsAdminSrc)
        .on("error", function onError(err) {
            log(colors.red("[Error]"), err.toString());
        })
        .pipe(uglify())
        .pipe(concat("all-admin-min.js"))
        .pipe(dest("./public/plug_compiled"));
};

const minifyGeneralScrips = function () {
    return src(jsUserNewSrc)
        .pipe(uglify())
        .pipe(concat("all-user-new-min.js"))
        .pipe(dest("./public/plug_compiled"))
        .on("error", function onError(err) {
            log(colors.red("[Error]"), err.toString());
        });
};

const minifyBloggersScrips = function () {
    return src(jsBloggersSrc)
        .pipe(uglify())
        .pipe(concat("all-bloggers-min.js"))
        .pipe(dest("./public/plug_compiled"))
        .on("error", function onError(err) {
            log(colors.red("[Error]"), err.toString());
        });
};

const minifyEPLDashboardScrips = function () {
    return src(jsEPLDashboardSrc)
        .pipe(uglify())
        .pipe(concat("all-epl-dashboard-min.js"))
        .pipe(dest("./public/plug_compiled"))
        .on("error", function onError(err) {
            log(colors.red("[Error]"), err.toString());
        });
};

const minifyAllScrips = series(minifyAdminScripts, minifyGeneralScrips, minifyBloggersScrips, minifyEPLDashboardScrips);

const buildGeneralStyles = function () {
    return src(scssSrcEP)
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(sassLoader().on("error", sassLoader.logError))
        .pipe(
            autoprefixer({
                cascade: false,
            })
        )
        .pipe(cleanCSS())
        .pipe(sourcemaps.write("./"))
        .pipe(dest(scssOutput));
};

const watchGeneralStyles = function () {
    watch(scssSrcEP.concat(["./assets/scss/**/*.scss"]), parallel("sass"));
};

const buildAllStyles = function () {
    return src(scssSrcAll)
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(sassLoader().on("error", sassLoader.logError))
        .pipe(
            autoprefixer({
                cascade: false,
            })
        )
        .pipe(sourcemaps.write("./"))
        .pipe(dest(scssOutput));
};

const watchAllStyles = function () {
    return watch(scssSrcAll.concat(["./assets/scss/**/*.scss"]), parallel("sassall"));
};

const buildAllStylesForProduction = function () {
    return src(scssSrcAll)
        .pipe(sassLoader().on("error", sassLoader.logError))
        .pipe(
            autoprefixer({
                cascade: false,
            })
        )
        .pipe(cleanCSS())
        .pipe(dest(scssOutput));
};

exports.sass = buildGeneralStyles;
exports["sass:watch"] = watchGeneralStyles;
exports.sassall = buildAllStyles;
exports["sassall:watch"] = watchAllStyles;
exports["sassall:prod"] = buildAllStylesForProduction;
exports.minbloggersjs = minifyBloggersScrips;
exports.minusernewjs = minifyGeneralScrips;
exports.minadminjs = minifyAdminScripts;
exports.minepldashboardjs = minifyEPLDashboardScrips;
exports.minjs = minifyAllScrips;
exports.default = series(minifyAllScrips, buildAllStylesForProduction);
