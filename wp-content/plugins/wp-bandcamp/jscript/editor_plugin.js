(function() {
    tinymce.create('tinymce.plugins.bandcamp', {
 
        init : function(ed, url){
            ed.addButton('bandcamp', {
            title : 'Insert Bandcamp Player',
                onclick : function() {
            		wp_bandcamp_init();
                },
                image: url + "/../images/editor-button.png"
            });
        }
    });
 
    tinymce.PluginManager.add('bandcamp', tinymce.plugins.bandcamp);
 
})();