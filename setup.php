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

print "Starting setup\n";

require 'localConfig.php';
require_once COMPOSER_ROOT_DIR.'/vendor/autoload.php'; 
require_once APP_ROOT_DIR.'/core/php/autoload.php';
require_once APP_ROOT_DIR.'/core/php/autoloadCLI.php';

use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\CountryRepository;
use repositories\SiteQuotaRepository;
use models\SiteModel;
use models\UserAccountModel;

################# Upgrade DB
db\migrations\MigrationManager::upgrade(false);

################# Load Static Data
tasks\LoadStaticData::load(false);


################# Admin User
$userRepository = new UserAccountRepository();
$user = $userRepository->loadByEmail($_ENV['ADMIN_EMAIL']);
if (!$user) {
	$user = new UserAccountModel();
	$user->setEmail(trim($_ENV['ADMIN_EMAIL']));
	$user->setUsername(trim($_ENV['ADMIN_USERNAME']));
	$user->setPassword(trim($_ENV['ADMIN_PASSWORD']));
	$userRepository->create($user);
	$userRepository->verifyEmail($user);
	$userRepository->makeSysAdmin($user);
}

################# Site
$siteRepository = new SiteRepository();
$site = $siteRepository->loadById($CONFIG->singleSiteID);
if (!$site) {
	$site = new SiteModel();
	$site->setSlug("calendar");
	$site->setTitle("calendar");
	$site->setIsListedInIndex(true);
	$site->setIsWebRobotsAllowed(true);
	$site->setIsAllUsersEditors(true);
	$site->setIsRequestAccessAllowed(false);
	$site->setIsFeatureCuratedList(true);
	$site->setIsFeatureImporter(false);
	$site->setIsFeatureMap(true);
	$site->setIsFeatureVirtualEvents(true);
	$site->setIsFeaturePhysicalEvents(true);
	$site->setIsFeatureGroup(true);
	$site->setPromptEmailsDaysInAdvance(10);

	$countryRepository = new CountryRepository();
	$siteQuotaRepository = new SiteQuotaRepository();

	$siteRepository->create(
				$site, 
				$user, 
				array(  $countryRepository->loadByTwoCharCode("GB") ),
				$siteQuotaRepository->loadByCode($CONFIG->newSiteHasQuotaCode)
		);

}
print "Done\n\n";


