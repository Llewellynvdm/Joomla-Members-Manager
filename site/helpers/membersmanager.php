<?php
/**
 * @package    Joomla.Members.Manager
 *
 * @created    6th September, 2015
 * @author     Llewellyn van der Merwe <https://www.joomlacomponentbuilder.com/>
 * @github     Joomla Members Manager <https://github.com/vdm-io/Joomla-Members-Manager>
 * @copyright  Copyright (C) 2015. All Rights Reserved
 * @license    GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Membersmanager component helper
 */
abstract class MembersmanagerHelper
{

	/**
	*	The Global Site Event Method.
	**/
	public static function globalEvent($document)
	{
		// the Session keeps track of all data related to the current session of this user
		self::loadSession();
	}

	/**
	* the params
	**/
	protected static $params;

	/**
	 * Get placeholders
	 *
	 * @param   string   $type     The type of placeholders to return
	 *
	 * @return array
	 *
	 */
	public static function getPlaceHolders($type)
	{
		if ('document' === $type)
		{
			// get company placeholders
			if (($company = self::getCompanyDetails('placeholder')) !== false && self::checkArray($company))
			{
				// just remove the footer and header placeholders
				unset($company['[company_doc_header]']);
				unset($company['[company_doc_footer]']);
				$placeholders = array_keys($company);
				return $placeholders;
			}
		}
		return false;
	}

