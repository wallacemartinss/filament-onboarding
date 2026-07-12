import * as esbuild from 'esbuild';
import * as fs from 'fs';

const isDev = process.argv.includes('--dev');

['./resources/dist/js', './resources/dist/css'].forEach((dir) => {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
});

esbuild
    .build({
        entryPoints: ['./resources/js/onboarding-tour.js', './resources/js/onboarding-media.js'],
        outdir: './resources/dist/js',
        bundle: true,
        minify: !isDev,
        platform: 'neutral',
        target: ['es2020'],
        format: 'esm',
        sourcemap: isDev,
    })
    .then(() => console.log('Alpine components built successfully'))
    .catch((error) => {
        console.error('Failed to build Alpine components:', error);
        process.exit(1);
    });

fs.copyFileSync('./resources/css/onboarding.css', './resources/dist/css/onboarding.css');
console.log('CSS copied successfully');
