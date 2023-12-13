const { resolve } = require("path");

const baseDirectory = "./";
const shippersBaseDirectory = "../shippers";
const sourceDirectory = "./assets/fonts/generate";
const publicViewDirectory = "./tinymvc/myapp/views/new/epicons";
const adminViewDirectory = "./tinymvc/myapp/views/admin/epicons";
const shipperViewDirectory = "./assets/import";
const makeWebfontOptions = (source, directory, cssDirectory, htmlDirectory, overrideOptions = {}) => {
    return {
        icons: {
            // SVG files to read in
            src: `${source}/svg/*.svg`,
            // Location to output fonts (expanded via brace expansion)
            dest: directory,
            // Location to output CSS variables
            destCss: cssDirectory,
            // options
            options: {
                engine: "node",
                htmlDemo: true,
                relativeDemoHtmlFontPath: "./",
                relativeFontPath: "./",
                // Location to input template HTML demo
                htmlDemoTemplate: `${source}/template/demo.html`,
                font: "iconsEP",
                stylesheet: "scss",
                types: "woff2,woff",
                hashes: true,
                syntax: "bem",
                templateOptions: {
                    baseClass: "ep-icon",
                    classPrefix: "ep-icon_",
                    mixinPrefix: "ep-icon_",
                },
                // Add override options
                ...overrideOptions,
                // Location to output HTML demo
                destHtml: htmlDirectory,
            },
        },
    };
};

module.exports = grunt => {
    const shippersDirectory = grunt.option("shipper-dir") || shippersBaseDirectory;
    const target = grunt.option("target") || "public";

    let webfontOptions;
    switch (target) {
        case "admin":
            webfontOptions = makeWebfontOptions(
                resolve(`${sourceDirectory}/ep-icons-admin`),
                resolve(`${baseDirectory}/public/css/fonts/admin`),
                resolve(`${baseDirectory}/public/sass_admin/import`),
                resolve(`${baseDirectory}/${adminViewDirectory}`),
                {
                    relativeDemoHtmlFontPath: "public/css/fonts/admin",
                    relativeFontPath: "fonts/admin/",
                }
            );

            break;

        case "shipper":
            webfontOptions = makeWebfontOptions(
                resolve(`${sourceDirectory}/ep-icons-public`),
                resolve(`${shippersDirectory}/assets/fonts`),
                resolve(`${shippersDirectory}/assets/scss/import`),
                resolve(`${shippersDirectory}/${shipperViewDirectory}`),
                {
                    relativeDemoHtmlFontPath: "assets/fonts/",
                    relativeFontPath: "assets/fonts/",
                }
            );

            break;

        case "public":
        default:
            webfontOptions = makeWebfontOptions(
                resolve(`${sourceDirectory}/ep-icons-public`),
                resolve(`${baseDirectory}/assets/fonts`),
                resolve(`${baseDirectory}/assets/scss/import`),
                resolve(`${baseDirectory}/${publicViewDirectory}`),
                {
                    relativeDemoHtmlFontPath: "assets/fonts/",
                    relativeFontPath: "assets/fonts/",
                }
            );

            break;
    }

    grunt.initConfig({ webfont: webfontOptions });
    grunt.loadNpmTasks("grunt-webfonts");
    grunt.registerTask("default", ["webfont"]);
};
