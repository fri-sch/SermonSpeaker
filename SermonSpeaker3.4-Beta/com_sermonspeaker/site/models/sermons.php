<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * SermonSpeaker Component Sermons Model
 */
class SermonspeakerModelSermons extends JModel
{
	// Variablen for JPagination
	var $_total = null;
 	var $_pagination = null;

	function __construct()
	{
		parent::__construct();
 
		global $mainframe, $option;
		
		$params = &JComponentHelper::getParams('com_sermonspeaker');
		$catid = $params->get('catid', JRequest::getInt('cat', ''));

		$this->seriesjoin = NULL;
		$this->catwhere = NULL;
		if ($catid != 0){
			$this->seriesjoin = " LEFT JOIN #__sermon_series AS ss ON j.series_id = ss.id \n";
			$this->catwhere = " AND ss.catid = '".(int)$catid."' \n";
		}

		// Get pagination request variables
//		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limit = $params->get('sermonresults');
		$limitstart = JRequest::getInt('limitstart', 0);
 		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
 
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	function getTotal()
	{
		$database =& JFactory::getDBO();
		$query	= "SELECT count(*) "
				. "FROM #__sermon_sermons AS j "
				.$this->seriesjoin
				. "WHERE j.published = '1'"
				.$this->catwhere;
		$database->setQuery( $query );
		$total_rows = $database->LoadResult();

        return $total_rows;
	}

	function getPagination()
	{
		jimport('joomla.html.pagination');
		$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );

        return $this->_pagination;
	}
	
	function _buildOrder()
	{
		$sort = JRequest::getWord('sort');
		if ($sort == "sermondate") {
			$orderby = "j.sermon_date DESC, (j.sermon_number+0) DESC";
		} else if ($sort == "mostrecentlypublished") {
			$orderby = "j.id DESC, (j.sermon_number+0) DESC";
		} else if ($sort == "mostviewed") {
			$orderby = "j.hits DESC, (j.sermon_number+0) DESC";
		} else if ($sort == "alphabetically") {
			$orderby = "j.sermon_title ASC, (j.sermon_number+0) DESC";
		} else {
			$orderby = "j.sermon_date DESC, (j.sermon_number+0) DESC";
		}
	
		return $orderby;
	}
	
	function getData()
	{
		$orderby	= $this->_buildOrder();
		$database 	= &JFactory::getDBO();
		$query		= "SELECT sermon_title, sermon_number, sermon_scripture, sermon_date, sermon_time, notes, k.name, k.pic, k.id as s_id, j.id, j.addfile, j.addfileDesc \n"
					. ", CASE WHEN CHAR_LENGTH(j.alias) THEN CONCAT_WS(':', j.id, j.alias) ELSE j.id END as slug \n"
					. "FROM #__sermon_sermons j \n"
					. "LEFT JOIN #__sermon_speakers k ON j.speaker_id = k.id \n"
					.$this->seriesjoin
					. "WHERE j.published='1' \n"
					.$this->catwhere
					. "ORDER BY ".$orderby." \n"
					. "LIMIT ".$this->getState('limitstart').','.$this->getState('limit');
		$database->setQuery($query);
		$rows		= $database->loadObjectList();

		return $rows;
	}
}