const { watch, series } = require('gulp');
const gulp = require("gulp");
//const sass = require("gulp-sass");
const sass = require('gulp-sass')(require('sass'));
const plumber = require("gulp-plumber");
const rename = require("gulp-rename");
const postcss = require("gulp-postcss");

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


exports.css = css;


exports.watch = function () {
  watch([
    './dev/scss/*.scss'
  ], css);

};

exports.build = series(css);

