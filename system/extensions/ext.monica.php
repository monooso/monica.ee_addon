<?php

/**
 * Super-simple site name replacement.
 *
 * @package   	Monica
 * @version   	0.1.0
 * @author    	Stephen Lewis <stephen@experienceinternet.co.uk>
 * @copyright 	Copyright (c) 2010, Stephen Lewis
 * @link      	http://experienceinternet.co.uk/software/
 */

if ( ! defined('EXT'))
{
	exit('Invalid file request');
}


class Monica {
  
	/* --------------------------------------------------------------
	 * PUBLIC PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * The extension name.
	 *
	 * @access  public
	 * @var     string
	 */
	public $name = 'Monica';

	/**
	 * The extension version.
	 *
	 * @access  public
	 * @var     string
	 */
	public $version = '0.1.0';

	/**
	 * The extension description.
	 *
	 * @access  public
	 * @var     string
	 */
	public $description = 'Super-simple site name replacement.';

	/**
	 * The documentation URL.
	 *
	 * @access  public
	 * @var     string
	 */
	public $docs_url = 'http://experienceinternet.co.uk/software/';

	/**
	 * Does this extension have custom settings?
	 *
	 * @access  public
	 * @var     string
	 */
	public $settings_exist = 'y';

	/**
	 * The extension settings.
	 *
	 * @access  public
	 * @var     array
	 */
	public $settings = array();


	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * The class name.
	 *
	 * @access	private
	 * @var 	string
	 */
	private $_class_name = '';
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   array|string    $settings     Associative array or empty string.
	 */
	public function __construct($settings = '')
	{
		$this->_class_name = get_class($this);
		$this->settings = $settings;
	}
	
	
	/**
	 * Activates the extension.
	 *
	 * @access  public
	 */
	public function activate_extension()
	{
		global $DB;
		
		$hooks = array(
			array(
				'hook'		=> 'show_full_control_panel_end',
				'method'	=> 'show_full_control_panel_end',
				'priority'	=> 10
			)
		);
			
		foreach ($hooks AS $hook)
		{
			$sql[] = $DB->insert_string('exp_extensions', array(
						'class'        => $this->_class_name,
						'enabled'      => 'y',
						'extension_id' => '',
						'hook'         => $hook['hook'],
						'method'       => $hook['method'],
						'priority'     => $hook['priority'],
						'settings'     => '',
						'version'      => $this->version
					));
		}
			
		foreach ($sql AS $query)
		{
			$DB->query($query);
		}		
	}
	
	
	/**
	 * Disables the extension, and deletes settings from the database.
	 *
	 * @access  public
	 */
	public function disable_extension()
	{
		global $DB;
		
		$DB->query("DELETE FROM exp_extensions WHERE class = '{$this->_class_name}'");
	}
	
	
	/**
	 * Displays the settings form.
	 *
	 * @access	public
	 * @return	array
	 */
	public function settings()
	{
		return array('suffix' => '');
	}
	
	
	/**
	 * Handles the 'show_full_control_panel_end' hook.
	 *
	 * @access	public
	 * @see		http://expressionengine.com/developers/extension_hooks/show_full_control_panel_end/
	 * @param	string		$out	The HTML to output.
	 * @return	string
	 */
	public function show_full_control_panel_end($out = '')
	{
		global $EXT, $PREFS;
		
		if ($EXT->last_call !== FALSE)
		{
			$out = $EXT->last_call;
		}
		
		$patterns = array();
		$replacements = array();
		
		// The site title.
		$patterns[] = "/(<div class=[\"']helpLinksLeft[\"']\W?>).*?(<\/div>)/is";
		$replacements[] = '$1<strong>' .$PREFS->ini('site_name') .'</strong>' .
			(isset($this->settings['suffix'])
				? '&nbsp;&nbsp;|&nbsp;&nbsp;<span>' .$this->settings['suffix'] .'</span>$2'
				: '$2');
		
		// CSS.
		$patterns[] = '/<\/head>/i';
		$replacements[] = <<<CSS

<style media="screen,projection" type="text/css">

	.helpLinksLeft {
	color : rgb(49, 62, 69);
	font : normal 14px/20px "Helvetica Neue", Arial, Helvetica, sans-serif;
	}
	
	.helpLinksLeft strong {
	color : rgb(255, 255, 255);
	font-size : 1em;
	font-weight : bold;
	}
	
	.helpLinksLeft span {color : rgb(200, 200, 200);}

</style>

CSS;
		
		return preg_replace($patterns, $replacements, $out);
	}
	
	
	/**
	 * Updates the extension.
	 *
	 * @access  public
	 * @param   string    $current    The current version of the extension (or an empty string).
	 * @return  bool      FALSE if the extension is not installed, or is the current version.
	 */
	public function update_extension($current='')
	{
		global $DB;

		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		if ($current < $this->version)
		{
			$DB->query("UPDATE exp_extensions
				SET version = '" . $DB->escape_str($this->version) . "' 
				WHERE class = '{$this->_class_name}'");
		}
	}
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------ */
	
	
}

/**
 * End of file ext.monica.php
 */