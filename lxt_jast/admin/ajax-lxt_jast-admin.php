<?php
/**
 * Just another survey tool.
 *
 * @package   lxt_jast_Admin
 * @author    isurgeli@gmail.com
 * @license   GPL-2.0+
 * @link      http://isurge.wordpress.com
 * @copyright 2013 Li xintao
 */

/**
 * Plugin class. This class used to do the ajax work for admin plugin work.
 */

class lxt_jast_ajax_Admin {
	protected $ver;
	protected $slug;
	protected $plugin;
	protected $admin;

	public function __construct() {
		$this->plugin = lxt_jast_plugin::get_instance(); 
		$this->admin = lxt_jast_plugin_Admin::get_instance(); 
		$this->slug = $this->plugin->get_slug();
		$this->ver = $this->plugin->get_ver();
	}
}
