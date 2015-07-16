<?php
/**
 * DB updater cronjob. command line only.
 *
 * @filesource db_update.php
 * @version    0.1.0
 * @link       https://github.com/codemasher/gw2-database/blob/master/cli/db_update.php
 * @created    02.08.14
 *
 * @author     Smiley <smiley@chillerlan.net>
 * @copyright  Copyright (c) 2014 {@link https://twitter.com/codemasher Smiley} <smiley@chillerlan.net>
 * @license    http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */

set_time_limit(0);

require_once('../common.php');

$gw2maps = new GW2Maps();
$gw2maps->refresh_floors();
$gw2maps->refresh_regions_maps();
$gw2maps->update_maps();

$gw2colors = new GW2Colors();
$gw2colors->color_refresh();
$gw2colors->color_update();

$gw2items = new GW2Items();
$gw2items->chunksize = 100;
$gw2items->item_refresh();
$gw2items->item_update();
$gw2items->recipe_refresh();
$gw2items->recipe_update();
$gw2items->skin_refresh();
$gw2items->skin_update();

exit;
