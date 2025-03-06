<?php
namespace Deployer;

require_once(__DIR__ . '/vendor/autoload.php');

require 'recipe/typo3.php';
require 'contrib/yarn.php';
require 'contrib/webpack_encore.php';
require 'contrib/rsync.php';
require 'contrib/cachetool.php';

new \SourceBroker\DeployerLoader\Load([
    ['path' => 'vendor/sourcebroker/deployer-instance/deployer'],
    ['path' => 'vendor/sourcebroker/deployer-extended-database/deployer'],
]);

// require 'contrib/ms-teams.php';
// set('teams_webhook', 'https://outlook.office.com/webhook/...');

import('deploy/inventory.yaml');

// Config
set('repository', 'https://github.com/Internationaler-Bund-e-V/ib-website.git');

add('shared_files', [
    '.env',
    '{{typo3_webroot}}/.htaccess',
    '{{typo3_webroot}}/google009d0a891c474c5a.html',
    '{{typo3_webroot}}/google0226696366ebd26f.html',
]);

add('shared_dirs', [
    '{{typo3_webroot}}/fileadmin',
    '{{typo3_webroot}}/secure',
    '{{typo3_webroot}}/typo3temp',
    '{{typo3_webroot}}/uploads',
    'var'
]);

add('writable_dirs', array: [
    '{{typo3_webroot}}/secure',
    '{{typo3_webroot}}/typo3temp',
    '{{typo3_webroot}}/typo3conf',
    '{{typo3_webroot}}/uploads',
    'var',
    'config',
]);

set('writable_recursive', true);

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
    '/deploy',
    '/deploy.php',
    '/docs',
    'dump.sql.gz',
    '/node_modules',
    '/package.json',
    'packages/*/Resources/Public/Css',
    'packages/*/Resources/Public/JavaScript',
    '/public/_assets',
    '/public/typo3conf',
    '/public/typo3temp',
    '/public/fileadmin',
    '/public/uploads',
    '/public/secure',
    '/public/index.php',
    'tsconfig.json',
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
before('deploy:update_code', 'typo3:lockBackend');
after('deploy:symlink', 'cache:flush');
after('deploy:symlink', 'typo3:unlockBackend');
// before('deploy', 'teams:notify');
// after('deploy:success', 'teams:notify:success');
// after('deploy:failed', 'teams:notify:failure');
// after('deploy:symlink', 'cachetool:clear:opcache');
// after('deploy:symlink', 'cachetool:clear:apcu');

// Tasks
task('build:local', function () {
    runLocally('yarn install');
    runLocally('yarn build');
});

desc('Use rsync task to pull project files');
task('deploy:update_code', function () {
    invoke('rsync');
});

task('cache:flush', function () {
    run('{{deploy_path}}/typo3/vendor/bin/typo3 staticfilecache:flushCache');
    run('{{deploy_path}}/typo3/vendor/bin/typo3 cache:flush');
    run('{{deploy_path}}/typo3/vendor/bin/typo3 cache:warmup');
});

task('typo3:lockBackend', function () {
    run('{{deploy_path}}/typo3/vendor/bin/typo3 backend:lockforeditors');
});

task('typo3:unlockBackend', function () {
    run('{{deploy_path}}/typo3/vendor/bin/typo3 backend:unlockforeditors');
});
