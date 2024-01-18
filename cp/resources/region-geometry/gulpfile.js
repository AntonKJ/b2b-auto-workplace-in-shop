var gulp = require('gulp');
var gutil = require('gulp-util');

var pump = require('pump');
var clean = require('gulp-clean');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var coffee = require('gulp-coffee');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var html2js = require('gulp-html2js');
var minifyCss = require('gulp-minify-css');

var runSequence = require('gulp-run-sequence');

var sources = {
	target: {
		appName: 'region-geometry'
	},
	coffee: [
		'src/modules/common/module.coffee',
		'src/modules/common/*.coffee',

		'src/modules/**/module.coffee',
		'src/modules/**/*.coffee',

		'src/*.coffee',
		'src/**/*.coffee'
	],
	templates: [
		'src/**/*.html'
	],
	sass: [
		'src/**/*.scss'
	],
	images: [
		'src/i/**/*'
	]
};

var errorHandler = function (error) {

	gutil.log(gutil.colors.red(error.message));
	gutil.log(error.stack);

};

gulp.task('clear', function () {
	return gulp.src(['build/*'], {read: false})
		.pipe(clean());
});

gulp.task('coffee', function () {

	return gulp.src(sources.coffee, {allowEmpty: true})
		.pipe(coffee({
			bare: true
		}))
		.pipe(concat(sources.target.appName + '.js'))
		.pipe(gulp.dest('build/'))
		.pipe(rename(sources.target.appName + '.min.js'))
		.pipe(uglify({
			outSourceMap: true
		}))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler)
		;
});

gulp.task('templates', function () {
	return gulp.src(sources.templates, {allowEmpty: true})
		.pipe(html2js(sources.target.appName + '.templates.js', {
			adapter: 'angular',
			quoteChar: '\'',
			name: 'templates',
			base: 'src',
			htmlmin: {
				collapseBooleanAttributes: false,
				collapseWhitespace: true,
				processScripts: ['text/ng-template'],
				removeComments: true,
				removeEmptyAttributes: true,
				removeRedundantAttributes: true
			}
		}))
		.pipe(gulp.dest('build/'))
		.pipe(rename(sources.target.appName + '.templates.min.js'))
		.pipe(uglify({
			outSourceMap: false
		}))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler)
		;
});

gulp.task('sass', function () {
	return gulp.src('src/sass/style.scss')
		.pipe(sass({outputStyle: 'expanded'}))
		//.on('error', sass.logError))
		.pipe(rename(sources.target.appName + '.css'))
		.pipe(gulp.dest('build/'))
		.pipe(minifyCss())
		.pipe(rename(sources.target.appName + '.min.css'))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler)
		;
});

gulp.task('scripts', gulp.series(gulp.parallel('coffee', 'templates'), function () {
	return gulp.src([
		'build/' + sources.target.appName + '.js',
		'build/' + sources.target.appName + '.templates.js'
	])
		.pipe(concat(sources.target.appName + '.bundle.js'))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler);
}));

gulp.task('app-js-full-compile', gulp.series('scripts', function () {
	return gulp.src(['build/' + sources.target.appName + '.min.js', 'build/' + sources.target.appName + '.templates.min.js'])
		.pipe(concat(sources.target.appName + '.bundle.min.js'))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler);
}));

gulp.task('default', gulp.series('clear', 'app-js-full-compile', 'sass'));

gulp.task('watch', gulp.series('default', function () {

	gulp.watch(sources.sass,  gulp.series('sass'));
	gulp.watch(sources.templates,  gulp.series('app-js-full-compile'));
	gulp.watch(sources.coffee,  gulp.series('app-js-full-compile'));

}));


