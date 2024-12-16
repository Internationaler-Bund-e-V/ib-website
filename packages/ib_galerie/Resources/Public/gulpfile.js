const { watch, series } = require('gulp');
const gulp = require("gulp");
const sass = require('gulp-sass')(require('sass'));
const plumber = require("gulp-plumber");
const rename = require("gulp-rename");
const postcss = require("gulp-postcss");
const browsersync = require("browser-sync").create();
const autoprefixer = require("autoprefixer");
const cssnano = require("cssnano");
const concat = require("gulp-concat");
const uglify = require("gulp-uglify");

function clean(cb) {
  cb();
}


function js(done) {
  gulp
    .src([
      "./js/*.js"
    ])
    .pipe(concat('ibgallery.js'))
    .pipe(uglify())
    .pipe(rename({ suffix: ".min" }))
    .pipe(gulp.dest("./dist/"));
  done();
}

function css(done) {
  gulp
    .src("./scss/app.scss")
    .pipe(plumber())
    .pipe(sass({ includePaths: ['./scss'] }))
    .pipe(rename('ibgallery.min.css'))
    .pipe(postcss([autoprefixer(), cssnano()]))
    .pipe(gulp.dest("./dist/"));
  //.pipe(browserSyncReload);
  done();
}

// BrowserSync Reload
function browserSyncReload(done) {
  browsersync.reload();
  done();
}

exports.css = css;
exports.js = js;

exports.watch = function () {
  watch('./scss/*.scss', css);
  watch('./js/*.js', js);
};

exports.build = series(css,js);

