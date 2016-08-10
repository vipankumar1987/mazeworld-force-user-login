<?php
/*
Plugin Name: MazeWorld Force User Login
Plugin URI: http://www.mazeworld.in
Description: Easily hide your WordPress site from public viewing by requiring visitors to log in first. Activate to turn on.
Version: 4.2
Author: Vipan Kumar
Author URI: http://www.mazeworld.in/

Text Domain: wordpress-force-user-login
Domain Path: /languages

License: GPLv2 or later
*/

final Class MazeWorld
{
	private $static;
	public function __construct()
	{
		add_action('init', array($this, 'requireLogin'));		
	}
	public function get()
	{
		if(is_null(self::$static))
		{
			self::$static = new MazeWorld();
		}
		return self::$static;
	}
	public function isLogin()
	{
		return is_user_logged_in();
	}
	public function safeRedirect($redirect_url)
	{
		wp_safe_redirect( wp_login_url( $redirect_url ), 302 ); exit();		
	}
	public function checkMultiSite()
	{
		// Only allow Multisite users access to their assigned sites
		if ( function_exists('is_multisite') && is_multisite() ) {
		  global $current_user; 
		  get_currentuserinfo();
		  if ( !is_user_member_of_blog( $current_user->ID ) && !is_super_admin() )
			wp_die( __( "You're not authorized to access this site.", 'wordpress-force-user-login' ), get_option('blogname') . ' &rsaquo; ' . __( "Error", 'wordpress-force-user-login' ) );
		}		
	}
	function requireLogin()
	{

	  // Exceptions for AJAX, Cron, or WP-CLI requests
	  if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	  }

	  // Redirect unauthorized visitors
	  if ( !$this->isLogin()) {
		// Get URL
		$url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
		$url .= '://' . $_SERVER['HTTP_HOST'];
		// port is prepopulated here sometimes
		if ( strpos( $_SERVER['HTTP_HOST'], ':' ) === FALSE ) {
		  $url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
		}
		$url .= $_SERVER['REQUEST_URI'];

		// Apply filters
		$whitelist = apply_filters( 'v_forcelogin_whitelist', array() );
		$redirect_url = apply_filters( 'v_forcelogin_redirect', $url );

		// Redirect visitors
		if ( preg_replace('/\?.*/', '', $url) != preg_replace('/\?.*/', '', wp_login_url()) && !in_array($url, $whitelist) ) {
		  $this->safeRedirect($redirect_url)
		}
	  }
	  else {
		  $this->checkMultiSite();
	  }
	}
}
MazeWorld::get();