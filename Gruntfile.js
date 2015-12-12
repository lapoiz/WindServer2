module.exports = function(grunt) {

    require('load-grunt-tasks')(grunt);

    // Project configuration.
    grunt.initConfig({

        copy: {
            main: {
                expand: true,
                cwd: 'src/LaPoiz/WindBundle/Resources/public/css/',
                src: '*.css',
                dest: 'web/bundles/lapoizwind/css/',
                flatten: true,
                filter: 'isFile'
            }
        },
        sass: {
            dist: {
                files: {
                    'app/Resources/public/lapoizSass/sass/lapoizwind.css': 'app/Resources/public/lapoizSass/sass/test.scss'
                }
            }
        },

        cssmin: {
            options: {
                shorthandCompacting: false,
                roundingPrecision: -1
            },
            target: {
                files: {
                    'web/bundles/lapoizwind/css/lapoizwind.min.css': ['app/Resources/public/lapoizSass/sass/lapoizwind.css']
                }
            }
        },

        watch : {
            scss: {
                files: ['app/Resources/public/lapoizSass/sass/test.scss'],
                tasks: ['sass', 'cssmin'],
                options: { spawn: false }
            },
            css: {
                files: ['src/LaPoiz/WindBundle/Resources/public/css/*.css'],
                tasks: ['copy'],
                options: { spawn: false }
            }
        }

    });

    // Default task(s).
    grunt.registerTask('default', ['copy', 'sass', 'cssmin']);

}
