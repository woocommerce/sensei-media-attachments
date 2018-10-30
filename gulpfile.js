var gulp      = require( 'gulp' );
var wpPot     = require( 'gulp-wp-pot' );
var sort      = require( 'gulp-sort' );
var zip       = require( 'gulp-zip' );

var paths = {
	packageContents: [
		'assets/**/*',
		'changelog.txt',
		'LICENSE',
		'classes/**/*',
		'woo-includes/**/*',
		'lang/**/*',
		'README.md',
		'sensei-media-attachments.php',
	],
	packageDir: 'build/sensei-media-attachments',
	packageZip: 'build/sensei-media-attachments.zip'
};

gulp.task( 'pot', function() {
        return gulp.src( [ '**/**.php', '!node_modules/**'] )
                .pipe( sort() )
                .pipe( wpPot({
                        domain: 'sensei_media_attachments'
                }) )
                .pipe( gulp.dest( 'lang' ) );
});

gulp.task( 'clean', gulp.series( function( cb ) {
	return del( [
		'build'
	], cb );
} ) );

gulp.task( 'copy-package', function() {
	return gulp.src( paths.packageContents, { base: '.' } )
		.pipe( gulp.dest( paths.packageDir ) );
} );

gulp.task( 'zip-package', function() {
	return gulp.src( paths.packageDir + '/**/*', { base: paths.packageDir + '/..' } )
		.pipe( zip( paths.packageZip ) )
		.pipe( gulp.dest( '.' ) );
} );

gulp.task( 'package', gulp.series( 'copy-package', 'zip-package' ) );
