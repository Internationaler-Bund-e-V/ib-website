module.exports = {
  devServer: {
    //proxy: 'https://redaktionstool.ddev.site',
    //proxy: 'https://ib.ddev.site',

  },
  //publicPath: '/typo3conf/ext/ibcontent/Resources/Public/dist',
  publicPath: '/typo3conf/ext/ibcontent/osmmap-vue/dist',
  configureWebpack: config => {
    config.optimization.splitChunks = false
    if (process.env.NODE_ENV === "production") {
      config.output.filename = 'js/osmMap.min.js'
      config.output.chunkFilename = 'js/osmMap.min.js'

    } else {
      config.output.filename = 'js/osmMap.min.js'
      config.output.chunkFilename = 'js/osmMap.min.js';
    }
  },
  css: { extract: false },

}