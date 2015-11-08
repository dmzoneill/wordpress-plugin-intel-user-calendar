<?php
/*
Plugin Name: Intel User Calendar
Plugin URI: http://shannon.intel.com
Description: Displays user Calendar
Version: 0.1
Author: David O Neill
Author URI: http://shannon.intel.com
License: GPLv2
*/

require_once( "mssql.class.php" );
require_once( "intel-user-Calendar-widget.class.php" );


class Intel_User_Calendar_Plugin 
{
	private static $instance = false;
	private $prefix = 'intel_user_calendar_';
	private $settings = array();
  
	public function __construct() 
	{
		$this->settings = $this->get_settings_obj( $this->prefix );

		add_action( 'admin_init', array( $this, 'save_settings' ) );
		add_action( 'admin_menu', array( $this, 'menu' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
    
		add_action( 'wp_enqueue_scripts', array( $this, 'intel_user_calendar_scripts' ) );
		add_action( 'widgets_init', array( $this, 'intel_user_calendar_load_widgets' ) );
	}
  
	public static function getInstance() 
	{
		if ( !self::$instance ) 
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function intel_user_calendar_load_widgets() 
	{
		register_widget( 'Intel_User_Calendar_Widget' );
	}

	public function intel_user_calendar_scripts() 
	{
		wp_enqueue_style( 'style-name', plugins_url( 'intel-user-calendar/css/calendar.css' ) );
	}
  
	public function activate() 
  {
		$this->add_setting('bluebadge_holidays', array("1/1/2016") );
	}

	public function menu() 
  {
		add_options_page( "Intel User Calendar", "Intel User Calendar", 'manage_options', "intel-user-calendar", array( $this, 'admin_page' ) );
	}

	public function admin_page() 
  {
		include 'intel-user-calendar-admin.php';
	}
  
	function get_settings_obj() 
	{
		return get_option("{$this->prefix}settings", false);
	}

	function set_settings_obj( $newobj ) 
	{
		return update_option("{$this->prefix}settings", $newobj);
	}

	function set_setting( $option = false, $newvalue ) 
	{
		if( $option === false ) return false;

		$this->settings = $this->get_settings_obj($this->prefix);
		$this->settings[$option] = $newvalue;
		return $this->set_settings_obj($this->settings);
	}

	function get_setting( $option = false ) 
	{
		if($option === false || ! isset($this->settings[$option]) ) return false;

		return apply_filters($this->prefix . 'get_setting', $this->settings[$option], $option);
	}

	function add_setting( $option = false, $newvalue ) 
	{
		if($option === false ) return false;

		if ( ! isset($this->settings[$option]) ) 
		{
			return $this->set_setting($option, $newvalue);
		} 
		else 
			return false;
	}

	function get_field_name($setting, $type = 'string') 
	{
		return "{$this->prefix}setting[$setting][$type]";
	}

	function save_settings()
	{
		if( isset($_REQUEST["{$this->prefix}setting"]) && check_admin_referer('save_sll_settings','save_the_sll') ) 
		{
			$new_settings = $_REQUEST["{$this->prefix}setting"];

			foreach( $new_settings as $setting_name => $setting_value  ) 
			{
				foreach( $setting_value as $type => $value ) 
				{
					if( $type == "array" ) 
					{
						$this->set_setting($setting_name, explode("\n", $value));
					} 
					else 
					{
						$this->set_setting($setting_name, $value);
					}
				}
			}
			add_action('admin_notices', array($this, 'saved_admin_notice') );
		}
	}

	function saved_admin_notice()
	{
		echo '<div class="updated"><p>Intel user sidebar settings have been saved.</p></div>';
	}
}

Intel_User_Calendar_Plugin::getInstance();
