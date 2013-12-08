<?php
/*
Plugin Name: Featured Posts Widget
Plugin URI: http://www.89pies.com/featured-posts-widget
Description: Add featured posts to a widget
Author: Tom Singer
Version: 1.0
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
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		$limit = $instance['limit'];
		$thumbnail_width = $instance['thumbnail_width'];
		$thumbnail_height = $instance['thumbnail_height'];
		$thumbnail_options = array('None' => 'none', 'Left' => 'left', 'Right' => 'right', 'Above' => 'above', 'Below' => 'below'); 
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>"><?php _e( 'Show Thumbnail:' )?></label>
			<select name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>"> 
			<?php foreach( $thumbnail_options as $description => $option ) { ?>
		  		<option value="<?php echo $option ?>" <?php echo ( $instance['show_thumbnail'] == $option ? ' selected="selected"' : '' ) ?> >
					<?php echo $description ?>
				</option>
			<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category:' )?></label>
			<select name="<?php echo $this->get_field_name( 'category' ); ?>" id="<?php echo $this->get_field_id( 'category' ); ?>"> 
			<option value=""><?php echo esc_attr( __('All Categories') ); ?></option> 
			<?php 
			$cat_args = array(
				'orderby' => 'term_group',
				'hide_empty' => true
			);
			$categories = get_categories( $cat_args ); 
			foreach( $categories as $category ) {
			  	$option = '<option value="' . $category->cat_ID . '"' . ( $instance['category'] == $category->cat_ID ? ' selected="selected"' : '' ) . '>';
				if( $category->parent )
					$option .= ' - ';
				$option .= $category->cat_name;
				$option .= '</option>';
				echo $option;
			}
			?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'thumbnail_width' ); ?>"><?php _e( 'Thumbnail Width:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'thumbnail_width' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail_width' ); ?>" type="text" value="<?php echo esc_attr( $thumbnail_width ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'thumbnail_height' ); ?>"><?php _e( 'Thumbnail Height:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'thumbnail_height' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail_height' ); ?>" type="text" value="<?php echo esc_attr( $thumbnail_height ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
		</p>
		<?php
	}

	function update ($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show_thumbnail'] = strip_tags( $new_instance['show_thumbnail'] );
		$instance['thumbnail_width'] = strip_tags( $new_instance['thumbnail_width'] );
		$instance['thumbnail_height'] = strip_tags( $new_instance['thumbnail_height'] );
		$instance['category'] = $new_instance['category'];
		$instance['limit'] = strip_tags( $new_instance['limit'] );
		return $instance;
	}

	function widget ( $args, $instance ) {
		extract($args);

		if (count($instance) > 0) {
			extract( $instance );
		}

		if (function_exists('add_image_size')) {
			if (is_null($thumbnail_width)) {
				$thumbnail_width = 0;
			}
			if (is_null($thumbnail_height)) {
				$thumbnail_height = 0;
			}
			add_image_size('featured-posts-widget-thumbnail', $instance['thumbnail_width'], $instance['thumbnail_height'], true);
		}

		if (is_null($show_thumbnail)) {
			$show_thumbnail = 'none';
		}

		if( ! $title )
			$title = "Featured Posts";
		
		$query_args = array(
			'meta_query' => array(
				array(
					'key' => 'featured_posts_widget_flag',
					'value' => 'true',
				)
			),
			'posts_per_page'=> -1,
			'ignore_sticky_posts' => true
		);
		if( $limit )
			$query_args['posts_per_page'] = intval( $limit );
		if( $category ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field' => 'id',
					'terms' => intval( $category ),
					'include_children' => false
				)
			);
		}
		
		$query = new WP_Query( $query_args );
		if( $query->post_count ) {
			$out = '<ul class="featured-posts-widget-thumbnail-' . $instance['show_thumbnail']  . '">';
			while( $query->have_posts() ) {
				$query->the_post();
				$out .= '<li>';
				if( ('above' == $show_thumbnail || 'left' == $show_thumbnail || 'right' == $show_thumbnail) && has_post_thumbnail( get_the_ID() ) ) {
					$out .= '<a href="' . get_permalink() . '">' . get_the_post_thumbnail( get_the_ID(), "featured-posts-widget-thumbnail" ) . '</a>';
				}
				if ('above' == $show_thumbnail) {
 					$out .= '<br />';
				}
				$out .= '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
				if ('below' == $show_thumbnail) {
 					$out .= '<br />';
				}
				if( ('below' == $show_thumbnail) && has_post_thumbnail( get_the_ID() ) ) {
					$out .= '<a href="' . get_permalink() . '">' . get_the_post_thumbnail( get_the_ID(), "featured-posts-widget-thumbnail" ) . '</a>';
				}
				$out .= '</li>';
			}
			$out .= '</ul>';

			echo $before_widget;
			echo $before_title . $title . $after_title;
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

function featured_posts_widget_scripts() {
	wp_register_style( 'featured-posts-widget-stylesheet', plugins_url('css/featured-posts-widget.css', __FILE__), array(), '1.0' );
	wp_enqueue_style( 'featured-posts-widget-stylesheet' ); 
}

add_action( 'wp_enqueue_scripts', 'featured_posts_widget_scripts' );

function featured_posts_widget_load_widgets() {
	register_widget('featured_posts_widget');
}

add_action('widgets_init', 'featured_posts_widget_load_widgets');

