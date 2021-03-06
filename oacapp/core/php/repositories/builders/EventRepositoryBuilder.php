<?php

namespace repositories\builders;

use models\SiteModel;
use models\EventModel;
use models\GroupModel;
use models\VenueModel;
use models\UserAccountModel;
use models\CountryModel;
use models\CuratedListModel;
use models\ImportURLModel;
use models\AreaModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRepositoryBuilder extends BaseRepositoryBuilder {
	
	protected $orderBy = " start_at ";
	protected $orderDirection = " ASC ";

	public  function setOrderByCreatedAt($newestFirst = true) {
		$this->orderBy = " created_at ";
		$this->orderDirection = ($newestFirst ? " DESC " : " ASC ");
	}

	public  function setOrderByStartAt($newestFirst = true) {
		$this->orderBy = " start_at ";
		$this->orderDirection = ($newestFirst ? " DESC " : " ASC ");
	}



	/** @var UserAccountModel **/
	protected $userAccount;
	protected $userAccountIncludeAll = false;
	protected $userAccountIncludePrivate = false;
	protected $userAccountIncludeAttending = true;
	protected $userAccountIncludeWatching = true;
	
	public function setUserAccount(UserAccountModel $user, $includeAll = false, $includePrivate = false, $userAccountIncludeAttending=true, $userAccountIncludeWatching=true) {
		if ($userAccountIncludeAttending || $userAccountIncludeWatching) {
			$this->userAccount = $user;
			$this->userAccountIncludeAll = $includeAll;
			$this->userAccountIncludePrivate = $includePrivate;
			$this->userAccountIncludeAttending = $userAccountIncludeAttending;
			$this->userAccountIncludeWatching = $userAccountIncludeWatching;
		}
	}


	/** @var SiteModel **/
	protected $site;
	
	public function setSite(SiteModel $site) {
		$this->site = $site;
	}


	/** @var GroupModel **/
	protected $group;
	
	public function setGroup(GroupModel $group) {
		$this->group = $group;
	}

	/** @var CountryModel **/
	protected $country;
	
	public function setCountry(CountryModel $country) {
		$this->country = $country;
	}

	/** @var AreaModel **/
	protected $area;
	
	public function setArea(AreaModel $area) {
		$this->area = $area;
	}

	/** @var VenueModel **/
	protected $venue;
	
	public function setVenue(VenueModel $venue) {
		$this->venue = $venue;
	}
	
	protected $venueVirtualOnly = false;
	
	public function setVenueVirtualOnly($value) {
		$this->venueVirtualOnly = $value;
	}

	

	/** @var ImportURLModel **/
	protected $importURL;
	
	public function setImportURL(ImportURLModel $importURL) {
		$this->importURL = $importURL;
	}

	
	/** @var CuratedListModel **/
	protected $curatedList;
	
	public function setCuratedList(CuratedListModel $curatedList) {
		$this->curatedList = $curatedList;
	}
	
	
	/** @var \DateTime **/
	protected $after;
	
	public function setAfter(\DateTime $a) {
		$this->after = $a;
		return $this;
	}
	
	public function setAfterNow() {
		$this->after = \TimeSource::getDateTime();
		return $this;
	}
	
	/** @var \DateTime **/
	protected $before;
	
	public function setBefore(\DateTime $b) {
		$this->before = $b;
		return $this;
	}

	
	/** @var \DateTime **/
	protected $start;
	
	public function setStart(\DateTime $b) {
		$this->start = $b;
		return $this;
	}
	
	/** @var \DateTime **/
	protected $end;
	
	public function setEnd(\DateTime $b) {
		$this->end = $b;
		return $this;
	}

	
	protected $include_deleted = true;

	public function setIncludeDeleted($value) {
		$this->include_deleted = $value;
	}
	
	protected $venue_lat_lng_only = false;

	public function setVenueLatLngOnly($value) {
		$this->venue_lat_lng_only = $value;
	}
	
	
	protected $include_imported = true;

	public function setIncludeImported($value) {
		$this->include_imported = $value;
	}
	
	
	protected function build() {
		global $DB;

		$this->select[] = 'event_information.*';
		$this->select[] = " group_information.title AS group_title ";
		$this->select[] = " group_information.id AS group_id ";
		$this->joins[] = " LEFT JOIN event_in_group ON event_in_group.event_id = event_information.id AND event_in_group.removed_at IS NULL AND event_in_group.is_main_group = '1' ";
		$this->joins[] = " LEFT JOIN group_information ON group_information.id = event_in_group.group_id ";
		
		if ($this->site) {
			$this->where[] =  " event_information.site_id = :site_id ";
			$this->params['site_id'] = $this->site->getId();
		}
		
		if ($this->group) {
			// We use a seperate table here so if event is in 2 groups and we select events in 1 group that isn't the main group only, 
			// the normal event_in_group table still shows the main group.
			$this->joins[] =  " JOIN event_in_group AS event_in_group_select ON event_in_group_select.event_id = event_information.id ".
					"AND event_in_group_select.removed_at IS NULL AND event_in_group_select.group_id = :group_id ";
			$this->params['group_id'] = $this->group->getId();
		}
		
		if ($this->country) {
			$this->where[] =  " event_information.country_id = :country_id ";
			$this->params['country_id'] = $this->country->getId();
		}
		
		if ($this->area) {
			
			// We were doing
			// $this->joins[] = " LEFT JOIN cached_area_has_parent ON cached_area_has_parent.area_id = venue_information.area_id";
			// $this->where[] =  " (venue_information.area_id = :area_id OR  cached_area_has_parent.has_parent_area_id = :area_id )";
			// but then we got duplicates
			
			$areaids = array( $this->area->getId() );
			
			$this->statAreas = $DB->prepare("SELECT area_id FROM cached_area_has_parent WHERE has_parent_area_id=:id");
			$this->statAreas->execute(array('id'=>$this->area->getId()));
			while($d = $this->statAreas->fetch()) {
				$areaids[] = $d['area_id'];
			}
			
			$this->joins[] = " LEFT JOIN venue_information ON venue_information.id = event_information.venue_id ";
			$this->where[] =  " (venue_information.area_id IN (".  implode(",", $areaids).") ".
					"OR event_information.area_id IN (".  implode(",", $areaids).")) ";
		}
		
		if ($this->venue) {
			$this->where[] =  " event_information.venue_id = :venue_id ";
			$this->params['venue_id'] = $this->venue->getId();
		}
		
		if ($this->importURL) {
			$this->where[] =  " event_information.import_url_id = :import_url_id ";
			$this->params['import_url_id'] = $this->importURL->getId();
		}
		
		if (!$this->site && !$this->group) {
			$this->joins[] = " JOIN site_information ON event_information.site_id = site_information.id ";
			$this->select[] = " site_information.slug AS site_slug ";
		}
		
		if ($this->curatedList) {
			$this->joins[] = " JOIN event_in_curated_list ON event_in_curated_list.event_id = event_information.id ".
				" AND event_in_curated_list.removed_at IS NULL AND event_in_curated_list.curated_list_id = :curated_list";
			$this->params['curated_list'] = $this->curatedList->getId();
		}
		
		if ($this->end) {
			$this->where[] = ' event_information.end_at = :end';
			$this->params['end'] = $this->end->format("Y-m-d H:i:s");			
		} else if ($this->after) {
			$this->where[] = ' event_information.end_at > :after';
			$this->params['after'] = $this->after->format("Y-m-d H:i:s");
		}
		
		if ($this->start) {
			$this->where[] = ' event_information.start_at = :start';
			$this->params['start'] = $this->start->format("Y-m-d H:i:s");
		} else if ($this->before) {
			$this->where[] = ' event_information.start_at < :before';
			$this->params['before'] = $this->before->format("Y-m-d H:i:s");
		}
		
		if (!$this->include_deleted) {
			$this->where[] = " event_information.is_deleted = '0' ";
		}
		
		if (!$this->include_imported) {
			$this->where[] = " event_information.import_url_id is null ";
		}
		
		if ($this->userAccount) {
			// user at event. we want info on this always for the extra selects, so outside if statement
			$this->joins[] = "  LEFT JOIN user_at_event_information ON user_at_event_information.event_id = event_information.id ".
					"AND user_at_event_information.user_account_id = :user_account_id ";
			$this->select[] = " user_at_event_information.is_plan_attending AS user_is_plan_attending ";
			$this->select[] = " user_at_event_information.is_plan_maybe_attending AS user_is_plan_maybe_attending ";
			if (!$this->userAccountIncludeAll) {
				// we don't always want extra info on this. It is only used for filtering, so inside if statement.
				$this->joins[] = " LEFT JOIN user_watches_group_information ON user_watches_group_information.group_id = group_information.id ".
						"AND user_watches_group_information.user_account_id = :user_account_id AND user_watches_group_information.is_watching='1'";
				$this->joins[] = " LEFT JOIN user_watches_site_information ON user_watches_site_information.site_id = event_information.site_id ".
						"AND user_watches_site_information.user_account_id = :user_account_id AND user_watches_site_information.is_watching='1'";				
				$w = array();
				if ($this->userAccountIncludeAttending) {
					if ($this->userAccountIncludePrivate) {
						$w[] = " user_at_event_information.is_plan_attending = '1' ";
						$w[] = " user_at_event_information.is_plan_maybe_attending = '1' ";
					} else {
						$w[] = " (user_at_event_information.is_plan_attending = '1' AND user_at_event_information.is_plan_public  = '1' )";
						$w[] = " (user_at_event_information.is_plan_maybe_attending = '1' AND user_at_event_information.is_plan_public  = '1' )";
					}
				}
				if ($this->userAccountIncludeWatching) {
					$w[] = " user_watches_site_information.is_watching='1'  ";
					$w[] = " user_watches_group_information.is_watching='1' ";
				}
				$this->where[] = "  (  ".  implode(" OR ", $w).") ";
			}
			$this->params['user_account_id'] = $this->userAccount->getId();			
		}
		
		if ($this->venue_lat_lng_only) {
			$this->joins[] = " JOIN venue_information ON venue_information.id = event_information.venue_id";
			$this->where[] = " venue_information.lat IS NOT NULL ";
			$this->where[] = " venue_information.lng IS NOT NULL ";
			$this->select[] = "  venue_information.lng AS venue_lng";
			$this->select[] = "  venue_information.lat AS venue_lat";
			$this->select[] = "  venue_information.title AS venue_title";
			$this->select[] = "  venue_information.slug AS venue_slug";
		}
		
		if ($this->venueVirtualOnly) {
			$this->where[] = " event_information.is_virtual = '1' ";
		}
	}
	
	protected function buildStat() {
		global $DB;
		
	
				
		$sql = "SELECT ".  implode(",", $this->select)." FROM event_information ".
				implode(" ",$this->joins).
				($this->where ? " WHERE ".implode(" AND ", $this->where) : "").
				" ORDER BY  ".$this->orderBy." ".$this->orderDirection . " LIMIT ".$this->limit;

		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
		
	}
	
	
	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		$results = array();
		while($data = $this->stat->fetch()) {
			$event = new EventModel();
			$event->setFromDataBaseRow($data);
			$results[] = $event;
		}
		return $results;
		
	}

}

