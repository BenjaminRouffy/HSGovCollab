'use strict';

let gulp = require('gulp'),
  sass = require('gulp-sass'),
  browserSync = require('browser-sync').create(),
  cssnano = require('gulp-cssnano'),
  postcss = require('gulp-postcss'),
  mqpacker = require('css-mqpacker'),
  scsslint = require('gulp-scss-lint'),
  jshint = require('gulp-jshint'),
  autoprefixer = require('gulp-autoprefixer'),
  babel = require('gulp-babel');

const PATH = {
  scss: 'scss/**/*.scss',
  css: 'css',
  js: 'js/main.js'
};

// ----------------------------------------------------------------------------
// Browser sync
gulp.task('browserSync', () => {
  browserSync.init({
    notify: false,
    logPrefix: 'P4H',
    server: {
      baseDir: './prototype'
    }
  });
});

// ----------------------------------------------------------------------------
// SCSS compile
gulp.task('sass', () => {
  const AUTOPREFIXER_BROWSERS = ['> 1%', 'Explorer >= 9', 'last 5 version'];

  return gulp.src(PATH.scss)
    .pipe(sass({
      errLogToConsole: true,
      outputStyle: 'expanded'
    }))
    .on('error', swallowError)
    .pipe(autoprefixer({browsers: AUTOPREFIXER_BROWSERS}))
    .pipe(gulp.dest('css'))
    .pipe(browserSync.reload({
      stream: true
    }))
});

// ----------------------------------------------------------------------------
// Babael for ES2015
// We can enable this task later for the future projects
gulp.task('es6', () => {
  return gulp.src('./js/main.js')
    .pipe(babel({
      plugins: ['transform-runtime'],
      presets: ['es2015']
    }))
    .on('error', swallowError)
    .pipe(gulp.dest('./js/main.es5.js'))
});

// ----------------------------------------------------------------------------
// CSS nano plugin
gulp.task('cssnano', () => {
  return gulp.src(PATH.css + '/screen.css')
    .pipe(cssnano({safe: true}))
    .pipe(gulp.dest(PATH.css))
});

// ----------------------------------------------------------------------------
// Linters

// SCSS Lint
gulp.task('scss-lint', () => {
  gulp.src([PATH.scss, '!./scss/util/_normalize.scss'])
    .pipe(scsslint({
      config: 'lint.yml'
    }));
});

// JS lint
gulp.task('js-lint', () => {
  return gulp.src(PATH.js)
    .pipe(jshint())
    .pipe(jshint.reporter('jshint-stylish'))
    .pipe(jshint.reporter('fail'));
});

// ----------------------------------------------------------------------------
// Tasks to run

// Development
gulp.task('serve', ['sass', 'browserSync'], () => {
  gulp.watch(PATH.scss, ['sass']);
});

gulp.task('watch', ['sass'], () => {
  gulp.watch(PATH.scss, ['sass']);
});

// Productoin build
gulp.task('build', ['cssnano']);


// ----------------------------------------------------------------------------
// Helpers
function swallowError(error) {
  // If you want details of the error in the console
  console.log(error.toString());
  this.emit('end')
}
