<?php

/**
 * Config file. For a full list of options, please see 
 * http://ican.openacalendar.org/docs/configvars.html
 * 
 * @link http://ican.openacalendar.org/docs/configvars.html Config File Options
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 */

/**
 * Is in Debug mode?
 */
$CONFIG->isDebug = false;

/**
 * DB details.
 */
$dbdata = parse_url($_ENV["DATABASE_URL"]);
$CONFIG->databaseName = substr($dbdata['path'], 1);
$CONFIG->databaseHost = $dbdata['host'];
$CONFIG->databaseUser = $dbdata['user'];
$CONFIG->databasePassword = $dbdata['pass'];

/**
 * Site Title.
 */
$CONFIG->siteTitle = isset($_ENV['SITE_NAME']) && $_ENV['SITE_NAME'] ? $_ENV['SITE_NAME'] : 'My Calendar';

/**
 * To understand the differences between Single Site and Multi Site mode see
 * http://ican.openacalendar.org/docs/singlesite.html
 */
$CONFIG->isSingleSiteMode = TRUE;

/** 
 * If single site mode, what is the ID of the calendar that this will serve?
 * If you created the calendar as part of the install procedure it will almost certainly be 1,
 */
$CONFIG->singleSiteID = 1;

/**
 * For Single Site mode these are all the same.
 * For Multi Site mode these are all different and webSiteDomain must allow subdomains.
 * eg a value of "example.co.uk" should allow and serve any domain "*.example.co.uk" 
 */
$CONFIG->webIndexDomain = $_ENV['URL'];
$CONFIG->webSiteDomain = $_ENV['URL'];
$CONFIG->webSysAdminDomain = $_ENV['URL'];

/** Is SSL available? **/ 
$CONFIG->hasSSL = FALSE;
$CONFIG->webIndexDomainSSL = $_ENV['URL'];
$CONFIG->webSiteDomainSSL = $_ENV['URL'];
$CONFIG->webSysAdminDomainSSL = $_ENV['URL'];

/**
 * For single site mode set same as webIndexDomain.
 * In multi site mode, cookies must travel between webIndexDomain, webSiteDomain and webSysAdminDomain.
 * Set this to be a common root such that cookies with this domain set can travel across all 3.
 */
$CONFIG->webCommonSessionDomain = $_ENV['URL'];


/**
  * Logging
  **/
$CONFIG->logFile = '/app/oacapp/log.txt';
$CONFIG->logToStdError = true;

if (isset($_ENV['MANDRILL_USERNAME']) && $_ENV['MANDRILL_USERNAME']){ 
	$CONFIG->SMTPPort = 587;
	$CONFIG->SMTPHost = "smtp.mandrillapp.com";
	$CONFIG->SMTPUsername = $_ENV['MANDRILL_USERNAME'];
	$CONFIG->SMTPPassword = $_ENV['MANDRILL_APIKEY'];
	$CONFIG->SMTPEncyption = null;
	$CONFIG->SMTPAuthMode = null;
}

/**
  * Sys admin
  **/
$CONFIG->sysAdminExtraPassword = $_ENV['SYSADMIN_EXTRA_PASSWORD'];


