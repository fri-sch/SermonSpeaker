<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$limit 		= (int)$this->params->get('limit', '');
?>
<div class="ss-sermons-container<?php echo htmlspecialchars($this->params->get('pageclass_sfx')); ?>">
<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif;
if ($this->cat): ?>
	<h2><span class="subheading-category"><?php echo $this->cat; ?></span></h2>
<?php endif;
if (in_array('sermons:player', $this->columns) && count($this->items)) : ?>
	<div class="ss-sermons-player">
		<hr class="ss-sermons-player" />
		<?php
		echo $this->player->mspace;
		echo $this->player->script;
		?>
		<hr class="ss-sermons-player" />
	<?php if ($this->params->get('fileswitch')): ?>
		<div>
			<img class="pointer" src="media/com_sermonspeaker/images/Video.png" onclick="Video()" alt="Video" />
			<img class="pointer" src="media/com_sermonspeaker/images/Sound.png" onclick="Audio()" alt="Audio" />
		</div>
	<?php endif; ?>
	</div>
<?php endif; ?>
<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" id="adminForm" name="adminForm">
	<?php if ($this->params->get('filter_field')) :?>
		<fieldset class="filters">
			<legend class="hidelabeltxt">
				<?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?>
			</legend>
			<div class="filter-search">
				<label class="filter-search-lbl" for="filter-search"><?php echo JText::_('JGLOBAL_FILTER_LABEL').'&nbsp;'; ?></label>
				<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_SERMONSPEAKER_FILTER_SEARCH_DESC'); ?>" />
			</div>
	<?php endif;
	if ($this->params->get('show_pagination_limit')) : ?>
			<div class="display-limit">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&nbsp;
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
	<?php endif;
	if ($this->params->get('filter_field')) : ?>
		</fieldset>
	<?php endif; ?>
	<?php if (!count($this->items)) : ?>
		<div class="no_entries"><?php echo JText::sprintf('COM_SERMONSPEAKER_NO_ENTRIES', JText::_('COM_SERMONSPEAKER_SERMONS')); ?></div>
	<?php else : ?>
		<table class="category">
		<!-- Create the headers with sorting links -->
			<thead><tr>
				<?php if (in_array('sermons:num', $this->columns)) : ?>
					<th class="num">
						<?php if (!$limit) :
							echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_SERMONNUMBER', 'sermon_number', $listDirn, $listOrder);
						else :
							echo JText::_('COM_SERMONSPEAKER_SERMONNUMBER');
						endif; ?>
					</th>
				<?php endif; ?>
				<th class="ss-title">
					<?php if (!$limit) :
						echo JHTML::_('grid.sort', 'JGLOBAL_TITLE', 'sermon_title', $listDirn, $listOrder);
					else :
						echo JText::_('JGLOBAL_TITLE');
					endif; ?>
				</th>
				<?php if (in_array('sermons:scripture', $this->columns)) : ?>
					<th class="ss-col">
						<?php if (!$limit) :
							echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_FIELD_SCRIPTURE_LABEL', 'sermon_scripture', $listDirn, $listOrder);
						else :
							echo JText::_('COM_SERMONSPEAKER_FIELD_SCRIPTURE_LABEL');
						endif; ?>
					</th>
				<?php endif;
				if (in_array('sermons:speaker', $this->columns)) : ?>
					<th class="ss-col">
						<?php if (!$limit) :
							 echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_SPEAKER', 'name', $listDirn, $listOrder);
						else :
							echo JText::_('COM_SERMONSPEAKER_SPEAKER');
						endif; ?>
					</th>
				<?php endif;
				if (in_array('sermons:date', $this->columns)) : ?>
					<th class="ss-col">
						<?php if (!$limit) :
							 echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_FIELD_DATE_LABEL', 'sermon_date', $listDirn, $listOrder);
						else :
							echo JText::_('COM_SERMONSPEAKER_FIELD_DATE_LABEL');
						endif; ?>
					</th>
				<?php endif;
				if (in_array('sermons:length', $this->columns)) : ?>
					<th class="ss-col">
						<?php if (!$limit) :
							 echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_FIELD_LENGTH_LABEL', 'sermon_time', $listDirn, $listOrder);
						else :
							echo JText::_('COM_SERMONSPEAKER_FIELD_LENGTH_LABEL');
						endif; ?>
					</th>
				<?php endif;
				if (in_array('sermons:series', $this->columns)) : ?>
					<th class="ss-col">
						<?php if (!$limit) :
							 echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_SERIES', 'series_title', $listDirn, $listOrder);
						else :
							echo JText::_('COM_SERMONSPEAKER_SERIES');
						endif; ?>
					</th>
				<?php endif;
				if (in_array('sermons:addfile', $this->columns)) : ?>
					<th class="ss-col">
						<?php if (!$limit) :
							 echo JHTML::_('grid.sort', 'COM_SERMONSPEAKER_ADDFILE', 'addfileDesc', $listDirn, $listOrder);
						else :
							echo JText::_('COM_SERMONSPEAKER_ADDFILE');
						endif; ?>
					</th>
				<?php endif;
				if (in_array('sermons:hits', $this->columns)) : ?>
					<th class="ss-col">
						<?php if (!$limit) :
							echo JHTML::_('grid.sort', 'JGLOBAL_HITS', 'hits', $listDirn, $listOrder);
						else :
							echo JText::_('JGLOBAL_HITS');
						endif; ?>
					</th>
				<?php endif; ?>
			</tr></thead>
		<!-- Begin Data -->
			<tbody>
				<?php foreach($this->items as $i => $item) : ?>
					<tr id="sermon<?php echo $i; ?>" class="<?php echo ($i % 2) ? "odd" : "even"; ?>">
						<?php if (in_array('sermons:num', $this->columns)) : ?>
							<td class="num">
								<?php echo $item->sermon_number; ?>
							</td>
						<?php endif; ?>
						<td class="ss-title">
							<?php echo SermonspeakerHelperSermonspeaker::insertSermonTitle($i, $item); ?>
						</td>
						<?php if (in_array('sermons:scripture', $this->columns)) : ?>
							<td class="ss-col">
								<?php echo JHTML::_('content.prepare', $item->sermon_scripture); ?>
							</td>
						<?php endif;
						if (in_array('sermons:speaker', $this->columns)) : ?>
							<td class="ss-col">
								<?php if ($item->speaker_state):
									echo SermonspeakerHelperSermonSpeaker::SpeakerTooltip($item->speaker_slug, $item->pic, $item->name);
								else :
									echo $item->name;
								endif; ?>
							</td>
						<?php endif;
						if (in_array('sermons:date', $this->columns)) : ?>
							<td class="ss-col">
								<?php echo JHTML::date($item->sermon_date, JText::_($this->params->get('date_format')), 'UTC'); ?>
							</td>
						<?php endif;
						if (in_array('sermons:length', $this->columns)) : ?>
							<td class="ss-col">
								<?php echo SermonspeakerHelperSermonspeaker::insertTime($item->sermon_time); ?>
							</td>
						<?php endif;
						if (in_array('sermons:series', $this->columns)) : ?>
							<td class="ss-col">
								<?php if ($item->series_state): ?>
									<a href="<?php echo JRoute::_(SermonspeakerHelperRoute::getSerieRoute($item->series_slug)); ?>">
										<?php echo $item->series_title; ?>
									</a>
								<?php else:
									echo $item->series_title;
								endif; ?>
							</td>
						<?php endif;
						if (in_array('sermons:addfile', $this->columns)) : ?>
							<td class="ss-col">
								<?php echo SermonspeakerHelperSermonspeaker::insertAddfile($item->addfile, $item->addfileDesc); ?>
							</td>
						<?php endif;
						if (in_array('sermons:hits', $this->columns)) : ?>
							<td class="ss-col">
								<?php echo $item->hits; ?>
							</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif;
	if ($this->params->get('show_pagination') && ($this->pagination->get('pages.total') > 1)) : ?>
		<div class="pagination">
			<?php if ($this->params->get('show_pagination_results', 1)) : ?>
				<p class="counter">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</p>
			<?php endif;
			echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
</form>
</div>