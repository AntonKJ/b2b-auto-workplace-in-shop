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

var sources = {
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

var errorHandler = function(error) {

	gutil.log(gutil.colors.red(error.message));
	gutil.log(error.stack);

};

gulp.task('clear', function () {
	gulp.src(['build/*'], {read: false})
		.pipe(clean());
});

gulp.task('images', function(){
	return gulp
		.src(sources.images)
		.pipe(gulp.dest('build/i'));
});

gulp.task('coffee', function () {
	return gulp.src(sources.coffee)
		.pipe(coffee({
			bare: true
		}))
		.pipe(concat('app.js'))
		.pipe(gulp.dest('build/'))
		.pipe(rename('app.min.js'))
		.pipe(uglify({
			outSourceMap: true
		}))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler)
		;
});

gulp.task('templates', function () {
	return gulp.src(sources.templates)
		.pipe(html2js('app.templates.js', {
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
		.pipe(rename('app.templates.min.js'))
		.pipe(uglify({
			outSourceMap: false
		}))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler)
		;
});

gulp.task('sass', function () {
	gulp.src('src/sass/style.scss')
		.pipe(sass({ outputStyle: 'expanded' }))
		//.on('error', sass.logError))
		.pipe(rename('app.css'))
		.pipe(gulp.dest('build/'))
		.pipe(minifyCss())
		.pipe(rename('app.min.css'))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler)
	;
});

//gulp.task('scripts-compile', ['coffee', 'templates']);

gulp.task('scripts', ['coffee', 'templates'], function () {
	return gulp.src(['build/app.js', 'build/app.templates.js'])
		.pipe(concat('app.bundle.js'))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler)
		;
});

gulp.task('scripts-min', ['scripts'], function () {
	return gulp.src(['build/app.min.js', 'build/app.templates.min.js'])
		.pipe(concat('app.bundle.min.js'))
		.pipe(gulp.dest('build/'))
		.on('error', errorHandler)
		;
});

gulp.task('watch', ['default'], function () {

	gulp.watch(sources.sass, ['sass']);
	gulp.watch(sources.templates, ['scripts']);
	gulp.watch(sources.coffee, ['scripts']);

});

//app.bundle.js

gulp.task('default', ['clear', 'scripts', 'scripts-min', 'sass', 'images']);



