<?php
defined('_JEXEC') or die('Restricted access');

/**
 * Sermonspeaker Component Sermonspeaker Helper
 */
class SermonspeakerHelperSermonspeaker
{
	function SpeakerTooltip($id, $pic, $name) {
		if ($pic == "") { // check if there is no picture and set nopict.jpg
			$pic = JURI::root().'components/com_sermonspeaker/images/nopict.jpg';
		} elseif (substr($pic,0,7) != "http://"){ // check if the picture is locally and add the Root to it
			$pic = JURI::root().$pic;
		}
		$html = '<a class="modal" href="'.JRoute::_('index.php?view=speaker&layout=popup&id='.$id.'&tmpl=component').'" rel="{handler: \'iframe\', size: {x: 700, y: 500}}">';
		$html .= JHTML::tooltip('<img src="'.$pic.'" alt="'.$name.'">',$name,'',$name).'</a>';
		
		return $html;
	}
		
	function insertAddfile($addfile, $addfileDesc) {
		$params	=& JComponentHelper::getParams('com_sermonspeaker');
		$path = $params->get('path');

		$link = SermonspeakerHelperSermonspeaker::makelink($addfile); 

		// Show filename if no addfileDesc is set
		if (!$addfileDesc){
			$file_arr = explode('/', $addfile);
			$addfileDesc = end($file_arr);
		}

		$filetype = trim(strrchr($addfile, '.'), '.');
		if (file_exists(JPATH_COMPONENT.DS.'icons'.DS.$filetype.'.png')) {
			$file = JURI::root().'components/com_sermonspeaker/icons/'.$filetype.'.png';
		} else {
			$file = JURI::root().'components/com_sermonspeaker/icons/icon.png';
		}
		if ($addfile) {
			$return = '<a title="'.JText::_('COM_SERMONSPEAKER_ADDFILE_HOOVER').'" href="'.$link.'" target="_blank"><img src="'.$file.'" width="18" height="20" alt="" /></a>&nbsp;<a title="'.JText::_('COM_SERMONSPEAKER_ADDFILE_HOOVER').'" href="'.$link.'" target="_blank">'.$addfileDesc.'</a>';

		return $return;
		} else { 
		return(NULL);
		}
	}
	
	function makelink($path) {
		if (substr($path, 0, 7) == 'http://'){
			$link = $path;
		} else {
			if (substr($path, 0, 1) == '/') {
				$path = substr($path, 1);
			}
			$link = JURI::root().$path;
		}

		return $link;
	}

	function insertdlbutton($id, $path) {
		//Check if link targets to an external source
		if (substr($path, 0, 7) == 'http://'){ //File is external
			$fileurl = $path;
		} else { //File is locally
			$fileurl = JURI::root().'index.php?option=com_sermonspeaker&amp;task=download&amp;id='.$id;
		}
		$return = '<input class="button download_btn" type="button" value="'.JText::_('COM_SERMONSPEAKER_DOWNLOADBUTTON').'" onclick="window.location.href=\''.$fileurl.'\'" />';

		return $return;
	}
	
	function insertPopupButton($id = NULL, $player) {
		$return = '<input class="button popup_btn" type="button" name="'.JText::_('COM_SERMONSPEAKER_POPUPPLAYER').'" value="'.JText::_('COM_SERMONSPEAKER_POPUPPLAYER').'" onclick="popup=window.open(\''.JRoute::_('index.php?view=sermon&layout=popup&id='.$id.'&tmpl=component').'\', \'PopupPage\', \'height='.$player['height'].',width='.$player['width'].',scrollbars=yes,resizable=yes\'); return false" />';

		return $return;
	}
	
