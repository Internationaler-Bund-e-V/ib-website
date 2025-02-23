const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // .setPublicPath('/typo3conf/ext/ib_template/Resources/Public/build')
    // only needed for CDN's or sub-directory deploy
    // .setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('bv_bbe', './packages/bv_bbe/Resources/Public/JavaScript/app.js')
    .addEntry('ib_srb', './packages/ib_srb/Resources/Public/JavaScript/app.ts')
    .addEntry('ib_template', './packages/ib_template/Resources/Public/JavaScript/app.ts')
    .addEntry('ibcontent', './packages/ibcontent/Resources/Public/JavaScript/app.ts')
    .addEntry('ibjobs', './packages/ibjobs/Resources/Public/JavaScript/app.js')
    .addEntry('ibsearch', './packages/ibsearch/Resources/Public/JavaScript/app.ts')
    .addEntry('ib_galerie', './packages/ib_galerie/Resources/Public/JavaScript/app.ts')
    .addEntry('ib_formbuilder_frontend', './packages/ib_formbuilder/Resources/Public/JavaScript/frontend/app.js')
    //.addEntry('ib_formbuilder_backend', './packages/ib_formbuilder/Resources/Public/JavaScript/backend/app.ts')

    .addEntry('fwd', './packages/ibcontent/Resources/Public/JavaScript/fwd-vue/src/main.js')
    .addEntry('osmmap', './packages/ibcontent/Resources/Public/JavaScript/osmmap-vue/src/main.js')

    .addStyleEntry('ib_template_rte', './packages/ib_template/Resources/Public/Css/rte.scss')
    .addStyleEntry('ib_dataprivacy', './packages/ib_dataprivacy/Resources/Public/Css/app.scss')
    .addStyleEntry('ib_cmt', './packages/ib_cmt/Resources/Public/Css/app.scss')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()

    // .enableBuildNotifications()

    .enableSourceMaps(!Encore.isProduction())

    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-transform-class-properties');
        config,sourceType = 'unambiguous';
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    .configureImageRule({
        // tell Webpack it should consider inlining
        type: 'asset',
        //maxSize: 4 * 1024, // 4 kb - the default is 8kb
    })

    .configureFontRule({
        type: 'asset',
        //maxSize: 4 * 1024
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    .enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment if you use Vue.js
    .enableVueLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    .enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    .addAliases({
        'ol/control/Control': 'ol/control/Control.js',
        'ol/MapBrowserEvent': 'ol/MapBrowserEvent.js'
    });

module.exports = Encore.getWebpackConfig();
