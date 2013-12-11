<?php
/**
 * Li xintao's public lib.
 *
 * @package   Public
 * @author    isurgeli@gmail.com
 * @license   GPL-2.0+
 * @link      http://isurge.wordpress.com
 * @copyright 2013 Li xinato
 */
include_once ("Smarty-3.1.15/libs/Smarty.class.php");

class lxt_public_lib {
	
	public static function post_query_title_filter($where, &$wp_query) {
		global $wpdb;
        if ( $search_term = $wp_query->get( 'search_prod_title' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( $search_term ) ) . '%\'';
        }
        return $where;
	}

	public static function smarty_template_array($temp, $para) {
		$smarty = new Smarty();
		$smarty->assign($para);
		return $smarty->fetch('string:'.$temp); 
	}
}
