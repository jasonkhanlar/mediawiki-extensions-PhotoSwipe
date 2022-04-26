/* eslint-env node, es6 */
module.exports = function ( grunt ) {
	var conf = grunt.file.readJSON( 'extension.json' );

	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-exec' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			options: {
				cache: true,
				fix: grunt.option( 'fix' )
			},
			all: '.'
		},
		stylelint: {
			all: [
				'**/*.{css,less}',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		banana: conf.MessagesDirs,
		exec: {
			'npm-update-photoswipe': {
				cmd: 'npm update photoswipe photoswipe-deep-zoom-plugin photoswipe-dynamic-caption-plugin',
				callback: function ( error, stdout, stderr ) {
					grunt.log.write( stdout );
					if ( stderr ) { grunt.log.write( 'Error: ' + stderr );}
					if ( error !== null ) { grunt.log.error( 'update error: ' + error ); }
				}
			}
		}
	} );

	grunt.registerTask( 'update-photoswipe', [ 'exec:npm-update-photoswipe', 'clean:photoswipe', 'copy:photoswipe', 'copy:photoswipe-license' ] );
	grunt.registerTask( 'test', [ 'eslint', 'stylelint', 'banana' ] );
	grunt.registerTask( 'default', 'test' );
};