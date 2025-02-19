<?php
namespace Deployer;

require 'recipe/typo3.php';
require 'contrib/webpack_encore.php';
// require 'contrib/ms-teams.php';
// set('teams_webhook', 'https://outlook.office.com/webhook/...');

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
]);

add('writable_dirs', array: [
    'public/fileadmin',
    'public/secure',
    'public/typo3temp',
    'public/typo3conf',
    'public/uploads',
    'var',
]);

// Hosts

host('prod')
    ->set('hostname', 'ib.de')
    ->set('label', 'prod')
    ->set('remote_user', 'ib')
    ->set('port', 4567)
    ->set('forward_agent', true)
    ->set('config_file', '~/ssh_config')
    ->set('deploy_path', '/var/www/ib.de/typo3');

host('stage')
    ->set('hostname', 'ib-staging.rmsdev.de')
    ->set('remote_user', 'ib')
    ->set('port', 4567)
    ->set('forward_agent', true)
    ->set('config_file', '~/ssh_config')
    ->set('deploy_path', '/var/www/ib-staging.rmsdev.de/typo3');

// Hooks
after('deploy:update_code', 'build');
after('deploy:failed', 'deploy:unlock');

// before('deploy', 'teams:notify');
// after('deploy:success', 'teams:notify:success');
// after('deploy:failed', 'teams:notify:failure');

// Tasks
task('build', function() {
    runLocally('yarn install && yarn run build');
});
