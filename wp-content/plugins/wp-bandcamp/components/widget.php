<?php


add_action('widgets_init', 'wp_bandcamp_widgets_init');


function wp_bandcamp_widgets_init()
{
	register_widget ( 'WP_Bandcamp_Player_Widget' );
}


class WP_Bandcamp_Player_Widget extends WP_Widget
{

	
	function WP_Bandcamp_Player_Widget()
	{
		if (is_admin() and is_widget_editor())
		{
			wp_enqueue_script( 'bc_colorpicker', plugins_url('/wp-bandcamp/jscript/bc_colorpicker.js'), array('farbtastic') );
			wp_enqueue_style( 'farbtastic' );
		}
		$widget_ops = array( 'description' => __( 'Embed Bandcamp player.' ) );
		$this->WP_Widget ( 'bandcamp_player', __('Bandcamp Player'), $widget_ops, wp_bandcamp_default_atts() );
	}
	
	
	function form($instance)
	{
		$instance = wp_parse_args( (array) $instance, wp_bandcamp_default_atts() );
		if (isset($instance['bg_color']))
		{
			$instance['bgcol'] = $instance['bg_color'];
			unset($instance['bg_color']);
		}
		if (isset($instance['link_color']))
		{
			$instance['linkcol'] = $instance['link_color'];
			unset($instance['link_color']);
		}
		?>
		<p><strong><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?>:</label></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" /></p>
		
		<p>
			<strong><label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Content type'); ?>:</label></strong><br/>
			<select name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>">
				<?php $options = wp_bandcamp_content_types(); ?>
				<?php foreach($options as $name => $label):?>
					<option value="<?php echo $name; ?>" <?php echo ($name == $instance['type']) ? 'selected="selected"' : ''; ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p><strong><label for="<?php echo $this->get_field_id('id'); ?>"><?php _e('ID'); ?>:</label></strong>
		<input class="widefat" id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>" type="text" value="<?php echo $instance['id']; ?>" /></p>
		
		<p>
			<strong style="text-transform:capitalize;"><?php _e('Colors'); ?>:</strong><br/>
			<label for="<?php echo $this->get_field_id('bgcol'); ?>"><?php _e('Background'); ?>:</label>
			<input class="bc-picker-field" id="<?php echo $this->get_field_id('bgcol'); ?>" name="<?php echo $this->get_field_name('bgcol'); ?>" style="background-color:<?php echo $instance['bgcol']; ?>" type="text" value="<?php echo $instance['bgcol']; ?>" size="6" maxlength="7" />
			
			<label for="<?php echo $this->get_field_id('linkcol'); ?>"><?php _e('Links'); ?>:</label>
			<input class="bc-picker-field" id="<?php echo $this->get_field_id('linkcol'); ?>" name="<?php echo $this->get_field_name('linkcol'); ?>" style="background-color:<?php echo $instance['linkcol']; ?>" type="text" value="<?php echo $instance['linkcol']; ?>" size="6" maxlength="7" />
		</p>
		
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name('transparent'); ?>" id="<?php echo $this->get_field_id('transparent'); ?>" value="1" <?php echo ($instance['transparent'] ? 'checked="checked"' : ''); ?>>
			<label for="<?php echo $this->get_field_id('transparent'); ?>"><?php _e('Transparent background'); ?></label>
		</p>
		
		<p>
			<strong><label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Size'); ?>:</label></strong><br/>
			<select name="<?php echo $this->get_field_name('size'); ?>" id="<?php echo $this->get_field_id('size'); ?>">
				<?php $options = wp_bandcamp_player_sizes(); ?>
				<?php foreach($options as $name => $label):?>
					<option value="<?php echo $name; ?>" <?php echo ($name == $instance['size']) ? 'selected="selected"' : ''; ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		
		<p><strong>Please think about a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=5QUG426XZWQSJ&lc=US&item_name=WP%20Bandcamp%20Plugin&item_number=1%2e0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" target="_blank">donation</a>!</strong></p>
		<?php
	}
	
	
	function widget($args, $instance)
	{
		extract($args);
		echo $before_widget;
		
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
		if ( $title)
			echo $before_title . $title . $after_title;
		
		$IDs = explode(',', $instance['id']);
		foreach($IDs as $ID)
		{
			echo wp_bandcamp_embed_player(array_merge($instance, array('id'=>trim($ID))));
		}
		echo $after_widget;
	}
	
	
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['transparent'] = FALSE;
		foreach ($new_instance as $field => $value)
		{
			switch ($field)
			{
				case 'type':
					$instance[$field] = in_array($value, array('track', 'album')) ? $value : $old_instance[$field];
					break;
				case 'id':
					$instance[$field] = strlen($value) ? trim(strip_tags($value), ', ') : $old_instance[$field];
					break;
				case 'title':
					$instance[$field] = trim(strip_tags($value));
					break;
				case 'size':
					$instance[$field] = strlen($value) ? strip_tags($value) : $old_instance[$field];
					break;
				case 'bgcol':
				case 'linkcol':
					$value = strtoupper(substr(trim($value), 0, 7));
					$instance[$field] = preg_match('/#[0-9A-F]{6}/', $value) ? $value : $old_instance[$field];
					break;
				case 'transparent':
					$instance[$field] = TRUE;
					break;
			}
		}
		return $instance;
	}
		
}






