var gulp = require('gulp'),
  sass = require('gulp-sass'),
  cssnano = require('gulp-cssnano'),
  postcss = require('gulp-postcss'),
  mqpacker = require('css-mqpacker'),
  autoprefixer = require('gulp-autoprefixer');

var PATH = {
    scss: 'scss/**/*.scss',
    css: 'css',
    js: 'js'
  };

gulp.task('sass', function () {
  return gulp.src(PATH.scss)
    .pipe(sass({
      errLogToConsole: true,
      outputStyle: 'expanded'
    }))
    .on('error', swallowError)
    .pipe(autoprefixer())
    .pipe(gulp.dest('css'))
});

gulp.task('cssnano', function () {
  return gulp.src(PATH.css + '/screen.css')
    .pipe(cssnano({safe: true}))
    .pipe(gulp.dest(PATH.css))
});

gulp.task('watch', ['sass'], function () {
  gulp.watch('scss/**/*.scss', ['sass']);
});

gulp.task('default', ['watch']);

gulp.task('build', ['cssnano']);


function swallowError(error) {
  // If you want details of the error in the console
  console.log(error.toString());
  this.emit('end')
}
