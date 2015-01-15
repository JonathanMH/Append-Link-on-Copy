<?php
	/*
	Plugin Name: Append Link on Copy
	Plugin URI: http://jonathanmh.com/wordpress-plugin-append-link-on-copy/
	Description: This plugin allows the user to automatically append a link to the current page, when users copy & paste a title or any line
	Version: 0.2
	Author: Jonathan M. Hethey
	Author URI: http://jonathanmh.com
	License: GPLv3
	*/

if ( ! class_exists( 'Appendlink' ) ){
class Appendlink {

	private $plugin_url;
	private $plugin_dir;
	private $options;

	function __construct() {
		$this->plugin_url = plugins_url( basename( dirname( __FILE__ ) ) );
		$this->plugin_dir = dirname( __FILE__ );

		$this->options = get_option('append_link_on_copy_options');

		add_action( 'init', array( &$this, 'init') );
		add_action( 'wp', array( &$this, 'load_script' ) );

		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	function init(){
		$options = get_option( 'append_link_on_copy_options' );
		if( !isset($options['readmore']) ) $options['readmore'] = 'Read more at: %link%';
		if( !isset($options['prepend_break']) ) $options['prepend_break'] = 2;
		if( !isset($options['use_title']) ) $options['use_title'] = 'false';
		if( !isset($options['add_site_name']) ) $options['add_site_name'] = 'true';
		if( !isset($options['always_link_site']) ) $options['always_link_site'] = 'false';
		$this->options = $options;
	}

	function load_script() {
		wp_register_script( 'append_link', $this->plugin_url . '/js/append_link.js');
		wp_enqueue_script( 'append_link' );

		global $post;

		/* debugging
		echo '<pre>';
		var_dump( $post );
		echo '</pre>';
		*/

		$options = $this->options;

		$params = 	array(
			  'read_more'			=> $options['readmore']
			, 'prepend_break'		=> $options['prepend_break']
			, 'use_title'			=> $options['use_title']
			, 'add_site_name'		=> $options['add_site_name']
			, 'site_name'			=> get_bloginfo('name')
			, 'site_url'			=> get_bloginfo('url')
			, 'always_link_site'	=> $options['always_link_site']
		);

		if ($options['use_title'] === 'true') {
			if (is_singular()){
				$params['page_title'] = get_the_title($post->ID);
			}
			if (is_home() || is_front_page()){
				$params['page_title'] = get_bloginfo('name');
				$params['add_site_name'] = 'false';
			}
		}


		wp_localize_script( 'append_link', 'append_link', $params );
	}

	function admin_menu() {
		// add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
		add_options_page(
			__( 'Append Link on Copy Options', 'append_link_on_copy' )
			, __( 'Append Link on Copy', 'append_link_on_copy' )
			, 'manage_options'
			, 'append_link_on_copy_options'
			, array(&$this, 'settings_page')
		);
	}

	function admin_init() {

		// register_setting( $option_group, $option_name, $sanitize_callback );
		register_setting(
			  'append_link_on_copy_options'
			, 'append_link_on_copy_options'
			, array( &$this, 'settings_validate' )
		);

		// add_settings_section( $id, $title, $callback, $page );
		add_settings_section(
			'main'
			, 'Main Settings'
			, array( &$this, 'section_main' )
			, 'append_link_on_copy_options'
		);

		add_settings_section(
			'preview'
			, 'Preview Area'
			, array( &$this, 'section_preview' )
			, 'append_link_on_copy_options'
		);

		// add_settings_field( $id, $title, $callback, $page, $section, $args );
		add_settings_field(
			'readmore'
			, "Read more link: (like: Text copied from %link% )"
			, array( &$this, 'field_readmore' )
			, 'append_link_on_copy_options'
			, 'main'
		);

		add_settings_field(
			'add_site_name'
			, "Add the site name after link"
			, array( &$this, 'field_add_site_name' )
			, 'append_link_on_copy_options'
			, 'main'
		);

		add_settings_field(
			'use_title'
			, "Use the post title in the paste"
			, array( &$this, 'field_use_title' )
			, 'append_link_on_copy_options'
			, 'main'
		);

		add_settings_field(
			'always_link_site'
			, "Always link to main site, instead of page/post"
			, array( &$this, 'field_always_link_site' )
			, 'append_link_on_copy_options'
			, 'main'
		);

		add_settings_field(
			'prepend_break'
			, "How many &lt;br /&gt; tags should be inserted before the link? (default: 2)"
			, array( &$this, 'field_prepend_break' )
			, 'append_link_on_copy_options'
			, 'main'
		);
	}

	function section_main() {
		echo __('Change the appearance and contents of the appended link.');
	}

	function section_preview() {
		echo '<b>Notice:</b> Even though the text preview may not show the link, many web systems automatically link everything starting with http://, also everything copied from the front page, will not append the site title';
		$sample_quote = "Hi, I'm <a href=\"http://jonathanmh.com/\">Jonathan M. Hethey</a> and very happy to provide you with this plugin.";
		$sample_page_link = 'http://jonathanmh.com/wordpress-plugin-append-link-on-copy/';
		$sample_site_link = 'http://jonathanmh.com/';
		$sample_site_name = 'JonathanMH.com';


		if ($this->options['always_link_site'] == true) {
			$link = '<a href="' . $sample_site_link . '">';
		}
		else {
			$link = '<a href="' . $sample_page_link . '">';
		}

		if ($this->options['use_title'] == 'true'){
			$link .= 'Append Link on Copy';
		}
		else {
			if ($this->options['always_link_site'] == true){
				$link .= $sample_site_link;
			}
			else {
				$link .= $sample_page_link;
			}
		}

		if ($this->options['add_site_name'] == 'true'){
			$link .= ' | ' . $sample_site_name;
		}

		$link .= '</a>';

		echo '<h4>' . 'Quoted text: </h4>';
		echo "<blockquote>";
		echo $sample_quote;
		echo "</blockquote>";
		echo '<p>sample page link: <b>' . $sample_page_link . '</b></p>';
		echo '<p>sample site link: <b>' . $sample_site_link . '</b></p>';
		echo '<p>sample site name: <b>' . $sample_site_name . '</b></p>';
		echo '<h4>' . 'HTML preview' . '</h4>';
		echo "<blockquote>";
		echo $sample_quote;
		for ($i = 0; $i < $this->options['prepend_break']; $i++){
			echo '<br />';
		}

		echo $this->options['readmore'] . ' ' . $link;
		echo "</blockquote>";
		echo '<h4>' . 'Text preview' . '</h4>';
		echo "<blockquote>";
		echo strip_tags($sample_quote);
		for ($i = 0; $i < $this->options['prepend_break']; $i++){
			echo '<br />';
		}

		echo $this->options['readmore'] . ' ' . strip_tags($link);
		echo "</blockquote>";
	}

	function field_readmore() {
		echo
			'<input id='
			. 'append_link_on_copy_options[readmore]'
			. '" name="'
			. 'append_link_on_copy_options[readmore]'
			. '" size="40" type="text" value="'
			. $this->options['readmore']
			. '" />';
	}

	function field_prepend_break() {
		echo
			'<input id='
			. 'append_link_on_copy_options[prepend_break]'
			. '" name="'
			. 'append_link_on_copy_options[prepend_break]'
			. '" size="40" type="text" value="'
			. $this->options['prepend_break']
			. '" />';
	}

	function field_add_site_name() {
	echo  '<input type="hidden" name="append_link_on_copy_options[add_site_name]" value="false" />'
		. '<label><input type="checkbox" name="append_link_on_copy_options[add_site_name]" value="true"'
		. ($this->options['add_site_name'] != 'false' ? ' checked="checked"' : '')
		.' />';
	}

	function field_use_title() {
	echo  '<input type="hidden" name="append_link_on_copy_options[use_title]" value="false" />'
		. '<label><input type="checkbox" name="append_link_on_copy_options[use_title]" value="true"'
		. ($this->options['use_title'] != 'false' ? ' checked="checked"' : '')
		.' />';
	}

	function field_always_link_site() {
		echo  '<input type="hidden" name="append_link_on_copy_options[always_link_site]" value="false" />'
		. '<label><input type="checkbox" name="append_link_on_copy_options[always_link_site]" value="true"'
		. ($this->options['always_link_site'] != 'false' ? ' checked="checked"' : '')
		.' />';
	}

    function settings_page()
    {
        require( $this->plugin_dir . '/settings.php' );
    }

    function settings_validate( $input ) {
		$newinput = $input;
		$newinput['readmore'] = strip_tags($input['readmore']);
		$newinput['prepend_break'] = (integer) $input['prepend_break'];
		//$newinput['prepend_break'] = trim($input['prepend_break']);

		return $newinput;
	}


}

$append_link = new Appendlink();

}
