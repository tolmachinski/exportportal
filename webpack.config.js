/* eslint-disable */
const Encore = require("@symfony/webpack-encore");
const Configs = require("./.webpack/config");
const Plugins = require("./.webpack/plugins");
const Loaders = require("./.webpack/loaders");
const Entries = require("./.webpack/entries");
const CacheGroups = require("./.webpack/cache-groups");

//#region Base configuration
Encore
    .setOutputPath(Configs.outputPath)
    .setPublicPath(Configs.publicPath)
    .configureFilenames(Configs.fileNames)
    .configureFontRule(Configs.fontRules)
    .configureImageRule(Configs.imageRules)
    .configureDefinePlugin(Configs.configureDefintions)
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableIntegrityHashes(Configs.isProduction)
    .enableVersioning(Configs.isProduction)
    .enableSourceMaps(!Configs.isProduction)
    .addAliases(Configs.pathAliases)
    .addExternals(Configs.externals)
    .copyFiles(Configs.fileCopies)
;

Loaders.forEach(loader => { Encore.addLoader(loader); });
Plugins.filter(f => f.enable).forEach(({ plugin }) => {
    if (Array.isArray(plugin)) {
        Encore.addPlugin(...plugin);
    } else {
        Encore.addPlugin(plugin);
    }
});

//#region Configure JS build
Encore
    .autoProvidejQuery()
    // .enableEslintLoader()
    .configureBabel(Configs.configureBabel, Configs.babelEncoreOptions)
;
//#endregion Configure JS build

//#region Configure CSS build
Encore
    .enableSassLoader()
    .enablePostCssLoader()
;
//#endregion Configure CSS build

//#region Optimization
Encore
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .configureSplitChunks(Configs.configureChunks)
    .configureTerserPlugin(Configs.configureTerser)
    .configureCssMinimizerPlugin(Configs.configureMinimizer)
;

CacheGroups.forEach(({ name, options }) => { Encore.addCacheGroup(name, options); });
//#endregion Optimization

//#region Entrypoints
Entries.forEach(({ name, src }) => { Encore.addEntry(name, src); });
//#endregion Entrypoints

const BaseConfigs = Encore.getWebpackConfig();
//#endregion Base configuration

module.exports = [BaseConfigs];
