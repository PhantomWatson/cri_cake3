var gulp = require('gulp');
var less = require('gulp-less');
var watchLess = require('gulp-watch-less');
var plumber = require('gulp-plumber');
var notify = require("gulp-notify");
var LessPluginCleanCSS = require('less-plugin-clean-css');
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");
var jshint = require('gulp-jshint');
var stylish = require('jshint-stylish');
var phpcs = require('gulp-phpcs');
var phpunit = require('gulp-phpunit');
var _ = require('lodash');
var concat = require('gulp-concat');
var runSequence = require('run-sequence');

function customNotify(message) {
	return notify({
        title: 'CRI',
        message: function(file) {
            return message + ': ' + file.relative;
        }
    })
}

gulp.task('default', ['less', 'js', 'php', 'watch']);



/**************
 *    PHP     *
 **************/

gulp.task('php', function(callback) {
    return runSequence('php_cs', 'php_unit', callback);
});

gulp.task('php_cs', function() {
    return gulp.src(['src/**/*.php'])
        // Validate files using PHP Code Sniffer
        .pipe(phpcs({
            bin: 'C:/xampp/htdocs/data_center_cakephp3/cri/vendor/bin/phpcs',
            standard: 'PSR2',
            warningSeverity: 0
        }))
        // Log all problems that was found
        .pipe(phpcs.reporter('log'));
});

function testNotification(status, pluginName, override) {
    var options = {
        title:   ( status == 'pass' ) ? 'Tests Passed' : 'Tests Failed',
        message: ( status == 'pass' ) ? '\n\nAll tests have passed!\n\n' : '\n\nOne or more tests failed...\n\n',
        icon:    __dirname + '/node_modules/gulp-' + pluginName +'/assets/test-' + status + '.png'
    };
    options = _.merge(options, override);
    return options;
}

gulp.task('php_unit', function() {
    gulp.src('phpunit.xml')
        .pipe(phpunit('', {notify: true}))
        .on('error', notify.onError(testNotification('fail', 'phpunit')))
        .pipe(notify(testNotification('pass', 'php_unit')));
});



/**************
 * Javascript *
 **************/
var srcJsFiles = [ 
    'webroot/js/script.js',
    'webroot/js/admin.js',
    'webroot/js/client.js'
];
var vendorJsFiles = [
    'vendor/twbs/bootstrap/dist/js/bootstrap.min.js'
];
var watchFiles = srcJsFiles.concat(vendorJsFiles);

gulp.task('js', function(callback) {
    return runSequence('js_lint', 'js_minify', 'js_concat', callback);
});

gulp.task('js_lint', function () {
    return gulp.src(srcJsFiles)
        .pipe(jshint())
        .pipe(jshint.reporter(stylish))
        .pipe(customNotify('JS linted'));
});

gulp.task('js_minify', function () {
    return gulp.src(srcJsFiles)
        .pipe(uglify())
        .pipe(rename({
            extname: '.min.js'
        }))
        .pipe(gulp.dest('webroot/js'))
        .pipe(customNotify('JS minified'));
});

// Concatenate vendor files and script(.min).js
gulp.task('js_concat', function () {
	var concatFiles = vendorJsFiles;
	
	concatFiles.push('webroot/js/script.js');
	gulp.src(concatFiles)
    	.pipe(concat('script.concat.js'))
    	.pipe(gulp.dest('webroot/js/'));
	
	concatFiles.pop();
	concatFiles.push('webroot/js/script.min.js');
	return gulp.src(concatFiles)
		.pipe(concat('script.concat.min.js'))
		.pipe(gulp.dest('webroot/js/'))
		.pipe(customNotify('JS concatenated'));
});



/**************
 *    LESS    *
 **************/

gulp.task('less', function () {
    var cleanCSSPlugin = new LessPluginCleanCSS({advanced: true});
    gulp.src('webroot/css/style.less')
        .pipe(less({plugins: [cleanCSSPlugin]}))
        .pipe(gulp.dest('webroot/css'))
        .pipe(customNotify('LESS compiled'));
});



/**************
 *  Watching  *
 **************/

gulp.task('watch', function() {
	// LESS
    var cleanCSSPlugin = new LessPluginCleanCSS({advanced: true});
    watchLess('webroot/css/style.less', ['less'])
        .pipe(less({plugins: [cleanCSSPlugin]}))
        .pipe(gulp.dest('webroot/css'))
        .pipe(customNotify('LESS compiled'));
    
    // JavaScript
    gulp.watch(watchFiles, ['js']);
    
    // PHP
    // All tests if a .php file is changed
    gulp.watch('src/**/*.php', ['php']);
    // Only unit tests if a .ctp file is changed until a proper ruleset for template files is added 
    gulp.watch('src/**/*.ctp', ['php_unit']);
});