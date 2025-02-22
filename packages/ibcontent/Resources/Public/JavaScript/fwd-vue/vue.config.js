module.exports = {
  devServer: {
    //proxy: 'https://redaktionstool.ddev.site',
    //proxy: 'https://redaktion-relaunch.ddev.site',
    proxy: 'https://ib.ddev.site',

  },
  publicPath: '/packages/ibcontent/Resources/Public/build',
  configureWebpack: config => {
    config.optimization.splitChunks = false
    if (process.env.NODE_ENV === "production") {
      config.output.filename = 'js/searchFWDApp.min.js'
      config.output.chunkFilename = 'js/vuechunk.min.js'

    } else {
      config.output.filename = 'js/searchFWDApp.min.js'
      config.output.chunkFilename = 'js/searchFWDApp.min.js';
    }
  },
  css: { extract: false }

}
