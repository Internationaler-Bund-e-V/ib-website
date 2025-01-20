/**
 * install node modules (see package.json):
 * sudo npm install
 * grunt build
 * grunt
 */

const sass = require('node-sass');
module.exports = function (grunt) {

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		/*
		 * --------------------------------------------------------------------------------
		 * sass
		 * --------------------------------------------------------------------------------
		 */
		sass: {
			FE: {
				options: {
                    implementation: sass,
					style: 'compact', // compressed compact nested expanded
					outputStyle: 'compact',
					sourcesearch: true
				},
				files: {
					'dist/search.min.css': 'scss/app.scss'
				}
			}
		},

		/*
		 * --------------------------------------------------------------------------------
		 * uglify js
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
					'dist/search.min.js': [
						'js/**/*.js'
					]
				}
			},

			BuildJsForProduction: {
				options: {
					mangle: true,
					beautify: false,
					report: 'gzip',
					compress: {
						drop_console: true
					}
				},
				files: {
					'dist/search.min.js': [
						'dist/search.min.js'
					]
				}
			}
		},

		/*
		 * --------------------------------------------------------------------------------
		 * css min -> run before bless
		 * --------------------------------------------------------------------------------
		 */
		cssmin: {
			options: {
				shorthandCompacting: false,
				roundingPrecision: -1
			},
			fe: {
				files: {
					'dist/search.min.css': ['dist/search.min.css']
				}
			}
		},

		/*
		 * --------------------------------------------------------------------------------
		 * ngtemplates
		 * --------------------------------------------------------------------------------
		 */
		ngtemplates: {
			options: {
				module: 'TxIbSearch_App',
				htmlmin: {
					collapseWhitespace: true,
					collapseBooleanAttributes: true,
					removeComments: true
				}
			},
			dist: {
				src: [
					'js/angular/**/**.html'
				],
				dest: 'dist/search_ngtemplates.js'
			}
		},

		/*
		 * --------------------------------------------------------------------------------
		 * concat additional files
		 * --------------------------------------------------------------------------------
		 */
		concat: {
			options: {
				//separator: ';',
			},
			js: {
				src: [
					'dist/search.min.js',
					'dist/search_ngtemplates.js'
				],
				dest: 'dist/search.min.js'
			},
			css: {
				src: [
					'dist/search.min.css'
				],
				dest: 'dist/search.min.css'
			}
		},

		/*
		 * --------------------------------------------------------------------------------
		 * watch tasks
		 * --------------------------------------------------------------------------------
		 */
		watch: {
			grunt: {
				files: ['Gruntfile.js']
			},

			sass: {
				files: ['scss/*/*.scss', 'scss/*.scss'],
				tasks: ['handle_css']
			},

			js: {
				files: [
					'js/*.js', 'js/**/*.js'
				],
				tasks: ['uglify:Frontend', 'concat:js']
			},
			html_templates: {
				files: [
					'js/angular/*.html', 'js/angular/**/*.html'
				],
				tasks: ['ngtemplates', 'concat:js']
			}
		}
	});

	/*
	 * --------------------------------------------------------------------------------
	 * load npm tasks
	 * --------------------------------------------------------------------------------
	 */
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-compass');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-angular-templates');

	/*
	 * --------------------------------------------------------------------------------
	 * define and register tasks
	 * --------------------------------------------------------------------------------
	 */
	grunt.registerTask('handle_css', ['sass', 'cssmin', 'concat:css']);
	grunt.registerTask('handle_js', ['uglify:Frontend', 'uglify:BuildJsForProduction', 'ngtemplates', 'concat:js']);
	grunt.registerTask('build', ['handle_css']);
	grunt.registerTask('default', ['build', 'watch']);
};
