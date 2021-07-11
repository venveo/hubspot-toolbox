const path = require('path')
const webpack = require('webpack')
const { VueLoaderPlugin } = require('vue-loader')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')

module.exports = (env = {}) => ({
    mode: env.prod ? 'production' : 'development',
    devtool: env.prod ? 'source-map' : 'eval-cheap-module-source-map',
    entry: path.resolve(__dirname, './src/main.js'),
    output: {
        path: path.resolve(__dirname, './dist'),
        publicPath: 'https://craft3-plugindev.test:8080/dist/'
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'src'),
        }
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                use: 'vue-loader'
            },
            {
                test: /\.png$/,
                use: {
                    loader: 'url-loader',
                    options: { limit: 8192 }
                }
            },
            {
                test: /\.css$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader,
                        options: { hmr: !env.prod }
                    },
                    'css-loader'
                ]
            }
        ]
    },
    plugins: [
        new VueLoaderPlugin(),
        new MiniCssExtractPlugin({
            filename: '[name].css'
        }),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: 'true',
            __VUE_PROD_DEVTOOLS__: 'false'
        })
    ],
    devServer: {
        inline: true,
        headers: { "Access-Control-Allow-Origin": "*" },
        hot: true,
        stats: 'minimal',
        contentBase: __dirname,
        overlay: true,
        disableHostCheck: true,
        host: '0.0.0.0', port: 8080,
        public: '0.0.0.0:8080',
        allowedHosts: [
            'craft3-plugindev.test'
        ]
    }
})