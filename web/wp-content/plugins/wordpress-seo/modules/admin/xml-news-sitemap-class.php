<?php

if (!class_exists('WPSEO_XML_Sitemap_Base'))
	require_once WPSEO_PATH.'sitemaps/xml-sitemap-base-class.php';
	
class WPSEO_XML_News_Sitemap extends WPSEO_XML_Sitemap_Base {

	function WPSEO_XML_News_Sitemap() {
		global $wpseo_echo;
		
		add_action( 'wpseo_dashboard', array(&$this, 'admin_panel' ), 10, 1 );

		$options = get_option("wpseo");

		if ( !isset($options['enablexmlnewssitemap']) || !$options['enablexmlnewssitemap'])
			return;
		
		add_filter('wpseo_metabox_entries',array(&$this, 'filter_meta_box_entries' ),10,1);

		if ( !isset($options['newssitemappath']) || empty($options['newssitemappath']) )
			return;

		$this->generate_sitemap( $options['newssitemapurl'], $options['newssitemappath'], $wpseo_echo );
		$this->ping_search_engines( $options['newssitemapurl'], $wpseo_echo );
	}
	
	function filter_meta_box_entries( $mbs ) {
		$mbs['newssitemap-genre'] = array(
			"name" => "newssitemap-genre",
			"type" => "multiselect",
			"std" => "blog",
			"title" => __("Google News Genre"),
			"description" => __("Genre to show in Google News Sitemap."),
			"options" => array(
				"pressrelease" => __("Press Release"),
				"satire" => __("Satire"),
				"blog" => __("Blog"),
				"oped" => __("Op-Ed"),
				"opinion" => __("Opinion"),
				"usergenerated" => __("User Generated"),
			),
		);
		return $mbs;
	}
	
	function admin_panel( $wpseo_admin ) {
		$content = '<p>'.__('You will generally only need XML News sitemap when your website is included in Google News. If it is, check the box below to enable the XML News Sitemap functionality.').'</p>';
		$content .= $wpseo_admin->checkbox('enablexmlnewssitemap',__('Enable  XML News sitemaps functionality.'));
		$content .= '<div id="newssitemapinfo">';
		$content .= '<p>'.__('Please check whether the auto-detected path and URL are correct:').'</p>';
		$content .= $wpseo_admin->textinput('newssitemappath',__('Path to the XML News Sitemap', 'yoast-wpseo'));
		$content .= $wpseo_admin->textinput('newssitemapurl',__('URL to the XML News Sitemap', 'yoast-wpseo'));
		$content .= '<br class="clear"/><br/>';
		$content .= $wpseo_admin->textinput('newssitemapname',__('Google News Publication Name', 'yoast-wpseo'));
		$content .= '<br class="clear"/><br/>';
		$content .= '<a class="button" href="javascript:testSitemap(\''.WPSEO_URL.'\',\'news\');">Test XML News sitemap values</a> ';
		$content .= '<a class="button" href="javascript:rebuildSitemap(\''.WPSEO_URL.'\',\'news\');">(Re)build XML News sitemap</a><br/><br/>';
		$content .= '<div id="newssitemaptestresult">'.wpseo_test_sitemap_callback(true, 'news').'</div>';
		$content .= '<br/>';
		$content .= '<div id="newssitemapgeneration"></div>';
		$content .= '</div>';
		$wpseo_admin->postbox('xmlnewssitemaps',__('XML News Sitemap', 'yoast-wpseo'),$content);
		
	}
	
	function generate_sitemap( $sitemapurl, $sitemappath, $echo = false) {

		global $wpdb, $wp_taxonomies, $wp_rewrite;
		$options = get_option("wpseo");
		
		$output = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$output .= '<?xml-stylesheet type="text/xsl" href="'.WPSEO_URL.'css/xml-news-sitemap.xsl"?>'."\n";
		$output .= '<!-- XML NEWS Sitemap Generated by Yoast WordPress SEO -->'."\n";
		$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:n="http://www.google.com/schemas/sitemap-news/0.9">'."\n"; 
		if ($echo)
			echo 'Starting to generate output.<br/><br/>';

		// Grab posts and pages and add to output
		$posts = $wpdb->get_results("SELECT ID, post_title, post_date FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC LIMIT 1000");
		if ($echo) {
			echo count($posts).' posts and pages found.<br/>';
		}

		foreach ($posts as $post) {
			if ( strpos(yoast_get_value( 'meta-robots', $post->ID ), 'noindex' ) !== false )
				continue;

			$link 	  = get_permalink( $post->ID );
			$keywords = '';
			$tags 	  = get_the_terms( $post->ID, 'post_tag' );
			if ($tags) {
				foreach ($tags as $tag) {
					$keywords .= $tag->name.', ';
				}
			}
			$keywords = preg_replace('/, $/','',$keywords);
			$genre = yoast_get_value("newssitemap-genre", $post->ID);
			if (is_array($genre))
				$genre = implode(",", $genre);
			$genre = preg_replace('/^none,?/','',$genre);
		
			$output .= "\t<url>\n";
			$output .= "\t\t<loc>".$link."</loc>\n";
			$output .= "\t\t<n:news>\n";
			$output .= "\t\t\t<n:publication>\n";
			$output .= "\t\t\t\t<n:name>".$options['newssitemapname']."</n:name>\n";
			$output .= "\t\t\t\t<n:language>".substr(get_locale(),0,2)."</n:language>\n";
			$output .= "\t\t\t</n:publication>\n";
			$output .= "\t\t\t<n:genres>".$genre."</n:genres>\n";
			$output .= "\t\t\t<n:publication_date>".$this->w3c_date($post->post_date)."</n:publication_date>\n";
			$output .= "\t\t\t<n:title>".$this->xml_clean($post->post_title)."</n:title>\n";
			if (strlen($keywords) > 0)
				$output .= "\t\t\t<n:keywords>".$this->xml_clean($keywords)."</n:keywords>\n";
			$output .= "\t\t</n:news>\n";
			$output .= "\t</url>\n"; 
		}
		unset($posts);

		$output .= '</urlset>';

		if ($this->write_sitemap( $sitemappath, $output ) && $echo)
			echo date('H:i').': <a href="'.$sitemapurl.'">News Sitemap</a> successfully (re-)generated.<br/><br/>';
	}
}

$wpseo_news_xml = new WPSEO_XML_News_Sitemap();