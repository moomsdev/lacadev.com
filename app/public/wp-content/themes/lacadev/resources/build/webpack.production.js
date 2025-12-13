/**
 * External dependencies.
 */
const { ProvidePlugin, WatchIgnorePlugin } = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default;
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');
const CopyWebpackPlugin = require('copy-webpack-plugin');

/**
 * Internal dependencies.
 */
const utils = require('./lib/utils');
const configLoader = require('./config-loader');
const spriteSmith = require('./spritesmith');
const postcss = require('./postcss');

/**
 * Setup the environment.
 */
const { env: envName } = utils.detectEnv();

/**
 * Setup Babel loader.
 */
const babelLoader = {
    loader: 'babel-loader',
    options: {
        cacheDirectory: false,
        comments: false,
        presets: [
            '@babel/preset-env'
        ],
    },
};

/**
 * Setup MiniCssExtractPlugin for CSS.
 */
const miniCss = new MiniCssExtractPlugin({
    filename: 'styles/[name].css',
});

/**
 * Setup Webpack plugins.
 */
const plugins = [
    new WatchIgnorePlugin({
        paths: [/node_modules/, /dist/],
    }),
    new ProvidePlugin({
        // $: 'jquery',
        // jQuery: 'jquery'
    }),
    miniCss,
    spriteSmith,
    // TEMPORARILY DISABLED: imagemin-webpack-plugin has compatibility issues with Webpack 5
    // Will use compress-images-webpack-plugin or sharp later
    // new ImageminPlugin({
    //     optipng: { optimizationLevel: 7 },
    //     gifsicle: { optimizationLevel: 3 },
    //     svgo: { plugins: [] },
    //     plugins: [
    //         require('imagemin-mozjpeg')({
    //             quality: 100
    //         })
    //     ]
    // }),
    new WebpackManifestPlugin(),
    new BundleAnalyzerPlugin({
        analyzerMode: 'static',
        reportFilename: 'bundle-report.html',
        openAnalyzer: false
    }),
    new CopyWebpackPlugin({
        patterns: [
            {
                from: utils.srcScriptsPath('sw.js'),
                to: utils.distPath('sw.js'),
            },
            {
                from: utils.srcScriptsPath('lib/instantpage.js'),
                to: utils.distPath('instantpage.js'),
            },
            {
                from: utils.srcScriptsPath('lib/smooth-scroll.min.js'),
                to: utils.distPath('smooth-scroll.min.js'),
            },
            {
                from: utils.srcScriptsPath('lib/lazysizes.min.js'),
                to: utils.distPath('lazysizes.min.js'),
            },
        ],
    }),
];

module.exports = {
    optimization: {
        minimize: true,
        minimizer: [
            new TerserPlugin({
                parallel: true,
                terserOptions: {
                    compress: {
                        drop_console: true
                    }
                }
            })
        ],
        splitChunks: {
            chunks: 'all',
            cacheGroups: {
                vendor: {
                    test: /[\\/]node_modules[\\/]/,
                    name: 'vendors',
                    chunks: 'all',
                    priority: 10,
                },
            },
        }
    },
    entry: require('./webpack/entry'),
    output: {
        ...require('./webpack/output'),
        clean: true,
    },
    resolve: require('./webpack/resolve'),
    externals: require('./webpack/externals'),
    module: {
        rules: [
            // Hỗ trợ import glob cho các file JS/CSS/SCSS.
            {
                enforce: 'pre',
                test: /\.(js|jsx|css|scss|sass)$/i,
                use: 'import-glob'
            },
            // Xử lý file config.json.
            {
                test: utils.themeRootPath('config.json'),
                use: configLoader
            },
            // Xử lý JS qua Babel.
            {
                test: utils.tests.scripts,
                exclude: /node_modules/,
                use: babelLoader
            },
            // Xử lý SCSS/CSS qua MiniCssExtractPlugin.
            {
                test: utils.tests.styles,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            sourceMap: false,
                            importLoaders: 2
                        }
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            sourceMap: false,
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: false,
                        }
                    }
                ]
            },
            // Xử lý hình ảnh với asset modules.
            {
                test: utils.tests.images,
                type: 'asset/resource',
                generator: {
                    filename: (pathData) => {
                        const hash = utils.filehash(pathData.filename).substr(0, 10);
                        return `images/[name].${hash}[ext]`;
                    }
                }
            },
            // Xử lý font với asset modules.
            {
                test: utils.tests.fonts,
                type: 'asset/resource',
                generator: {
                    filename: (pathData) => {
                        const hash = utils.filehash(pathData.filename).substr(0, 10);
                        return `fonts/[name].${hash}[ext]`;
                    }
                }
            }
        ]
    },
    plugins,
    mode: 'production',
    cache: false,
    bail: false,
    watch: false,
    devtool: false,
    performance: {
        maxEntrypointSize: 512000,
        maxAssetSize: 512000,
    },
};
