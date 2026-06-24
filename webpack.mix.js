const mix  = require('laravel-mix');
const path = require('path');

function _path(p) {
  return path.join(__dirname, p);
}
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig(webpack => {
    return {
        resolve: {
            alias: {
            'jquery.inputmask': _path('node_modules/inputmask/dist/jquery.inputmask'),
            'jquery.validation': _path('node_modules/jquery-validation/dist/jquery.validate'),
            },
        },
        plugins: [
            new webpack.ProvidePlugin({
                $: 'jquery',
                jQuery: 'jquery',
                'window.jQuery': 'jquery',
                Popper: ['popper.js', 'default'],
            })
        ]
    };
});


mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .browserSync('laradminator.local')
    .version();