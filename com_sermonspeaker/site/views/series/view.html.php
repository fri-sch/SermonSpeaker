<?php
/**
 * @package     SermonSpeaker
 * @subpackage  Component.Site
 * @author      Thomas Hunziker <admin@sermonspeaker.net>
 * @copyright   © 2016 - Thomas Hunziker
 * @license     http://www.gnu.org/licenses/gpl.html
 **/

defined('_JEXEC') or die();

/**
 * HTML View class for the SermonSpeaker Component
 *
 * @since  3.4
 */
class SermonspeakerViewSeries extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return mixed A string if successful, otherwise a Error object.
	 *
	 * @throws \Exception
	 * @since ?
	 */
	public function display($tpl = null)
	{
		// Get some data from the models
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Get Category stuff from models
		$this->category  = $this->get('Category');
		$children        = $this->get('Children');
		$this->parent    = $this->get('Parent');
		$this->children  = array($this->category->id => $children);
		$this->params    = $this->state->get('params');
		$this->col_serie = $this->params->get('col_serie');

		if (!$this->col_serie)
		{
			$this->col_serie = array();
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Getting the Speakers for each Series and check if there are avatars at all, only showing column if needed
		$this->av = null;
		$model    = $this->getModel();

		foreach ($this->items as $item)
		{
			if (!$this->av && !empty($item->avatar))
			{
				$this->av = 1;
			}

			if (in_array('series:speaker', $this->col_serie))
			{
				$speakers = $model->getSpeakers($item->id);
				$names    = array();

				foreach ($speakers as $speaker)
				{
					$speaker->speaker_slug  = $speaker->slug;
					$speaker->speaker_state = $speaker->state;
					$names[]                = JLayoutHelper::render('titles.speaker', array('item' => $speaker, 'params' => $this->params));
				}

				$item->speakers = implode(', ', $names);
			}
		}

		if ($this->category == false)
		{
			throw new Exception(JText::_('JGLOBAL_CATEGORY_NOT_FOUND'), 404);
		}

		if ($this->parent == false && $this->category->id != 'root')
		{
			throw new Exception(JText::_('JGLOBAL_CATEGORY_NOT_FOUND'), 404);
		}

		if ($this->category->id == 'root')
		{
			$this->params->set('show_category_title', 0);
			$this->cat = '';
		}
		else
		{
			// Get the category title for backward compatibility
			$this->cat = $this->category->title;
		}

		// Check whether category access level allows access.
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();

		if (!in_array($this->category->access, $groups))
		{
			throw new Exception(JText::_('JGLOBAL_CATEGORY_NOT_FOUND'), 403);
		}

		// Set layout from parameters if not already set elsewhere
		if ($this->getLayout() == 'default')
		{
			$this->setLayout($this->params->get('serieslayout', 'normal'));
		}

		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$this->maxLevel      = $this->params->get('maxLevel', -1);
		$this->_prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since ?
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();

		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_SERMONSPEAKER_SERIES_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetaData('robots', $this->params->get('robots'));
		}
	}
}
