<?php
if (!$_ENV['URL']) {
	print "Set URL config";
	die();
}
if (!$_ENV['ADMIN_EMAIL']) {
	print "Set ADMIN_EMAIL config";
	die();
}
if (!$_ENV['ADMIN_USERNAME']) {
	print "Set ADMIN_USERNAME config";
	die();
}
if (!$_ENV['ADMIN_PASSWORD']) {
	print "Set ADMIN_PASSWORD config";
	die();
}
?>
<html><head><title>Setup</title></head><body>
<?php
print "<p>Starting Cron</p>";

require 'localConfig.php';
require_once APP_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadWebApp.php';


tasks\UpdateAreaBoundsCache::update(false);

tasks\UpdateAreaFutureEventsCache::update(false);

tasks\UpdateAreaParentCache::update(false);

tasks\UpdateSiteCache::update(false);

tasks\UpdateHistoryChangedFlags::update(false);

print "<p>Done</p>";
?>
</body></html>


