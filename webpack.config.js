const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .splitEntryChunks()
    .enableSourceMaps(!Encore.isProduction())
    .enableSassLoader()
    .enablePostCssLoader()
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enableVersioning(Encore.isProduction())
    .addEntry('app', './assets/styles/app.scss')
    .addEntry('typed', './assets/js/typed.js')
;

module.exports = Encore.getWebpackConfig();