	function insertPlayer($lnk, $time = NULL, $count = '1', $title = NULL, $artist = NULL) {
		$params	=& JComponentHelper::getParams('com_sermonspeaker');
		$view = JRequest::getCmd('view');
		$callback = NULL;
		if ($params->get('autostart') == '1' && $view != 'seriessermon') {
			$start[0]='true'; $start[1]='1'; $start[2]='yes';
		} else {
			$start[0]='false'; $start[1]='0'; $start[2]='no';
		}
		if ($params->get('alt_player') && (substr_compare($lnk, '.mp3', -4, 4, true) == 0)){
			$player = JURI::root().'components/com_sermonspeaker/media/player/audio_player/player.swf';
			$options = NULL;
			if ($title){
				$options = 'titles: "'.$title.'",';
			}
			if ($artist){
				$options .= 'artists: "'.$artist.'",';
			}
		} else {
			$player = JURI::root().'components/com_sermonspeaker/media/player/player.swf';
			if ($params->get('ga')) {
				$callback = "so.addVariable('callback','".$params->get('ga')."');";
			} else {
				$callback = NULL;
			}
			if ($time){
				$duration = "so.addVariable('duration','".$time."');";
			} else {
				$duration = NULL;
			}
		}
		if(substr_compare($lnk, 'index.php', 0, 9, true) == 0){
			// Playlist
			$return['mspace'] = '<div id="mediaspace'.$count.'" style="text-align:center;">Flashplayer needs Javascript turned on</div>';
			$return['script'] = '<script type="text/javascript">'
								."	var so = new SWFObject('".$player."','player1','80%','84','9');"
								."	so.addParam('allowfullscreen','true');"
								."	so.addParam('allowscriptaccess','always');"
								."	so.addParam('wmode','transparent');"
								."	so.addVariable('playlistfile','".$lnk."');"
								."	so.addVariable('playlistsize','60');"
								."	so.addVariable('playlist','bottom');"
								."	so.addVariable('autostart','".$start."');"
								.'	'.$callback
								."	so.write('mediaspace".$count."');"
								.'</script>';
			$return['height'] = $params->get('popup_height');
			$return['width']  = '380';
		} else {
			// Single File
			if((substr_compare($lnk, '.mp3', -4, 4, true) == 0) || (substr_compare($lnk, '.m4a', -4, 4, true) == 0)) { 
				// Audio File
				$return['mspace'] = '<div id="mediaspace'.$count.'" align="center">Flashplayer needs Javascript turned on</div>';
				if ($params->get('alt_player') && (substr_compare($lnk, '.mp3', -4, 4, true) == 0)){
					$return['script'] = '<script type="text/javascript">'
										.'	AudioPlayer.embed("mediaspace'.$count.'", {'
										.'		soundFile: "'.urlencode($lnk).'",'
										.'		'.$options
										.'		autostart: "'.$start[2].'"'
										.'	})'
										.'</script>';
				} else {
					$return['script'] = '<script type="text/javascript">'
										."	var so = new SWFObject('".$player."','player1','250','24','9');"
										."	so.addParam('allowfullscreen','true');"
										."	so.addParam('allowscriptaccess','always');"
										."	so.addParam('wmode','opaque');"
										."	so.addVariable('file','".$lnk."');"
										."	so.addVariable('autostart','".$start[0]."');"
										.'	'.$duration
										.'	'.$callback
										."	so.write('mediaspace".$count."');"
										.'</script>';
				}
				$return['height'] = $params->get('popup_height');
				$return['width']  = '380';
			} elseif((substr_compare($lnk, '.flv', -4, 4, true) == 0) || (substr_compare($lnk, '.mp4', -4, 4, true) == 0) || (substr_compare($lnk, '.m4v', -4, 4, true) == 0)) { 
				// Video File
				$return['mspace'] = '<div id="mediaspace'.$count.'" align="center">Flashplayer needs Javascript turned on</div>';
				$return['script'] = '<script type="text/javascript">'
									."	var so = new SWFObject('".$player."','player1','".$params->get('mp_width')."','".$params->get('mp_height')."','9');"
									."	so.addParam('allowfullscreen','true');"
									."	so.addParam('allowscriptaccess','always');"
									."	so.addParam('wmode','opaque');"
									."	so.addVariable('file','".$lnk."');"
									."	so.addVariable('autostart','".$start[0]."');"
									.'	'.$callback
									."	so.write('mediaspace".$count."');"
									.'</script>';
				$return['height'] = $params->get('mp_height') + 100 + $params->get('popup_height');
				$return['width']  = $params->get('mp_width') + 130;
			} elseif(strcasecmp(substr($lnk,-4),".wmv") == 0) {
				// WMV File
				$return['mspace'] = '<object id="mediaplayer" width="400" height="323" classid="clsid:22d6f312-b0f6-11d0-94ab-0080c74c7e95 22d6f312-b0f6-11d0-94ab-0080c74c7e95" type="application/x-oleobject">'
									.'	<param name="filename" value="'.$lnk.'">'
									.'	<param name="autostart" value="'.$start[1].'">'
									.'	<param name="transparentatstart" value="true">'
									.'	<param name="showcontrols" value="1">'
									.'	<param name="showdisplay" value="0">'
									.'	<param name="showstatusbar" value="1">'
									.'	<param name="autosize" value="1">'
									.'	<param name="animationatstart" value="false">'
									.'	<embed name="MediaPlayer" src="'.$lnk.'" width="'.$params->get('mp_width').'" height="'.$params->get('mp_height').'" type="application/x-mplayer2" autostart="'.$start[1].'" showcontrols="1" showstatusbar="1" transparentatstart="1" animationatstart="0" loop="false" pluginspage="http://www.microsoft.com/windows/windowsmedia/download/default.asp">'
									.'	</embed>'
									.'</object>';
				$return['script'] = NULL;
				$return['height'] = $params->get('mp_height') + 100 + $params->get('popup_height');
				$return['width']  = $params->get('mp_width') + 130;
			}
		}

		return $return;
	}

	function insertTime($time) {
		$tmp = explode(':', $time);
		if ($tmp[0] == 0) {
			$return = $tmp[1].':'.$tmp[2];
		} else {
			$return = $tmp[0].':'.$tmp[1].':'.$tmp[2];
		}

		return($return);
	} // end of insertTime

	function fu_logoffbtn () {
		$str = '<FORM><INPUT TYPE="BUTTON" VALUE="'.JText::_('COM_SERMONSPEAKER_FU_LOGOUT').'" ONCLICK="window.location.href=\'index.php?option=com_sermonspeaker&amp;task=fu_logout\'"> </FORM>';
		return $str;
	} // end of fu_logoffbtn
}