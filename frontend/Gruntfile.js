module.exports = function (grunt) {
    var distDir = grunt.option('distDir') || '../demo/dist';
    var tempDir = grunt.option('tempDir') || './temp';
    grunt.initConfig({
        "bower-install-simple": {
            options: {
                color: true,
                directory: "bower_components"
            },
            "prod": {
                options: {
                    production: true
                }
            },
            "dev": {
                options: {
                    production: false
                }
            }
        },
        concat: {
            commonCSS: {
                src: [
                    'bower_components/bootstrap/dist/css/bootstrap.min.css',
                    'bower_components/bootstrap/dist/css/bootstrap-theme.min.css',
                    'bower_components/pipaslot-frontend/dist/nette.min.css',
                    'bower_components/pipaslot-frontend/dist/nette.min.css',
                    'bower_components/pipaslot-frontend/dist/pipas.min.css'
                ],
                dest: tempDir + '/css/modules.css'
            },
            commonJS: {
                src: [
                    'bower_components/jquery/jquery.min.js',
                    'bower_components/bootstrap/dist/js/bootstrap.min.js',
                    'bower_components/nette-live-form-validation/live-form-validation.js',
                    'bower_components/nette.ajax.js/nette.ajax.js',
                    'bower_components/pipaslot-frontend/dist/pipas.min.js',
                    'bower_components/pipaslot-frontend/dist/nette-extensions.min.js'
                ],
                dest: tempDir + '/js/modules.js'
            },
            initJS: {
                src: [
                    'bower_components/pipaslot-frontend/dist/nette-init.min.js'
                ],
                dest: tempDir + '/js/modules-init.js'
            }
        },
        uglify: {
            media: {
                files: [{
                    expand: true,
                    src: ['**/*.js', '!**/*.min.js'],
                    cwd: tempDir,
                    dest: tempDir,
                    ext: '.min.js'
                }]
            }
        },
        cssmin: {
            media: {
                files: [{
                    expand: true,
                    src: ['**/*.css', '!**/*.min.css'],
                    cwd: tempDir,
                    dest: tempDir,
                    ext: '.min.css'
                }]
            }
        },
        copy: {
            fontAwesome: {
                expand: true,
                src: 'bower_components/components-font-awesome/fonts/*',
                dest: distDir + '/fonts/',
                flatten: true
            },
            fontGlyphIcon: {
                expand: true,
                src: 'bower_components/bootstrap/fonts/*',
                dest: distDir + '/fonts/',
                flatten: true
            },
            css: {
                expand: true,
                src: tempDir + '/css/*',
                dest: distDir + '/css',
                flatten: true
            },
            js: {
                expand: true,
                src: tempDir + '/js/*',
                dest: distDir + '/js',
                flatten: true
            }

        }
    });
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks("grunt-bower-install-simple");
    grunt.registerTask('default', ['bower-install-simple', 'concat', 'uglify', 'cssmin', 'copy']);

};