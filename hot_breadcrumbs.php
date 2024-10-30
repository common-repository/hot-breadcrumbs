<?php
/*
Plugin Name: Hot Breadcrumbs
Plugin URI: http://hot-themes.com/wordpress/plugins/breadcrumbs
Description: Hot Breadcrumbs widget shows a pathway (breadcrumbs) with link hierarchy of your site. This gives a better idea to the visitors how did they get to this page and what's the way back. Showing a link hierarchy also has positive SEO effects and allows better crawling of your site.
Author: HotThemes
Author URI: http://hot-themes.com
Version: 1.3
Tags: link, links, widget
License: GNU/GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'hot_breadcrumbs_load_widgets' );

/**
 * Register our widget.
 * 'Breadcrumbs' is the widget class used below.
 *
 * @since 0.1
 */
function hot_breadcrumbs_load_widgets() {
	register_widget( 'HotBreadcrumbs' );
}

/**
 * Breadcrumbs Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class HotBreadcrumbs extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function __construct() {
	
	    add_action('admin_init', array($this, 'Breadcrumbs_textdomain'));
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'breadcrumbs', 'description' => __('Makes a pathway of links.', 'breadcrumbs') );

		/* Widget control settings. */
		$control_ops = array(  'id_base' => 'breadcrumbs-widget' );

		/* Create the widget. */
		parent::__construct( 'breadcrumbs-widget', __('Hot Breadcrumbs', 'breadcrumbs'), $widget_ops, $control_ops );

		/* Load CSS file */
		add_action('wp_print_styles', array( $this, 'HotBreadcrumbs_styles'));
		add_action('wp_head', array( $this, 'HotBreadcrumbs_inline_scripts_and_styles'));
	}

	function HotBreadcrumbs_styles(){
		wp_enqueue_style( 'hot-breadcrumbs-style', plugins_url('/style.css', __FILE__));
	}

	function HotBreadcrumbs_inline_scripts_and_styles(){
		$all_options = parent::get_settings();
		echo '<style type="text/css">';
		foreach ($all_options as $key => $value){
			$options = $all_options[$key];
			$defaults = $this->GetDefaults();
	 	    $options = wp_parse_args( (array) $options, $defaults );
			echo '
	            .hot-breadcrumbs, .hot-breadcrumbs a {
					font-size: '.$options['font_size'].';
					color: '.$options['font_color'].';
				}
			';
		}
		echo '</style>';
	}

	function GetDefaults()
	{
		return array(   
			'font_size' => '12px',
			'font_color' => '#333333'
		);
	}
	
	function Breadcrumbs_textdomain() {
		load_plugin_textdomain('hot-breadcrumbs', false, dirname(plugin_basename(__FILE__) ) . '/languages');
    }

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );
		/* Our variables from the widget settings. */
		$hide_home = isset( $instance['hide_home'] ) ? $instance['hide_home'] : 0;
		$separator = isset( $instance['separator'] ) ? $instance['separator'] : '&gt;';
		$before_text = isset( $instance['before_text'] ) ? $instance['before_text'] : '';
		$font_size = isset( $instance['font_size'] ) ? $instance['font_size'] : '12px';
		$font_color = isset( $instance['font_color'] ) ? $instance['font_color'] : '#333333';

		/* Before widget (defined by themes). */
		echo $before_widget; ?>
        <div class="hot-breadcrumbs">
        	<?php echo $before_text." "; ?>
        	<ol vocab="http://schema.org/" typeof="BreadcrumbList">
			<?php
			$positionCounter = 1;
			$posts_title = get_the_title( get_option('page_for_posts', true) );

			//var_dump(get_option('page_for_posts'));

			if($hide_home == 0){
				echo '<li property="itemListElement" typeof="ListItem"><a href="';
				echo get_home_url();
				echo '">';
				bloginfo('name');
				echo '</a> '.$separator.'<meta property="position" content="'.$positionCounter.'"></li>';
				$positionCounter++;
			}
			if (is_category() || is_single() || is_home()) {
				$category_id = get_cat_ID( 'Category Name' );
				if (is_category()) {
					echo '<li property="itemListElement" typeof="ListItem"> ';
					the_category(" & ");
					echo ' '.$separator.'<meta property="position" content="'.$positionCounter.'"></li>';
					$positionCounter++;
				}
				if (is_single()) {
					echo '<li property="itemListElement" typeof="ListItem"> ';
					if (get_option('page_for_posts') != "0") { ?>
						<a href="<?php echo esc_url( get_page_link( get_option('page_for_posts') ) ); ?>">
							<?php echo $posts_title; ?>
						</a>
						<?php
					} else {
						the_category(" & ");
					}
					echo ' '.$separator.'<meta property="position" content="'.$positionCounter.'"></li>';
					$positionCounter++;
					echo '<li property="itemListElement" typeof="ListItem"> ';
					the_title();
					echo '<meta property="position" content="'.$positionCounter.'"></li>';
					$positionCounter++;
				}
				if (is_home()) {
					echo '<li property="itemListElement" typeof="ListItem"> ';
					if (get_option('page_for_posts') != "0") {
						echo $posts_title;
					} else {
						the_category(" & ");
					}
					echo ' '.'<meta property="position" content="'.$positionCounter.'"></li>';
					$positionCounter++;
				}
			} elseif (is_page()) {
				echo '<li property="itemListElement" typeof="ListItem"> ';
				echo the_title();
				echo '<meta property="position" content="'.$positionCounter.'"></li>';
				$positionCounter++;
			} elseif (is_tag()) {
				echo '<li property="itemListElement" typeof="ListItem"> ';
				echo "Tag: "; echo single_tag_title();
				echo '<meta property="position" content="'.$positionCounter.'"></li>';
				$positionCounter++;
			}
			elseif (is_author()) {
				echo '<li property="itemListElement" typeof="ListItem"> ';
				echo "Author: "; the_author();
				echo '<meta property="position" content="'.$positionCounter.'"></li>';
				$positionCounter++;
			}
			elseif (is_archive()) {
				echo '<li property="itemListElement" typeof="ListItem"> ';
				echo the_archive_title();
				echo '<meta property="position" content="'.$positionCounter.'"></li>';
				$positionCounter++;
			}

		    ?>
		    </ol>
	    </div>
	    <?php
		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
    	
		foreach($new_instance as $key => $option) {
			$instance[$key] = $new_instance[$key];
		} 
		
		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'hide_home' => true, 'separator' => '&gt;', 'before_text' => '', 'font_size' => '12px', 'font_color' => '#333333' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Hot Breadcrumbs: Text Input -->
		<p>
		    <label for="<?php echo $this->get_field_id( 'hide_home' ); ?>"><?php _e('Hide home page link', 'hot-breadcrumbs'); ?></label>
			<select class="select"  id="<?php echo $this->get_field_id( 'hide_home' ); ?>" name="<?php echo $this->get_field_name( 'hide_home' ); ?>" >
                <option value="1" <?php if($instance['hide_home'] == "1") echo 'selected="selected"'; ?> ><?php _e('Yes', 'hot-breadcrumbs'); ?></option>
                <option value="0" <?php if($instance['hide_home'] == "0") echo 'selected="selected"'; ?> ><?php _e('No', 'hot-breadcrumbs'); ?></option>				
            </select>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'separator' ); ?>"><?php _e('Separator (HTML allowed)', 'hot-breadcrumbs'); ?></label>
			<textarea class="widefat" type="text" name="<?php echo $this->get_field_name( 'separator' ); ?>" id="<?php echo $this->get_field_id( 'separator' ); ?>"><?php echo $instance['separator']; ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'before_text' ); ?>"><?php _e('Before Text', 'hot-breadcrumbs'); ?></label>
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'before_text' ); ?>" id="<?php echo $this->get_field_id( 'before_text' ); ?>" value="<?php echo $instance['before_text']; ?>" /> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'font_size' ); ?>"><?php _e('Font Size', 'hot-breadcrumbs'); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'font_size' ); ?>" id="<?php echo $this->get_field_id( 'font_size' ); ?>" value="<?php echo $instance['font_size']; ?>" /> 
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'font_color' ); ?>"><?php _e('Font Color', 'hot-breadcrumbs'); ?></label>
			<input type="color" name="<?php echo $this->get_field_name( 'font_color' ); ?>" id="<?php echo $this->get_field_id( 'font_color' ); ?>" value="<?php echo $instance['font_color']; ?>" /> 
		</p>
	<?php
	}
}

?>