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
const browserify = require("browserify");
const babelify = require("babelify");
const source = require("vinyl-source-stream");

function clean(cb) {
  cb();
}

const paths = {
  source: "./dev/js/",
  build: "./dist/js/"
};

// Let's write our task in a function to keep things clean
function javascriptBuild() {
  // Start by calling browserify with our entry pointing to our main javascript file
  return (
    browserify({
      entries: [`${paths.source}app.js`],
      // Pass babelify as a transform and set its preset to @babel/preset-env
      transform: [babelify.configure({ presets: ["@babel/preset-env"] })]
    })
      // Bundle it all up!
      .bundle()
      // Source the bundle
      .pipe(source("app.js"))
      // Then write the resulting files to a folder
      .pipe(gulp.dest(`${paths.build}`))
  );
}

function jsLibs(done) {
  gulp
    .src([
      //"./node_modules/jquery/dist/jquery.min.js",
      "./node_modules/foundation-sites/dist/js/foundation.min.js",
      "./node_modules/slick-carousel/slick/slick.min.js",
      "./node_modules/shariff/dist/shariff.min.js",
      "./dev/js/customCookiebot.js",
      "./dev/js/lightbox.min.js",
      "./dev/js/obfuscateEmail.js"
    ])
    .pipe(concat('libs.js'))
    .pipe(rename('libs.min.js'))
    .pipe(gulp.dest("./dist/js/"));
  done();
}

function js(done) {
  gulp
    .src([
      "./dist/js/app.js"
    ])
    .pipe(concat('app.js'))
    .pipe(uglify())
    .pipe(rename({ suffix: ".min" }))
    .pipe(gulp.dest("./dist/js/"));
  done();
}

function cssLibs(done) {
  gulp
    .src([
      "./dev/scss/foundation-sites.scss"
    ])
    .pipe(plumber())
    .pipe(sass({ outputStyle: "expanded", includePaths: ['./node_modules/foundation-sites/scss'] }))
    .pipe(rename('libs.min.css'))
    .pipe(postcss([autoprefixer(), cssnano()]))
    .pipe(gulp.dest("./dist/css/"));
  //.pipe(browserSyncReload);
  done();
}
function css(done) {
  gulp
    .src("./dev/scss/app.scss")
    .pipe(plumber())
    .pipe(sass({ includePaths: ['./dev/scss'] }))
    .pipe(rename('app.min.css'))
    .pipe(postcss([autoprefixer(), cssnano()]))
    //.pipe(postcss([autoprefixer()]))
    .pipe(gulp.dest("./dist/css/"));
  //.pipe(browserSyncReload);
  done();
}

function cssRTE(done) {
  gulp
    .src("./dev/scss/rte/rte.scss")
    .pipe(plumber())
    .pipe(sass({ includePaths: ['./dev/scss'] }))
    .pipe(rename('rteStyles.min.css'))
    .pipe(postcss([autoprefixer(), cssnano()]))
    //.pipe(postcss([autoprefixer()]))
    .pipe(gulp.dest("./dist/css/"));
  //.pipe(browserSyncReload);
  done();
}

// BrowserSync Reload
function browserSyncReload(done) {
  browsersync.reload();
  done();
}

function copy(done) {
  gulp.src([
    './dev/css/faAll.min.css',
    './dev/css/lightbox.min.css'
  ]).pipe(gulp.dest("./dist/css/"));
  done();
}


exports.cssLibs = cssLibs;
exports.css = css;
exports.cssRTE = cssRTE;
exports.jsLibs = jsLibs;
exports.js = js;
exports.copy = copy;


exports.watch = function () {
  watch([
    //'./dev/scss/mask/*.scss',
    //'./dev/scss/news/*.scss',    
    //'./dev/scss/slick_slider/*.scss',
    './dev/scss/*.scss',
    './dev/scss/mask/*.scss',
    './dev/scss/news/*.scss',
    './dev/scss/form/*.scss',
    './dev/scss/solr/*.scss',
    './dev/scss/cookiebot/*.scss'
  ], css);
  //watch(['./dev/scss/rte/*.scss'], cssRTE);
  watch('./dev/js/*.js', series(css, javascriptBuild, js));
};

exports.build = series(cssLibs, css, jsLibs, javascriptBuild, js, copy);

