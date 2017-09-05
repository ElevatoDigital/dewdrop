const webpack                   = require('webpack')
const path                      = require('path')
const {getIfUtils, removeEmpty} = require('webpack-config-utils')
const CopyWebpackPlugin         = require('copy-webpack-plugin')
const ConcatPlugin              = require('webpack-concat-plugin')

module.exports = env => {
    const {ifProd, ifNotProd} = getIfUtils(env)

    const config = {
        externals: {
            'Modernizr': 'Modernizr',
            'key': 'key',
            'jquery': 'jQuery',
            '$': 'jQuery'
        },
        context: __dirname,
        resolve: {
            extensions: ['.js', '.jsx', '.json']
        },
        stats: {
            colors: true
        },
        devtool: ifProd('source-map', 'eval')
    }

    let webConfig  = Object.assign({}, config)
    let nodeConfig = Object.assign({}, config)

    webConfig.entry = {
        core: './www/src/js/core'
    }
    webConfig.output = {
        path: path.join(__dirname, '/www/dist/js'),
        filename: '[name].js'
        //publicPath: This is set at runtime in dewdrop.js https://github.com/webpack/docs/wiki/configuration#outputpublicpath
    }
    webConfig.plugins = removeEmpty([
        new webpack.DefinePlugin({ // required for summernote
            "require.specified": "require.resolve"
        }),
        ifProd(new webpack.optimize.UglifyJsPlugin({
            beautify: false,
            mangle: {
                screw_ie8: true,
                keep_fnames: true
            },
            compress: {
                screw_ie8: true,
                warnings: false
            },
            comments: false,
            sourceMap: true
        })),
        // Modernizr hack (just copy the file *make sure to exclude it where applicable)
        new CopyWebpackPlugin([
            {
                from: path.resolve(__dirname, 'www/src/js/modernizr.custom.63049.js'),
                to: path.resolve(__dirname, 'www/dist/js/modernizr.custom.63049.js')
            }
        ]),
        // Automatically bundle node_module sourced files into a vendor file
        ifProd(new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: function(module){
                return module.context && module.context.indexOf('node_modules') !== -1;
            }
        })),
        /*
         * Create a file that combines all legacy vendor code that was originally globally required.
         */
        new ConcatPlugin({
            uglify: ifProd(true, false),
            fileName: 'vendor-legacy.js',
            filesToConcat: [
                './node_modules/keymaster/keymaster.js',
                './node_modules/moment/min/moment-with-locales.min.js',
                './node_modules/velocity-animate/velocity.min.js',
                './node_modules/velocity-animate/velocity.ui.min.js',
                './node_modules/underscore/underscore.js',
                './node_modules/backbone/backbone-min.js'
            ]
        })
    ])
    webConfig.module = {
        rules: [
            {
                include: path.resolve(__dirname, 'www/src/js'),
                loader: 'babel-loader',
                options: {
                    plugins: [
                        'syntax-dynamic-import'
                    ]
                }
            },
            {
                test: [/\.txt$/, /\.html$/],
                use: 'raw-loader'
            }
        ]
    }

    nodeConfig.entry = {
        dewdrop: './www/src/js/dewdrop'
    }
    nodeConfig.target = 'node'
    nodeConfig.output = {
        path: path.join(__dirname, '/www/dist/node'),
        filename: '[name].js',
        libraryTarget: 'umd'
        //publicPath: This is set at runtime in dewdrop.js https://github.com/webpack/docs/wiki/configuration#outputpublicpath
    }
    nodeConfig.plugins = removeEmpty([
        new webpack.DefinePlugin({ // required for summernote
            "require.specified": "require.resolve"
        })
    ])
    nodeConfig.module =  {
        rules: [
            {
                include: path.resolve(__dirname, 'www/src/js'),
                loader: 'babel-loader',
                options: {
                    presets: [
                        'stage-0',
                        'es2015'
                    ],
                    plugins: [
                        'add-module-exports',
                        'transform-es2015-modules-umd',
                        'dynamic-import-node'
                    ]
                }
            },
            {
                test: [/\.txt$/, /\.html$/],
                use: 'raw-loader'
            }
        ]
    }

    return [webConfig, nodeConfig]
}
