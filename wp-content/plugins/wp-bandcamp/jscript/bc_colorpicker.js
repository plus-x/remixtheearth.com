jQuery(document).ready(function() {
	
	jQuery('.bc-picker-field').live('click', function(){
		var $this = jQuery(this);
		var id = $this.attr("id");
		
		if (!this.hasFarbtastic)
		{
			var picker = jQuery('<div/>', {
				'class':'bc-picker',
				'id':id + '-picker'
			}).farbtastic( '#' + id );
			var wrapper = jQuery('<div/>', {
				'class':'bc-picker-wrap',
				'id':id + '-picker-wrap'
			}).html(picker).css({
				'border':'3px solid #CCC',
				'padding':'3px',
				'backgroundColor':'#fff',
				'display':'none',
				'position':'absolute',
				'zIndex':'9999'
			});
			
			jQuery('body').append(wrapper);
			
			var offset = $this.offset();
			var left = offset.left;
			if ((left + wrapper.width()) > jQuery(window).width())
			{
				var right = jQuery(window).width() - (left + $this.width() + 8);
				wrapper.css('right', right);
			} else {
				wrapper.css('left', left);
			}
			wrapper.css('top', offset.top + $this.height() + 6);
			
			this.hasFarbtastic = true;
		}
		
		if (!jQuery("#" + id + "-picker-wrap").is(':visible'))
		{
			jQuery(".bc-picker-wrap").hide();
			jQuery("#" + id + "-picker-wrap").show();
		}
	});
	
	jQuery(document).click(function(e){
		if (jQuery(".bc-picker-wrap:visible").length)
		{
			if (!jQuery(e.target).is('.bc-picker-field, .bc-picker-wrap, .bc-picker-wrap *'))
			{
				jQuery('.bc-picker-field').each(function(){
					jQuery(this).val(jQuery(this).val().toUpperCase());
				});
				jQuery(".bc-picker-wrap").hide();
			}
		}
	});
 });