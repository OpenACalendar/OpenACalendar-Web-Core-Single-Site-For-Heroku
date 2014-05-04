<?php
define('APP_ROOT_DIR',__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

print "Starting ".date("c")."\n";


use repositories\builders\AreaRepositoryBuilder;
use repositories\AreaRepository;

$areaRepository = new AreaRepository();

$arb = new AreaRepositoryBuilder();
$arb->setCacheNeedsBuildingOnly(true);

foreach($arb->fetchAll() as $area) {
	$areaRepository->buildCacheAreaHasParent($area);
	print ".";
}

print "\nFinished ".date("c")."\n";
