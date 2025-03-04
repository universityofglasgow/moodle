const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: "/local/studentmygradesstaffview/ui/dist/",
  filenameHashing: false,

  configureWebpack: {
    externals: {
      jquery: 'window.jQuery'
    }
  },
})
