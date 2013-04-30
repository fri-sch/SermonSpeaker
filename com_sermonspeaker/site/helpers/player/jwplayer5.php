<?php
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/components/com_sermonspeaker/helpers/player.php');

/**
 * JW Player 5
 */
class SermonspeakerHelperPlayerJwplayer5 extends SermonspeakerHelperPlayer
{
	private static $script_loaded;

	public function isSupported($file){
		$ext		= JFile::getExt($file);
		$audio_ext	= array('aac', 'm4a', 'mp3');
		$video_ext	= array('mp4', 'mov', 'f4v', 'flv', '3gp', '3g2');
		if (in_array($ext, $audio_ext))
		{
			// Audio File
			$this->mode	= 'audio';
		}
		elseif (in_array($ext, $video_ext))
		{
			$this->mode	= 'video';
		}
		elseif (strpos($file, 'http://www.youtube.com') === 0)
		{
			$this->mode	= 'video';
		}
		else
		{
			$this->mode	= false;
		}
		return $this->mode;
	}

	public function getName()
	{
		return 'JW Player 5';
	}

	public function preparePlayer($item, $config)
	{
		$this->config	= $config;
		$this->player	= 'JWPlayer';
		$this->mspace	= '<div id="mediaspace'.$this->config['count'].'">'.JText::_('COM_SERMONSPEAKER_PLAYER_NEEDS_JAVASCRIPT').'</div>';
		$this->toggle	= $this->params->get('fileswitch', 0);

		// Setting some general player options
		$modes[0]	= "{type:'flash', src:'".JURI::base(true)."/media/com_sermonspeaker/player/jwplayer/player.swf'}";
		$modes[1]	= "{type:'html5'}";
		$modes[2]	= "{type:'download'}";
		$options['modes']	= ($this->params->get('jwmode', 0)) ? '['.$modes[1].','.$modes[0].','.$modes[2].']' : '['.$modes[0].','.$modes[1].','.$modes[2].']';
		$options['autostart']	= $this->config['autostart'] ? 'true' : 'false';
		$options['controlbar']	= "'bottom'";
		if ($skin = $this->params->get('jwskin', ''))
		{
			$options['skin'] = "'".SermonspeakerHelperSermonspeaker::makeLink($skin)."'";
		}
		// Plugins
		if ($this->params->get('ga_id', ''))
		{
			$plugins['gapro-2'] = "{idstring:'SermonSpeaker/||provider||:||file||'}";
		}
		if ($this->params->get('fbit', 0))
		{
			$plugins['fbit-1'] = '{}';
		}
		if ($this->params->get('tweetit', 0))
		{
			$plugins['tweetit-1'] = '{}';
		}
		if ($this->params->get('plusone', 0))
		{
			$plugins['plusone-1'] = '{}';
		}
		if ($this->params->get('share', 0))
		{
			$plugins['sharing-3'] = '{}';
		}
		if ($this->params->get('viral', 0))
		{
			$plugins['viral-2'] = '{}';
		}
		if (isset($plugins))
		{
			foreach ($plugins as $key => $value)
			{
				$plugins[$key] = "'".$key."':".$value;
			}
			$options['plugins'] = '{'.implode(',', $plugins).'}';
		}
		if (is_array($item))
		{
			// Playlist
			$this->setDimensions('23px', '100%');
			$type = ($this->config['type'] == 'audio' || ($this->config['type'] == 'auto' && !$this->config['prio'])) ? 'a' : 'v';
			// Make sure to not use < or && in JavaScript code as it will break XHTML compatibility
			$options['events']	= '{'
					.'onPlaylistItem: function(event){'
						.'var i = 0;'
						.'while (document.id("sermon"+i)){'
							.'document.id("sermon"+i).removeClass("ss-current");'
								.'i++;'
							.'}'
						.'document.id("sermon"+event.index).addClass("ss-current");'
						.'entry = jwplayer().getPlaylistItem();'
						.'if (entry.duration > 0){'
							.'time = new Array();'
							.'var hrs = Math.floor(entry.duration/3600);'
							.'if (hrs > 0){time.push(hrs);}'
							.'var min = Math.floor((entry.duration - hrs * 3600)/60);'
							.'if (hrs == 0 || min >= 10){'
								.'time.push(min);'
							.'} else {'
								.'time.push("0" + min);'
							.'}'
							.'var sec = entry.duration - hrs * 3600 - min * 60;'
							.'if (sec >= 10){'
								.'time.push(sec);'
							.'} else {'
								.'time.push("0" + sec);'
							.'}'
							.'var duration = time.join(":");'
							.'document.id("playing-duration").innerHTML = duration;'
						.'} else {'
							.'document.id("playing-duration").innerHTML = "";'
						.'}'
						.'document.id("playing-pic").src = entry.image;'
						.'if(entry.image){'
							.'document.id("playing-pic").style.display = "block";'
						.'}else{'
							.'document.id("playing-pic").style.display = "none";'
						.'}'
						.'if(entry.error){'
							.'document.id("playing-error").innerHTML = entry.error;'
							.'document.id("playing-error").style.display = "block";'
						.'}else{'
							.'document.id("playing-error").style.display = "none";'
						.'}'
						.'document.id("playing-title").innerHTML = entry.title;'
						.'document.id("playing-desc").innerHTML = entry.description;'
					.'}'
				.'}';
			$entries = array();
			$audios	= array();
			$videos	= array();
			foreach ($item as $temp_item)
			{
				$entry = array();
				// Choose picture to show
				$img = SermonspeakerHelperSermonspeaker::insertPicture($temp_item, 1);
				// Choosing the default file to play based on prio and availabilty
				if ($this->config['type'] == 'auto')
				{
					$file	= SermonspeakerHelperSermonspeaker::getFileByPrio($temp_item, $this->config['prio']);
				}
				else
				{
					$file	= ($this->config['type'] == 'video') ? $temp_item->videofile : $temp_item->audiofile;
				}
				if ($file)
				{
					$entry['file']	= SermonspeakerHelperSermonspeaker::makeLink($file);
				}
				else
				{
					$entry['file']	= ($img) ? $img : JURI::base(true).'/media/com_sermonspeaker/images/'.$this->params->get('defaultpic', 'nopict.jpg');
					$entry['error']	= JText::_('JGLOBAL_RESOURCE_NOT_FOUND');
				}
				$entry['title']	= addslashes($temp_item->title);
				$desc = array();
				if ($temp_item->sermon_date)
				{
					$desc[] = JText::_('JDATE').': '.JHtml::Date($temp_item->sermon_date, JText::_($this->params->get('date_format')), true);
				}
				if ($temp_item->speakers_title)
				{
					$desc[] = JText::_('COM_SERMONSPEAKER_SPEAKER').': '.addslashes($temp_item->speakers_title);
				}
				$entry['description'] = implode('\x3Cbr />', $desc);
				if ($temp_item->sermon_time != '00:00:00')
				{
					$time_arr = explode(':', $temp_item->sermon_time);
					$seconds = ($time_arr[0] * 3600) + ($time_arr[1] * 60) + $time_arr[2];
					$entry['duration'] = $seconds;
				}
				if ($img)
				{
					$entry['image'] = $img;
				}
				foreach ($entry as $key => $value)
				{
					$entry[$key] = $key.":'".$value."'";
				}
				$entries[] = '{'.implode(',', $entry).'}';
				if ($this->toggle)
				{
					// Preparing specific playlists for audio and video
					if ($temp_item->audiofile)
					{
						$file = SermonspeakerHelperSermonspeaker::makeLink($temp_item->audiofile);
						unset($entry['error']);
					}
					else
					{
						$file = ($img) ? $img : JURI::base(true).'/media/com_sermonspeaker/images/'.$this->params->get('defaultpic', 'nopict.jpg');
						$entry['error'] = "error:'".JText::_('JGLOBAL_RESOURCE_NOT_FOUND')."'";
					}
					$entry['file']	= "file:'".$file."'";
					$audios[] = '{'.implode(',', $entry).'}';
					if ($temp_item->videofile)
					{
						$file = SermonspeakerHelperSermonspeaker::makeLink($temp_item->videofile);
						unset($entry['error']);
					}
					else
					{
						$file = ($img) ? $img : JURI::base(true).'/media/com_sermonspeaker/images/'.$this->params->get('defaultpic', 'nopict.jpg');
						$entry['error']	= "error:'".JText::_('JGLOBAL_RESOURCE_NOT_FOUND')."'";
					}
					$entry['file']	= "file:'".$file."'";
					$videos[] = '{'.implode(',', $entry).'}';
				}
			}
			$this->playlist['default'] = implode(',', $entries);
			if ($this->toggle)
			{
				$this->playlist['audio'] = implode(',', $audios);
				$this->playlist['video'] = implode(',', $videos);
			}
		}
		else
		{
			// Single
			$this->setDimensions('23px', '250px');
			$type	= ($this->mode == 'audio') ? 'a' : 'v';
			$entry	= array();

			// Detect file to use
			if ($this->config['type'] == 'auto')
			{
				$file	= SermonspeakerHelperSermonspeaker::getFileByPrio($item, $this->config['prio']);
			}
			else
			{
				$file	= ($this->config['type'] == 'video') ? $item->videofile : $item->audiofile;
			}
			$entry['file']	= SermonspeakerHelperSermonspeaker::makeLink($file);
			if ($img = SermonspeakerHelperSermonspeaker::insertPicture($item, 1))
			{
				$entry['image'] = $img;
			}
			if ($item->sermon_time != '00:00:00')
			{
				$time_arr = explode(':', $item->sermon_time);
				$seconds = ($time_arr[0] * 3600) + ($time_arr[1] * 60) + $time_arr[2];
				$entry['duration'] = $seconds;
			}
			foreach ($entry as $key => $value)
			{
				$entry[$key] = $key.":'".$value."'";
			}
			
			$this->playlist['default'] = '{'.implode(',', $entry).'}';
			if ($this->toggle)
			{
				if ($item->audiofile && $item->videofile)
				{
					$this->playlist['audio']	= "{file:'".SermonspeakerHelperSermonspeaker::makeLink($item->audiofile)."'}";
					$this->playlist['video']	= "{file:'".SermonspeakerHelperSermonspeaker::makeLink($item->videofile)."'}";
				}
				else
				{
					$this->toggle = false;
				}
			}
		}
		foreach ($options as $key => $value)
		{
			$options[$key] = $key.':'.$value;
		}
		$this->setPopup($type);
		$this->script	= '<script type="text/javascript">'
							."jwplayer('mediaspace".$this->config['count']."').setup({"
								."playlist:[".$this->playlist['default']."],"
								."width:'".$this->config[$type.'width']."',"
								."height:'".$this->config[$type.'height']."',"
								.implode(',', $options)
							.'});'
						.'</script>';

		// Loading needed Javascript only once
		if (!self::$script_loaded){
			JHtml::Script('media/com_sermonspeaker/player/jwplayer/jwplayer.js');
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration('function ss_play(id){jwplayer().playlistItem(id);}');
			if ($this->toggle)
			{
				$awidth		= is_numeric($this->config['awidth']) ? $this->config['awidth'].'px' : $this->config['awidth'];
				$aheight	= is_numeric($this->config['aheight']) ? $this->config['aheight'].'px' : $this->config['aheight'];
				$vwidth		= is_numeric($this->config['vwidth']) ? $this->config['vwidth'].'px' : $this->config['vwidth'];
				$vheight	= is_numeric($this->config['vheight']) ? $this->config['vheight'].'px' : $this->config['vheight'];
				if (!is_array($item))
				{
					$url = 'index.php?&task=download&id='.$item->slug.'&type=';
					$download_video = 'document.getElementById("sermon_download").onclick=function(){window.location.href=\''.JRoute::_($url.'video').'\'};document.getElementById("sermon_download").value="'.JText::_('COM_SERMONSPEAKER_DOWNLOADBUTTON_VIDEO').'"';
					$download_audio = 'document.getElementById("sermon_download").onclick=function(){window.location.href=\''.JRoute::_($url.'audio').'\'};document.getElementById("sermon_download").value="'.JText::_('COM_SERMONSPEAKER_DOWNLOADBUTTON_AUDIO').'"';
				} 
				else 
				{
					$download_video = '';
					$download_audio = '';
				}
				$doc->addScriptDeclaration('
					function Video() {
						jwplayer().load(['.$this->playlist['video'].']).resize("'.$vwidth.'","'.$vheight.'");
						document.getElementById("mediaspace'.$this->config['count'].'_wrapper").style.width="'.$vwidth.'";
						document.getElementById("mediaspace'.$this->config['count'].'_wrapper").style.height="'.$vheight.'";
						'.$download_video.'
					}
				');
				$doc->addScriptDeclaration('
					function Audio() {
						jwplayer().load(['.$this->playlist['audio'].']).resize("'.$awidth.'","'.$aheight.'");
						document.getElementById("mediaspace'.$this->config['count'].'_wrapper").style.width="'.$awidth.'";
						document.getElementById("mediaspace'.$this->config['count'].'_wrapper").style.height="'.$aheight.'";
						'.$download_audio.'
					}
				');
			}
			self::$script_loaded = 1;
		}
		return;
	}
}