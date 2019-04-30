const {mix} = require('laravel-mix');
const CleanWebpackPlugin = require('clean-webpack-plugin');

// paths to clean
var pathsToClean = [
    'public/assets/app/js',
    'public/assets/app/css',
    'public/assets/dashboards/js',
    'public/assets/dashboards/css',
    'public/assets/auth/css',
];

// the clean options to use
var cleanOptions = {};

mix.webpackConfig({
    plugins: [
        new CleanWebpackPlugin(pathsToClean, cleanOptions)
    ]
});

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

/*
 |--------------------------------------------------------------------------
 | Core
 |--------------------------------------------------------------------------
 |
 */
mix.scripts([
    'node_modules/jquery/dist/jquery.js',
    'node_modules/pace-progress/pace.js',

], 'public/assets/app/js/app.js').version();


mix.scripts([
    'resources/assets/dashboards/js/socket.js',
    'resources/assets/dashboards/js/echo.js',
], 'public/assets/app/js/websocket.js').version();

mix.styles([
    'node_modules/font-awesome/css/font-awesome.css',
    'node_modules/pace-progress/themes/blue/pace-theme-minimal.css',
], 'public/assets/app/css/app.css').version();

mix.copy([
    'node_modules/font-awesome/fonts/',
], 'public/assets/app/fonts');


/*
 |--------------------------------------------------------------------------
 | Auth
 |--------------------------------------------------------------------------
 |
 */
mix.styles('resources/assets/auth/css/login.css', 'public/assets/auth/css/login.css').version();
mix.styles('resources/assets/auth/css/register.css', 'public/assets/auth/css/register.css').version();
mix.styles('resources/assets/auth/css/passwords.css', 'public/assets/auth/css/passwords.css').version();

mix.styles([
    'node_modules/bootstrap/dist/css/bootstrap.css',
    'node_modules/gentelella/vendors/animate.css/animate.css',
    'node_modules/gentelella/build/css/custom.css',
], 'public/assets/auth/css/auth.css').version();


/*
 |--------------------------------------------------------------------------
 | Dashboards
 |--------------------------------------------------------------------------
 |
 */
mix.scripts([
    'node_modules/bootstrap/dist/js/bootstrap.js',
    'node_modules/gentelella/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js',
    'node_modules/gentelella/build/js/custom.js',
], 'public/assets/dashboards/js/dashboards.js').version();

mix.styles([
    'node_modules/bootstrap/dist/css/bootstrap.css',
    'node_modules/gentelella/vendors/animate.css/animate.css',
    'node_modules/gentelella/build/css/custom.css',
    'node_modules/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.css',
   // 'resources/assets/dashboards/css/dashboards.css'
], 'public/assets/dashboards/css/dashboards.css').version();


mix.copy([
    'node_modules/gentelella/vendors/bootstrap/dist/fonts',
], 'public/assets/dashboards/fonts');


mix.scripts([
    'node_modules/select2/dist/js/select2.full.js',
    'resources/assets/dashboards/js/users/edit.js',
], 'public/assets/dashboards/js/users/edit.js').version();

mix.styles([
    'node_modules/select2/dist/css/select2.css',
], 'public/assets/dashboards/css/users/edit.css').version();

mix.scripts([
    'node_modules/gentelella/vendors/Flot/jquery.flot.js',
    'node_modules/gentelella/vendors/Flot/jquery.flot.time.js',
    'node_modules/gentelella/vendors/Flot/jquery.flot.pie.js',
    'node_modules/gentelella/vendors/Flot/jquery.flot.stack.js',
    'node_modules/gentelella/vendors/Flot/jquery.flot.resize.js',

    'node_modules/gentelella/vendors/flot.orderbars/js/jquery.flot.orderBars.js',
    'node_modules/gentelella/vendors/DateJS/build/date.js',
    'node_modules/gentelella/vendors/flot.curvedlines/curvedLines.js',
    'node_modules/gentelella/vendors/flot-spline/js/jquery.flot.spline.min.js',

    'node_modules/gentelella/production/js/moment/moment.min.js',
    'node_modules/gentelella/vendors/bootstrap-daterangepicker/daterangepicker.js',


    'node_modules/gentelella/vendors/Chart.js/dist/Chart.js',
    'node_modules/jcarousel/dist/jquery.jcarousel.min.js',

    'resources/assets/dashboards/js/dashboards.js',
], 'public/assets/dashboards/js/dashboards.js').version();


/*
 |--------------------------------------------------------------------------
 | Pnotify
 |--------------------------------------------------------------------------
 |
 */
mix.styles([
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.css',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.buttons.css',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.mobile.css'
], 'public/assets/dashboards/css/pnotify.css').version();

mix.scripts([
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.js',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.animate.js',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.callbacks.js',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.buttons.js',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.desktop.js',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.history.js',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.mobile.js',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.reference.js',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.tooltip.js',
    'node_modules/gentelella/vendors/pnotify/dist/pnotify.nonblock.js'
], 'public/assets/dashboards/js/pnotify.js').version();


/*
 |--------------------------------------------------------------------------
 | Words/Management
 |--------------------------------------------------------------------------
 |
 */
mix.styles([
    'resources/assets/dashboards/css/words/words_management.css'
], 'public/assets/dashboards/css/words_management.css').version();

mix.scripts([
    'resources/assets/dashboards/js/words/words_management.js'
], 'public/assets/dashboards/js/words_management.js').version();

/*
 |--------------------------------------------------------------------------
 | Play_1
 |--------------------------------------------------------------------------
 |
 */
mix.scripts([
    'resources/assets/dashboards/js/game.js',
], 'public/assets/dashboards/js/game.js').version();

mix.scripts([
    'resources/assets/dashboards/js/index.js',
], 'public/assets/dashboards/js/index.js').version();


mix.scripts([
    'node_modules/bootstrap/js/modal.js',
], 'public/assets/dashboards/js/modal.js').version();
/*
 |----------------
 | Nes.css
 |---------------
 */
mix.styles([
    'node_modules/bootstrap/dist/css/bootstrap.css',
    'resources/assets/dashboards/css/font.css',
    'resources/assets/dashboards/css/nes.css',
    'resources/assets/dashboards/css/game.css',
], 'public/assets/css/nes.css').version();