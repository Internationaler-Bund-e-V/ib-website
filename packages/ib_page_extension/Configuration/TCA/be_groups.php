<?php

declare(strict_types=1);

/*
 * ----------------------------------------------------------------------------
 * INCREASE THE LIMIT OF MAX SUBGROUPS FOR A BE GROUP
 * ----------------------------------------------------------------------------
 * increase limit from 20 (default) to 100
 * @2016-11-29, mkettel
 */
$GLOBALS['TCA']['be_groups']['columns']['subgroup']['config']['maxitems'] = 100;
