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
        entryPoints: ['./resources/js/onboarding-tour.js'],
        outfile: './resources/dist/js/onboarding-tour.js',
        bundle: true,
        minify: !isDev,
        platform: 'neutral',
        target: ['es2020'],
        format: 'esm',
        sourcemap: isDev,
    })
    .then(() => console.log('Alpine tour component built successfully'))
    .catch((error) => {
        console.error('Failed to build Alpine tour component:', error);
        process.exit(1);
    });

fs.copyFileSync('./resources/css/onboarding.css', './resources/dist/css/onboarding.css');
console.log('CSS copied successfully');
