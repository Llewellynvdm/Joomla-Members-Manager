<?php
/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th July, 2018
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */


// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Membersmanager Route Helper
 **/
abstract class MembersmanagerHelperRoute
{
	protected static $lookup;

	/**
	 * @param int The route of the Members
	 */
	public static function getMembersRoute($id = 0, $catid = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'members'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_membersmanager&view=members&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'members'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_membersmanager&view=members';
		}
		if ($catid > 1)
		{
			$categories = JCategories::getInstance('membersmanager.members');
			$category = $categories->get($catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * @param int The route of the Cpanel
	 */
	public static function getCpanelRoute($id = 0, $catid = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'cpanel'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_membersmanager&view=cpanel&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'cpanel'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_membersmanager&view=cpanel';
		}
		if ($catid > 1)
		{
			$categories = JCategories::getInstance('membersmanager.cpanel');
			$category = $categories->get($catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * @param int The route of the Profile
	 */
	public static function getProfileRoute($id = 0, $catid = 0)
	{
		if ($id > 0)
		{
			// Initialize the needel array.
			$needles = array(
				'profile'  => array((int) $id)
			);
			// Create the link
			$link = 'index.php?option=com_membersmanager&view=profile&id='. $id;
		}
		else
		{
			// Initialize the needel array.
			$needles = array(
				'profile'  => array()
			);
			// Create the link but don't add the id.
			$link = 'index.php?option=com_membersmanager&view=profile';
		}
		if ($catid > 1)
		{
			$categories = JCategories::getInstance('membersmanager.profile');
			$category = $categories->get($catid);
			if ($category)
			{
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		if ($item = self::_findItem($needles))
		{
			$link .= '&Itemid='.$item;
		}

		return $link;
	}

	/**
	 * Get the URL route for membersmanager category from a category ID and language
	 *
	 * @param   mixed    $catid     The id of the items's category either an integer id or a instance of JCategoryNode
	 * @param   mixed    $language  The id of the language being used.
	 *
	 * @return  string  The link to the contact
	 *
	 * @since   1.5
	 */
	public static function getCategoryRoute_keep_for_later($catid, $language = 0)
	{
		if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;			
			$category = $catid;			 
		}
		else
		{			
			throw new Exception('First parameter must be JCategoryNode');			
		}
	
		$views = array();
		$view = $views[$category->extension];
       
		if ($id < 1 || !($category instanceof JCategoryNode))
		{
			$link = '';
		}
		else
		{
			//Create the link
			$link = 'index.php?option=com_membersmanager&view='.$view.'&category='.$category->slug;
			
			$needles = array(
					$view => array($id),
					'category' => array($id)
			);
	
			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				$db		= JFactory::getDbo();
				$query	= $db->getQuery(true)
					->select('a.sef AS sef')
					->select('a.lang_code AS lang_code')
					->from('#__languages AS a');
	
				$db->setQuery($query);
				$langs = $db->loadObjectList();
				foreach ($langs as $lang)
				{
					if ($language == $lang->lang_code)
					{
						$link .= '&lang='.$lang->sef;
						$needles['language'] = $language;
					}
				}
			}
	
			if ($item = self::_findItem($needles,'category'))
			{

				$link .= '&Itemid='.$item;				
			}
			else
			{
				if ($category)
				{
					$catids = array_reverse($category->getPath());
					$needles = array(
							'category' => $catids
					);
					if ($item = self::_findItem($needles,'category'))
					{
						$link .= '&Itemid='.$item;
					}
					elseif ($item = self::_findItem(null, 'category'))
					{
						$link .= '&Itemid='.$item;
					}
				}
			}
		}
		return $link;
	}

	protected static function _findItem($needles = null,$type = null)
	{
		$app      = JFactory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();

			$component  = JComponentHelper::getComponent('com_membersmanager');

			$attributes = array('component_id');
			$values     = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}

					if (isset($item->query['id']))
					{
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
					else
					{
						self::$lookup[$language][$view][0] = $item->id;
					}
				}
			}
		}

		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					if (MembersmanagerHelper::checkArray($ids))
					{
						foreach ($ids as $id)
						{
							if (isset(self::$lookup[$language][$view][(int) $id]))
							{
								return self::$lookup[$language][$view][(int) $id];
							}
						}
					}
					elseif (isset(self::$lookup[$language][$view][0]))
					{
						return self::$lookup[$language][$view][0];
					}
				}
			}
		}

		if ($type)
		{
			// Check if the global menu item has been set.
			$params = JComponentHelper::getParams('com_membersmanager');
			if ($item = $params->get($type.'_menu', 0))
			{
				return $item;
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();

		if ($active
			&& $active->component == 'com_membersmanager'
			&& ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}
}
