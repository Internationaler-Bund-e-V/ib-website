<?php
namespace Deployer;

require 'recipe/typo3.php';
require 'contrib/yarn.php';
require 'contrib/webpack_encore.php';
require 'contrib/rsync.php';
// require 'contrib/ms-teams.php';
// set('teams_webhook', 'https://outlook.office.com/webhook/...');

import('deploy/inventory.yaml');

// Config
set('repository', 'https://github.com/Internationaler-Bund-e-V/ib-website.git');

add('shared_files', [
    'public/.htaccess',
    'public/google009d0a891c474c5a.html',
    'public/google0226696366ebd26f.html',
]);
add('shared_dirs', [
    'public/fileadmin',
    'public/secure',
    'public/typo3temp',
    'public/uploads',
    'var',
    'vendor'
]);

add('writable_dirs', array: [
    'public/fileadmin',
    'public/secure',
    'public/typo3temp',
    'public/typo3conf',
    'public/uploads',
    'var',
]);

set('rsync_dest', '{{release_path}}');

set('exclude', [
    '.git',
    '/.ddev',
    '/.editorconfig',
    '/.env.example',
    '/.env.local',
    '/.env',
    '/.github',
    '/.gitignore',
    '/.idea',
    '/.vscode',
    '/deploy.php',
    '/docs',
    '/dump.sql.gz',
    '/node_modules',
    '/package.json',
    'packages/*/Resources/Public/Css',
    'packages/*/Resources/Public/JavaScript',
    'packages/*/Resources/Public/tsconfig.json',
    '/public/typo3conf',
    '/public/typo3temp',
    '/public/fileadmin',
    '/public/uploads',
    '/public/secure',
    '/public/index.php',
    '/tsconfig.json',
    '/types',
    '/vendor',
    '/var',
    '/webpack.config.js',
    '/yarn.lock',
]);

set('rsync', function () {
    return [
        'exclude' => array_unique(get('exclude', [])),
        'exclude-file' => false,
        'include' => [],
        'include-file' => false,
        'filter' => [],
        'filter-file' => false,
        'filter-perdir' => false,
        'flags' => 'rz',
        'options' => ['delete'],
        'timeout' => 3600,
    ];
});

// Hooks
after('deploy:failed', 'deploy:unlock');
before('deploy:release', 'build:local');
after('rsync', 'build:remote');
// before('deploy', 'teams:notify');
// after('deploy:success', 'teams:notify:success');
// after('deploy:failed', 'teams:notify:failure');

// Tasks
task('build:local', function () {
    runLocally('yarn install');
    runLocally('yarn build');
});

task('build:remote', function () {
    run('composer install');
});

desc('Use rsync task to pull project files');
task('deploy:update_code', function () {
    invoke('rsync');
});