	/**
	 * Add Join Tables based on filter
	 *
	 * @param   object   $query  The database query object
	 * @param   string   $filter  The target area that made the request
	 *
	 * @return array
	 *
	 */
	protected static function joinMemberDetails(&$query, $filter = 'none', $db = null)
	{
		// check if we have the DB object
		if (!self::checkObject($db))
		{
			// get the database object
			$db = JFactory::getDBO();
		}
		if ('none' === $filter)
		{
			// From the users table.
			$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('a.user') . ' = ' . $db->quoteName('u.id') . ')');
			// From the membersmanager_member table.
			$query->join('LEFT', $db->quoteName('#__membersmanager_member', 'm') . ' ON (' . $db->quoteName('a.main_member') . ' = ' . $db->quoteName('m.id') . ')');
			// From the membersmanager_type table.
			$query->join('LEFT', $db->quoteName('#__membersmanager_type', 't') . ' ON (' . $db->quoteName('a.type') . ' = ' . $db->quoteName('t.id') . ')');
		}
	}

	/**
	 * Get selection  based on type
	 *
	 * @param   string   $table     The main table to select
	 * @param   string   $method    The type of values to return
	 * @param   string   $filter    The kind of filter (to return only values required)
	 * @param   object   $db     The database object
	 *
	 * @return array
	 *
	 */
	protected static function getSelection($table = 'member', $method = 'placeholder', $filter = 'none', $db = null)
	{
		// prep for placeholders
		$f = '';
		$b = '';
		if ('placeholder' === $method)
		{
			$f = '[member_';
			$b = ']';
		}
		// only get what we need
		if ('none' === $filter)
		{
			// check if we have the DB object
			if (!self::checkObject($db))
			{
				// get the database object
				$db = JFactory::getDBO();
			}
			// get the database columns of this table
			$columns = $db->getTableColumns("#__membersmanager_" . $table, false);
			// always remove these
			$remove = array('asset_id', 'checked_out', 'checked_out_time');
			// remove
			foreach ($remove as $key)
			{
				unset($columns[$key]);
			}
			// prep the columns
			$columns = array_keys($columns);
		}
		// convert the columns for query selection
		$selection = array();
		foreach ($columns as $column)
		{
			$selection['a.' . $column] = $f . $column . $b;
		}
		// add joined values if needed (based on filter)
		if ('member' === $table && 'none' === $filter)
		{
			$selection['u.name'] = $f . 'user_name' . $b;
			$selection['u.email'] = $f . 'user_email' . $b;
			$selection['t.name'] = $f . 'type_name' . $b;
			$selection['m.user'] = $f . 'main_user' . $b;
			$selection['m.name'] = $f . 'main_name' . $b;
			$selection['m.email'] = $f . 'main_email' . $b;
		}
		return $selection;
	}

	/**
	 * Get a user Access Types
	 *
	 * @param   mix      $id    The the user ID/object
	 * @param   int      $type  The type of access to return (1 = type, 2 = groups)
	 * @param   object   $db    The database object
	 *
	 * @return  mix array
	 *
	 */
	public static function getAccess($user = null, $type = 1, $db = null)
	{
		// get DB
		if (!$db)
		{
			$db = JFactory::getDBO();
		}
		// get user
		if (!self::checkObject($user))
		{
			if (is_numeric($user) && $user > 0)
			{
				$user = JFactory::getUser($user);
			}
			else
			{
				$user = JFactory::getUser();
			}
		}
		// function to setup the group array
		$getGroups = function ($groups) {
			// convert to array
			if (self::checkJson($groups))
			{
				return (array) json_decode($groups, true);
			}
			elseif (is_numeric($groups))
			{
				return array($groups);
			}
			return false;
		};
		// merge groups
		$mergeGroups = function ($types) use($getGroups){
			$bucket = array();
			foreach ($types as $id => $groups)
			{
				$groups = $getGroups($groups);
				if (self::checkArray($groups))
				{
					foreach ($groups as $group)
					{
						$bucket[$group] = $group;
					}
				}
			}
			return $bucket;
		};
		// check if access it needed
		if (!$user->authorise('core.admin'))
		{
			// get all types
			$query = $db->getQuery(true);
			$query->select(array('a.id', 'a.groups_access'));
			$query->from('#__membersmanager_type AS a');
			$db->setQuery($query);
			$db->execute();
			// get all types
			$types = $db->loadAssocList('id', 'groups_access');
			if (self::checkArray($types))
			{
				$bucket = array();
				foreach ($types as $id => $groups_access)
				{
					$groups_access = $getGroups($groups_access);
					if (self::checkArray($groups_access) && array_intersect($groups_access, $user->groups))
					{
						$bucket[$id] = $id;
					}
				}
				// return types that this member can access
				if (1 == $type && self::checkArray($bucket))
				{
					return $bucket;
				}
				elseif (self::checkArray($bucket))
				{
					// get the targeted groups
					$query = $db->getQuery(true);
					$query->select(array('a.id', 'a.groups_target'));
					$query->from('#__membersmanager_type AS a');
					$query->where('a.id IN ('. implode(',', $bucket) . ')');
					$db->setQuery($query);
					$db->execute();
					// get all types
					$targettypes = $db->loadAssocList('id', 'groups_target');
					if (self::checkArray($targettypes))
					{
						$targetbucket = $mergeGroups($targettypes);
						// return types that this member can access
						if (self::checkArray($targetbucket))
						{
							return $targetbucket;
						}
					}
				}
			}
			return false;
		}
		// return all types/groups
		$query = $db->getQuery(true);
		if (1 == $type)
		{
			$query->select(array('a.id'));
		}
		else
		{
			$query->select(array('a.id', 'a.groups_target'));
		}
		$query->from('#__membersmanager_type AS a');
		$db->setQuery($query);
		$db->execute();
		if (1 == $type)
		{
			return $db->loadColumn();
		}
		// get all types with group target
		$types = $db->loadAssocList('id', 'groups_target');
		if (self::checkArray($types))
		{
			$bucket = $mergeGroups($types);
			// return types that this member can access
			if (self::checkArray($bucket))
			{
				return $bucket;
			}
		}
		// return all groups
		$query = $db->getQuery(true);
		$query->select(array('a.id'));
		$query->from('#__usergroups AS a');
		$db->setQuery($query);
		$db->execute();
		return $db->loadColumn();
	}

	/**
	* set the session defaults if not set
	**/
	protected static function setSessionDefaults()
	{
		// noting for now
		return true;
	}

	/**
	 *	Change to nice fancy date
	 */
	public static function fancyDate($date)
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('jS \o\f F Y',$date);
	}

	/**
	 *	Change to nice fancy day time and date
	 */
	public static function fancyDayTimeDate($time)
	{
		if (!self::isValidTimeStamp($time))
		{
			$time = strtotime($time);
		}
		return date('D ga jS \o\f F Y',$time);
	}

	/**
	 *	Change to nice fancy time and date
	 */
	public static function fancyDateTime($time)
	{
		if (!self::isValidTimeStamp($time))
		{
			$time = strtotime($time);
		}
		return date('(G:i) jS \o\f F Y',$time);
	}

	/**
	 *	Change to nice hour:minutes time
	 */
	public static function fancyTime($time)
	{
		if (!self::isValidTimeStamp($time))
		{
			$time = strtotime($time);
		}
		return date('G:i',$time);
	}

	/**
	 *	Check if string is a valid time stamp
	 */
	public static function isValidTimeStamp($timestamp)
	{
		return ((int) $timestamp === $timestamp)
		&& ($timestamp <= PHP_INT_MAX)
		&& ($timestamp >= ~PHP_INT_MAX);
	}
 

	/**
	* 	the Butler
	**/
	public static $session = array();

	/**
	* 	the Butler Assistant 
	**/
	protected static $localSession = array();

	/**
	* 	start a session if not already set, and load with data
	**/
	public static function loadSession()
	{
		if (!isset(self::$session) || !self::checkObject(self::$session))
		{
			self::$session = JFactory::getSession();
		}
		// set the defaults
		self::setSessionDefaults();
	}

	/**
	* 	give Session more to keep
	**/
	public static function set($key, $value)
	{
		// set to local memory to speed up program
		self::$localSession[$key] = $value;
		// load to session for later use
		return self::$session->set($key, self::$localSession[$key]);
	}

	/**
	* 	get info from Session
	**/
	public static function get($key, $default = null)
	{
		// check if in local memory
		if (!isset(self::$localSession[$key]))
		{
			// set to local memory to speed up program
			self::$localSession[$key] = self::$session->get($key, $default);
		}
		return self::$localSession[$key];
	}


	/**
	* 	prepare base64 string for url
	**/
	public static function base64_urlencode($string, $encode = false)
	{
		if ($encode)
		{
			$string = base64_encode($string);
		}
		return str_replace(array('+', '/'), array('-', '_'), $string);
	}

	/**
	* 	prepare base64 string form url
	**/
	public static function base64_urldecode($string, $decode = false)
	{
		$string = str_replace(array('-', '_'), array('+', '/'), $string);
		if ($decode)
		{
			$string = base64_decode($string);
		}
		return $string;
	}


	/**
	* the locker
	*
	* @var array 
	**/
	protected static $locker = array();

	/**
	* the dynamic replacement salt
	*
	* @var array 
	**/
	protected static $globalSalt = array();

	/**
	* the timer
	*
	* @var object
	**/
	protected static $keytimer;

	/**
	* To Lock string
	*
	* @param string   $string     The string/array to lock
	* @param string   $key        The custom key to use
	* @param int      $salt       The switch to add salt and type of salt
	* @param int      $dynamic    The dynamic replacement array of salt build string
	* @param int      $urlencode  The switch to control url encoding
	*
	* @return string    Encrypted String
	*
	**/
	public static function lock($string, $key = null, $salt = 2, $dynamic = null, $urlencode = true)
	{
		// get the global settings
		if (!$key || !self::checkString($key))
		{
			// set temp timer
			$timer = 2;
			// if we have a timer use it
			if ($salt > 0)
			{
				$timer = $salt;
			}
			// set the default key
			$key = self::salt($timer, $dynamic);
			// try getting the system key
			if (method_exists(get_called_class(), "getCryptKey")) 
			{
				// try getting the medium key first the fall back to basic, and then default
				$key = self::getCryptKey('medium', self::getCryptKey('basic', $key));
			}
		}
		// check if we have a salt timer
		if ($salt > 0)
		{
			$key .= self::salt($salt, $dynamic);
		}
		// get the locker settings
		if (!isset(self::$locker[$key]) || !self::checkObject(self::$locker[$key]))
		{
			self::$locker[$key] = new FOFEncryptAes($key, 128);
		}
		// convert array or object to string
		if (self::checkArray($string) || self::checkObject($string))
		{
			$string = serialize($string);
		}
		// prep for url
		if ($urlencode && method_exists(get_called_class(), "base64_urlencode"))
		{
			return self::base64_urlencode(self::$locker[$key]->encryptString($string));
		}
		return self::$locker[$key]->encryptString($string);
	}

	/**
	* To un-Lock string
	*
	* @param string  $string       The string to unlock
	* @param string  $key          The custom key to use
	* @param int      $salt           The switch to add salt and type of salt
	* @param int      $dynamic    The dynamic replacement array of salt build string
	* @param int      $urlencode  The switch to control url decoding
	*
	* @return string    Decrypted String
	*
	**/
	public static function unlock($string, $key = null, $salt = 2, $dynamic = null, $urlencode = true)
	{
		// get the global settings
		if (!$key || !self::checkString($key))
		{
			// set temp timer
			$timer = 2;
			// if we have a timer use it
			if ($salt > 0)
			{
				$timer = $salt;
			}
			// set the default key
			$key = self::salt($timer, $dynamic);
			// try getting the system key
			if (method_exists(get_called_class(), "getCryptKey")) 
			{
				// try getting the medium key first the fall back to basic, and then default
				$key = self::getCryptKey('medium', self::getCryptKey('basic', $key));
			}
		}
		// check if we have a salt timer
		if ($salt > 0)
		{
			$key .= self::salt($salt, $dynamic);
		}
		// get the locker settings
		if (!isset(self::$locker[$key]) || !self::checkObject(self::$locker[$key]))
		{
			self::$locker[$key] = new FOFEncryptAes($key, 128);
		}
		// make sure we have real base64
		if ($urlencode && method_exists(get_called_class(), "base64_urldecode"))
		{
			$string = self::base64_urldecode($string);
		}
		// basic decrypt string.
		if (!empty($string) && !is_numeric($string) && $string === base64_encode(base64_decode($string, true)))
		{
			$string = rtrim(self::$locker[$key]->decryptString($string), "\0");
			// convert serial string to array
			if (self::is_serial($string))
			{
				$string = unserialize($string);
			}
		}
		return $string;
	}

	/**
	* The Salt
	*
	* @param int   $type      The type of length the salt should be valid
	* @param int   $dynamic   The dynamic replacement array of salt build string
	*
	* @return string
	*
	**/
	public static function salt($type = 1, $dynamic = null)
	{
		// get dynamic replacement salt
		$dynamic = self::getDynamicSalt($dynamic);
		// get the key timer
		if (!self::checkObject(self::$keytimer))
		{
			// load the date time object
			self::$keytimer = new DateTime;
			// set the correct time stamp
			$vdmLocalTime = new DateTimeZone('Africa/Windhoek');
			self::$keytimer->setTimezone($vdmLocalTime);
		}
		// set type
		if ($type == 2)
		{
			// hour
			$format = 'Y-m-d \o\n ' . self::periodFix(self::$keytimer->format('H'));
		}
		elseif ($type == 3)
		{
			// day
			$format = 'Y-m-' . self::periodFix(self::$keytimer->format('d'));
		}
		elseif ($type == 4)
		{
			// month
			$format = 'Y-' . self::periodFix(self::$keytimer->format('m'));
		}
		else
		{
			// minute
			$format = 'Y-m-d \o\n H:' . self::periodFix(self::$keytimer->format('i'));
		}
		// get key
		if (self::checkArray($dynamic))
		{
			return md5(str_replace(array_keys($dynamic), array_values($dynamic), self::$keytimer->format($format) . ' @ VDM.I0'));
		}
		return md5(self::$keytimer->format($format) . ' @ VDM.I0');
	}

	/**
	* The function to insure the salt is valid within the given period (third try)
	*
	* @param int $main    The main number
	*/
	protected static function periodFix($main)
	{
		return round($main / 3) * 3;
	}

	/**
	* Check if a string is serialized
	*
	* @param  string   $string
	*
	* @return Boolean
	*
	*/
	public static function is_serial($string)
	{
		return (@unserialize($string) !== false);
	}

	/**
	* Get dynamic replacement salt
	*/
	public static function getDynamicSalt($dynamic = null)
	{
		// load global if not manually set
		if (!self::checkArray($dynamic))
		{
			return self::getGlobalSalt();
		}
		// return manual values if set
		else
		{
			return $dynamic;
		}
	}

	/**
	* The random or dynamic secret salt
	*/
	public static function getSecretSalt($string = null, $size = 9)
	{
		// set the string
		if (!$string)
		{
			// get random string 
			$string = self::randomkey($size);
		}
		// convert string to array
		$string = self::safeString($string);
		// convert string to array
		$array = str_split($string);
		// insure only unique values are used
		$array = array_unique($array);
		// set the size
		$size = ($size <= count($array)) ? $size : count($array);
		// down size the 
		return array_slice($array, 0, $size);
	}

	/**
	* Get global replacement salt
	*/
	public static function getGlobalSalt()
	{
		// load from memory if found
		if (!self::checkArray(self::$globalSalt))
		{
			// get the global settings
			if (!self::checkObject(self::$params))
			{
				self::$params = JComponentHelper::getParams('com_membersmanager');
			}
			// check if we have a global dynamic replacement array available (format -->  ' 1->!,3->E,4->A')
			$tmp = self::$params->get('dynamic_salt', null);
			if (self::checkString($tmp) && strpos($tmp, ',') !== false && strpos($tmp, '->') !== false)
			{
				$salt = array_map('trim', (array) explode(',', $tmp));
				if (self::checkArray($salt ))
				{
					foreach($salt as $replace)
					{
						$dynamic = array_map('trim', (array) explode('->', $replace));
						if (isset($dynamic[0]) && isset($dynamic[1]))
						{
							self::$globalSalt[$dynamic[0]] = $dynamic[1];
						}
					}
				}
			}
		}
		// return global if found
		if (self::checkArray(self::$globalSalt))
		{
			return self::$globalSalt;
		}
		// return default as fail safe
		return array('1' => '!', '3' => 'E', '4' => 'A');	
	}

	/**
	* Close public protocol
	*/
	public static function closePublicProtocol($id, $public)
	{
		// get secret salt
		$secretSalt = self::getSecretSalt(self::salt(1,array('4' => 'R','1' => 'E','2' => 'G','7' => 'J','8' => 'A')));
		// get the key
		$key = self::salt(1, $secretSalt);
		// get secret salt
		$secret = self::getSecretSalt();
		// set the secret
		$close['SECRET'] = self::lock($secret, $key, 1, array('1' => 's', '3' => 'R', '4' => 'D'));
		// get the key
		$key = self::salt(1, $secret);
		// get the public key
		$close['PUBLIC'] = self::lock($public, $key, 1, array('1' => '!', '3' => 'E', '4' => 'A'));
		// get secret salt
		$secretSalt = self::getSecretSalt($public);
		// get the key
		$key = self::salt(1, $secretSalt);
		// get the ID
		$close['ID'] = self::unlock($id, $key, 1, array('1' => 'i', '3' => 'e', '4' => 'B'));
		// return closed values
		return $close;
	}

	/**
	* Open public protocol
	*/
	public static function openPublicProtocol($SECRET, $ID, $PUBLIC)
	{
		// get secret salt
		$secretSalt = self::getSecretSalt(self::salt(1,array('4' => 'R','1' => 'E','2' => 'G','7' => 'J','8' => 'A')));
		// get the key
		$key = self::salt(1, $secretSalt);
		// get the $SECRET
		$SECRET = self::unlock($SECRET, $key, 1, array('1' => 's', '3' => 'R', '4' => 'D'));
		// get the key
		$key = self::salt(1, $SECRET);
		// get the public key
		$open['public'] = self::unlock($PUBLIC, $key, 1, array('1' => '!', '3' => 'E', '4' => 'A'));
		// get secret salt
		$secretSalt = self::getSecretSalt($open['public']);
		// get the key
		$key = self::salt(1, $secretSalt);
		// get the ID
		$open['id'] = self::unlock($ID, $key, 1, array('1' => 'i', '3' => 'e', '4' => 'B'));
		// return opened values
		return $open;
	}

	protected static $dynamicData = array();

	public static function setDynamicData($data, $placeholders)
	{
		$keyMD5 = md5($data.json_encode($placeholders));
		if (!isset(self::$dynamicData[$keyMD5]))
		{
			if (self::checkArray($placeholders))
			{
				self::setTheIF($data, $placeholders);
				self::$dynamicData[$keyMD5] = str_replace(array_keys($placeholders), array_values($placeholders), $data);
			}
			else
			{
				self::$dynamicData[$keyMD5] = $data;
			}
		}
		return self::$dynamicData[$keyMD5];
	}

	protected static function setTheIF(&$string, $placeholders)
	{		
		// only normal if endif
		$condition 	= '[a-z0-9\_\-]+';
		$inner		= '((?:(?!\[\/?IF)(?!\[\/?ELSE)(?!\[\/?ELSEIF).)*?)';
		$if		= '\[IF\s?('.$condition.')\]';
		$elseif		= '\[ELSEIF\s?('.$condition.')\]';
		$else		= '\[ELSE\]';
		$endif		= '\[ENDIF\]';
		// set the patterns
		$patterns = array();
		// normal if endif
		$patterns[] = '#'.$if.$inner.$endif.'#is';
		// normal if else endif
		$patterns[] = '#'.$if.$inner.$else.$inner.$endif.'#is';
		// dynamic if elseif's endif
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$endif.'#is';
		// dynamic if elseif's else endif
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		$patterns[] = '#'.$if.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$elseif.$inner.$else.$inner.$endif.'#is';
		// run the patterns to setup the string
		foreach ($patterns as $pattern)
		{
			while (preg_match($pattern, $string, $match))
			{
				$keep 	= self::remainderIF($match, $placeholders);
				$string	= preg_replace($pattern, $keep, $string, 1);
			}
		}
	}

	protected static function remainderIF(&$match, &$placeholders)
	{	
		// default we keep nothing
		$keep = '';
		$found = false;
		// get match lenght
		$length = count($match);
		// ranges to check
		$ii = range(2,30,2); // even numbers (content)
		$iii = range(1, 25, 2); // odd numbers (placeholder)
		// if empty value remove whole line else show line but remove all [CODE]
		foreach ($iii as $content => $placeholder)
		{
			if (isset($match[$placeholder]) && empty($placeholders['['.$match[$placeholder].']']))
			{
				// keep nothing or next option
				$keep = '';
			}
			elseif (isset($match[$ii[$content]]))
			{
				$keep = addcslashes($match[$ii[$content]], '$');
				$found = true;
				break;
			}
		}
		// if not found load else if set
		if (!$found && in_array($length, $ii))
		{
			$keep = addcslashes($match[$length - 1], '$');
		}
		return $keep;	
	}


	/**
	* 	The Global Templates Field
	**/
	protected static $templates = array();

	/**
	* 	Get The Template
	**/
	public static function getTemplate($type, $default = null, $target = 'global')
	{
		// set global key
		$key = md5(json_encode($type).$default.$target);
		// check if we already have the template set
		if (!isset(self::$templates[$key]))
		{
			// get the template from the global settings
			if ('global' === $target && self::checkString($type))
			{
				// get the global settings
				if (!self::checkObject(self::$params))
				{
					self::$params = JComponentHelper::getParams('com_membersmanager');
				}
				self::$templates[$key] = self::$params->get($type, $default);
			}
			// get the template from template view
			elseif (self::checkArray($type) && isset($type['value']) && isset($type['target']) && isset($type['get']))
			{
				if (self::$templates[$key] = self::getVar($target, $type['value'], $type['target'], $type['get']))
				{
					// check if we should decode
					if (isset($type['decode']))
					{
						if ('json_array' === $type['decode'])
						{
							self::$templates[$key] = json_decode(self::$templates[$key], true);
						}
						elseif ('json' === $type['decode'])
						{
							self::$templates[$key] = json_decode(self::$templates[$key]);
						}
						elseif ('base64' === $type['decode'])
						{
							self::$templates[$key] = base64_decode(self::$templates[$key]);
						}
					}
				}
				else
				{
					// set the default
					self::$templates[$key] = $default;
				}
			}
		}
		// check if we still have array or object
		if (isset(self::$templates[$key]) && (self::checkArray(self::$templates[$key]) || self::checkObject(self::$templates[$key])))
		{
			self::$templates[$key] = implode("\n", (array) self::$templates[$key]);
		}
		// return the template if found
		if (isset(self::$templates[$key]) && self::checkString(self::$templates[$key]))
		{
			return self::$templates[$key];
		}
		// convert to string if array
		if (self::checkArray($type))
		{
			$type = implode(', ', $type);
		}
		return JText::sprintf('COM_MEMBERSMANAGER_NO_TEMPLATE_FOR_BSB_WERE_FOUND', self::safeString($type, 'w'));
	}


	/**
	* Get the file path or url
	* 
	* @param  string   $type              The (url/path) type to return
	* @param  string   $target            The Params Target name (if set)
	* @param  string   $default           The default path if not set in Params (fallback path)
	* @param  bool     $createIfNotSet    The switch to create the folder if not found
	*
	* @return  string    On success the path or url is returned based on the type requested
	* 
	*/
	public static function getFolderPath($type = 'path', $target = 'folderpath', $default = '', $createIfNotSet = true)
	{
		// make sure to always have a string/path
		if(!self::checkString($default))
		{
			$default = JPATH_SITE . '/images/';
		}
		// get the global settings
		if (!self::checkObject(self::$params))
		{
			self::$params = JComponentHelper::getParams('com_membersmanager');
		}
		$folderPath = self::$params->get($target, $default);
		jimport('joomla.filesystem.folder');
		// create the folder if it does not exist
		if ($createIfNotSet && !JFolder::exists($folderPath))
		{
			JFolder::create($folderPath);
		}
		// return the url
		if ('url' === $type)
		{
			if (strpos($folderPath, JPATH_SITE) !== false)
			{
				$folderPath = trim( str_replace( JPATH_SITE, '', $folderPath), '/');
				return JURI::root() . $folderPath . '/';
			}
			// since the path is behind the root folder of the site, return only the root url (may be used to build the link)
			return JURI::root();
		}
		// sanitize the path
		return '/' . trim( $folderPath, '/' ) . '/';
	}


	/**
	 * @param $fileName
	 * @param $fileFormat
	 * @param $target
	 * @param $path
	 * @param $fullPath
	 * @return bool
	 */
	public static function resizeImage($fileName, $fileFormat, $target, $path, $fullPath)
	{
		// get the global settings
		if (!self::checkObject(self::$params))
		{
			self::$params = JComponentHelper::getParams('com_membersmanager');
		}
		// first check if we should resize this target
		if (1 == self::$params->get('crop_'.$target, 0))
		{
			// load the size to be set
			$height = self::$params->get($target.'_height', 'not_set');
			$width = self::$params->get($target.'_width', 'not_set');
			// get image properties
			$image = self::getImageFileProperties($fileName.'.'.$fileFormat, $path);
			// make sure we have an object
			if (self::checkObject($image))
			{
				if ($width !== 'not_set' && $height !== 'not_set' && ($image->width != $width || $image->height != $height))
				{
					// if image is huge and should only be scaled, resize it on the fly
					if(($image->width > 900 || $image->height > 700) && ($height == 0 || $width == 0))
					{
						if($fileFormat == "jpg" || $fileFormat == "jpeg" )
						{
							$src = imagecreatefromjpeg($fullPath);
						}
						elseif($fileFormat == "png")
						{
							$src = imagecreatefrompng($fullPath);
						}
						elseif($fileFormat == "gif")
						{
							$src = imagecreatefromgif($fullPath);
						}
						else
						{
							return false;
						}
						if ($height != 0)
						{
							$hRatio = $image->height / $height;
						}
						if ($width != 0)
						{
							$wRatio = $image->width / $width;
						}
						if (isset($hRatio) && isset($wRatio))
						{
							$maxRatio	= max($wRatio, $hRatio);
						}
						elseif (isset($wRatio))
						{
							$maxRatio	= $wRatio;
						}
						elseif (isset($hRatio))
						{
							$maxRatio	= $hRatio;
						}
						if ($maxRatio > 1)
						{
							$newwidth	= $image->width / $maxRatio;
							$newheight	= $image->height / $maxRatio;
						}
						else
						{
							$newwidth	= $image->width;
							$newheight	= $image->height;
						}

						$tmp			= imagecreatetruecolor($newwidth, $newheight);
						$backgroundColor	= imagecolorallocate($tmp, 255, 255, 255);

						imagefill($tmp, 0, 0, $backgroundColor);
						imagecopyresampled($tmp, $src, 0, 0, 0, 0,$newwidth, $newheight, $image->width, $image->height);
						imagejpeg($tmp, $fullPath, 100);
						imagedestroy($src);
						imagedestroy($tmp);
					}
					// only continue if image should be cropped
					if ($height != 0 && $width != 0)
					{
						// Include wideimage - http://wideimage.sourceforge.net
						require_once(JPATH_ADMINISTRATOR . '/components/com_membersmanager/helpers/wideimage/WideImage.php');
						$builder = WideImage::load($fullPath);
						$resized = $builder->resize($width, $height, 'outside')->crop('center', 'middle', $width, $height);
						$resized->saveToFile($fullPath);
					}
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $image
	 * @return bool|stdClass
	 */
	public static function getImageFileProperties($image, $folder = false)
	{
		if ($folder)
		{
			$localfolder = $folder;
		}
		else
		{
			$setimagesfolder = JComponentHelper::getParams('com_membersmanager')->get('setimagesfolder', 1);
			if (2 == $setimagesfolder)
			{
				$localfolder = JComponentHelper::getParams('com_membersmanager')->get('imagesfolder', JPATH_SITE.'/images/membersmanager');
			}
			elseif (1 == $setimagesfolder)
			{
				$localfolder =  JPATH_SITE.'/images';
			}
			else // just in-case :)
			{
				$localfolder =  JPATH_SITE.'/images/membersmanager';
			}
		}
		// import all needed classes
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		jimport('joomla.image.image');
		// setup the folder if it does not exist
		if (JFolder::exists($localfolder) && JFile::exists($localfolder.'/'.$image))
		{
			$properties = JImage::getImageFileProperties($localfolder.'/'.$image);
			// check if we have properties
			if (self::checkObject($properties))
			{
				// remove the server path
				$imagePath = trim(str_replace(JPATH_SITE,'',$localfolder),'/').'/'.$image;
				// now add the src path to show the image
				$properties->src = JURI::root().$imagePath;
				// return the image properties
				return $properties;
			}
		}
		return false;
	}


	/**
	 * @return array of link options
	 */
	public static function getLinkOptions($lock = 0, $session = 0)
	{
		// get the global settings
		if (!self::checkObject(self::$params))
		{
			self::$params = JComponentHelper::getParams('com_membersmanager');
		}
		$linkoptions = self::$params->get('link_option', null);
		// set the options to array
		$options = array('lock' => $lock, 'session' => $session);
		if (MembersmanagerHelper::checkArray($linkoptions))
		{
			if (in_array(1, $linkoptions))
			{
				// lock the filename
				$options['lock'] = 1;
			}
			if (in_array(2, $linkoptions))
			{
				// add session to the links
				$options['session'] = 1;
			}
		}
		return $options;
	}

	/**
	* Get the html/link of the image
	* 
	* @param  object   $item    The item to get image for
	* @param  string   $target   The target in the item to use
	* @param  string   $name   The name target in item to use
	* @param  string   $filelink   The file link
	*
	* @return  string    image html/link
	* 
	*/
	public static function getImageLink(&$item, $target, $name = 'name', $filelink = null, $html = true)
	{
		// check that we have a value
		if (isset($item->{$target}) && MembersmanagerHelper::checkString($item->{$target}))
		{
			// load the file link path if not set
			if (!$filelink)
			{
				$filelink = self::getFolderPath('url');
			}
			// set image link
			if (strpos($item->{$target}, '_') !== false)
			{
				$extention = explode('_', $item->{$target});
				$actualName = self::safeString($target, 'w');
				if (strpos($item->{$target}, 'VDM') !== false)
				{
					$fileNameArray = explode('VDM', $item->{$target});
					if (isset($fileNameArray[1]) && MembersmanagerHelper::checkString($fileNameArray[1]))
					{
						$actualName = $fileNameArray[1];
					}
				}
				// check if we have the extention
				if (isset($extention[2]))
				{
					// set the link
					$link = $filelink . $item->{$target} . '.' . $extention[2];
					// return ready html
					if ($html)
					{
						return '<img src="' . $link . '" alt="' . $actualName . ' ' . $item->{$name} . '" data-uk-tooltip title="' . $item->{$name} . '"/>';
					}
					// return just the link
					else
					{
						return $link;
					}
				}
			}
		}
		return false;
	}

	/**
	* Get an edit button
	* 
	* @param  int      $item       The item to edit
	* @param  string   $view       The type of item to edit
	* @param  string   $views      The list view controller name
	* @param  string   $ref        The return path
	* @param  string   $component  The component these views belong to
	* @param  string   $headsup    The message to show on click of button
	*
	* @return  string    On success the full html link
	* 
	*/
	public static function getEditButton(&$item, $view, $views, $ref = '', $component = 'com_membersmanager', $headsup = 'COM_MEMBERSMANAGER_ALL_UNSAVED_WORK_ON_THIS_PAGE_WILL_BE_LOST_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE')
	{
		// get URL
		$url = self::getEditURL($item, $view, $views, $ref, $component);
		// check if we found any
		if (self::checkString($url))
		{
			// get the global settings
			if (!self::checkObject(self::$params))
			{
				self::$params = JComponentHelper::getParams('com_membersmanager');
			}
			// get UIKIT version
			$uikit = self::$params->get('uikit_version', 2);
			// check that we have the ID
			if (self::checkObject($item) && isset($item->id))
			{
				// check if the checked_out is available
				if (isset($item->checked_out))
				{
					$checked_out = (int) $item->checked_out;
				}
			}
			elseif (self::checkArray($item) && isset($item['id']))
			{
				// check if the checked_out is available
				if (isset($item['checked_out']))
				{
					$checked_out = (int) $item['checked_out'];
				}
			}
			// set the link title
			$title = self::safeString(JText::_('COM_MEMBERSMANAGER_EDIT') . ' ' . $view, 'W');
			// check that there is a check message
			if (self::checkString($headsup))
			{
				if (3 == $uikit)
				{
					$href = 'onclick="UIkit.modal.confirm(\''.JText::_($headsup).'\').then( function(){ window.location.href = \'' . $url . '\' } )"  href="javascript:void(0)"';
				}
				else
				{
					$href = 'onclick="UIkit2.modal.confirm(\''.JText::_($headsup).'\', function(){ window.location.href = \'' . $url . '\' })"  href="javascript:void(0)"';
				}
			}
			else
			{
				$href = 'href="' . $url . '"';
			}
			// return UIKIT version 3
			if (3 == $uikit)
			{
				// check if it is checked out
				if (isset($checked_out) && $checked_out > 0)
				{
					// is this user the one who checked it out
					if ($checked_out == JFactory::getUser()->id)
					{
						return ' <a ' . $href . ' uk-icon="icon: lock" title="' . $title . '"></a>';
					}
					return ' <a href="#" disabled uk-icon="icon: lock" title="' . JText::sprintf('COM_MEMBERSMANAGER__HAS_BEEN_CHECKED_OUT_BY_S', self::safeString($view, 'W'), JFactory::getUser($checked_out)->name) . '"></a>'; 
				}
				// return normal edit link
				return ' <a ' . $href . ' uk-icon="icon: pencil" title="' . $title . '"></a>';
			}
			// check if it is checked out (return UIKIT version 2)
			if (isset($checked_out) && $checked_out > 0)
			{
				// is this user the one who checked it out
				if ($checked_out == JFactory::getUser()->id)
				{
					return ' <a ' . $href . ' class="uk-icon-lock" title="' . $title . '"></a>';
				}
				return ' <a href="#" disabled class="uk-icon-lock" title="' . JText::sprintf('COM_MEMBERSMANAGER__HAS_BEEN_CHECKED_OUT_BY_S', self::safeString($view, 'W'), JFactory::getUser($checked_out)->name) . '"></a>'; 
			}
			// return normal edit link
			return ' <a ' . $href . ' class="uk-icon-pencil" title="' . $title . '"></a>';
		}
		return '';
	}

	/**
	* Get the edit URL
	* 
	* @param  int      $item        The item to edit
	* @param  string   $view        The type of item to edit
	* @param  string   $views       The list view controller name
	* @param  string   $ref         The return path
	* @param  string   $component   The component these views belong to
	* @param  bool     $jRoute      The switch to add use JRoute or not
	*
	* @return  string    On success the edit url
	* 
	*/
	public static function  getEditURL(&$item, $view, $views, $ref = '', $component = 'com_membersmanager', $jRoute = true)
	{
		// build record
		$record = new stdClass();
		// check that we have the ID
		if (self::checkObject($item) && isset($item->id))
		{
			$record->id = (int) $item->id;
			// check if created_by is available
			if (isset($item->created_by) && $item->created_by > 0)
			{
				$record->created_by = (int) $item->created_by;
			}
		}
		elseif (self::checkArray($item) && isset($item['id']))
		{
			$record->id = (int) $item['id'];
			// check if created_by is available
			if (isset($item['created_by']) && $item['created_by'] > 0)
			{
				$record->created_by = (int) $item['created_by'];
			}
		}
		elseif (is_numeric($item))
		{
			$record->id = (int) $item;
		}
		// check ID
		if (isset($record->id) && $record->id > 0)
		{
			// get user action permission to edit
			$action = self::getActions($view, $record, $views, 'edit', str_replace('com_', '', $component));
			// check if the view permission is set
			if (($edit = $action->get($view . '.edit', 'none-set')) === 'none-set')
			{
				// fall back on the core permission then
				$edit = $action->get('core.edit', 'none-set');
			}
			// can edit
			if ($edit)
			{
				// set the edit link
				if ($jRoute)
				{
					return JRoute::_("index.php?option=" . $component . "&view=" . $views . "&task=" . $view . ".edit&id=" . $record->id . $ref);
				}
				return "index.php?option=" . $component . "&view=" . $views . "&task=" . $view . ".edit&id=" . $record->id . $ref;
			}
		}
		return false;
	}


	/**
	* Get a create button
	*
	* @param  string   $view       The type of item to edit
	* @param  string   $views      The list view controller name
	* @param  string   $ref        The return path
	* @param  string   $component  The component these views belong to
	* @param  string   $headsup    The message to show on click of button
	*
	* @return  string    On success the full html create button
	*
	*/
	public static function getCreateButton($view, $views, $ref = '', $component = 'com_membersmanager', $headsup = 'COM_MEMBERSMANAGER_ALL_UNSAVED_WORK_ON_THIS_PAGE_WILL_BE_LOST_ARE_YOU_SURE_YOU_WANT_TO_CONTINUE')
	{
		// get URL
		$url = self::getCreateURL($view, $views, $ref, $component);
		// check if we found any
		if (self::checkString($url))
		{
			// get the global settings
			if (!self::checkObject(self::$params))
			{
				self::$params = JComponentHelper::getParams('com_membersmanager');
			}
			// get UIKIT version
			$uikit = self::$params->get('uikit_version', 2);
			// set the link title
			$title = self::safeString(JText::_('COM_MEMBERSMANAGER_ADD') . ' ' . $view, 'W');
			// check that there is a check message
			if (self::checkString($headsup))
			{
				if (3 == $uikit)
				{
					$href = 'onclick="UIkit.modal.confirm(\''.JText::_($headsup).'\').then( function(){ window.location.href = \'' . $url . '\' } )"  href="javascript:void(0)"';
				}
				else
				{
					$href = 'onclick="UIkit2.modal.confirm(\''.JText::_($headsup).'\', function(){ window.location.href = \'' . $url . '\' })"  href="javascript:void(0)"';
				}
			}
			else
			{
				$href = 'href="' . $url . '"';
			}
			// return normal create new link
			return ' <a ' . $href . ' class="btn btn-small button-new btn-success" title="' . $title . '"><span class="icon-new icon-white"></span> ' . JText::_('COM_MEMBERSMANAGER_NEW') . '</span></a>';
		}
		return '';
	}

	/**
	* Get the create URL
	*
	* @param  string   $view        The type of item to edit
	* @param  string   $views       The list view controller name
	* @param  string   $ref         The return path
	* @param  string   $component   The component these views belong to
	* @param  bool     $jRoute      The switch to add use JRoute or not
	*
	* @return  string    On success the create url
	*
	*/
	public static function  getCreateURL($view, $views, $ref = '', $component = 'com_membersmanager', $jRoute = true)
	{
		// can create
		if (JFactory::getUser()->authorise($view . '.create', $component))
		{
			// set create task
			$create = "&task=" . $view . ".edit";
			// check if this button must work with task or layout
			if ($views === $view)
			{
				// set layout edit
				$create = "&layout=edit";
			}
			// set the edit link
			if ($jRoute)
			{
				return JRoute::_("index.php?option=" . $component . "&view=" . $views . $create . $ref);
			}
			return "index.php?option=" . $component . "&view=" . $views . $create . $ref;
		}
		return false;
	}


	/**
	 * Check if a field is unique
	 *
	 * @param   int        $currentID  The current item ID
	 * @param   string     $field      The field name
	 * @param   array      $value      The the value
	 * @param   array      $table      The table
	 *
	 * @return  bool
	 *
	 */
	public static function checkUnique($currentID, $field, $value, $table)
	{
		// make sure we have a table
		if (self::checkString($table))
		{
			// Get the database object and a new query object.
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true);
			// Build the query.
			$query->select('COUNT(*)')
				->from('#__membersmanager_' . (string) $table)
				->where($db->quoteName($field) . ' = ' . $db->quote($value));
			// remove this item from the list
			if ($currentID > 0)
			{
				$query->where($db->quoteName('id') . ' <> ' . (int) $currentID);
			}
			// Set and query the database.
			$db->setQuery($query);
			$duplicate = (bool) $db->loadResult();
			if (!$duplicate)
			{
				return true;
			}
		}
		return false;
	}


	/**
	 * The memory of the company details
	 *
	 * @var     array
	 */
	protected static $companyDetails = array();

	/**
	 * Get company details
	 *
	 * @param   string   $method    The type of values to return
	 * @param   string   $filter          The kind of filter (to return only values required)
	 *
	 * @return array/object   based on $method
	 *
	 */
	public static function getCompanyDetails($method = 'array', $filter = null)
	{
		if (!isset(self::$companyDetails[$method]))
		{
			$f = '';
			$b = '';
			if ('placeholder' == $method)
			{
				$f = '[';
				$b = ']';
			}
			// get the global settings
			if (!self::checkObject(self::$params))
			{
				self::$params = JComponentHelper::getParams('com_membersmanager');
			}
			// get the logo
			$logo = self::$params->get('companylogo', self::$params->get('logo', null));
			// start loading the company details
			self::$companyDetails[$method] = array();
			self::$companyDetails[$method][$f.'company_name'.$b] = self::$params->get('companyname', self::$params->get('name', ''));
			// only lod logo if found
			if ($logo)
			{
				self::$companyDetails[$method][$f.'company_logo'.$b] = '<img alt="'.self::$companyDetails[$method][$f.'company_name'.$b].'" src="'.JURI::root().$logo.'">';
				self::$companyDetails[$method][$f.'company_logo_link'.$b] = JURI::root().$logo;
			}
			else
			{
				self::$companyDetails[$method][$f.'company_logo'.$b] = '';
				self::$companyDetails[$method][$f.'company_logo_link'.$b] = '';
			}
			self::$companyDetails[$method][$f.'company_email'.$b] = self::$params->get('email', '');
			self::$companyDetails[$method][$f.'company_phone'.$b] = self::$params->get('phone', '');
			self::$companyDetails[$method][$f.'company_mobile'.$b] = self::$params->get('mobile_phone', '');
			self::$companyDetails[$method][$f.'company_street'.$b] = self::$params->get('street', '');
			self::$companyDetails[$method][$f.'company_postal'.$b] = self::$params->get('postal', '');
			self::$companyDetails[$method][$f.'company_postalcode'.$b] = self::$params->get('postalcode', '');
			self::$companyDetails[$method][$f.'company_city'.$b] = self::$params->get('city', '');
			self::$companyDetails[$method][$f.'company_website'.$b] = self::$params->get('website', '');
			// set the region
			$region = self::$params->get('region', '');
			if ($region && !is_numeric($region) && self::checkString($region))
			{
				// set the region name
				self::$companyDetails[$method][$f.'company_region_name'.$b] = $region;
				// just set this incase
				self::$companyDetails[$method][$f.'company_region'.$b] = $region;
			}
			elseif (is_numeric($region) && $region > 0)
			{
				// set the region name
				self::$companyDetails[$method][$f.'company_region_name'.$b] = self::getVar('region', $region, 'id', 'name');
				// just set this incase
				self::$companyDetails[$method][$f.'company_region'.$b] = '';
			}
			else
			{
				// set the region name to blank
				self::$companyDetails[$method][$f.'company_region_name'.$b] = '';
				// just set this incase
				self::$companyDetails[$method][$f.'company_region'.$b] = '';
			}
			// set the country
			$country = self::$params->get('country', '');
			if ($country && !is_numeric($country) && self::checkString($country))
			{
				// set the country name
				self::$companyDetails[$method][$f.'company_country_name'.$b] = $country;
				// just set this incase
				self::$companyDetails[$method][$f.'company_country'.$b] = $country;
			}
			elseif (is_numeric($country) && $country > 0)
			{
				// set the country name
				self::$companyDetails[$method][$f.'company_country_name'.$b] = self::getVar('country', $country, 'id', 'name');
				// just set this incase
				self::$companyDetails[$method][$f.'company_country'.$b] = '';
			}
			else
			{
				// set the country name to blank
				self::$companyDetails[$method][$f.'company_country_name'.$b] = '';
				// just set this incase
				self::$companyDetails[$method][$f.'company_country'.$b] = '';
			}
			// add and update the header footer and header if setDynamicData is found on placeholder request
			if (method_exists(__CLASS__, 'setDynamicData') && 'placeholder' == $method)
			{
				// add document header and footer
				self::$companyDetails[$method][$f.'company_doc_header'.$b] = self::setDynamicData(self::$params->get('doc_header', ''), self::$companyDetails[$method]);
				self::$companyDetails[$method][$f.'company_doc_footer'.$b] = self::setDynamicData(self::$params->get('doc_footer', ''), self::$companyDetails[$method]);
			}
			// if object is called for
			if ('object' == $method)
			{
				self::$companyDetails[$method] = (object) self::$companyDetails[$method];
			}
		}
		// return the values
		if (!isset(self::$companyDetails[$method]))
		{
			self::$companyDetails[$method] = false;
		}
		return self::$companyDetails[$method];
	}

	/**
	 * get a report
	 *
	 * @param   int     $id         The item ID
	 * @param   string  $component  The component being targeted
	 *
	 * @return string
	 *
	 */
	public static function getReport($id, $_component = 'com_membersmanager')
	{
		// check if user are allowed to view this report
		if ($id > 0 && JFactory::getUser()->authorise('form.report.viewtab', $_component))
		{
			// get template
			if (($template = self::getAnyTemplate($_component, 'report_template')) === false || !self::checkString($template))
			{
				return false;
			}
			// start loading the placeholders
			$placeholders = array();
			// get form placeholders
			if (($form = self::getAnyFormDetails($id, 'id', $_component, 'placeholder', 'report', 'id')) !== false && self::checkArray($form))
			{
				$placeholders[] = $form;
			}
			// get company placeholders
			if (($company = self::getAnyCompanyDetails('com_membersmanager', 'placeholder')) !== false && self::checkArray($company))
			{
				$placeholders[] = $company;
			}
			// check that we have placeholders
			if (self::checkArray($placeholders))
			{
				// set placeholders
				$placeholders = self::mergeArrays($placeholders);
				// get the ID
				$divID = self::randomkey(10);
				// get the global settings
				if (!self::checkObject(self::$params))
				{
					self::$params = JComponentHelper::getParams('com_membersmanager');
				}
				// get uikit version
				$uikitVersion = self::$params->get('uikit_version', 2);
				if (3 == $uikitVersion)
				{
					return '<a href="javascript:void(0)" onclick="printMe(\'' . JFactory::getConfig()->get( 'sitename' ) . '\', \'' . $divID . '\')" ></a>' . JText::_('COM_MEMBERSMANAGER_PRINT') . '<br /><div id="' . $divID . '">' .
							self::setDynamicData($template, $placeholders) .
						'</div>';
				}
				// return html
				return '<a href="javascript:void(0)" onclick="printMe(\'' . JFactory::getConfig()->get( 'sitename' ) . '\', \'' . $divID . '\')" class="uk-icon-hover uk-icon-print"></a><br /><div id="' . $divID . '">' .
						self::setDynamicData($template, $placeholders) .
					'</div>';
			}
		}
		return false;
	}


	/**
	 * Get Any template details
	 *
	 * @param   string   $_component    The component element name
	 * @param   string   $type          The type of template
	 * @param   string   $default       The default if not found
	 * @param   string   $target        The target
	 *
	 * @return string
	 *
	 */
	public static function getAnyTemplate($_component = 'com_membersmanager', $type, $default = null, $target = 'global')
	{
		// check if we are in the correct class
		if ('com_membersmanager' !== $_component)
		{
			// get component name
			$component = str_replace('com_', '', $_component);
			// build component helper name
			$componentHelper = self::safeString($component, 'F') . 'Helper';
			// check if it is already set
			if (!class_exists($componentHelper, FALSE))
			{
				// set the correct path focus
				$focus = JPATH_ADMINISTRATOR;
				if (JFactory::getApplication()->isSite())
				{
					// set admin path
					$adminPath = $focus . '/components/' . $_component . '/helpers/' . $component . '.php';
					// change the focus
					$focus = JPATH_ROOT;
				}
				// set path based on focus
				$path = $focus . '/components/' . $_component . '/helpers/' . $component . '.php';
				// check if file exist, if not try admin again.
				if (file_exists($path))
				{
					// make sure to load the helper
					JLoader::register($componentHelper, $path);
				}
				elseif (isset($adminPath) && file_exists($adminPath)) // fallback option
				{
					// make sure to load the helper
					JLoader::register($componentHelper, $adminPath);
				}
			}
			// check if the class and method exist
			if (class_exists($componentHelper) && method_exists($componentHelper, 'getTemplate'))
			{
				return $componentHelper::getTemplate($type, $default, $target);
			}
			return false;
		}
		// check if the class and method exist
		elseif (method_exists(__CLASS__, 'getTemplate'))
		{
			return self::getTemplate($type, $default, $target);
		}
		return false;
	}


	/**
	 * Get Any form details
	 *
	 * @param   int      $memberID      The item ID
	 * @param   string   $type          The type of ID
	 * @param   string   $_component    The component element name
	 * @param   string   $method        The type of values to return
	 * @param   string   $filter        The kind of filter (to return only values required)
	 * @param   string   $masterkey     The master key
	 *
	 * @return array/object   based on $method
	 *
	 */
	public static function getAnyFormDetails($memberID, $type = 'member', $_component = 'com_membersmanager', $method = 'array', $filter = 'none', $masterkey = 'member')
	{
		// check if we are in the correct class
		if ('com_membersmanager' !== $_component)
		{
			// get component name
			$component = str_replace('com_', '', $_component);
			// build component helper name
			$componentHelper = self::safeString($component, 'F') . 'Helper';
			// check if it is already set
			if (!class_exists($componentHelper, FALSE))
			{
				// set the correct path focus
				$focus = JPATH_ADMINISTRATOR;
				if (JFactory::getApplication()->isSite())
				{
					// set admin path
					$adminPath = $focus . '/components/' . $_component . '/helpers/' . $component . '.php';
					// change the focus
					$focus = JPATH_ROOT;
				}
				// set path based on focus
				$path = $focus . '/components/' . $_component . '/helpers/' . $component . '.php';
				// check if file exist, if not try admin again.
				if (file_exists($path))
				{
					// make sure to load the helper
					JLoader::register($componentHelper, $path);
				}
				elseif (isset($adminPath) && file_exists($adminPath))
				{
					// make sure to load the helper
					JLoader::register($componentHelper, $adminPath);
				}
			}
			// check if the class and method exist
			if (class_exists($componentHelper) && method_exists($componentHelper, 'getFormDetails'))
			{
				return $componentHelper::getFormDetails($memberID, $type, 'form', $method, $filter, $masterkey);
			}
			return false;
		}
		// check if the class and method exist
		elseif (method_exists(__CLASS__, 'getFormDetails'))
		{
			return self::getFormDetails($memberID, $type, 'form', $method, $filter, $masterkey);
		}
		return false;
	}


	/**
	 * Get company details from any other component
	 *
	 * @param   string   $_component    The component name
	 * @param   string   $method    The type of values to return
	 * @param   string   $filter          The kind of filter (to return only values required)
	 *
	 * @return array/object   based on $method
	 *
	 */
	public static function getAnyCompanyDetails($_component = 'com_membersmanager', $method = 'array', $filter = null)
	{
		// check if we are in the correct class
		if ('com_membersmanager' !== $_component)
		{
			// get component name
			$component = str_replace('com_', '', $_component);
			// build component helper name
			$componentHelper = self::safeString($component, 'F') . 'Helper';
			// check if it is already set
			if (!class_exists($componentHelper, FALSE))
			{
				// set the correct path focus
				$focus = JPATH_ADMINISTRATOR;
				if (JFactory::getApplication()->isSite())
				{
					// set admin path
					$adminPath = $focus . '/components/' . $_component . '/helpers/' . $component . '.php';
					// change the focus
					$focus = JPATH_ROOT;
				}
				// set path based on focus
				$path = $focus . '/components/' . $_component . '/helpers/' . $component . '.php';
				// check if file exist, if not try admin again.
				if (file_exists($path))
				{
					// make sure to load the helper
					JLoader::register($componentHelper, $path);
				}
				elseif (isset($adminPath) && file_exists($adminPath)) // fallback option
				{
					// make sure to load the helper
					JLoader::register($componentHelper, $adminPath);
				}
			}
			// check if the class and method exist
			if (class_exists($componentHelper) && method_exists($componentHelper, 'getCompanyDetails'))
			{
				return $componentHelper::getCompanyDetails($method, $filter);
			}
			return false;
		}
		// check if the class and method exist
		elseif (method_exists(__CLASS__, 'getCompanyDetails'))
		{
			return self::getCompanyDetails($method, $filter);
		}
		return false;
	}


	/**
	 * Get Member forms
	 *
	 * @param   int       $id            The item ID.
	 * @param   string    $_component    The component element name
	 * @param   string    $method        The type of values to return
	 * @param   array     $data          Data for the form.
	 * @param   boolean   $loadData      True if the form is to load its own data (default case), false if not.
	 *
	 * @return array/object
	 *
	 */
	public static function getMemberForms($id = 0, $_component = 'com_membersmanager', $_model = 'form', $data = array(), $loadData = true)
	{
		$form = false;
		// get APP
		$app = JFactory::getApplication();
		// get component name
		$component = str_replace('com_', '', $_component);
		// build component name
		$Component = self::safeString($component, 'F');
		// get old ID
		$old_id = $app->input->get('a_id', 0, 'INT');
		// set the ID
		$app->input->set('a_id', $id);
		// get Model
		$model = self::getModel($_model, JPATH_ADMINISTRATOR . '/components/' . $_component, $Component);
		// do we have the model
		if ($model)
		{
			// force other component path (TODO) will be an issue if forms and fields are the same
			\JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $_component . '/models/forms');
			\JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/' . $_component . '/models/fields');
			// we must load the data
			if (!self::checkArray($data) && $id > 0)
			{
				$data = $model->getItem($id);
			}
			else
			{
				$loadData = false;
			}
			// set the form data
			if ($loadData)
			{
				$app->setUserState($_component . '.edit.' . $_model . '.data', $data);
			}
			// get the form (handles all default permissions)
			$form = $model->getForm($data, $loadData, array('control' => $component));
		}
		// set the old ID again
		$app->input->set('a_id', $old_id);
		// now return form
		return $form;
	}

	/**
	 * The memory of the member details
	 *
	 * @var     array
	 */
	protected static $memberDetails = array();

	/**
	 * The global details key (set per/query)
	 *
	 * @var     string
	 */
	protected static $k3y;

	/**
	 * Get member details
	 *
	 * @param   int      $id         The the form ID
	 * @param   string   $type       The type of ID
	 * @param   string   $table      The table of ID
	 * @param   string   $method     The type of values to return
	 * @param   string   $filter     The kind of filter (to return only values required)
	 * @param   string   $masterkey  The master key for many values in the member table
	 *
	 * @return array/object   based on $method
	 *
	 */
	public static function getMemberDetails($id, $type = 'id', $table = 'member', $method = 'array', $filter = 'none', $masterkey = 'member')
	{
		// always make sure that we have a member column
		if ($table !== 'member' && $type !== $masterkey)
		{
			// convert to master key
			if (($id = self::getVar((string) $table, (int) $id, $type, $masterkey)) === false)
			{
				return false;
			}
			$type = $masterkey;
			$table = 'member';
		}
		// get database object
		$db = JFactory::getDbo();
		// if not id validate column
		if ($type !== 'id' && $type !== $masterkey)
		{
			// get the database columns of this table
			$columns = $db->getTableColumns("#__membersmanager_member", false);
			// check if the type is found
			if (!isset($columns[$type]))
			{
				return false;
			}
		}
		// get the global settings
		if (!self::checkObject(self::$params))
		{
			self::$params = JComponentHelper::getParams('com_membersmanager');
		}
		// get the relations (1 = one to one || 2 = one to many)
		$relations = self::$params->get('membersmanager_relation_type', 1);
		// always make sure that we have a masterkey ID
		if ($relations == 2 && $type !== $masterkey)
		{
			// convert to masterkey ID
			if (($id = self::getVar('member', (int) $id, $type, $masterkey)) === false)
			{
				return false;
			}
			// set master key as type of id
			$type = $masterkey;
		}
		// set the global key
		self::$k3y = $id.$method.$filter;
		// check if we have the member details in memory
		if (is_numeric($id) && $id > 0 && !isset(self::$memberDetails[self::$k3y]))
		{
			// get the member details an place it in memory
			$query = $db->getQuery(true);
			// check if we can getSelection
			if (method_exists(__CLASS__, "getSelection"))
			{
				// Select some fields
				if (($selection = self::getSelection('member', $method, $filter, $db)) !== false)
				{
					// strip selection values not direct SQL (does not have point)
					$selection = array_filter(
						$selection,
						function ($key) {
							return strpos($key, '.');
						},
						ARRAY_FILTER_USE_KEY
					);
				}
			}
			// check if we have a selection
			if (isset($selection) && self::checkArray($selection))
			{
				// set the selection
				$query->select($db->quoteName(array_keys($selection), array_values($selection)));
				// From the membersmanager_member table
				$query->from($db->quoteName('#__membersmanager_member', 'a'));
				// check if we have more join tables for the member details
				if (method_exists(__CLASS__, "joinMemberDetails"))
				{
					self::joinMemberDetails($query, $filter, $db);
				}
				// check if we have more get where details
				if (method_exists(__CLASS__, "whereMemberDetails"))
				{
					self::whereMemberDetails($query, $filter, $db);
				}
				// get by type ID
				$query->where('a.' . $type . ' = ' . (int) $id);
				$db->setQuery($query);
				$db->execute();
				$numberRows = $db->getNumRows();
				if ($numberRows)
				{
					if ('object' == $method)
					{
						// if one to one
						if ($relations == 1 || $numberRows == 1)
						{
							self::$memberDetails[self::$k3y] = $db->loadObject();
						}
						// if one to many (so we must return many)
						else
						{
							self::$memberDetails[self::$k3y] = $db->loadObjectList();
						}
					}
					else
					{
						// if one to one
						if ($relations == 1 || $numberRows == 1)
						{
							self::$memberDetails[self::$k3y] = $db->loadAssoc();
						}
						// if one to many (so we must return many)
						else
						{
							self::$memberDetails[self::$k3y] = $db->loadAssocList();
						}
					}
				}
				// check if we have been able to get the member details
				if (!isset(self::$memberDetails[self::$k3y]))
				{
					self::$memberDetails[self::$k3y] = false;
				}
				// check if we must model the details
				elseif (method_exists(__CLASS__, "modelMemberDetails"))
				{
					self::modelMemberDetails($id, $method, $filter, $numberRows, $db);
					// check if we must remove some details after modeling
					if (method_exists(__CLASS__, "removeMemberDetails"))
					{
						self::removeMemberDetails($id, $method, $filter, $numberRows, $db);
					}
				}
			}
			else
			{
				self::$memberDetails[self::$k3y] = false;
			}
		}
		return self::$memberDetails[self::$k3y];
	}


	/**
	 * Model the member details/values
	 *
	 * @param   object   $id          The the member ID
	 * @param   string   $method      The type of values to return
	 * @param   string   $filter      The kind of filter (to return only values required)
	 * @param   int      $numberRows  The number of rows
	 * @param   object   $db          The database object
	 *
	 * @return void
	 *
	 */
	protected static function modelMemberDetails($id, $method, $filter, $numberRows, $db = null)
	{
		// check that we have values
		if (method_exists(__CLASS__, 'getSelection') && isset(self::$memberDetails[self::$k3y]) && self::$memberDetails[self::$k3y])
		{
			// get language object
			$lang = JFactory::getLanguage();
			// try to load the translation
			$lang->load('com_membersmanager', JPATH_ADMINISTRATOR, null, false, true);
			// Select some fields
			$_builder = self::getSelection('member', $method, $filter, $db);
			// check if we have params to model
			if (method_exists(__CLASS__, "paramsModelMemberDetails"))
			{
				self::paramsModelMemberDetails($_builder, $method, $numberRows);
			}
			// get values that must be set (not SQL values)
			$builder = array_filter(
				$_builder,
				function ($key) {
					return strpos($key, ':');
				},
				ARRAY_FILTER_USE_KEY
			);
			// start the builder
			if (self::checkArray($builder))
			{
				// prep for placeholders
				$f = '';
				$b = '';
				if ('placeholder' === $method)
				{
					// get the placeholder prefix
					$prefix = self::$params->get('placeholder_prefix', 'membersmanager');
					$f = '[' . $prefix . '_';
					$b = ']';
				}
				// loop builder
				foreach ($builder as $build => $set)
				{
					// get function and key
					$_build = explode(':', $build);
					// check the number of values must be two
					if (count((array)$_build) == 2)
					{
						// check if more then one value must be passed
						if (strpos($_build[1], '|') !== false)
						{
							// get all value names
							$valueKeys = explode('|', $_build[1]);
							// continue only if we have values
							if (self::checkArray($valueKeys))
							{
								// start the modeling
								if ($numberRows == 1)
								{
									$object = new JObject;
									foreach ($valueKeys as $valueKey)
									{
										// work with object
										if ('object' === $method && isset(self::$memberDetails[self::$k3y]->{$valueKey}))
										{
											// load the properties
											$object->set($valueKey, self::$memberDetails[self::$k3y]->{$valueKey});
										}
										// work with array
										elseif (self::checkArray(self::$memberDetails[self::$k3y]) && isset(self::$memberDetails[self::$k3y][$f.$valueKey.$b]))
										{
											// load the properties
											$object->set($valueKey, self::$memberDetails[self::$k3y][$f.$valueKey.$b]);
										}
									}
									// now set the new value
									if ('object' === $method)
									{
										$result = self::{$_build[0]}($object);
										if (self::checkArray($result) || self::checkObject($result))
										{
											foreach ($result as $_key => $_val)
											{
												self::$memberDetails[self::$k3y]->{$set . '_' . $_key} = $_val;
											}
										}
										else
										{
											self::$memberDetails[self::$k3y]->{$set} = $result;
										}
									}
									// work with array
									else
									{
										$result = self::{$_build[0]}($object);
										if (self::checkArray($result) || self::checkObject($result))
										{
											$set = str_replace(array($f, $b), '', $set);
											foreach ($result as $_key => $_val)
											{
												self::$memberDetails[self::$k3y][$f . $set . '_' . $_key . $b] = $_val;
											}
										}
										else
										{
											self::$memberDetails[self::$k3y][$set] = $result;
										}
									}
								}
								elseif (self::checkArray(self::$memberDetails[self::$k3y]))
								{
									foreach (self::$memberDetails[self::$k3y] as $_nr => $details)
									{
										$object = new JObject;
										foreach ($valueKeys as $valueKey)
										{
											// work with object
											if ('object' === $method && isset($details->{$valueKey}))
											{
												// load the properties
												$object->set($valueKey, $details->{$valueKey});
											}
											// work with array
											elseif (self::checkArray($details) && isset($details[$f.$valueKey.$b]))
											{
												// load the properties
												$object->set($valueKey, $details[$f.$valueKey.$b]);
											}
										}
										// now set the new value
										if ('object' === $method)
										{
											$result = self::{$_build[0]}($object);
											if (self::checkArray($result) || self::checkObject($result))
											{
												foreach ($result as $_key => $_val)
												{
													self::$memberDetails[self::$k3y][$_nr]->{$set . '_' . $_key} = $_val;
												}
											}
											else
											{
												self::$memberDetails[self::$k3y][$_nr]->{$set} = $result;
											}
										}
										// work with array
										else
										{
											$result = self::{$_build[0]}($object);
											if (self::checkArray($result) || self::checkObject($result))
											{
												$set = str_replace(array($f, $b), '', $set);
												foreach ($result as $_key => $_val)
												{
													self::$memberDetails[self::$k3y][$_nr][$f . $set . '_' . $_key . $b] = $_val;
												}
											}
											else
											{
												self::$memberDetails[self::$k3y][$_nr][$set] = $result;
											}
										}
									}
								}
							}
						}
						else
						{
							if ($numberRows == 1)
							{
								// work with object
								if ('object' === $method && isset(self::$memberDetails[self::$k3y]->{$_build[1]}))
								{
									$result = self::{$_build[0]}(self::$memberDetails[self::$k3y]->{$_build[1]});
									if (self::checkArray($result) || self::checkObject($result))
									{
										foreach ($result as $_key => $_val)
										{
											self::$memberDetails[self::$k3y]->{$set . '_' . $_key} = $_val;
										}
									}
									else
									{
										self::$memberDetails[self::$k3y]->{$set} = $result;
									}
								}
								// work with array
								elseif (self::checkArray(self::$memberDetails[self::$k3y]) && isset(self::$memberDetails[self::$k3y][$f.$_build[1].$b]))
								{
									$result = self::{$_build[0]}(self::$memberDetails[self::$k3y][$f.$_build[1].$b]);
									if (self::checkArray($result) || self::checkObject($result))
									{
										$set = str_replace(array($f, $b), '', $set);
										foreach ($result as $_key => $_val)
										{
											self::$memberDetails[self::$k3y][$f . $set . '_' . $_key . $b] = $_val;
										}
									}
									else
									{
										self::$memberDetails[self::$k3y][$set] = $result;
									}
								}
							}
							elseif (self::checkArray(self::$memberDetails[self::$k3y]))
							{
								foreach (self::$memberDetails[self::$k3y] as $_nr => $details)
								{
									// work with object
									if ('object' === $method && isset(self::$memberDetails[self::$k3y][$_nr]->{$_build[1]}))
									{
										$result = self::{$_build[0]}(self::$memberDetails[self::$k3y][$_nr]->{$_build[1]});
										if (self::checkArray($result) || self::checkObject($result))
										{
											foreach ($result as $_key => $_val)
											{
												self::$memberDetails[self::$k3y][$_nr]->{$set . '_' . $_key} = $_val;
											}
										}
										else
										{
											self::$memberDetails[self::$k3y][$_nr]->{$set} = $result;
										}
									}
									// work with array
									elseif (self::checkArray(self::$memberDetails[self::$k3y][$_nr]) && isset(self::$memberDetails[self::$k3y][$_nr][$f.$_build[1].$b]))
									{
										$result = self::{$_build[0]}(self::$memberDetails[self::$k3y][$_nr][$f.$_build[1].$b]);
										if (self::checkArray($result) || self::checkObject($result))
										{
											$set = str_replace(array($f, $b), '', $set);
											foreach ($result as $_key => $_val)
											{
												self::$memberDetails[self::$k3y][$_nr][$f . $set . '_' . $_key . $b] = $_val;
											}
										}
										else
										{
											self::$memberDetails[self::$k3y][$_nr][$set] = $result;
										}
									}
								}
							}
						}
					}
				}
			}
			// check if we have labels to model
			if (method_exists(__CLASS__, "labelModelMemberDetails") && property_exists(__CLASS__, 'memberParams'))
			{
				self::labelModelMemberDetails($_builder, $method, $numberRows);
			}
			// check if we have templates to model
			if (method_exists(__CLASS__, "templateModelMemberDetails") && property_exists(__CLASS__, 'memberParams'))
			{
				self::templateModelMemberDetails($_builder, $method, $numberRows);
			}
		}
	}


	/**
	* Get the members name
	* 
	* @param  int        $id    The member ID
	* @param  int        $user  The user ID
	* @param  string     $name  The name
	* @param  string     $surname  The surname
	*
	* @return  string    the members name
	* 
	*/
	public static function  getMemberName($id, $user = null, $name = null, $surname = null)
	{
		// always get surname
		if (!self::checkString($surname))
		{
			if(($surname = self::getVar('member', $id, 'id', 'surname', '=', 'membersmanager')) === false || !self::checkString($surname))
			{
				$surname = '';
			}
		}
		// check name given
		if (self::checkstring($name))
		{
			return $name . ' ' . $surname;
		}
		// check user given
		elseif ((is_numeric($user) && $user > 0) || (is_numeric($id) && $id > 0 && ($user = self::getVar('member', $id, 'id', 'user', '=', 'membersmanager')) !== false && $user > 0))
		{
			return JFactory::getUser($user)->name . ' ' . $surname;
		}
		// get the name
		elseif (is_numeric($id) && $id > 0 && ($name = self::getVar('member', $id, 'id', 'name', '=', 'membersmanager')) !== false && self::checkstring($name))
		{
			return $name . ' ' . $surname;
		}
		return JText::_('COM_MEMBERSMANAGER_NO_NAME');
	}


	/**
	* the info components
	**/
	protected static $infoComponents = array();

	/**
	 * Get available infos based on type
	 */
	public static function getInfoAvaillable($type, $account, $multiDimensionalAllowed = true)
	{
		$infos = self::getInfoComponents($type, $account);
		// check if we found components
		if (self::checkArray($infos))
		{
			// sort into types of info
			$bucketInfos = array();
			foreach ($infos as $component)
			{
				if (isset($component->params->info_type_name) && self::checkString($component->params->info_type_name))
				{
					$infoTypeName = $component->params->info_type_name;
				}
				else
				{
					$infoTypeName = $component->name;
				}
				// package based on relations (one to one or one to many)
				if (isset($component->params->membersmanager_relation_type) && $component->params->membersmanager_relation_type == 1 || !$multiDimensionalAllowed)
				{
					// set data (one to one)
					$bucketInfos[$infoTypeName] = $component;
				}
				else
				{
					// start array if not already set
					if (!isset($bucketInfos[$infoTypeName]))
					{
						$bucketInfos[$infoTypeName] = array();
					}
					// set data (one to many)
					$bucketInfos[$infoTypeName][] = $component;
				}
			}
			// return the info bucket
			return $bucketInfos;
		}
		return false;
	}

	/**
	 * Get type info names
	 */
	public static function getTypeInfosNames($type, $account, $as = 'string')
	{
		$infos = self::getInfoAvaillable($type, $account);
		$names = array();
		if (self::checkArray($infos))
		{
			foreach ($infos as $name => $info)
			{
				$names[] = $name;
			}
		}
		if (self::checkArray($names))
		{
			// return as
			if ('string' === $as)
			{
				return implode(', ', $names);
			}
			return $names;
		}
		// still return string
		if ('string' === $as)
		{
			return 'Infos';
		}
		return false;
	}

	/**
	 * Get info components
	 */
	public static function getInfoComponents($type = null, $account = null)
	{
		if (!self::checkArray(self::$infoComponents))
		{
			// get list of Extensions
			self::$infoComponents = self::setInfoComponents();
		}
		// filter by type & account
		if ($type && $account && self::checkArray(self::$infoComponents))
		{
			// filter out the components we need
			return array_filter(
				self::$infoComponents,
				function ($component) use ($type, $account) {
					// check if the component is available to this type of member
					return ((isset($component->params->membersmanager_target_type) && in_array($type, (array) $component->params->membersmanager_target_type)) && 
						(isset($component->params->membersmanager_target_account) && in_array($account, (array) $component->params->membersmanager_target_account)));
				}
			);
		}
		return self::$infoComponents;
	}

	/**
	 * set info components
	 */
	protected static function setInfoComponents()
	{
		$db = JFactory::getDBO();
		// get components
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__extensions AS a');
		$query->where('a.type = ' . $db->quote('component'));
		$query->where('a.protected = 0')->where('a.enabled = 1');
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			// get all components
			$listComponents = $db->loadObjectList();
			// filter out the components we need
			$listComponents = array_filter(
				$listComponents,
				function ($component) {
					if (strpos($component->params, 'activate_membersmanager_info') !== false)
					{
						// check if this component is active
						return json_decode($component->params)->activate_membersmanager_info;
					}
					return false;
				}
			);
			// check if we found components
			if (self::checkArray($listComponents))
			{
				// get language object
				$lang = JFactory::getLanguage();
				foreach ($listComponents as $listComponent)
				{
					// lets do a quick params setup (to objects)
					$listComponent->params = json_decode($listComponent->params);
					// try to load the translation
					$lang->load($listComponent->element, JPATH_ADMINISTRATOR, null, false, true);
					// translate the extension name if possible
					$listComponent->name = JText::_($listComponent->name);
					// translate the info type name
					if (isset($listComponent->params->info_type_name))
					{
						$listComponent->params->info_type_name = JText::_(strtoupper($listComponent->element) . '_CONFIG_' . $listComponent->params->info_type_name);
					}
				}
				return $listComponents;
			}
		}
		return false;
	}


	/**
	* the assessment components
	**/
	protected static $assessmentComponents = array();

	/**
	 * Get available assessments based on type
	 */
	public static function getAssessmentAvaillable($type, $account, $multiDimensionalAllowed = true)
	{
		$assessments = self::getAssessmentComponents($type, $account);
		// check if we found components
		if (self::checkArray($assessments))
		{
			// sort into types of assessment
			$bucketAssessments = array();
			foreach ($assessments as $component)
			{
				if (isset($component->params->assessment_type_name) && self::checkString($component->params->assessment_type_name))
				{
					$assessmentTypeName = $component->params->assessment_type_name;
				}
				else
				{
					$assessmentTypeName = $component->name;
				}
				// package based on relations (one to one or one to many)
				if (isset($component->params->membersmanager_relation_type) && $component->params->membersmanager_relation_type == 1 || !$multiDimensionalAllowed)
				{
					// set data (one to one)
					$bucketAssessments[$assessmentTypeName] = $component;
				}
				else
				{
					// start array if not already set
					if (!isset($bucketAssessments[$assessmentTypeName]))
					{
						$bucketAssessments[$assessmentTypeName] = array();
					}
					// set data (one to many)
					$bucketAssessments[$assessmentTypeName][] = $component;
				}
			}
			// return the assessment bucket
			return $bucketAssessments;
		}
		return false;
	}

	/**
	 * Get type assessment names
	 */
	public static function getTypeAssessmentsNames($type, $account, $as = 'string')
	{
		$assessments = self::getAssessmentAvaillable($type, $account);
		$names = array();
		if (self::checkArray($assessments))
		{
			foreach ($assessments as $name => $assessment)
			{
				$names[] = $name;
			}
		}
		if (self::checkArray($names))
		{
			// return as
			if ('string' === $as)
			{
				return implode(', ', $names);
			}
			return $names;
		}
		// still return string
		if ('string' === $as)
		{
			return 'Assessments';
		}
		return false;
	}

	/**
	 * Get assessment components
	 */
	public static function getAssessmentComponents($type = null, $account = null)
	{
		if (!self::checkArray(self::$assessmentComponents))
		{
			// get list of Extensions
			self::$assessmentComponents = self::setAssessmentComponents();
		}
		// filter by type & account
		if ($type && $account && self::checkArray(self::$assessmentComponents))
		{
			// filter out the components we need
			return array_filter(
				self::$assessmentComponents,
				function ($component) use ($type, $account) {
					// check if the component is available to this type of member
					return ((isset($component->params->membersmanager_target_type) && in_array($type, (array) $component->params->membersmanager_target_type)) && 
						(isset($component->params->membersmanager_target_account) && in_array($account, (array) $component->params->membersmanager_target_account)));
				}
			);
		}
		return self::$assessmentComponents;
	}

	/**
	 * set assessment components
	 */
	protected static function setAssessmentComponents()
	{
		$db = JFactory::getDBO();
		// get components
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__extensions AS a');
		$query->where('a.type = ' . $db->quote('component'));
		$query->where('a.protected = 0')->where('a.enabled = 1');
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			// get all components
			$listComponents = $db->loadObjectList();
			// filter out the components we need
			$listComponents = array_filter(
				$listComponents,
				function ($component) {
					if (strpos($component->params, 'activate_membersmanager_assessment') !== false)
					{
						// check if this component is active
						return json_decode($component->params)->activate_membersmanager_assessment;
					}
					return false;
				}
			);
			// check if we found components
			if (self::checkArray($listComponents))
			{
				// get language object
				$lang = JFactory::getLanguage();
				foreach ($listComponents as $listComponent)
				{
					// lets do a quick params setup (to objects)
					$listComponent->params = json_decode($listComponent->params);
					// try to load the translation
					$lang->load($listComponent->element, JPATH_ADMINISTRATOR, null, false, true);
					// translate the extension name if possible
					$listComponent->name = JText::_($listComponent->name);
					// translate the assessment type name
					if (isset($listComponent->params->assessment_type_name))
					{
						$listComponent->params->assessment_type_name = JText::_(strtoupper($listComponent->element) . '_CONFIG_' . $listComponent->params->assessment_type_name);
					}
				}
				return $listComponents;
			}
		}
		return false;
	}


	/**
	 * Load the tabs
	 *
	 * @param   object   $item     Data for the form
	 * @param   string   $view     The view name
	 * @param   string   $return   The return value if found
	 *
	 * @return string
	 *
	 */
	public static function loadDynamicTabs(&$item, $view = 'member', $return = '')
	{
		// only loads if type and account is set
		if (is_numeric($item->type) && $item->type > 0 && is_numeric($item->account) && $item->account > 0)
		{
			// get all the available component calling metods
			$class = new ReflectionClass('MembersmanagerHelper');
			$methods = array_filter($class->getMethods(ReflectionMethod::IS_PUBLIC),
				function ($method) {
					if (strpos($method->name, 'get') !== false && strpos($method->name, 'Availlable') !== false ) // The spelling mistake (Availlable) is to unique identify those classes.
					{
						return true;
					}
					return false;
				}
			);
			// set the tabs
			$tabs = array();
			$layout = array();
			if (self::checkArray($methods))
			{
				foreach ($methods as $method)
				{
					// get components
					$components = self::{$method->name}($item->type, $item->account);
					// check if we found components
					if (self::checkArray($components))
					{
						// get assessment details
						foreach ($components as $_name => $component)
						{
							if (self::checkArray($component))
							{
								$tables = array();
								foreach ($component as $_nr => $comp)
								{
									if (($ids = self::getVars('form', $item->id, $view, 'id', 'IN', str_replace('com_', '', $comp->element))) !== false && self::checkArray($ids))
									{
										$tables[] = self::getTabLinksTable($ids, $item, $comp, $view, $return);
									}
								}
								// load the tables to the layout
								if (self::checkArray($tables))
								{
									foreach ($tables as $table)
									{
										if (self::checkString($table))
										{
											if (!isset($layout[$_name]))
											{
												$layout[$_name] = $table;
											}
											else
											{
												$layout[$_name] .= $table;
											}
										}
									}
								}
								// add layout to tabs
								if (self::checkArray($layout) && count((array) $layout) == 2)
								{
									$tabs[] = self::setTab($layout, $view);
									$layout = array();
								}
							}
							elseif (self::checkObject($component) && isset($component->element))
							{
								if (($id = self::getVar('form', $item->id, $view, 'id', '=', str_replace('com_', '', $component->element))) === false) // get item ID
								{
									// if no item was found set to zero
									$id = 0;
								}
								// check if user are allowed to edit form values or create form values
								if (($id > 0 && JFactory::getUser()->authorise('form.edit', $component->element . '.form.' . (int) $id)) || ($id == 0 && JFactory::getUser()->authorise('form.create', $component->element)))
								{
									$fields = self::getTabFields($id, $component);
									// load the fields to the layout
									if (self::checkString($fields))
									{
										$layout[$_name] = $fields;
									}
									// add layout to tabs
									if (self::checkArray($layout) && count((array) $layout) == 2)
									{
										$tabs[] = self::setTab($layout, $view);
										$layout = array();
									}
								}
							}
						}
					}
				}
			}
			// add layout to tabs
			if (self::checkArray($layout))
			{
				$tabs[] = self::setTab($layout, $view);
			}
			// check if we have tabs
			if (self::checkArray($tabs))
			{
				return implode("\n", $tabs);
			}
		}
		return '';
	}

	/**
	 * get the tabe fields
	 *
	 * @param   int      $id          The item id
	 * @param   object   $component   The target component details
	 *
	 * @return string
	 *
	 */
	protected static function getTabFields($id, &$component)
	{
		// build the rows
		$rows = '';
		// get the form
		if (method_exists(__CLASS__, "getMemberForms") && ($form = self::getMemberForms($id, $component->element)) !== false && self::checkObject($form))
		{
			// get the fields for this form
			if (($fields = JComponentHelper::getParams($component->element)->get('edit_fields', false)) !== false && self::checkObject($fields))
			{
				// add the id field if the id was found (but hidden)
				if ($id > 0)
				{
					$form->setFieldAttribute('id', 'type', 'hidden');
					$rows = $form->renderField('id');
				}
				// add the rest of the fields
				foreach ($fields as $row)
				{
					if ($form->getField($row->field))
					{
						$rows .= PHP_EOL . $form->renderField($row->field);
					}
				}
			}
		}
		return $rows;
	}

	/**
	 * get the tab table of links
	 *
	 * @param   array    $ids         The target ids
	 * @param   object   $item        The target item details
	 * @param   object   $component   The target component details
	 * @param   string   $view        The view name
	 * @param   string   $return     The return value if found
	 *
	 * @return string
	 *
	 */
	protected static function getTabLinksTable($ids, &$item, &$comp, &$view, &$return)
	{
		// get the global settings
		$params = JComponentHelper::getParams($comp->element);
		// get the profile fields
		$profile_fields = $params->get('profile_fields', false);
		// set some defaults
		$_return = '&ref=' . $view . '&refid=' . $item->id . '&return=' . urlencode(base64_encode('index.php?option=com_membersmanager&view=' . $view . '&layout=edit&id=' . $item->id . $return));
		$rows = array();
		// add a row to create a new item
		if (($create_button = self::getCreateButton('form', 'forms', $_return, $comp->element)) !== false && self::checkString($create_button))
		{
			$rows[] = '<td data-column="'.$comp->name.'">' . $create_button . '</td>';
		}
		// build the links
		foreach ($ids as $id)
		{
			if (self::checkObject($profile_fields))
			{
				// the bucket
				$bucket = array();
				foreach ($profile_fields as $profile)
				{
					$bucket[$profile->field] = self::getVar('form', $id, 'id', $profile->field, '=', str_replace('com_', '', $comp->element));
				}
				$rows[] = '<td data-column="'.$comp->name.'">' . implode(', ', $bucket) . self::getEditButton($id, 'form', 'forms', $_return, $comp->element) . '</td>';
			}
			else
			{
				$created = self::getVar('form', $id, 'id', 'created', '=', str_replace('com_', '', $comp->element));
				$rows[] = '<td data-column="'.$comp->name.'">' . self::fancyDayTimeDate($created) . self::getEditButton($id, 'form', 'forms', $_return, $comp->element) . '</td>';
			}
		}
		// set the header
		$head = array($comp->name);
		// return the table
		return self::setSubformTable($head, $rows, $view . '_' . $comp->name);
	}


	/**
	 * get the form fields
	 *
	 * @param   string   $layout    The layout array
	 * @param   string   $code   The tab/view code name
	 * @param   string   $name   The tab name
	 * @param   int      $span      The span trigger
	 * @param   bool      $alert      Show the alert
	 *
	 * @return string
	 *
	 */
	protected static function setTab(&$layout, $code, $name = null, $span = 6, $alert = true)
	{
		// build the tab name
		if (!$name || !self::checkString($name))
		{
			$name = implode(' & ', array_keys($layout));
		}
$tmp = JHtml::_('bootstrap.addTab', $code . 'Tab', self::randomkey(10), $name);
		$tmp .= PHP_EOL . '<div class="row-fluid form-horizontal-desktop">';
		if (count((array) $layout) == 1)
		{
			if ($span == 6)
			{
				$tmp .= PHP_EOL . '<div class="span6">';
				if ($alert)
				{
					$tmp .= PHP_EOL . '<div class="uk-alert uk-alert-success"><b>' . array_keys($layout)[0] . '</b></div>';
				}
				$tmp .= PHP_EOL . array_values($layout)[0];
				$tmp .= PHP_EOL . '</div>';
				$tmp .= PHP_EOL . '<div class="span6">';
				$tmp .= PHP_EOL . '</div>';
			}
			else
			{
				$tmp .= PHP_EOL . '<div class="span12">';
				if ($alert)
				{
					$tmp .= PHP_EOL . '<div class="uk-alert uk-alert-success"><b>' . array_keys($layout)[0] . '</b></div>';
				}
				$tmp .= PHP_EOL . array_values($layout)[0];
				$tmp .= PHP_EOL . '</div>';
			}
		}
		else
		{
			foreach ($layout as $name => $value)
			{
				$tmp .= PHP_EOL . '<div class="span6">';
				if ($alert)
				{
					$tmp .= PHP_EOL . '<div class="uk-alert uk-alert-success"><b>' . $name . '</b></div>';
				}
				$tmp .= PHP_EOL . $value;
				$tmp .= PHP_EOL . '</div>';
			}
		}
		$tmp .= PHP_EOL . '</div>';
		$tmp .= JHtml::_('bootstrap.endTab');
		return $tmp;
	}


	/**
	 * set subform type table
	 *
	 * @param   array   $head    The header names
	 * @param   array   $rows    The row values
	 * @param   string  $idName  The prefix to the table id
	 *
	 * @return string
	 *
	 */
	public static function setSubformTable($head, $rows, $idName)
	{
		$table[] = "<div class=\"row-fluid\" id=\"vdm_table_display_".$idName."\">";
		$table[] = "\t<div class=\"subform-repeatable-wrapper subform-table-layout subform-table-sublayout-section-byfieldsets\">";
		$table[] = "\t\t<div class=\"subform-repeatable\">";
		$table[] = "\t\t\t<table class=\"adminlist table table-striped table-bordered\">";
		$table[] = "\t\t\t\t<thead>";
		$table[] = "\t\t\t\t\t<tr>";
		$table[] = "\t\t\t\t\t\t<th>" .  implode("</th><th>", $head) . "</th>";
		$table[] = "\t\t\t\t\t</tr>";
		$table[] = "\t\t\t\t</thead>";
		$table[] = "\t\t\t\t<tbody>";
		foreach ($rows as $row)
		{
			$table[] = "\t\t\t\t\t<tr class=\"subform-repeatable-group\">";
			$table[] = "\t\t\t\t\t\t" . $row;
			$table[] = "\t\t\t\t\t</tr>";
		}
		$table[] = "\t\t\t\t</tbody>";
		$table[] = "\t\t\t</table>";
		$table[] = "\t\t</div>";
		$table[] = "\t</div>";
		$table[] = "</div>";
		// return the table
		return implode("\n", $table);
	}


	/**
	 * save the dynamic values
	 *
	 * @param   object   $date     The main Data
	 * @param   string   $view     The view name
	 *
	 * @return string
	 *
	 */
	public static function saveDynamicValues(&$data, $view = 'member')
	{
		// get all the available component calling metods
		$class = new ReflectionClass('MembersmanagerHelper');
		$methods = array_filter($class->getMethods(ReflectionMethod::IS_PUBLIC),
			function ($method) {
				if (strpos($method->name, 'get') !== false && strpos($method->name, 'Availlable') !== false )
				{
					return true;
				}
				return false;
			}
		);
		// check if we have methods
		if (self::checkArray($methods))
		{
			// get the app object
			$app = JFactory::getApplication();
			// get the post object
			$post = JFactory::getApplication()->input->post;
			// get the user object
			$user = JFactory::getUser();
			// get the database object
			$db = JFactory::getDBO();
			// start looping the metods
			foreach ($methods as $method)
			{
				// get components
				$components = self::{$method->name}($data['type'], $data['account']);
				// check if we found components
				if (self::checkArray($components))
				{
					// get assessment details
					foreach ($components as $_name => $comp)
					{
						// only save one to one components
						if (self::checkObject($comp) && isset($comp->element))
						{
							$_component = $comp->element;
							$component = str_replace('com_', '', $_component);
							$Component = self::safeString($component, 'F');
							$COMponent = self::safeString($component, 'W');
							// get the posted date if there were any
							$_data  = $post->get($component, array(), 'array');
							// check if user are allowed to edit form values or create form values
							if (self::checkArray($_data))
							{
								// make sure the ID is set
								if (!isset($_data['id']) || !is_numeric($_data['id']))
								{
									$_data['id'] = 0;
								}
								// check if user may edit
								if ($_data['id'] > 0 && !$user->authorise('form.edit', $_component . '.form.' . (int) $_data['id']))
								{
									// check edit own
									if (($created_by = self::getVar('form', $_data['id'], 'id', 'created_by', '=', $component)) === false || $created_by != $user->id || !$user->authorise('form.edit.own', $_component))
									{
										$app->enqueueMessage(JText::sprintf('COM_MEMBERSMANAGER_YOU_DO_NOT_HAVE_PERMISSION_TO_EDIT_S_PLEASE_CONTACT_YOUR_SYSTEM_ADMINISTRATOR', $COMponent, $_data['id']), 'warning');
										continue;
									}
								}
								// check if user may create
								if ($_data['id'] == 0 && !$user->authorise('form.create', $_component))
								{
									$app->enqueueMessage(JText::sprintf('COM_MEMBERSMANAGER_YOU_DO_NOT_HAVE_PERMISSION_TO_ADD_DATA_TO_S_PLEASE_CONTACT_YOUR_SYSTEM_ADMINISTRATOR', $COMponent), 'warning');
									continue;
								}
								// make sure the member ID is set if view is member
								if ('member' === $view && !isset($_data[$view]) || !is_numeric($_data[$view]) || $_data[$view] == 0)
								{
									if ($_data['id'] > 0 && $data['id'] > 0)
									{
										// get the member ID
										if (($member = self::getVar('form', $_data['id'], 'id', $view, '=', $component)) === false || $member != $data['id'])
										{
											$app->enqueueMessage(JText::sprintf('COM_MEMBERSMANAGER_MEMBER_ID_MISMATCH_SS_COULD_NOT_BE_SAVED', $COMponent, $_data['id']), 'error');
											continue;
										}
									}
									elseif ($_data['id'] > 0)
									{
										// get the member ID
										if (($member = self::getVar('form', $_data['id'], 'id', $view, '=', $component)) === false || $member == 0)
										{
											$app->enqueueMessage(JText::sprintf('COM_MEMBERSMANAGER_MEMBER_ID_MISMATCH_SS_COULD_NOT_BE_SAVED', $COMponent, $_data['id']), 'error');
											continue;
										}
									}
									elseif ($data['id'] > 0)
									{
										// get the member ID
										$member = $data['id'];
									}
									else
									{
										$app->enqueueMessage(JText::sprintf('COM_MEMBERSMANAGER_S_COULD_NOT_BE_SAVED_PLEASE_TRY_AGAIN_THIS_COULD_BE_DUE_TO_THE_FACT_THIS_THE_MEMBER_ID_WAS_NOT_READY', $COMponent), 'error');
										continue;
									}
									// set the member ID
									$_data[$view] = $member;
								}
								// get the model
								$model = self::getModel('form', JPATH_ADMINISTRATOR . '/components/' . $_component, $Component);
								// do we have the model
								if ($model)
								{
									// force other component path (TODO) will be an issue if forms and fields are the same
									\JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $_component . '/models/forms');
									\JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/' . $_component . '/models/fields');
									// Validate the posted data.
									// Sometimes the form needs some posted data, such as for plugins and modules.
									$form = $model->getForm($_data, false);
									if (!$form)
									{
										$app->enqueueMessage($model->getError(), 'error');
										continue;
									}
									// remove all fields not part of the allowed edit fields
									if (($fields = JComponentHelper::getParams($_component)->get('edit_fields', false)) !== false && self::checkObject($fields))
									{
										// build a fields array bucket
										$fieldActive = array();
										foreach ($fields as $row)
										{
											$fieldActive[$row->field] = $row->field;
										}
										// set the keep values
										$fieldActive['id'] = 'id';
										$fieldActive['member'] = 'member';
										$fieldActive['asset_id'] = 'asset_id';
										$fieldActive['created'] = 'created';
										$fieldActive['created_by'] = 'created_by';
										$fieldActive['modified'] = 'modified';
										$fieldActive['modified_by'] = 'modified_by';
										$fieldActive['version'] = 'version';
										$fieldActive['rules'] = 'rules';
										// get the database columns of this table
										$columns = $db->getTableColumns("#__" . $component . "_form", false);
										// no make sure the fields that are not editable are removed (so can't be updated via this form)
										foreach(array_keys($columns) as $field)
										{
											if (!isset($fieldActive[$field]))
											{
												$form->removeField($field);
											}
										}
									}
									// Send an object which can be modified through the plugin event
									$objData = (object) $_data;
									$app->triggerEvent(
										'onContentNormaliseRequestData',
										array($_component . '.form', $objData, $form)
									);
									$_data = (array) $objData;
									// Test whether the data is valid.
									$validData = $model->validate($form, $_data);
									// Check for validation errors.
									if ($validData === false)
									{
										// Get the validation messages.
										$errors = $model->getErrors();
										// Push up to three validation messages out to the user.
										for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
										{
											if ($errors[$i] instanceof \Exception)
											{
												$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
											}
											else
											{
												$app->enqueueMessage($errors[$i], 'warning');
											}
										}
										continue;
									}
									// Attempt to save the data.
									if (!$model->save($validData))
									{
										$app->enqueueMessage(JText::sprintf('COM_MEMBERSMANAGER_S_DATA_COULD_NOT_BE_SAVED', $COMponent), 'error');
									}
								}
							}
						}
					}
				}
			}
		}
	}

	
	public static function jsonToString($value, $sperator = ", ", $table = null, $id = 'id', $name = 'name')
	{
		// do some table foot work
		$external = false;
		if (strpos($table, '#__') !== false)
		{
			$external = true;
			$table = str_replace('#__', '', $table);
		}
		// check if string is JSON
		$result = json_decode($value, true);
		if (json_last_error() === JSON_ERROR_NONE)
		{
			// is JSON
			if (self::checkArray($result))
			{
				if (self::checkString($table))
				{
					$names = array();
					foreach ($result as $val)
					{
						if ($external)
						{
							if ($_name = self::getVar(null, $val, $id, $name, '=', $table))
							{
								$names[] = $_name;
							}
						}
						else
						{
							if ($_name = self::getVar($table, $val, $id, $name))
							{
								$names[] = $_name;
							}
						}
					}
					if (self::checkArray($names))
					{
						return (string) implode($sperator,$names);
					}	
				}
				return (string) implode($sperator,$result);
			}
			return (string) json_decode($value);
		}
		return $value;
	}

	/**
	*	Load the Component xml manifest.
	**/
	public static function manifest()
	{
		$manifestUrl = JPATH_ADMINISTRATOR."/components/com_membersmanager/membersmanager.xml";
		return simplexml_load_file($manifestUrl);
	}

	/**
	*	Joomla version object
	**/	
	protected static $JVersion;

	/**
	*	set/get Joomla version
	**/
	public static function jVersion()
	{
		// check if set
		if (!self::checkObject(self::$JVersion))
		{
			self::$JVersion = new JVersion();
		}
		return self::$JVersion;
	}

	/**
	*	Load the Contributors details.
	**/
	public static function getContributors()
	{
		// get params
		$params	= JComponentHelper::getParams('com_membersmanager');
		// start contributors array
		$contributors = array();
		// get all Contributors (max 20)
		$searchArray = range('0','20');
		foreach($searchArray as $nr)
		{
			if ((NULL !== $params->get("showContributor".$nr)) && ($params->get("showContributor".$nr) == 2 || $params->get("showContributor".$nr) == 3))
			{
				// set link based of selected option
				if($params->get("useContributor".$nr) == 1)
                                {
					$link_front = '<a href="mailto:'.$params->get("emailContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
                                elseif($params->get("useContributor".$nr) == 2)
                                {
					$link_front = '<a href="'.$params->get("linkContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
                                else
                                {
					$link_front = '';
					$link_back = '';
				}
				$contributors[$nr]['title']	= self::htmlEscape($params->get("titleContributor".$nr));
				$contributors[$nr]['name']	= $link_front.self::htmlEscape($params->get("nameContributor".$nr)).$link_back;
			}
		}
		return $contributors;
	}

	/**
	 *	Can be used to build help urls.
	 **/
	public static function getHelpUrl($view)
	{
		return false;
	}

	/**
	*	Get any component's model
	**/
	public static function getModel($name, $path = JPATH_COMPONENT_SITE, $component = 'Membersmanager', $config = array())
	{
		// fix the name
		$name = self::safeString($name);
		// full path
		$fullPath = $path . '/models';
		// set prefix
		$prefix = $component.'Model';
		// load the model file
		JModelLegacy::addIncludePath($fullPath, $prefix);
		// get instance
		$model = JModelLegacy::getInstance($name, $prefix, $config);
		// if model not found (strange)
		if ($model == false)
		{
			jimport('joomla.filesystem.file');
			// get file path
			$filePath = $path.'/'.$name.'.php';
			$fullPath = $fullPath.'/'.$name.'.php';
			// check if it exists
			if (JFile::exists($filePath))
			{
				// get the file
				require_once $filePath;
			}
			elseif (JFile::exists($fullPath))
			{
				// get the file
				require_once $fullPath;
			}
			// build class names
			$modelClass = $prefix.$name;
			if (class_exists($modelClass))
			{
				// initialize the model
				return new $modelClass($config);
			}
		}
		return $model;
	}

	/**
	*	Add to asset Table
	*/
	public static function setAsset($id,$table)
	{
		$parent = JTable::getInstance('Asset');
		$parent->loadByName('com_membersmanager');
		
		$parentId = $parent->id;
		$name     = 'com_membersmanager.'.$table.'.'.$id;
		$title    = '';

		$asset = JTable::getInstance('Asset');
		$asset->loadByName($name);

		// Check for an error.
		$error = $asset->getError();

		if ($error)
		{
			$this->setError($error);

			return false;
		}
		else
		{
			// Specify how a new or moved node asset is inserted into the tree.
			if ($asset->parent_id != $parentId)
			{
				$asset->setLocation($parentId, 'last-child');
			}

			// Prepare the asset to be stored.
			$asset->parent_id = $parentId;
			$asset->name      = $name;
			$asset->title     = $title;
			// get the default asset rules
			$rules = self::getDefaultAssetRules('com_membersmanager',$table);
			if ($rules instanceof JAccessRules)
			{
				$asset->rules = (string) $rules;
			}

			if (!$asset->check() || !$asset->store())
			{
				JFactory::getApplication()->enqueueMessage($asset->getError(), 'warning');
				return false;
			}
			else
			{
				// Create an asset_id or heal one that is corrupted.
				$object = new stdClass();

				// Must be a valid primary key value.
				$object->id = $id;
				$object->asset_id = (int) $asset->id;

				// Update their asset_id to link to the asset table.
				return JFactory::getDbo()->updateObject('#__membersmanager_'.$table, $object, 'id');
			}
		}
		return false;
	}

	/**
	 *	Gets the default asset Rules for a component/view.
	 */
	protected static function getDefaultAssetRules($component,$view)
	{
		// Need to find the asset id by the name of the component.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__assets'))
			->where($db->quoteName('name') . ' = ' . $db->quote($component));
		$db->setQuery($query);
		$db->execute();
		if ($db->loadRowList())
		{
			// asset alread set so use saved rules
			$assetId = (int) $db->loadResult();
			$result =  JAccess::getAssetRules($assetId);
			if ($result instanceof JAccessRules)
			{
				$_result = (string) $result;
				$_result = json_decode($_result);
				foreach ($_result as $name => &$rule)
				{
					$v = explode('.', $name);
					if ($view !== $v[0])
					{
						// remove since it is not part of this view
						unset($_result->$name);
					}
					else
					{
						// clear the value since we inherit
						$rule = array();
					}
				}
				// check if there are any view values remaining
				if (count((array)$_result))
				{
					$_result = json_encode($_result);
					$_result = array($_result);
					// Instantiate and return the JAccessRules object for the asset rules.
					$rules = new JAccessRules($_result);

					return $rules;
				}
				return $result;
			}
		}
		return JAccess::getAssetRules(0);
	}

	/**
	 * xmlAppend
	 *
	 * @param   SimpleXMLElement   $xml      The XML element reference in which to inject a comment
	 * @param   mixed              $node     A SimpleXMLElement node to append to the XML element reference, or a stdClass object containing a comment attribute to be injected before the XML node and a fieldXML attribute containing a SimpleXMLElement
	 *
	 * @return  null
	 *
	 */
	public static function xmlAppend(&$xml, $node)
	{
		if (!$node)
		{
			// element was not returned
			return;
		}
		switch (get_class($node))
		{
			case 'stdClass':
				if (property_exists($node, 'comment'))
				{
					self::xmlComment($xml, $node->comment);
				}
				if (property_exists($node, 'fieldXML'))
				{
					self::xmlAppend($xml, $node->fieldXML);
				}
				break;
			case 'SimpleXMLElement':
				$domXML = dom_import_simplexml($xml);
				$domNode = dom_import_simplexml($node);
				$domXML->appendChild($domXML->ownerDocument->importNode($domNode, true));
				$xml = simplexml_import_dom($domXML);
				break;
		}
	}

	/**
	 * xmlComment
	 *
	 * @param   SimpleXMLElement   $xml        The XML element reference in which to inject a comment
	 * @param   string             $comment    The comment to inject
	 *
	 * @return  null
	 *
	 */
	public static function xmlComment(&$xml, $comment)
	{
		$domXML = dom_import_simplexml($xml);
		$domComment = new DOMComment($comment);
		$nodeTarget = $domXML->ownerDocument->importNode($domComment, true);
		$domXML->appendChild($nodeTarget);
		$xml = simplexml_import_dom($domXML);
	}

	/**
	 * xmlAddAttributes
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $attributes   The attributes to apply to the XML element
	 *
	 * @return  null
	 *
	 */
	public static function xmlAddAttributes(&$xml, $attributes = array())
	{
		foreach ($attributes as $key => $value)
		{
			$xml->addAttribute($key, $value);
		}
	}

	/**
	 * xmlAddOptions
	 *
	 * @param   SimpleXMLElement   $xml          The XML element reference in which to inject a comment
	 * @param   array              $options      The options to apply to the XML element
	 *
	 * @return  void
	 *
	 */
	public static function xmlAddOptions(&$xml, $options = array())
	{
		foreach ($options as $key => $value)
		{
			$addOption = $xml->addChild('option');
			$addOption->addAttribute('value', $key);
			$addOption[] = $value;
		}
	}

	/**
	 * get the field object
	 *
	 * @param   array      $attributes   The array of attributes
	 * @param   string     $default      The default of the field
	 * @param   array      $options      The options to apply to the XML element
	 *
	 * @return  object
	 *
	 */
	public static function getFieldObject($attributes, $default = '', $options = null)
	{
		// make sure we have attributes and a type value
		if (self::checkArray($attributes) && isset($attributes['type']))
		{
			// make sure the form helper class is loaded
			if (!method_exists('JFormHelper', 'loadFieldType'))
			{
				jimport('joomla.form.form');
			}
			// get field type
			$field = JFormHelper::loadFieldType($attributes['type'],true);
			// start field xml
			$XML = new SimpleXMLElement('<field/>');
			// load the attributes
			self::xmlAddAttributes($XML, $attributes);
			// check if we have options
			if (self::checkArray($options))
			{
				// load the options
				self::xmlAddOptions($XML, $options);
			}
			// setup the field
			$field->setup($XML, $default);
			// return the field object
			return $field;
		}
		return false;
	}

	/**
	 * Render Bool Button
	 *
	 * @param   array   $args   All the args for the button
	 *                             0) name
	 *                             1) additional (options class) // not used at this time
	 *                             2) default
	 *                             3) yes (name)
	 *                             4) no (name)
	 *
	 * @return  string    The input html of the button
	 *
	 */
	public static function renderBoolButton()
	{
		$args = func_get_args();
		// check if there is additional button class
		$additional = isset($args[1]) ? (string) $args[1] : ''; // not used at this time
		// button attributes
		$buttonAttributes = array(
			'type' => 'radio',
			'name' => isset($args[0]) ? self::htmlEscape($args[0]) : 'bool_button',
			'label' => isset($args[0]) ? self::safeString(self::htmlEscape($args[0]), 'Ww') : 'Bool Button', // not seen anyway
			'class' => 'btn-group',
			'filter' => 'INT',
			'default' => isset($args[2]) ? (int) $args[2] : 0);
		// set the button options
		$buttonOptions = array(
			'1' => isset($args[3]) ? self::htmlEscape($args[3]) : 'JYES',
			'0' => isset($args[4]) ? self::htmlEscape($args[4]) : 'JNO');
		// return the input
		return self::getFieldObject($buttonAttributes, $buttonAttributes['default'], $buttonOptions)->input;
	}

	/**
	 *  UIKIT Component Classes
	 **/
	public static $uk_components = array(
			'data-uk-grid' => array(
				'grid' ),
			'uk-accordion' => array(
				'accordion' ),
			'uk-autocomplete' => array(
				'autocomplete' ),
			'data-uk-datepicker' => array(
				'datepicker' ),
			'uk-form-password' => array(
				'form-password' ),
			'uk-form-select' => array(
				'form-select' ),
			'data-uk-htmleditor' => array(
				'htmleditor' ),
			'data-uk-lightbox' => array(
				'lightbox' ),
			'uk-nestable' => array(
				'nestable' ),
			'UIkit.notify' => array(
				'notify' ),
			'data-uk-parallax' => array(
				'parallax' ),
			'uk-search' => array(
				'search' ),
			'uk-slider' => array(
				'slider' ),
			'uk-slideset' => array(
				'slideset' ),
			'uk-slideshow' => array(
				'slideshow',
				'slideshow-fx' ),
			'uk-sortable' => array(
				'sortable' ),
			'data-uk-sticky' => array(
				'sticky' ),
			'data-uk-timepicker' => array(
				'timepicker' ),
			'data-uk-tooltip' => array(
				'tooltip' ),
			'uk-placeholder' => array(
				'placeholder' ),
			'uk-dotnav' => array(
				'dotnav' ),
			'uk-slidenav' => array(
				'slidenav' ),
			'uk-form' => array(
				'form-advanced' ),
			'uk-progress' => array(
				'progress' ),
			'upload-drop' => array(
				'upload', 'form-file' )
			);

	/**
	 *  Add UIKIT Components
	 **/
	public static $uikit = false;

	/**
	 *  Get UIKIT Components
	 **/
	public static function getUikitComp($content,$classes = array())
	{
		if (strpos($content,'class="uk-') !== false)
		{
			// reset
			$temp = array();
			foreach (self::$uk_components as $looking => $add)
			{
				if (strpos($content,$looking) !== false)
				{
					$temp[] = $looking;
				}
			}
			// make sure uikit is loaded to config
			if (strpos($content,'class="uk-') !== false)
			{
				self::$uikit = true;
			}
			// sorter
			if (self::checkArray($temp))
			{
				// merger
				if (self::checkArray($classes))
				{
					$newTemp = array_merge($temp,$classes);
					$temp = array_unique($newTemp);
				}
				return $temp;
			}
		}
		if (self::checkArray($classes))
		{
			return $classes;
		}
		return false;
	}

	/**
	 * Greate user and update given table
	 */
	public static function createUser($new)
	{
		// load the user component language files if there is an error.
		$lang = JFactory::getLanguage();
		$extension = 'com_users';
		$base_dir = JPATH_SITE;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
		// load the user regestration model
		$model = self::getModel('registration', JPATH_ROOT. '/components/com_users', 'Users');
		// make sure no activation is needed
		$useractivation = self::setParams('com_users','useractivation',0);
		// make sure password is send
		$sendpassword = self::setParams('com_users','sendpassword',1);
		// Check if password was set
		if (isset($new['password']) && isset($new['password2']) && self::checkString($new['password']) && self::checkString($new['password2']))
		{
			// Use the users passwords
			$password = $new['password'];
			$password2 = $new['password2'];
		}
		else
		{
			// Set random password
			$password = self::randomkey(8);
			$password2 = $password;
		}
		// set username if not set
		if (!isset($new['username']) || !self::checkString($new['username']))
		{
			$new['username'] = self::safeString($new['name']);
		}
		// linup new user data
		$data = array(
			'username' => $new['username'],
			'name' => $new['name'],
			'email1' => $new['email'],
			'password1' => $password, // First password field
			'password2' => $password2, // Confirm password field
			'block' => 0 );
		// register the new user
		$userId = $model->register($data);
		// set activation back to default
		self::setParams('com_users','useractivation',$useractivation);
		// set send password back to default
		self::setParams('com_users','sendpassword',$sendpassword);
		// if user is created
		if ($userId > 0)
		{
			return $userId;
		}
		return $model->getError();
	}

	protected static function setParams($component,$target,$value)
	{
		// Get the params and set the new values
		$params = JComponentHelper::getParams($component);
		$was = $params->get($target, null);
		if ($was != $value)
		{
			$params->set($target, $value);
			// Get a new database query instance
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			// Build the query
			$query->update('#__extensions AS a');
			$query->set('a.params = ' . $db->quote((string)$params));
			$query->where('a.element = ' . $db->quote((string)$component));
			
			// Execute the query
			$db->setQuery($query);
			$db->query();
		}
		return $was;
	}

	/**
	 * Update user values
	 */
	public static function updateUser($new)
	{
		// load the user component language files if there is an error.
		$lang = JFactory::getLanguage();
		$extension = 'com_users';
		$base_dir = JPATH_ADMINISTRATOR;
		$language_tag = 'en-GB';
		$reload = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);
		// load the user model
		$model = self::getModel('user', JPATH_ADMINISTRATOR . '/components/com_users', 'Users');
		// Check if password was set
		if (isset($new['password']) && isset($new['password2']) && self::checkString($new['password']) && self::checkString($new['password2']))
		{
			// Use the users passwords
			$password = $new['password'];
			$password2 = $new['password2'];
		}
		// set username
		if (isset($new['username']) && self::checkString($new['username']))
		{
			$new['username'] = self::safeString($new['username']);
		}
		else
		{
			$new['username'] = self::safeString($new['name']);
		}
		// linup update user data
		$data = array(
			'id' => $new['id'],
			'username' => $new['username'],
			'name' => $new['name'],
			'email' => $new['email'],
			'password1' => $password, // First password field
			'password2' => $password2, // Confirm password field
			'block' => 0 );
		// set groups if found
		if (isset($new['groups']) && self::checkArray($new['groups']))
		{
			$data['groups'] = $new['groups'];
		}
		// register the new user
		$done = $model->save($data);
		// if user is updated
		if ($done)
		{
			return $new['id'];
		}
		return $model->getError();
	}

	/**
	 * Get a variable 
	 *
	 * @param   string   $table        The table from which to get the variable
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 *
	 * @return  mix string/int/float
	 *
	 */
	public static function getVar($table, $where = null, $whereString = 'user', $what = 'id', $operator = '=', $main = 'membersmanager')
	{
		if(!$where)
		{
			$where = JFactory::getUser()->id;
		}
		// Get a db connection.
		$db = JFactory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array($what)));		
		if (empty($table))
		{
			$query->from($db->quoteName('#__'.$main));
		}
		else
		{
			$query->from($db->quoteName('#__'.$main.'_'.$table));
		}
		if (is_numeric($where))
		{
			$query->where($db->quoteName($whereString) . ' '.$operator.' '.(int) $where);
		}
		elseif (is_string($where))
		{
			$query->where($db->quoteName($whereString) . ' '.$operator.' '. $db->quote((string)$where));
		}
		else
		{
			return false;
		}
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			return $db->loadResult();
		}
		return false;
	}

	/**
	 * Get array of variables
	 *
	 * @param   string   $table        The table from which to get the variables
	 * @param   string   $where        The value where
	 * @param   string   $whereString  The target/field string where/name
	 * @param   string   $what         The return field
	 * @param   string   $operator     The operator between $whereString/field and $where/value
	 * @param   string   $main         The component in which the table is found
	 * @param   bool     $unique       The switch to return a unique array
	 *
	 * @return  array
	 *
	 */
	public static function getVars($table, $where = null, $whereString = 'user', $what = 'id', $operator = 'IN', $main = 'membersmanager', $unique = true)
	{
		if(!$where)
		{
			$where = JFactory::getUser()->id;
		}

		if (!self::checkArray($where) && $where > 0)
		{
			$where = array($where);
		}

		if (self::checkArray($where))
		{
			// prep main <-- why? well if $main='' is empty then $table can be categories or users
			if (self::checkString($main))
			{
				$main = '_'.ltrim($main, '_');
			}
			// Get a db connection.
			$db = JFactory::getDbo();
			// Create a new query object.
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array($what)));
			if (empty($table))
			{
				$query->from($db->quoteName('#__'.$main));
			}
			else
			{
				$query->from($db->quoteName('#_'.$main.'_'.$table));
			}
			$query->where($db->quoteName($whereString) . ' '.$operator.' (' . implode(',',$where) . ')');
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				if ($unique)
				{
					return array_unique($db->loadColumn());
				}
				return $db->loadColumn();
			}
		}
		return false;
	} 

	public static function isPublished($id,$type)
	{
		if ($type == 'raw')
		{
			$type = 'item';
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.published'));
		$query->from('#__membersmanager_'.$type.' AS a');
		$query->where('a.id = '. (int) $id);
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		{
			return true;
		}
		return false;
	}

	public static function getGroupName($id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(array('a.title'));
		$query->from('#__usergroups AS a');
		$query->where('a.id = '. (int) $id);
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
		{
			return $db->loadResult();
		}
		return $id;
	}

	/**
	* Get the action permissions
	*
	* @param  string   $view        The related view name
	* @param  int      $record      The item to act upon
	* @param  string   $views       The related list view name
	* @param  mixed    $target      Only get this permission (like edit, create, delete)
	* @param  string   $component   The target component
	*
	* @return  object   The JObject of permission/authorised actions
	* 
	**/
	public static function getActions($view, &$record = null, $views = null, $target = null, $component = 'membersmanager')
	{
		// get the user object
		$user = JFactory::getUser();
		// load the JObject
		$result = new JObject;
		// make view name safe (just incase)
		$view = self::safeString($view);
		if (self::checkString($views))
		{
			$views = self::safeString($views);
 		}
		// get all actions from component
		$actions = JAccess::getActionsFromFile(
			JPATH_ADMINISTRATOR . '/components/com_' . $component . '/access.xml',
			"/access/section[@name='component']/"
		);
		// if non found then return empty JObject
		if (empty($actions))
		{
			return $result;
		}
		// get created by if not found
		if (self::checkObject($record) && !isset($record->created_by) && isset($record->id))
		{
			$record->created_by = self::getVar($view, $record->id, 'id', 'created_by', '=', $component);
		}
		// set actions only set in component settings
		$componentActions = array('core.admin', 'core.manage', 'core.options', 'core.export');
		// check if we have a target
		$checkTarget = false;
		if ($target)
		{
			// convert to an array
			if (self::checkString($target))
			{
				$target = array($target);
			}
			// check if we are good to go
			if (self::checkArray($target))
			{
				$checkTarget = true;
			}
		}
		// loop the actions and set the permissions
		foreach ($actions as $action)
		{
			// check target action filter
			if ($checkTarget && self::filterActions($view, $action->name, $target))
			{
				continue;
			}
			// set to use component default
			$fallback = true;
			// reset permission per/action
			$permission = false;
			$catpermission = false;
			// set area
			$area = 'comp';
			// check if the record has an ID and the action is item related (not a component action)
			if (self::checkObject($record) && isset($record->id) && $record->id > 0 && !in_array($action->name, $componentActions) &&
				(strpos($action->name, 'core.') !== false || strpos($action->name, $view . '.') !== false))
			{
				// we are in item
				$area = 'item';
				// The record has been set. Check the record permissions.
				$permission = $user->authorise($action->name, 'com_' . $component . '.' . $view . '.' . (int) $record->id);
				// if no permission found, check edit own
				if (!$permission)
				{
					// With edit, if the created_by matches current user then dig deeper.
					if (($action->name === 'core.edit' || $action->name === $view . '.edit') && $record->created_by > 0 && ($record->created_by == $user->id))
					{
						// the correct target
						$coreCheck = (array) explode('.', $action->name);
						// check that we have both local and global access
						if ($user->authorise($coreCheck[0] . '.edit.own', 'com_' . $component . '.' . $view . '.' . (int) $record->id) &&
							$user->authorise($coreCheck[0]  . '.edit.own', 'com_' . $component))
						{
							// allow edit
							$result->set($action->name, true);
							// set not to use global default
							// because we already validated it
							$fallback = false;
						}
						else
						{
							// do not allow edit
							$result->set($action->name, false);
							$fallback = false;
						}
					}
				}
				elseif (self::checkString($views) && isset($record->catid) && $record->catid > 0)
				{
					// we are in item
					$area = 'category';
					// set the core check
					$coreCheck = explode('.', $action->name);
					$core = $coreCheck[0];
					// make sure we use the core. action check for the categories
					if (strpos($action->name, $view) !== false && strpos($action->name, 'core.') === false )
					{
						$coreCheck[0] = 'core';
						$categoryCheck = implode('.', $coreCheck);
					}
					else
					{
						$categoryCheck = $action->name;
					}
					// The record has a category. Check the category permissions.
					$catpermission = $user->authorise($categoryCheck, 'com_' . $component . '.' . $views . '.category.' . (int) $record->catid);
					if (!$catpermission && !is_null($catpermission))
					{
						// With edit, if the created_by matches current user then dig deeper.
						if (($action->name === 'core.edit' || $action->name === $view . '.edit') && $record->created_by > 0 && ($record->created_by == $user->id))
						{
							// check that we have both local and global access
							if ($user->authorise('core.edit.own', 'com_' . $component . '.' . $views . '.category.' . (int) $record->catid) &&
								$user->authorise($core . '.edit.own', 'com_' . $component))
							{
								// allow edit
								$result->set($action->name, true);
								// set not to use global default
								// because we already validated it
								$fallback = false;
							}
							else
							{
								// do not allow edit
								$result->set($action->name, false);
								$fallback = false;
							}
						}
					}
				}
			}
			// if allowed then fallback on component global settings
			if ($fallback)
			{
				// if item/category blocks access then don't fall back on global
				if ((($area === 'item') && !$permission) || (($area === 'category') && !$catpermission))
				{
					// do not allow
					$result->set($action->name, false);
				}
				// Finally remember the global settings have the final say. (even if item allow)
				// The local item permissions can block, but it can't open and override of global permissions.
				// Since items are created by users and global permissions is set by system admin.
				else
				{
					$result->set($action->name, $user->authorise($action->name, 'com_' . $component));
				}
			}
		}
		return $result;
	}

	/**
	* Filter the action permissions
	*
	* @param  string   $action   The action to check
	* @param  array    $targets  The array of target actions
	*
	* @return  boolean   true if action should be filtered out
	* 
	**/
	protected static function filterActions(&$view, &$action, &$targets)
	{
		foreach ($targets as $target)
		{
			if (strpos($action, $view . '.' . $target) !== false ||
				strpos($action, 'core.' . $target) !== false)
			{
				return false;
				break;
			}
		}
		return true;
	}

	/**
	*	Check if have an json string
	*
	*	@input	string   The json string to check
	*
	*	@returns bool true on success
	**/
	public static function checkJson($string)
	{
		if (self::checkString($string))
		{
			json_decode($string);
			return (json_last_error() === JSON_ERROR_NONE);
		}
		return false;
	}

	/**
	*	Check if have an object with a length
	*
	*	@input	object   The object to check
	*
	*	@returns bool true on success
	**/
	public static function checkObject($object)
	{
		if (isset($object) && is_object($object))
		{
			return count((array)$object) > 0;
		}
		return false;
	}

	/**
	*	Check if have an array with a length
	*
	*	@input	array   The array to check
	*
	*	@returns bool true on success
	**/
	public static function checkArray($array, $removeEmptyString = false)
	{
		if (isset($array) && is_array($array) && count((array)$array) > 0)
		{
			// also make sure the empty strings are removed
			if ($removeEmptyString)
			{
				foreach ($array as $key => $string)
				{
					if (empty($string))
					{
						unset($array[$key]);
					}
				}
				return self::checkArray($array, false);
			}
			return true;
		}
		return false;
	}

	/**
	*	Check if have a string with a length
	*
	*	@input	string   The string to check
	*
	*	@returns bool true on success
	**/
	public static function checkString($string)
	{
		if (isset($string) && is_string($string) && strlen($string) > 0)
		{
			return true;
		}
		return false;
	}

	/**
	*	Check if we are connected
	*	Thanks https://stackoverflow.com/a/4860432/1429677
	*
	*	@returns bool true on success
	**/
	public static function isConnected()
	{
		// If example.com is down, then probably the whole internet is down, since IANA maintains the domain. Right?
		$connected = @fsockopen("www.example.com", 80); 
			// website, port  (try 80 or 443)
		if ($connected)
		{
			//action when connected
			$is_conn = true;
			fclose($connected);
		}
		else
		{
			//action in connection failure
			$is_conn = false;
		}
		return $is_conn;
	}

	/**
	*	Merge an array of array's
	*
	*	@input	array   The arrays you would like to merge
	*
	*	@returns array on success
	**/
	public static function mergeArrays($arrays)
	{
		if(self::checkArray($arrays))
		{
			$arrayBuket = array();
			foreach ($arrays as $array)
			{
				if (self::checkArray($array))
				{
					$arrayBuket = array_merge($arrayBuket, $array);
				}
			}
			return $arrayBuket;
		}
		return false;
	}

	// typo sorry!
	public static function sorten($string, $length = 40, $addTip = true)
	{
		return self::shorten($string, $length, $addTip);
	}

	/**
	*	Shorten a string
	*
	*	@input	string   The you would like to shorten
	*
	*	@returns string on success
	**/
	public static function shorten($string, $length = 40, $addTip = true)
	{
		if (self::checkString($string))
		{
			$initial = strlen($string);
			$words = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
			$words_count = count((array)$words);

			$word_length = 0;
			$last_word = 0;
			for (; $last_word < $words_count; ++$last_word)
			{
				$word_length += strlen($words[$last_word]);
				if ($word_length > $length)
				{
					break;
				}
			}

			$newString	= implode(array_slice($words, 0, $last_word));
			$final	= strlen($newString);
			if ($initial != $final && $addTip)
			{
				$title = self::shorten($string, 400 , false);
				return '<span class="hasTip" title="'.$title.'" style="cursor:help">'.trim($newString).'...</span>';
			}
			elseif ($initial != $final && !$addTip)
			{
				return trim($newString).'...';
			}
		}
		return $string;
	}

	/**
	*	Making strings safe (various ways)
	*
	*	@input	string   The you would like to make safe
	*
	*	@returns string on success
	**/
	public static function safeString($string, $type = 'L', $spacer = '_', $replaceNumbers = true, $keepOnlyCharacters = true)
	{
		if ($replaceNumbers === true)
		{
			// remove all numbers and replace with english text version (works well only up to millions)
			$string = self::replaceNumbers($string);
		}
		// 0nly continue if we have a string
		if (self::checkString($string))
		{
			// create file name without the extention that is safe
			if ($type === 'filename')
			{
				// make sure VDM is not in the string
				$string = str_replace('VDM', 'vDm', $string);
				// Remove anything which isn't a word, whitespace, number
				// or any of the following caracters -_()
				// If you don't need to handle multi-byte characters
				// you can use preg_replace rather than mb_ereg_replace
				// Thanks @Łukasz Rysiak!
				// $string = mb_ereg_replace("([^\w\s\d\-_\(\)])", '', $string);
				$string = preg_replace("([^\w\s\d\-_\(\)])", '', $string);
				// http://stackoverflow.com/a/2021729/1429677
				return preg_replace('/\s+/', ' ', $string);
			}
			// remove all other characters
			$string = trim($string);
			$string = preg_replace('/'.$spacer.'+/', ' ', $string);
			$string = preg_replace('/\s+/', ' ', $string);
			// remove all and keep only characters
			if ($keepOnlyCharacters)
			{
				$string = preg_replace("/[^A-Za-z ]/", '', $string);
			}
			// keep both numbers and characters
			else
			{
				$string = preg_replace("/[^A-Za-z0-9 ]/", '', $string);
			}
			// select final adaptations
			if ($type === 'L' || $type === 'strtolower')
			{
				// replace white space with underscore
				$string = preg_replace('/\s+/', $spacer, $string);
				// default is to return lower
				return strtolower($string);
			}
			elseif ($type === 'W')
			{
				// return a string with all first letter of each word uppercase(no undersocre)
				return ucwords(strtolower($string));
			}
			elseif ($type === 'w' || $type === 'word')
			{
				// return a string with all lowercase(no undersocre)
				return strtolower($string);
			}
			elseif ($type === 'Ww' || $type === 'Word')
			{
				// return a string with first letter of the first word uppercase and all the rest lowercase(no undersocre)
				return ucfirst(strtolower($string));
			}
			elseif ($type === 'WW' || $type === 'WORD')
			{
				// return a string with all the uppercase(no undersocre)
				return strtoupper($string);
			}
			elseif ($type === 'U' || $type === 'strtoupper')
			{
				// replace white space with underscore
				$string = preg_replace('/\s+/', $spacer, $string);
				// return all upper
				return strtoupper($string);
			}
			elseif ($type === 'F' || $type === 'ucfirst')
			{
				// replace white space with underscore
				$string = preg_replace('/\s+/', $spacer, $string);
				// return with first caracter to upper
				return ucfirst(strtolower($string));
			}
			elseif ($type === 'cA' || $type === 'cAmel' || $type === 'camelcase')
			{
				// convert all words to first letter uppercase
				$string = ucwords(strtolower($string));
				// remove white space
				$string = preg_replace('/\s+/', '', $string);
				// now return first letter lowercase
				return lcfirst($string);
			}
			// return string
			return $string;
		}
		// not a string
		return '';
	}

	public static function htmlEscape($var, $charset = 'UTF-8', $shorten = false, $length = 40)
	{
		if (self::checkString($var))
		{
			$filter = new JFilterInput();
			$string = $filter->clean(html_entity_decode(htmlentities($var, ENT_COMPAT, $charset)), 'HTML');
			if ($shorten)
			{
           		return self::shorten($string,$length);
			}
			return $string;
		}
		else
		{
			return '';
		}
	}

	public static function replaceNumbers($string)
	{
		// set numbers array
		$numbers = array();
		// first get all numbers
		preg_match_all('!\d+!', $string, $numbers);
		// check if we have any numbers
		if (isset($numbers[0]) && self::checkArray($numbers[0]))
		{
			foreach ($numbers[0] as $number)
			{
				$searchReplace[$number] = self::numberToString((int)$number);
			}
			// now replace numbers in string
			$string = str_replace(array_keys($searchReplace), array_values($searchReplace),$string);
			// check if we missed any, strange if we did.
			return self::replaceNumbers($string);
		}
		// return the string with no numbers remaining.
		return $string;
	}

	/**
	*	Convert an integer into an English word string
	*	Thanks to Tom Nicholson <http://php.net/manual/en/function.strval.php#41988>
	*
	*	@input	an int
	*	@returns a string
	**/
	public static function numberToString($x)
	{
		$nwords = array( "zero", "one", "two", "three", "four", "five", "six", "seven",
			"eight", "nine", "ten", "eleven", "twelve", "thirteen",
			"fourteen", "fifteen", "sixteen", "seventeen", "eighteen",
			"nineteen", "twenty", 30 => "thirty", 40 => "forty",
			50 => "fifty", 60 => "sixty", 70 => "seventy", 80 => "eighty",
			90 => "ninety" );

		if(!is_numeric($x))
		{
			$w = $x;
		}
		elseif(fmod($x, 1) != 0)
		{
			$w = $x;
		}
		else
		{
			if($x < 0)
			{
				$w = 'minus ';
				$x = -$x;
			}
			else
			{
				$w = '';
				// ... now $x is a non-negative integer.
			}

			if($x < 21)   // 0 to 20
			{
				$w .= $nwords[$x];
			}
			elseif($x < 100)  // 21 to 99
			{ 
				$w .= $nwords[10 * floor($x/10)];
				$r = fmod($x, 10);
				if($r > 0)
				{
					$w .= ' '. $nwords[$r];
				}
			}
			elseif($x < 1000)  // 100 to 999
			{
				$w .= $nwords[floor($x/100)] .' hundred';
				$r = fmod($x, 100);
				if($r > 0)
				{
					$w .= ' and '. self::numberToString($r);
				}
			}
			elseif($x < 1000000)  // 1000 to 999999
			{
				$w .= self::numberToString(floor($x/1000)) .' thousand';
				$r = fmod($x, 1000);
				if($r > 0)
				{
					$w .= ' ';
					if($r < 100)
					{
						$w .= 'and ';
					}
					$w .= self::numberToString($r);
				}
			} 
			else //  millions
			{    
				$w .= self::numberToString(floor($x/1000000)) .' million';
				$r = fmod($x, 1000000);
				if($r > 0)
				{
					$w .= ' ';
					if($r < 100)
					{
						$w .= 'and ';
					}
					$w .= self::numberToString($r);
				}
			}
		}
		return $w;
	}

	/**
	*	Random Key
	*
	*	@returns a string
	**/
	public static function randomkey($size)
	{
		$bag = "abcefghijknopqrstuwxyzABCDDEFGHIJKLLMMNOPQRSTUVVWXYZabcddefghijkllmmnopqrstuvvwxyzABCEFGHIJKNOPQRSTUWXYZ";
		$key = array();
		$bagsize = strlen($bag) - 1;
		for ($i = 0; $i < $size; $i++)
		{
			$get = rand(0, $bagsize);
			$key[] = $bag[$get];
		}
		return implode($key);
	}

	/**
	 *	Get The Encryption Keys
	 *
	 *	@param  string        $type     The type of key
	 *	@param  string/bool   $default  The return value if no key was found
	 *
	 *	@return  string   On success
	 *
	 **/
	public static function getCryptKey($type, $default = false)
	{
		// Get the global params
		$params = JComponentHelper::getParams('com_membersmanager', true);
		// Medium Encryption Type
		if ('medium' === $type)
		{
			// check if medium key is already loaded.
			if (self::checkString(self::$mediumCryptKey))
			{
				return (self::$mediumCryptKey !== 'none') ? trim(self::$mediumCryptKey) : $default;
			}
			// get the path to the medium encryption key.
			$medium_key_path = $params->get('medium_key_path', null);
			if (self::checkString($medium_key_path))
			{
				// load the key from the file.
				if (self::getMediumCryptKey($medium_key_path))
				{
					return trim(self::$mediumCryptKey);
				}
			}
		}

		return $default;
	}


	/**
	 *	The Medium Encryption Key
	 *
	 *	@var  string/bool
	 **/
	protected static $mediumCryptKey = false;

	/**
	 *	Get The Medium Encryption Key
	 *
	 *	@param   string    $path  The path to the medium crypt key folder
	 *
	 *	@return  string    On success
	 *
	 **/
	public static function getMediumCryptKey($path)
	{
		// Prep the path a little
		$path = '/'. trim(str_replace('//', '/', $path), '/');
		jimport('joomla.filesystem.folder');
		/// Check if folder exist
		if (!JFolder::exists($path))
		{
			// Lock key.
			self::$mediumCryptKey = 'none';
			// Set the error message.
			JFactory::getApplication()->enqueueMessage(JText::_('COM_MEMBERSMANAGER_CONFIG_MEDIUM_KEY_PATH_ERROR'), 'Error');
			return false;
		}
		// Create FileName and set file path
		$filePath = $path.'/.'.md5('medium_crypt_key_file');
		// Check if we already have the file set
		if ((self::$mediumCryptKey = @file_get_contents($filePath)) !== FALSE)
		{
			return true;
		}
		// Set the key for the first time
		self::$mediumCryptKey = self::randomkey(128);
		// Open the key file
		$fh = @fopen($filePath, 'w');
		if (!is_resource($fh))
		{
			// Lock key.
			self::$mediumCryptKey = 'none';
			// Set the error message.
			JFactory::getApplication()->enqueueMessage(JText::_('COM_MEMBERSMANAGER_CONFIG_MEDIUM_KEY_PATH_ERROR'), 'Error');
			return false;
		}
		// Write to the key file
		if (!fwrite($fh, self::$mediumCryptKey))
		{
			// Close key file.
			fclose($fh);
			// Lock key.
			self::$mediumCryptKey = 'none';
			// Set the error message.
			JFactory::getApplication()->enqueueMessage(JText::_('COM_MEMBERSMANAGER_CONFIG_MEDIUM_KEY_PATH_ERROR'), 'Error');
			return false;
		}
		// Close key file.
		fclose($fh);
		// Key is set.
		return true;
	}
}
