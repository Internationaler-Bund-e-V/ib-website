<?php
namespace Deployer;

require_once(__DIR__ . '/vendor/autoload.php');

// require generic TYPO3 recipe
require 'recipe/typo3.php';

require 'contrib/rsync.php';
require 'contrib/yarn.php';

new \SourceBroker\DeployerLoader\Load([
    ['path' => 'vendor/sourcebroker/deployer-instance/deployer'],
    ['path' => 'vendor/sourcebroker/deployer-extended-database/deployer'],
]);

// Task Config: db:*
set('db_allow_pull_live', false);
set('db_databases', [
    'typo3' => [
        [
            'truncate_tables' => [
                'be_sessions',
                'cache_.*',
                'cf_.*',
                'fe_sessions',
                'sys_file_processedfile',
                'sys_log',
            ],
        ],
        (new \Deploy\Driver\Typo3EnvDriver())->getDatabaseConfig()
    ],
]);

// load host configuration
import('deploy/inventory.yaml');

// Task Config: db:*
set('db_databases', [
    (new \SourceBroker\DeployerExtendedDatabase\Driver\EnvDriver())->getDatabaseConfig()
]);

// define localhost
localhost('local')
    ->set('bin/php', 'php')
    ->set('deploy_path', getcwd());

// GIT Config
set('repository', 'https://github.com/Internationaler-Bund-e-V/ib-website.git');

## Task Config: deploy:symlink
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

// Task Config: rsync
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
    '/docs',
    'dump.sql.gz',
    '/node_modules',
    '/package.json',
    'packages/*/Resources/Public/Css',
    'packages/*/Resources/Public/JavaScript',
    '/public/_assets',
    '/public/.htaccess',
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
before('deploy:release', 'deploy:prepare_release_info');
before('deploy:update_code', 'typo3:lockBackend');
before('deploy:update_code', 'rsync:warmup');
after('deploy:symlink', 'cache:flush');
after('deploy:symlink', 'typo3:unlockBackend');

// Tasks
desc('Build CSS and JavaScript on local machine');
task('build:local', function () {
    runLocally('yarn install');
    runLocally('yarn run build');
})->hidden();

desc('Get release information from git');
task('deploy:prepare_release_info', function() {
    set('target', function() {
        return escapeshellarg(runLocally("git rev-parse --abbrev-ref HEAD"));
    });

    // Save git revision in REVISION file.
    $target = get('target');
    set('revision', escapeshellarg(runLocally("git rev-list $target -1")));
});

desc('Use rsync task to pull project files');
task('deploy:update_code', function () {
    invoke('rsync');
    $rev = get('revision');
    run("echo $rev > {{release_path}}/REVISION");
})->hidden();

desc('Use TYPO3 CLI to flush cache');
task('cache:flush', function () {
    run('{{deploy_path}}/typo3/vendor/bin/typo3 staticfilecache:flushCache');
    run('{{deploy_path}}/typo3/vendor/bin/typo3 cache:flush');
    run('{{deploy_path}}/typo3/vendor/bin/typo3 cache:warmup');
});

desc('Use TYPO3 CLI to lock the backend login');
task('typo3:lockBackend', function () {
    run('{{deploy_path}}/typo3/vendor/bin/typo3 backend:lockforeditors');
})->hidden();

desc('Use TYPO3 CLI to unlock the backend login');
task('typo3:unlockBackend', function () {
    run('{{deploy_path}}/typo3/vendor/bin/typo3 backend:unlockforeditors');
})->hidden();

desc('Download new files in TYPO3 fileadmin from remote to local');
task('files:pull', function() {
    writeln('Sync folder public/fileadmin from host "{{alias}}" to local');
    download('{{deploy_path}}/typo3/public/fileadmin/', '{{rsync_src}}/public/fileadmin', [
        'progress_bar' => true,
        'exclude' => [
            '_processed_/**/*',
            '_temp_/**/*'
        ]
    ]);
    writeln('Sync folder public/secure from host "{{alias}}" to local');
    download('{{deploy_path}}/typo3/public/secure/', '{{rsync_src}}/public/secure', [
        'progress_bar' => true,
        'exclude' => [
            '_processed_/**/*',
            '_temp_/**/*'
        ]
    ]);
    writeln('Sync folder public/uploads from host "{{alias}}" to local');
    download('{{deploy_path}}/typo3/public/uploads/', '{{rsync_src}}/public/uploads', [
        'progress_bar' => true,
        'exclude' => [
            '_processed_/**/*',
            '_temp_/**/*'
        ]
    ]);
});
