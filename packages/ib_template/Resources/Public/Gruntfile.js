/**
 * install node modules:
 * sudo npm install grunt-sass grunt-contrib-watch grunt-contrib-compass grunt-contrib-concat grunt-contrib-uglify grunt-contrib-clean grunt-postcss grunt-autoprefixer grunt-contrib-cssmin --save-dev
 * grunt build
 * grunt
 */
const sass = require('sass');
module.exports = function (grunt) {

	grunt.initConfig({
		sass: {
			dist: {
				options: {
					implementation: sass,
					includePaths: ['node_modules/foundation-sites/scss',],
					style: 'expanded'
				},
				files: {
					'css/min/app.min.css': [
						'css/scss/app.scss',
						'css/font-awesome.css',
						'css/ib-font.css',
						'css/jetmenu.css'
					],
					'css/min/print.min.css': [
						'css/scss/print.scss'
					]
				}
			}
		},

		/*
		 * --------------------------------------------------------------------------------
		 * post css
		 * --------------------------------------------------------------------------------
		 */
		postcss: {

			// build task with minification of css
			build: {
				options: {
					processors: [
						//require('autoprefixer')({browsers: 'last 3 versions'}) // add vendor prefixes
						//require('cssnano')({zindex: false}) // minify the result
					]
				},
				files: {
					'css/min/app.min.css': 'css/min/app.min.css',
					'css/min/print.min.css': 'css/min/print.min.css'
				}
			}
		},

		concat: {
			CSS_App: {
				src: [
					'node_modules/select2/dist/css/select2.css',
					'node_modules/motion-ui/dist/motion-ui.min.css',
					'css/min/app.min.css'
				],
				dest: 'css/min/app.min.css'
			},
			JS_LIBS: {
				src: [
					'node_modules/foundation-sites/js/foundation.core.js',
					'node_modules/foundation-sites/js/foundation.abide.js',
					'node_modules/foundation-sites/js/foundation.tabs.js',
					'node_modules/foundation-sites/js/foundation.reveal.js',
					'node_modules/foundation-sites/js/foundation.util.box.js',
					'node_modules/foundation-sites/js/foundation.util.mediaQuery.js',
					'node_modules/foundation-sites/js/foundation.util.imageLoader.js',
					'node_modules/foundation-sites/js/foundation.util.triggers.js',
					'node_modules/foundation-sites/js/foundation.util.touch.js',
					'node_modules/foundation-sites/js/foundation.equalizer.js',
					'node_modules/foundation-sites/js/foundation.util.keyboard.js',
					'node_modules/foundation-sites/js/foundation.util.motion.js',
					'node_modules/foundation-sites/js/foundation.util.timerAndImageLoader.js',
					//'node_modules/jquery-appear/src/jquery.appear.js',
					//'node_modules/slick-carousel/slick/slick.js',
					'node_modules/jquery.appear/jquery.appear.js',
					'node_modules/slick-carousel/slick/slick.js',
					'node_modules/headroom.js/dist/headroom.js',
					'node_modules/headroom.js/dist/jQuery.headroom.js',
					'node_modules/select2/dist/js/select2.full.js',
					'node_modules/clipboard/dist/clipboard.min.js',
					'node_modules/motion-ui/dist/motion-ui.min.js'
				],
				dest: 'js/min/libs.min.js'
			},
			JS_HEADER: {
				src: [
					'node_modules/angular/angular.js',
					'js/header/header.js'
				],
				dest: 'js/min/header.min.js'
			},
			JQUERY: {
				// no minification necessary, we use the original minified file
				// do not change order of jquery and migrate!
				src: [
					'node_modules/jquery/dist/jquery.min.js',
					'node_modules/jquery-migrate/dist/jquery-migrate.min.js',
				],
				dest: 'js/min/jquery.min.js'
			}
		},

		/*
		 * --------------------------------------------------------------------------------
		 * cssmin
		 * --------------------------------------------------------------------------------
		 */
		cssmin: {
			options: {
				shorthandCompacting: false,
				roundingPrecision: -1
			},
			css: {
				files: {
					'css/min/app.min.css': [
						'css/min/app.min.css'
					],
					'css/min/print.min.css': [
						'css/min/print.min.css'
					]
				}
			}
		},

		/*
		 * --------------------------------------------------------------------------------
		 * bless
		 * --------------------------------------------------------------------------------
		 */
		bless: {
			css: {
				options: {
					compress: true,
					force: true
				},
				files: {
					'css/min/app.min.css': 'css/min/app.min.css'
				}
			}
		},

		/*
		 * --------------------------------------------------------------------------------
		 * js
		 * --------------------------------------------------------------------------------
		 */
		uglify: {
			options: {
				mangle: false,
				beautify: true,
				compress: {
					drop_console: false
				}
			},

			Frontend: {
				files: {
					'js/min/frontend.min.js': [
						'js/src/**/*.js'
					]
				}
			},
			BuildForProduction: {
				options: {
					mangle: true,
					beautify: false,
					compress: {
						drop_console: true
					}
				},
				files: {
					'js/min/libs.min.js': ['js/min/libs.min.js'],
					'js/min/header.min.js': ['js/min/header.min.js'],
					'js/min/frontend.min.js': ['js/min/frontend.min.js']
				}
			}
		},
		watch: {
			grunt: {
				files: ['Gruntfile.js']
			},

			sass: {
				files: ['css/scss/*/*.scss', 'css/scss/*.scss'],
				tasks: ['sass', 'concat:CSS_App']
			},

			js: {
				files: ['js/src/*.js', 'js/src/**/*.js', 'js/jquery_stuff/**/*.js', '!js/src/Tests/**'],
				tasks: ['uglify:Frontend']
			}
		}

	});

	//grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-postcss');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-bless');
	grunt.loadNpmTasks('grunt-contrib-concat');

	//grunt.registerTask('build', ['sass', 'concat:CSS_App', 'postcss', 'cssmin', 'uglify:Libraries', 'uglify:Frontend', 'uglify:Header', 'uglify:BuildForProduction']); //'bless',
	grunt.registerTask('build', [
		'sass',
		'concat:CSS_App',
		'postcss',
		'cssmin',
		'concat:JS_LIBS', 
		'concat:JS_HEADER', 
		'concat:JQUERY',
		'uglify:Frontend',
		'uglify:BuildForProduction'
	]); //'bless',
	grunt.registerTask('default', ['build', 'watch']);
};