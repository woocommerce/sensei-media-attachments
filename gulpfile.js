const { dest, parallel, series, src } = require( 'gulp' );
const del = require( 'del' );
const minify = require('gulp-minify');
const sass = require( 'gulp-sass' );
const wpPot = require( 'gulp-wp-pot' );
const zip = require( 'gulp-zip' );

const buildDir = 'build/sensei-media-attachments';

function clean() {
	return del( [ 'build' ] );
}

function css() {
	return src( 'assets/css/*.css')
		.pipe( dest( buildDir + '/assets/css' ) )
}

function cssMinify() {
	return src( 'assets/css/*.css')
		.pipe( sass( { outputStyle: 'compressed' } ) )
		.pipe( dest( buildDir + '/assets/css' ) )
}

function docs() {
	return src( [ 'changelog.txt', 'README.md' ] )
		.pipe( dest( buildDir ) )
}

function js() {
	return src( 'assets/js/*.js')
		.pipe( dest( buildDir + '/assets/js' ) )
}

function jsMinify() {
	return src( 'assets/js/*.js')
		.pipe( minify( {
			ext:{ min:'.js' },
			noSource: true
		} ) )
		.pipe( dest( buildDir + '/assets/js' ) )
}

function languages() {
	return src( 'lang/*.*', { base: '.' } )
		.pipe( dest( buildDir ) );
}

function php() {
	return src( [ 'sensei-media-attachments.php', 'classes/**/*.php' ], { base: '.' } )
		.pipe( dest( buildDir ) )
}

function pot() {
	return src( [ 'sensei-media-attachments.php', 'classes/**/*.php' ] )
		.pipe( wpPot( {
			domain: 'sensei_media_attachments',
			package: 'Sensei Media Attachments',
		} ) )
		.pipe( dest( 'lang/' ) );
}

function zipFiles() {
	return src( buildDir + '/**/*', { base: buildDir + '/..' } )
		.pipe( zip( buildDir + '.zip' ) )
		.pipe( dest( '.' ) );
}

exports.clean = clean;
exports.css = css;
exports.docs = docs;
exports.js = js;
exports.languages = languages;
exports.php = php;
exports.pot = pot;
exports.zipFiles = zipFiles;

if ( process.env.NODE_ENV === 'dev' ) {
	exports.package = series(
		clean,
		parallel(
			css,
			docs,
			js,
			series( pot, languages ),
			php,
		),
		zipFiles,
	);
} else {
	exports.package = series(
		clean,
		parallel(
			cssMinify,
			docs,
			jsMinify,
			series( pot, languages ),
			php,
		),
		zipFiles,
	);
}
