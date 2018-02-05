const gulp = require('gulp')
const browserSync = require('browser-sync')
const browserify = require('browserify')
const sourcemaps = require('gulp-sourcemaps')
const autoprefixer = require('gulp-autoprefixer')
const source = require('vinyl-source-stream')
const buffer = require('vinyl-buffer')

/* pathConfig*/
let entryPoint = './dev/main.js',
    browserDir = './',
    htmlWatchPath = './**/*.php';

gulp.task('browser-sync', function () {
    const config = {
        port: 4200,
        proxy: {
          target: 'localhost:8888'
        }
    };

    return browserSync(config);
});

gulp.task('watch', function () {
    gulp.watch(htmlWatchPath, function () {
        return gulp.src('')
            .pipe(browserSync.reload({stream: true}))
    });
    gulp.watch('./Templates/*.php', function () {
        return gulp.src('')
            .pipe(browserSync.reload({stream: true}))
    });
    gulp.watch('./core/*.php', function () {
      return gulp.src('')
          .pipe(browserSync.reload({stream: true}))
    });
    gulp.watch('./page-templates/*.php', function () {
      return gulp.src('')
          .pipe(browserSync.reload({stream: true}))
    });
    gulp.watch('./core/css/*.css', function () {
      return gulp.src('')
          .pipe(browserSync.reload({stream: true}))
    });
    gulp.watch('./style.css', function () {
      return gulp.src('')
          .pipe(browserSync.reload({stream: true}))
    });
});

gulp.task('run', ['watch', 'browser-sync']);