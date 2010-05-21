<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
// JHTML::_('behavior.tooltip');
$Itemid	= JRequest::getInt('Itemid');
global $option;
if ($this->params->get('ga')) { $callback = "&callback=".$this->params->get('ga'); }
$return = SermonspeakerHelperSermonspeaker::insertAddfile($this->row[0]->addfile, $this->row[0]->addfileDesc);
$id = $this->row[0]->id;
?>
<table width="100%" cellpadding="2" cellspacing="0">
	<tr class="componentheading">
		<th align="left" valign="bottom"><?php echo JText::_('SINGLESERMON'); ?></th>
	</tr>
</table>
<!-- Begin Data -->
<table border="0" cellpadding="2" cellspacing="2" width="100%">
	<?php if ($this->params->get('hide_dl') == "0" && strlen($this->row[0]->sermon_path) > 0) { ?>
		<tr>
			<td valign="top"><b><?php echo JText::_('SERMONNAME'); ?>:</b></td>
			<td><a title="<?php echo JText::_('SINGLESERMON'); ?>" href="<?php echo $this->lnk.'">'.$this->escape($this->row[0]->sermon_title); ?></a></td>
		</tr>
		<?php if ($this->params->get('dl_button') == "1") { ?>
		<tr>
			<td></td>
			<?php
			echo SermonspeakerHelperSermonspeaker::insertdlbutton($id, $this->row[0]->sermon_path);
			?>
		</tr>
		<?php }
	} else { ?>
		<tr>
			<td valign ="top"><b><?php echo JText::_('SERMONNAME'); ?>:</b></td>
			<td><?php echo $this->escape($this->row[0]->sermon_title); ?></td>
		</tr>
	<?php } ?>
	<tr>
		<td valign="top"><b><?php echo JText::_('SERMON_DATE'); ?>:</b></td>
		<td><?php echo JHTML::date($this->row[0]->sermon_date, '%x', 0); ?></td>
	</tr>
	<tr>
		<td valign="top"><b><?php echo JText::_('SCRIPTURE'); ?>:</b></td>
		<td><?php echo $this->escape($this->row[0]->sermon_scripture); ?></td>
	</tr>
	<tr>
		<td valign="top"><b><?php echo JText::_('SERIES'); ?>:</b></td>
		<td>
			<a href="<?php echo JRoute::_('index.php?view=serie&id='.$this->serie->id); ?>">
			<?php echo $this->escape($this->serie->series_title); ?>
			</a>
		</td>
	</tr>
	<tr>
		<td valign="top"><b><?php echo JText::_('SPEAKER'); ?>:</b></td>
		<td>
			<?php echo SermonspeakerHelperSermonSpeaker::SpeakerTooltip($this->speaker->id, $this->speaker->pic, $this->speaker->name); ?>
		</td>
	</tr>
	<?php if ($this->speaker->pic) { ?>
	<tr>
		<td></td>
		<td><img height=150 src="<?php echo $this->speaker->pic; ?>"></td>
	</tr>
	<?php } ?>
	<tr>
		<td valign="top"><b><?php echo JText::_('SERMONTIME'); ?>:</b></td>
		<td><?php echo SermonspeakerHelperSermonspeaker::insertTime($this->row[0]->sermon_time); ?></td>
	</tr>
	<tr>
		<td valign="top"><b><?php echo JText::_('HITS'); ?>:</b></td>
		<td><?php echo $this->row[0]->hits; ?></td>
	</tr>
	<?php if ($this->params->get('client_col_sermon_notes') && strlen($this->row[0]->notes) > 0){ ?>
		<tr>
			<td valign="top"><b><?php echo JText::_('SERMONNOTES'); ?>:</b></td>
			<td><?php echo $this->row[0]->notes; ?></td>
		</tr>
	<?php }
	if ($this->params->get('client_col_player')){ ?>
	<tr>
		<td></td>
		<td>
			<br />
			<?php 
			$ret = SermonspeakerHelperSermonspeaker::insertPlayer($this->lnk);
			$pp_ret = explode("/",$ret);
			$pp_h = $pp_ret[0];
			$pp_w = $pp_ret[1];
			?>
			<br />
		</td>
	</tr>
	<?php } // if client_col_player
	if ($this->params->get('popup_player') == "1") { 
	?>
		<tr><td></td>
		<td><input class="popup_btn" type="button" name="<?php echo JText::_('POPUP_PLAYER'); ?>" value="<?php echo JText::_('POPUP_PLAYER'); ?>" onclick="popup = window.open('<?php echo JRoute::_("index.php?view=sermon&layout=popup&id=$id&tmpl=component"); ?>', 'PopupPage', 'height=<?php echo $pp_h.",width=".$pp_w; ?>,scrollbars=yes,resizable=yes'); return false" /></td></tr>
	<?php }
	$return = SermonspeakerHelperSermonspeaker::insertAddfile($this->row[0]->addfile, $this->row[0]->addfileDesc);
	if ($return != NULL) { ?>
		<tr>
			<td valign="top"><b><?php echo JText::_('ADDFILE'); ?>:</b></td>
			<td><?php echo $return; ?></td>
		</tr>
	<?php } ?>
</table>
<table width="100%">
	<?php
	// Support for JComments
	$comments = JPATH_BASE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php';
	if (file_exists($comments)) {
		require_once($comments); ?>
		<tr><td><br></td></tr>
		<tr>
			<td>
				<?php echo JComments::showComments($id, 'com_sermonspeaker', $this->row[0]->sermon_title); ?>
			</td>
		</tr>
	<?php } ?>
</table>