var gulp      = require( 'gulp' );
var wpPot     = require( 'gulp-wp-pot' );
var sort      = require( 'gulp-sort' );

gulp.task( 'pot', function() {
        return gulp.src( [ '**/**.php', '!node_modules/**'] )
                .pipe( sort() )
                .pipe( wpPot({
                        domain: 'sensei_media_attachments'
                }) )
                .pipe( gulp.dest( 'lang' ) );
});
