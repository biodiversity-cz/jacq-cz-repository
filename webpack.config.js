const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('htdocs/www/dist')
    .setPublicPath('/dist')
    .setManifestKeyPrefix('')
    .addEntry('app', './assets/application.js')
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .enablePostCssLoader()
    .configureBabel(()=> {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .autoProvideVariables({
        $: 'jquery',
        naja: ['naja', 'default'], //https://github.com/naja-js/naja/discussions/203
    });

module.exports = Encore.getWebpackConfig();
