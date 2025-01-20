/**
 * install node modules:
 * grunt grunt-autoprefixer grunt-cli grunt-contrib-clean grunt-contrib-concat grunt-contrib-cssmin grunt-contrib-sass grunt-contrib-uglify grunt-contrib-watch install npm
 * grunt build
 * grunt
 */
const sass = require('node-sass');
module.exports = function (grunt) {

	/*
	 * --------------------------------------------------------------------------------
	 * SASS
	 * --------------------------------------------------------------------------------
	 */
	grunt.initConfig({
		sass: {
			options: {
				implementation: sass,
				style: 'expanded'
			},
			dist: {
				files: {
					'Css/backend.min.css': [
						'Css/scss/backend/app.scss'
					],
					'Css/frontend.min.css': [
						'Css/scss/frontend/app.scss'
					]
				}
			}
		},


		/*
		 * --------------------------------------------------------------------------------
		 * UGLIFY
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
					'JavaScript/backend.min.js': [
						'JavaScript/jQuery/src_backend/**/*.js'
					],
					'JavaScript/frontend.min.js': [
						'JavaScript/jQuery/src_frontend/**/*.js'
					]
				}
			},
			Production: {
				options: {
					mangle: false,
					beautify: false,
					compress: {
						drop_console: true
					}
				},
				files: {
					'JavaScript/backend.min.js': [
						'JavaScript/backend.min.js'
					],
					'JavaScript/frontend.min.js': [
						'JavaScript/frontend.min.js'
					]
				}
			}
		},

		copy: {
			formBuilder: {
				expand: true, flatten: true,
				src: 'node_modules/formBuilder/dist/*',
				dest: 'libs/formBuilder/',
			},
			jQuery: {
				expand: true, flatten: true,
				src: 'node_modules/jquery/dist/*',
				dest: 'libs/jQuery/',
			},
			// we include the static file from libsStatic
			// because this file hjere does not work
			// mk@rms 2026-06-13
			//jQueryUiSortable: {
			//	expand: true, flatten: true,
			//	src: 'node_modules/jquery-ui-sortable/*.js',
			//	dest: 'libs/jQueryUiSortable/',
			//},
		},

		/*
		 * --------------------------------------------------------------------------------
		 * WATCH
		 * --------------------------------------------------------------------------------
		 */
		watch: {
			sass: {
				files: ['Css/scss/**/*.scss'],
				tasks: ['sass']
			},
			js: {
				files: ['JavaScript/jQuery/**/*.js'],
				tasks: ['uglify:Frontend']
			}
		}
	});

	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-copy');

	grunt.registerTask('build', ['copy', 'sass', 'uglify:Frontend', 'uglify:Production']);
	grunt.registerTask('default', ['build', 'watch']);
};