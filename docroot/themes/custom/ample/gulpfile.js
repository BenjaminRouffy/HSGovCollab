var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');

gulp.task('sass', function() {
  return gulp.src('scss/**/*.scss')
    .pipe(sass())
    .pipe(autoprefixer())
    .pipe(gulp.dest('css'))
});

gulp.task('watch', ['sass'], function() {
  gulp.watch('scss/**/*.scss', ['sass']);
});

gulp.task('default', ['watch']);
