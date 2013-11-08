<?php
/*
Plugin Name: Featured Posts Widget
Plugin URI: http://www.89pies.com/featured-posts-widget
Description: Add featured posts to a widget
Author: Tom Singer
Version: 0.1
Author URI: http://www.89pies.com
License: MIT
*/

class featured_posts_widget extends WP_Widget {
 
	function featured_posts_widget() {
		$widget_ops = array(
			'classname' => 'featured_posts_widget',
			'description' => 'Create Featured Widget');

		$control_ops = array(
			'width' => 250,
			'height' => 250,
			'id_base' => 'featured_posts_widget');

		$this->WP_Widget('featured_posts_widget', 'Featured Posts', $widget_ops, $control_ops );
	}
 
	function form ($instance) {
	}

	function update ($new_instance, $old_instance) {
	}

	function widget ($args,$instance) {
		extract($args);

		$title = "Featured Posts";

		$post_args = array(
			'meta_query' => array(
				array(
					'key' => 'featured_posts_widget_flag',
					'value' => 'true',
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
	add_action('admin_menu', 'featured_posts_widget_meta_init');
	add_action('save_post', 'featured_posts_widget_meta_handler');
}

function featured_posts_widget_meta_init() {
	add_meta_box('featured_posts_widget', 'Featured Post', 'featured_posts_widget_meta_box', 'post', 'side');
}

function featured_posts_widget_meta_box() {
	global $post_ID;
	$featured_posts_widget_flag = get_post_meta($post_ID, 'featured_posts_widget_flag', true);
	
	echo "<input type=\"hidden\" name=\"featured_posts_widget_nonce\" id=\"featured_posts_widget_nonce\" value=\"" . wp_create_nonce(wp_hash(plugin_basename(__FILE__))) . "\" />";
	echo "<input type=\"checkbox\" id=\"featured_posts_widget_field\" name=\"featured_posts_widget_field\" value=\"true\"";
	if ( $featured_posts_widget_flag == 'true' ) {
		echo " checked=\"checked\"";
	}
	echo " />";
	echo " <label for=\"featured_posts_widget_field\">" . __("Make Post Featured", 'featured_posts_widget') . "</label>";
}

function featured_posts_widget_meta_handler($post_id) {
	if ( !isset($_POST['featured_posts_widget_nonce']) || !wp_verify_nonce($_POST['featured_posts_widget_nonce'], wp_hash(plugin_basename(__FILE__))) ) {
		return $post_id;
	}

	if ( !current_user_can('edit_post', $post_id) ) {
		return $post_id;
	}

	if ( isset($_POST['featured_posts_widget_field']) && $_POST['featured_posts_widget_field'] == 'true' ) {
		update_post_meta($post_id, 'featured_posts_widget_flag', 'true');
	} else {
		update_post_meta($post_id, 'featured_posts_widget_flag', 'false');
	}
}

function featured_posts_widget_load_widgets() {
	register_widget('featured_posts_widget');
}

add_action('widgets_init', 'featured_posts_widget_load_widgets');
