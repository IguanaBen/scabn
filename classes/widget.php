<?php


class scabn_Widget extends WP_Widget {
    /** constructor */
    function scabn_Widget() {
		$widget_ops = array('classname' => 'scabn_w', 'description' => __( 'Allows to display of SCABN\'s shopping cart'));
		$this->WP_Widget('scabn_s', __('SCABN Checkout Cart'), $widget_ops);
	}

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
    	extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		if (isset($before_widget)) { echo $before_widget;}
		if ( !empty($title) ) {
			echo $before_title . $title . $after_title;
		}
		echo apply_filters('scabn_display_cart','widget');
		if (isset($after_widget)) { echo $after_widget;}

    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
      return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
		$defaults = array('title' => 'SCABN Shopping Cart Widget');
		$instance = wp_parse_args( (array) $instance, $defaults );

		$output = "<p>";
		$output .= "<label for=\"". $this->get_field_id( 'title' ) . "\">Title:</label>";
		$output .= "<input id=\"" . $this->get_field_id( 'title' ) . "\" name=\"" . $this->get_field_name( 'title' ) . "\" value=\"" . $instance['title'] . "\"/>";
		$output .= "</p>";
		echo $output;
    }

} // class scabnWidget


// register scabnWidget widget
add_action('widgets_init', create_function('', 'return register_widget("scabn_Widget");'));

