<?php
/*
Plugin Name: Featured Widget
Plugin URI: http://www.89pies.com/featured-widget
Description: Add featured posts to a widget
Author: Tom Singer
Version: 0.1
Author URI: http://www.89pies.com
License: MIT
*/

class tsi_featured_widget extends WP_Widget {
 
	function tsi_featured_widget() {
		$widget_ops = array(
			'classname' => 'tsi_featured_widget',
			'description' => 'Create Featured Widget');

		$control_ops = array(
			'width' => 250,
			'height' => 250,
			'id_base' => 'tsi_featured_widget');

		$this->WP_Widget('tsi_featured_widget', 'Featured Posts', $widget_ops, $control_ops );
	}
 
	function form ($instance) {
	}

	function update ($new_instance, $old_instance) {
	}

	function widget ($args,$instance) {
		extract($args);

		$title = "Featured Posts";

 		global $wpdb;

		$post_args = array(
			'meta_query' => array(
				array(
					'key' => 'tsi_featured',
					'value' => 'yes',
				)
			)
		);
 
		$posts = get_posts($post_args);

		if ( count($posts) > 0 ) {
			$out = '<ul>';
				foreach($posts as $post) {
					$out .= '<li><a href="'.get_permalink($post->ID).'">'.$post->post_title.'</a></li>';
				}
			$out .= '</ul>';

			echo $before_widget;
			echo $before_title.$title.$after_title;
			echo $out;
			echo $after_widget;
		}
	}
}

if ( is_admin() ) {
	add_action('admin_menu', 'tsi_meta_init');
	add_action('save_post', 'tsi_meta_handler');
}

function tsi_meta_init() {
	add_meta_box('tsi_featured_widget', 'Featured Post', 'tsi_meta_box', 'post', 'side');
}

function tsi_meta_box() {
	global $post_ID;
	$tsi_featured_flag = get_post_meta($post_ID, 'tsi_featured', true);
	
	echo "<input type=\"hidden\" name=\"tsi_featured_nonce\" id=\"tsi_featured_nonce\" value=\"" . wp_create_nonce(wp_hash(plugin_basename(__FILE__))) . "\" />";
	echo "<input type=\"checkbox\" id=\"tsi_featured_field\" name=\"tsi_featured_field\" value=\"yes\"";
	if ( $tsi_featured_flag == 'yes' ) {
		echo " checked=\"checked\"";
	}
	echo " />";
	echo " <label for=\"tsi_featured_field\">" . __("Make Post Featured", 'tsi_featured') . "</label>";
}

function tsi_meta_handler($post_id) {
	if ( !isset($_POST['tsi_featured_nonce']) || !wp_verify_nonce($_POST['tsi_featured_nonce'], wp_hash(plugin_basename(__FILE__))) ) {
		return $post_id;
	}

	if ( !current_user_can('edit_post', $post_id) ) {
		return $post_id;
	}

	if ( isset($_POST['tsi_featured_field']) && $_POST['tsi_featured_field'] == 'yes' ) {
		update_post_meta($post_id, 'tsi_featured', 'yes');
	} else {
		update_post_meta($post_id, 'tsi_featured', 'no');
	}
}

function tsi_load_widgets() {
	register_widget('tsi_featured_widget');
}

add_action('widgets_init', 'tsi_load_widgets');
