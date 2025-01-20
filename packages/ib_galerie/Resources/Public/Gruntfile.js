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
					sourceMap: true
				},
				files: {
					'dist/ibgallery.min.css': 'scss/app.scss'
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
					'dist/ibgallery.min.js': [
						'js/**/*.js',
						'node_modules/masonry-layout/dist/masonry.pkgd.js'
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
					'dist/ibgallery.min.js': [
						'dist/ibgallery.min.js'
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
					'dist/ibgallery.min.css': ['dist/ibgallery.min.css']
				}
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
				tasks: ['uglify:Frontend']
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
	grunt.registerTask('handle_css', ['sass', 'cssmin']);
	grunt.registerTask('handle_js', ['uglify:Frontend', 'uglify:BuildJsForProduction']);
	grunt.registerTask('build', ['handle_css', 'handle_js']);
	grunt.registerTask('default', ['build', 'watch']);
};
