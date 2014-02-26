/**
 * @author Andre Fredette
 * @version 1.0 October 2009
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('phpurchase');

	tinymce.create('tinymce.plugins.phpurchase', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mcephproduct', function() {
				ed.windowManager.open({
					file : url + '/phpurchaseDialog.php',
					width : 500,
					height : 255 + (tinyMCE.isNS7 ? 20 : 0) + (tinyMCE.isMSIE ? 0 : 0),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register example button
			ed.addButton('phpurchase', {
				title : 'phpurchase.phpurchase_button_desc',
				cmd : 'mcephproduct',
				image : url + '/img/phpurchase.gif'
			});

		},

		/**
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'PHPurchase',
				author : 'Andre Fredette',
				authorurl : 'http://www.phpoet.com/',
				infourl : 'http://www.phpoet.com/',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('phpurchase', tinymce.plugins.phpurchase);
})();
