module.exports = function(grunt) {

    require('load-grunt-tasks')(grunt);

    // Project configuration.
    grunt.initConfig({

        copy: {
            inBundle: {
                expand: true,
                cwd: 'src/LaPoiz/WindBundle/Resources/public/css/',
                src: '*.css',
                dest: 'web/bundles/lapoizwind/css/',
                flatten: true,
                filter: 'isFile'
            },
            inSrc: {
                expand: true,
                cwd: 'app/Resources/public/lapoizSass/sass/',
                src: '*.css',
                dest: 'src/LaPoiz/WindBundle/Resources/public/css/',
                flatten: true,
                filter: 'isFile'
            },
            jsGraphInBundle: {
                expand: true,
                cwd: 'src/LaPoiz/GraphBundle/Resources/public/js/',
                src: '*.js',
                dest: 'src/LaPoiz/WindBundle/Resources/public/js/',
                flatten: true,
                filter: 'isFile'
            }
        },
        sass: {
            dist: {
                files: {
                    'app/Resources/public/lapoizSass/sass/lapoizwind-bootstrap.css': 'app/Resources/public/lapoizSass/sass/lapoizwind-bootstrap.scss'
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
                    'web/bundles/lapoizwind/css/lapoizwind-bootstrap.min.css': ['app/Resources/public/lapoizSass/sass/lapoizwind-bootstrap.css']
                }
            }
        },

        watch : {
            scss: {
                files: ['app/Resources/public/lapoizSass/sass/lapoizwind-bootstrap.scss'],
                tasks: ['sass', 'cssmin', 'copy:inSrc'],
                options: { spawn: false }
            },
            css: {
                files: ['src/LaPoiz/WindBundle/Resources/public/css/*.css'],
                tasks: ['copy:inBundle'],
                options: { spawn: false }
            },
            jsGraph: {
                files: ['src/LaPoiz/GraphBundle/Resources/public/js/*.js'],
                tasks: ['copy:jsGraphInBundle'],
                options: { spawn: false }
            }
        }

    });

    // Default task(s).
    grunt.registerTask('default', ['copy', 'sass', 'cssmin']);

}
