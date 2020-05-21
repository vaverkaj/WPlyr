// ==========================================================================
// Gulp build script
// ==========================================================================
/* global require, __dirname */
/* eslint no-console: "off" */

const del = require('del');
const path = require('path');
const gulp = require('gulp');
const gutil = require('gulp-util');
const concat = require('gulp-concat');
const filter = require('gulp-filter');
const sass = require('gulp-sass');
const cleancss = require('gulp-clean-css');
const header = require('gulp-header');
const prefix = require('gulp-autoprefixer');
const svgstore = require('gulp-svgstore');
const svgmin = require('gulp-svgmin');
const rename = require('gulp-rename');
const size = require('gulp-size');
const rollup = require('gulp-better-rollup');
const babel = require('rollup-plugin-babel');
const sourcemaps = require('gulp-sourcemaps');
const uglify = require('gulp-uglify-es').default;
const commonjs = require('rollup-plugin-commonjs');
const resolve = require('rollup-plugin-node-resolve');

const bundles = {
    "player": {
        "sass": {
            "player.css": "src/sass/bundles/player.scss",
            "error.css": "src/sass/bundles/error.scss"
        },
        "js": {
            "player.js": "src/js/player.js",
        }
    }
};

const minSuffix = '.min';

// Paths
const root = __dirname;
const paths = {
    player: {
        // Source paths
        src: {
            sass: path.join(root, 'src/sass/**/*.scss'),
            js: path.join(root, 'src/js/**/*.js'),
        },

        // Output paths
        output: path.join(root, 'dist/'),

        root: path.join(root, ''),
    },
    upload: [
        path.join(root, `dist/*${minSuffix}.*`),
        path.join(root, 'dist/*.css'),
    ],
};

// Task arrays
const tasks = {
    sass: [],
    js: [],
    sprite: [],
    clean: ['clean'],
};

// Size plugin
const sizeOptions = {
    showFiles: true,
    gzip: true
};

// Browserlist
const browsers = ['> 1%'];

// Babel config
const babelrc = (polyfill = false) => ({
    presets: [
        [
            '@babel/preset-env',
            {
                targets: {
                    browsers,
                },
                useBuiltIns: polyfill ? 'usage' : false,
                modules: false,
            },
        ],
    ],
    babelrc: false,
    exclude: 'node_modules/**',
});

// Clean out /dist
gulp.task('clean', done => {
    const dirs = [paths.player.output].map(dir => path.join(dir, '**/*'));

    del(dirs);

    done();
});

const build = {
    js(files, bundle, options) {
        Object.keys(files).forEach(key => {
            const name = `js:${key}`;
            tasks.js.push(name);
            const {
                output
            } = paths[bundle];
            const polyfill = name.includes('polyfilled');

            return gulp.task(name, () =>
                gulp
                .src(bundles[bundle].js[key])
                .pipe(sourcemaps.init())
                .pipe(concat(key))
                .pipe(
                    rollup({
                            plugins: [resolve(), commonjs(), babel(babelrc(polyfill))],
                        },
                        options,
                    ),
                )
                .pipe(header('typeof navigator === "object" && ')) // "Support" SSR (#935)
                .pipe(sourcemaps.write(''))
                .pipe(gulp.dest(output))
                .pipe(filter('**/*.js'))
                .pipe(uglify())
                .pipe(size(sizeOptions))
                .pipe(rename({
                    suffix: minSuffix
                }))
                .pipe(sourcemaps.write(''))
                .pipe(gulp.dest(output)),
            );
        });
    },
    sass(files, bundle) {
        Object.keys(files).forEach(key => {
            const name = `sass:${key}`;
            tasks.sass.push(name);

            return gulp.task(name, () =>
                gulp
                .src(bundles[bundle].sass[key])
                .pipe(sass())
                .on('error', gutil.log)
                .pipe(concat(key))
                .pipe(prefix(browsers, {
                    cascade: false
                }))
                .pipe(cleancss())
                .pipe(size(sizeOptions))
                .pipe(gulp.dest(paths[bundle].output)),
            );
        });
    },
    sprite(bundle) {
        const name = `svg:sprite:${bundle}`;
        tasks.sprite.push(name);

        // Process Icons
        return gulp.task(name, () =>
            gulp
            .src(paths[bundle].src.sprite)
            .pipe(
                svgmin({
                    plugins: [{
                        removeDesc: true,
                    }, ],
                }),
            )
            .pipe(svgstore())
            .pipe(rename({
                basename: bundle
            }))
            .pipe(size(sizeOptions))
            .pipe(gulp.dest(paths[bundle].output)),
        );
    },
};
// Implementation files
build.sass(bundles.player.sass, 'player');
build.js(bundles.player.js, 'player', {
    format: 'iife'
});

// Build all JS
gulp.task('js', () => gulp.parallel(tasks.js));

// Watch for file changes
gulp.task('watch', () => {
    // Implementation
    gulp.watch(paths.player.src.js, gulp.parallel(tasks.js));
    gulp.watch(paths.player.src.sass, gulp.parallel(tasks.sass));
});
// Build distribution
gulp.task('build', gulp.series(tasks.clean, gulp.parallel(tasks.js, tasks.sass, tasks.sprite)));
// Default gulp task
gulp.task('default', gulp.series('build', 'watch'));