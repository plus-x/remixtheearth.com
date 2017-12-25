<?php


add_action ( 'init', 'wp_bandcamp_admin_init' );

function wp_bandcamp_admin_init()
{
	wp_enqueue_script( 'bc_colorpicker', plugins_url('/wp-bandcamp/jscript/bc_colorpicker.js'), array('farbtastic') );
	wp_enqueue_style( 'farbtastic' );
	
	wp_enqueue_script( 'jquery-ui-dialog' );
	add_action ( 'admin_footer', 'wp_bandcamp_admin_footer' );
	
	add_filter( 'mce_external_plugins', 'wp_bandcamp_mce_external_plugins' );
	add_filter( 'mce_buttons', 'wp_bandcamp_mce_buttons' );
}


function wp_bandcamp_mce_external_plugins($plugins)
{
	$plugins['bandcamp'] = plugins_url('/wp-bandcamp/jscript/editor_plugin.js');
	return $plugins;
}


function wp_bandcamp_mce_buttons( $buttons )
{
	array_push($buttons, "separator", "bandcamp");
	return $buttons;
}


function wp_bandcamp_admin_footer()
{
	$defaults = wp_bandcamp_default_atts();
	?>
	<div class="ui-hidden">
		<div id="bc-dialog">
			<div class="bc-dialog-content">
				<p>
					<?php $options = wp_bandcamp_content_types(); ?>
					<?php foreach($options as $name => $label):?>
						<label><input type="radio" name="bc-type" class="bc-type" value="<?php echo $name; ?>" <?php echo ($name == $defaults['type']) ? 'checked="checked"' : ''; ?>> <?php echo $label; ?></label> &nbsp;
					<?php endforeach; ?>
					&nbsp; &nbsp; &nbsp;				
					<span id="bc-track-container" class="bc-container" style="display:none;">
						<label><?php _e('Track ID'); ?>:
							<input type="text" name="bc-track-id" id="bc-track-id"/>
						</label>
					</span>
					<span id="bc-album-container" class="bc-container">
						<label><?php _e('Album ID'); ?>:
							<input type="text" name="bc-album-id" id="bc-album-id"/>
						</label>
					</span> &nbsp; &nbsp; &nbsp; &nbsp;
					
					<label for="bc-size"><?php _e('Size'); ?>:</label>
					<select name="bc-size" id="bc-size">
						<?php $options = wp_bandcamp_player_sizes(); ?>
						<?php foreach($options as $name => $label):?>
							<option value="<?php echo $name; ?>" <?php echo ($name == $defaults['size']) ? 'selected="selected"' : ''; ?>><?php echo $label; ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label for="bc-bg_color"><?php _e('Background Color'); ?>:</label>
					<input class="bc-picker-field" id="bc-bg_color" name="bc-bg_color" style="background-color:<?php echo $defaults['bgcol']; ?>" type="text" value="<?php echo $defaults['bgcol']; ?>" size="6" maxlength="7" /> &nbsp;
					
					<input id="bc-bg_transparent" name="bc-bg_transparent" type="checkbox" value="1" <?php echo ($defaults['bg_transparent'] ? 'checked="checked"' : ''); ?> />
					<label for="bc-bg_transparent"><?php _e('Transparent'); ?></label> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;

					<label for="bc-link_color"><?php _e('Links Color'); ?>:</label>
					<input class="bc-picker-field" id="bc-link_color" name="bc-link_color" style="background-color:<?php echo $defaults['linkcol']; ?>" type="text" value="<?php echo $defaults['linkcol']; ?>" size="6" maxlength="7" />
				</p>
				<p><strong>Please think about a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=5QUG426XZWQSJ&lc=US&item_name=WP%20Bandcamp%20Plugin&item_number=1%2e0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" target="_blank">donation</a>!</strong></p>		
			</div>
		</div>
	</div>
	<style type="text/css">
input.bc-input-error {border:1px solid red;}
.bc-dialog-content p {margin-bottom:1.5em;}
.bc-dialog-content select {padding:2px;height:2em;font-size:11px;}

/*dialog*/
.ui-dialog {
	/*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none;
	font-family: ;
	font-size: px;
	background: #fff;
	color: #333;
	border: 1px solid #AAA;
	position: relative;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	border-radius:6px;
	-moz-box-shadow:5px 5px 10px #666;
	-webkit-box-shadow:5px 5px 10px #666;
	box-shadow:5px 5px 10px #666;
}
.ui-dialog-titlebar {
	/*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none;
	padding: .5em 1.5em .5em 1em;
	color: #464646;
	border-bottom: 1px solid #DFDFDF;
	font-size: 1em;
	font-weight: bold;
	position: relative;
	background:url(/wp-admin/images/gray-grad.png) repeat-x scroll left top #DFDFDF;
	text-shadow:0 1px 0 #FFF;
	-moz-border-radius:6px 6px 0 0;
	-webkit-border-radius:6px 6px 0 0;
	border-radius:6px 6px 0 0;
}
.ui-dialog-titlebar-close {
	/*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none;
	background: url(<?php echo plugins_url('/wp-bandcamp/images/333333_11x11_icon_close.gif')?>) 0 0 no-repeat;
	position: absolute;
	right: 8px;
	top: .7em;
	width: 11px;
	height: 11px;
	z-index: 100;
}
.ui-dialog-titlebar-close-hover, .ui-dialog-titlebar-close:hover {
	background: url(<?php echo plugins_url('/wp-bandcamp/images/d54e21_11x11_icon_close.gif')?>) 0 0 no-repeat;
}
.ui-dialog-titlebar-close:active {
	background: url(<?php echo plugins_url('/wp-bandcamp/images/333333_11x11_icon_close.gif')?>) 0 0 no-repeat;
}
.ui-dialog-titlebar-close span {
	display: none;
}
.ui-dialog-content {
	/*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none;
	color: #333;
	padding: 1.5em 1.7em;	
}
.ui-dialog-buttonpane {
	position: absolute;
	bottom: 0;
	width: 100%;
	text-align: center;
	border-top: 1px solid #DFDFDF;
	background:#fff;
	padding: 10px 0;
	-moz-border-radius:0 0 6px 6px;
	-webkit-border-radius:0 0 6px 6px;
	border-radius:0 0 6px 6px;
}
.ui-dialog-buttonpane button {
	margin-right: 5px;
}
/* This file skins dialog */
.ui-dialog.ui-draggable .ui-dialog-titlebar,
.ui-dialog.ui-draggable .ui-dialog-titlebar {
	cursor: move;
}

/*hidden elements*/
.ui-hidden {
	display: none;/* for accessible hiding: position: absolute; left: -99999999px*/;
}
.ui-accessible-hidden {
	 position: absolute; left: -99999999px;
}
/*reset styles*/
.ui-reset {
	/*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none;
}
/*clearfix class*/
.ui-clearfix:after {
	content: ".";
	display: block;
	height: 0;
	clear: both;
	visibility: hidden;
}
.ui-clearfix {display: inline-block;}
/* Hides from IE-mac \*/
* html .ui-clearfix {height: 1%;}
.ui-clearfix {display: block;}
/* End hide from IE-mac */

.ui-dialog-title img {
	position: absolute;
	top: 5px;
	left: 10px;
}
#bc-size {
	width:150px;
}
	</style>
	<script type="text/javascript">
	function wp_bandcamp_init() {
		var buttons = { "<?php echo js_escape(__('OK')); ?>": wp_bandcamp_ok, "<?php echo js_escape(__('Cancel')); ?>": wp_bandcamp_cancel };

		jQuery("#bc-dialog").dialog({resizable:false, autoOpen: false, width:600, minWidth:600, height:256, minHeight:256, maxHeight:256, title: "Bandcamp Player", buttons: buttons });
		jQuery("#bc-dialog").dialog("open");
		wp_bandcamp_reset();
		jQuery("#bc-id").focus();
		
		jQuery(".ui-dialog button").addClass("button");
		jQuery(".ui-dialog button:first").addClass("button-primary");

		jQuery('.bc-type').live('change', function() {
			jQuery('.bc-container').hide();
			jQuery('#bc-size option').removeAttr('disabled');
			if (jQuery(this).is(':checked'))
			{
				jQuery('#bc-' + jQuery(this).val() + '-container').show();
				if (jQuery(this).val() == 'track')
				{
					jQuery('#bc-size option[value=grande2]').attr('disabled', 'disabled');
					jQuery('#bc-size option[value=grande3]').attr('disabled', 'disabled');
					jQuery('#bc-size option[value=tall2]').attr('disabled', 'disabled');
				}
			}
		});
		
		jQuery("#bc-id").live('keyup', function(){
			if (jQuery(this).val().length)
			{
				jQuery(this).removeClass('bc-input-error');
			}
		});

		jQuery("#bc-dialog :input").keyup(function(event){
			if ( 13 == event.keyCode ) // 13 == Enter
			{
				wp_bandcamp_ok();
			}
		});
	}

	function wp_bandcamp_reset()
	{
		jQuery("#bc-track-id, #bc-album-id").val('').removeClass('bc-input-error');
		jQuery("#bc-bg_transparent").removeAttr('checked');
		jQuery(".bc-type[value=<?php echo js_escape($defaults['type']); ?>]").attr("checked", "checked").trigger('change');
	}
	
	function wp_bandcamp_ok()
	{
		var type = jQuery('.bc-type:checked').val();
		switch(type)
		{
			case 'band':
				type = 'album';
			case 'track':
			case 'album':
			default:
				var id = jQuery('#bc-' + type + '-id').val();
				break;
		}
		if (id)
		{
			var size = jQuery('#bc-size').val();
			var bg_color = jQuery('#bc-bg_color').val().replace('#', '');
			var link_color = jQuery('#bc-link_color').val().replace('#', '');
			var bg_transparent = jQuery('#bc-bg_transparent').is(':checked');
			
			var transparent_text = bg_transparent ? ' transparent=' + bg_transparent : '';
			var text = '[bandcamp ' + type + '=' + id + ' bgcol=' + bg_color + ' linkcol=' + link_color + ' size=' + size + transparent_text + ']';
			//var text = '[wp_bandcamp_player type="' + type + '" id="' + id + '" size="' + size + '" bg_color="' + bg_color + '" link_color="' + link_color + '" transparent="' + bg_transparent + '"]';
			if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.activeEditor ) && !ed.isHidden() ) {
				ed.focus();
				if (tinymce.isIE)
				{
					ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);
				}
				ed.execCommand('mceInsertContent', false, text);
			} else
			{
				edInsertContent(edCanvas, text);
			}
			jQuery("#bc-dialog").dialog("close");
		} else {
			alert("<?php echo js_escape(__('ID value is mandatory!')); ?>");
			jQuery("#bc-' + type + '-id").addClass('bc-input-error').focus();
		}
	}

	function wp_bandcamp_cancel()
	{
		jQuery("#bc-dialog").dialog("close");
	}
	</script>
	<?php
}