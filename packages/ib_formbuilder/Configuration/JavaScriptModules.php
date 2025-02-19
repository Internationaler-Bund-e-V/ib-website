<?php

declare(strict_types=1);

return [
    'dependencies' => ['core', 'backend'],

    'imports' => [
        // load external libraries
        'formbuilder' => 'EXT:ib_formbuilder/Resources/Public/libs/formBuilder/form-builder.min.js',
        'formrenderer' => 'EXT:ib_formbuilder/Resources/Public/libs/formBuilder/form-render.min.js',

        // load custom modules
        '@rms/mfbb' => 'EXT:ib_formbuilder/Resources/Public/JavaScript/Es6ModuleBackend/MyFormBuilderBackend.js',
        '@rms/myinit' => 'EXT:ib_formbuilder/Resources/Public/JavaScript/Es6ModuleBackend/InitBackendFormBuilderScripts.js',
    ],
];
