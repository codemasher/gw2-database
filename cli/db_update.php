<?php
/**
 * WvW stats cronjob. command line only.
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
#$gw2db = new DBupdater();
#$gw2db->log_file = 'gw2db-update.log';
#$gw2db->loop();


$gw2items = new GW2Items();
$gw2items->refresh_db();
$gw2items->update_db(true);

