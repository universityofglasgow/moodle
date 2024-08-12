const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "/local/gugrades/ui/dist/",
  //indexPath: "index.php",
  filenameHashing: false,

  configureWebpack: {
    externals: {
      jquery: 'window.jQuery'
    }
  },
})
