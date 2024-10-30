<?php
/*
Plugin Name: ie Insert Toc
Plugin URI: https://wordpress.org/plugins/ie-insert-toc
Description: Automatically pick up the H tag, securely create the table of contents and insert it.
Version: 2.1
Author: ryusai
Author URI: https://it-experience.tokyo/
License: GPL2
Text Domain: ie-insert-toc
Domain Path: /languages/
*/

/*  Copyright 2019 ryusai (email : info@it-experience.tokyo)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/**
*limit access from other
*/
if (!defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

/**
 * =========translation read=================================
 */

function ieitoc_textdomain() {
	load_plugin_textdomain( 'ie-insert-toc', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'ieitoc_textdomain');



/**
 * =========insert Processing =================================
 * @param string $content :article,
*                           $options :option values
 *                             [add_level] => add_level
 *                             [postpage] => postpage
 *                             [fixedpage] => fixedpage
 *                             [home] => home
 *                             [number] => number
 *                             [head_number] => head_number
 *                             [design] => non_design or base_design or special_design
 *                             [toc_title] => text
 * @return array content: new article
 */


if (!class_exists( 'Insert_Toc' ) ) :
class Insert_Toc{
  private $options;//options values
	private $comp_toc;
	private $shortcode_outline = '[ieitoc]';

   public function __construct()
   {
		 $this ->options = get_option('ieitoc_opt');
		 add_filter( 'widget_text', array($this, 'ieitoc_widget'));
     add_filter('the_content', array($this, 'ieitoc_add_toc'));
			if($this->options['design'] == 'base_design'){
				add_action( 'wp_head', array($this, 'ieitoc_custom_desgin_style'),999);
			}else	if($this->options['design'] == 'special_design'){
				add_action( 'wp_head', array($this, 'ieitoc_custom_special_style'),999);
			}
   }

	 //design CSS read
   public function ieitoc_custom_desgin_style(){
?>
		<style type="text/css">
	 .ietitoc_header{font-size:1.2em;position:relative;display:inline-block}#ieitoc_outline{overflow:hidden;margin:50px 2.5px;padding:2.5px}#ieitoc_outline ul{list-style-type:none;background:#eee}#ieitoc_outline>ul{box-shadow:2px 2px 5px rgba(0,0,0,.3);padding:15px;margin:5px}#ieitoc_outline li{padding:3px 0;line-height:1.8!important;color:#000}.ietitoc_header:after{content:'';position:absolute;top:calc(50% - 1px);right:-110px;height:1px;width:75pt;background:#333}
	 </style>
<?php
   }
   public function ieitoc_custom_special_style(){
?>
	 <style type="text/css">
		.ietitoc_header{font-size:1.2em;color:#000;position:relative;display:inline-block}#ieitoc_outline{overflow:hidden;margin:50px 2.5px;padding:2.5px}#ieitoc_outline ul{list-style-type:none;background:#eee;margin-left:0}#ieitoc_outline>ul{box-shadow:2px 2px 5px rgba(0,0,0,.3);padding:15px;margin:5px}#ieitoc_outline li{line-height:1.6!important;background-image:url(  <?php echo plugins_url('/images/cat_foot.png',__FILE__); ?> );background-size:1pc 1pc;background-repeat:no-repeat;background-position:0 8px;padding:5px 0 5px 18px!important}#ieitoc_outline a{position:relative;color:#000}.ietitoc_header:after{content:'';position:absolute;top:calc(50% - 1px);right:-110px;height:1px;width:75pt;background:#333}
		</style>
<?php
   }

   public function ieitoc_get_headline_info($content) {
		 $outline = '';
		 if (preg_match_all('/<h([1-6]).*?>(.*?)<\/h\1>/', $content, $matches,  PREG_SET_ORDER)) {
			 $min_level = min(array_map(function($m) { return $m[1]; }, $matches));
			 $current_level = $min_level - 1;
			 $sub_levels = array('1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0, '6' => 0);
			 $h_number = 0;

			 // loop h tag========================================
			 foreach ($matches as $m) {
				 $level = $m[1];
				 $text = $m[2];
				 $h_number++;

				 if(!isset($this->options['add_level'])){
					 if($m === reset($matches)){
						 $outline = "<ul><li>";
					 }else{
						 $outline .=  '</li><li>';
					 }
				 }else{
					 while ($current_level > $level) {
						 $current_level--;
						 $outline .= '</li></ul>';
					 }
					 if ($current_level == $level) {
						 $outline .= '</li><li>';
					 } else {
						 while ($current_level < $level) {
							 $current_level++;
							 $outline .= '<ul><li>';
						 }
						 for ($idx = $current_level + 0; $idx < count($sub_levels); $idx++){
							 $sub_levels[$idx] = 0;
						 }
					 }
				 }
				 $sub_levels[$current_level]++;
				 $level_fullpath = array();
				 for ($idx = $min_level; $idx <= $level; $idx++) {
				 $level_fullpath[] = $sub_levels[$idx];
				 }

				  // if there are not id attribute in htag
				 if (preg_match('/<h[1-6](.*?)>/', $m[0], $other_attr)){
					 $other_attr[1] = preg_replace('/\sid=".*?"/', '', $other_attr[1]);
					 $htag_other_attr = $other_attr[1];
				 }

				 // if there are id attribute
					if (preg_match('/\sid="(.*?)"/', $m[0], $id_attr)){
						$target_anchor = $id_attr[1];
					}else{
						if(!isset($this->options['add_level'])){
							$target_anchor = 'ietoc-' . $h_number;
						}else{
							$target_anchor = 'ietoc-' . implode('-', $level_fullpath);
						}
					}

					// creat a link tag
					if(!isset($this->options['add_level'])){
						if(!isset($this->options['number'])){
							$outline .= sprintf('<a href="#%s">%s</a>',  $target_anchor, $text);
						}else{
							$outline .= sprintf('%s. <a href="#%s">%s</a>', $h_number, $target_anchor, $text);
						}
					}else{
						if(!isset($this->options['number'])){
							$outline .= sprintf('<a href="#%s">%s</a>',  $target_anchor, $text);
						}else {
							$outline .= sprintf('%s  <a href="#%s">%s</a>', implode('.', $level_fullpath), $target_anchor, $text);
						}

					}

				 //all delete attribute
				 if ($m === reset($matches)) {
					 $content = preg_replace('/<h([1-6])\s.*?>/', '<h\1>',$content);
				 }
				 if(isset($this->options['head_number'])){
					 if(!isset($this->options['add_level'])){
						 $content = preg_replace('/<h([1-6])>(.*?)(<\/h\1>)/', '<h\1>' .  $h_number . '.' . '\2\3',$content,1);
						 //non;
					 }else{
						 $content = preg_replace('/<h([1-6])>(.*?)(<\/h\1>)/', '<h\1>' .  implode('.', $level_fullpath) . ' ' . '\2\3',$content,1);
					 }
				 }
				 //add attribute
				 $content = preg_replace('/<h([1-6])>/', '<h\1 id="' . $target_anchor . '"' . $htag_other_attr . '>',$content,1);

				 // end of replace h tag ===========
			 }//end of foreach===============================

				 if(!isset($this->options['add_level'])){
					 $outline .= '</li></ul>';
				 }else{
					 while ($current_level >= $min_level) {
								 $outline .= '</li></ul>';
								 $current_level--;
						 }
				 }



		 }// endif;
		 return array('content' => $content, 'outline' => $outline);
  }//end of ieitoc_get_headline_info


  /**
   * =========create table of contents ======================
   */


  public function ieitoc_add_toc($content) {
		// print_r($this->options);

      if (!is_single() && !is_page()) {
          // Hide non posted and fixed pages
          return $content;
      } else if (strtolower(get_post_meta(get_the_ID(), 'disable_outline', true)) == 'true') {
          // disable_outline = true
          return $content;
      }
      //option check

			if(mb_substr_count($content, "<h") < 3){
				return $content;
			}
			if(
				is_single() && !isset($this->options['postpage']) ||
				is_page() && !isset($this->options['fixedpage']) ||
				is_front_page() && !isset($this->options['home'])

				){
					if(isset($this->options['widget_h_number'])){
						$headline_info = $this -> ieitoc_get_headline_info($content);
						$content = $headline_info['content'];

						if (preg_match('/<h[1-6].*>/', $content, $matches, PREG_OFFSET_CAPTURE)) {
							$pos = $matches[0][1];
							$content = substr($content, 0, $pos) . substr($content, $pos);
						}
					}
				return $content;
			}


      //creat toc
			$headline_info = $this -> ieitoc_get_headline_info($content);
			$content = $headline_info['content'];
			$headline = $headline_info['outline'];
			$decorated_toc = sprintf('<div id="ieitoc_outline"><div class="ietitoc_header">%s</div>%s</div>',  $this ->options['toc_title'], $headline);

			if (preg_match('/<h[1-6].*>/', $content, $matches, PREG_OFFSET_CAPTURE)) {
				$pos = $matches[0][1];
				$content = substr($content, 0, $pos) . $decorated_toc . substr($content, $pos);
			}
			$this ->comp_toc = $decorated_toc;
		return $content;
  }

	public function ieitoc_widget($widget){


	if (strpos($widget, $this->shortcode_outline) !== false) {
		if(
			is_single() && !isset($this->options['postpage']) ||
			is_page() && !isset($this->options['fixedpage']) ){
				$content = get_the_content();
				if(mb_substr_count($content, "<h") < 3){
					return $widget;
				}
				$headline_info = $this -> ieitoc_get_headline_info($content);
				$content = $headline_info['content'];
				$headline = $headline_info['outline'];
				$decorated_toc = sprintf('<div id="ieitoc_outline"><div class="ietitoc_header">%s</div>%s</div>',  $this ->options['toc_title'], $headline);
			}else{

				$decorated_toc = $this ->comp_toc;
			}
			$widget = str_replace($this->shortcode_outline, $decorated_toc, $widget);
 		}
		return $widget;

}

}//end of class
if( !is_admin() ){
  $insert_info = new Insert_Toc();
}
endif;














/**
* =========Setting Processing =================================
*
**/

if (!class_exists( 'Ie_Toc_Setting' ) ) :
class Ie_Toc_Setting
{
  private $options = array();
	const PAY_URL = 'https://paypal.me/ryusai';

  //constructer
  public function __construct()
  {
		register_activation_hook(__FILE__, array($this, 'ieitoc_activation'));
		add_action( 'upgrader_process_complete', array($this, 'ieitoc_activation'));

    add_action( 'admin_menu', array( $this, 'ieitoc_add_setting_page' ) );
    add_action( 'admin_init', array( $this, 'ieitoc_page_init' ) );
    add_action( 'admin_head', array($this, 'ieitoc_custom_admin_style'));
  }

  //activation options value init
  public function ieitoc_activation(){
    //default options values : all checked
		$translate_text6 = __('Fatal error :Please contact the auther! : https://it-experience.tokyo');
		$translate_text7 = __('There is no important file!');
    $this ->options = array(
      'add_level' => 'add_level',
      'postpage'	=> 'postpage',
      'fixedpage'	=> 'fixedpage',
      'design'	=> 'non_design',
			'toc_title' => 'Table Of Contents'
    );

		update_option('ieitoc_opt',$this->options);
  }

  //options CSS read
  public function ieitoc_custom_admin_style(){

  $read_url =  dirname( __FILE__ ) . '/css/ieitoc.css';
  $css_url = plugins_url('/css/ieitoc.css',__FILE__);
    if (file_exists($read_url)) {
      wp_enqueue_style( 'custom', $css_url);
    }else{
      add_settings_error(
        'ieitoc_opt',
        esc_attr( 'settings_error' ),
        'Not found CSS file!',
        'error'
      );
    }
  }



  /**
   * register menu=========================
   */

  public function ieitoc_add_setting_page()
  {
  	add_options_page(
      'ie Insert TOC settings',
      'ie Insert TOC',
      'manage_options',//$capability
      'ieitoc-setting-admin',//$menu_slug
      array( $this, 'ieitoc_create_admin_page' )
    );
  }

  /**
   * create setting page =========================
   */
  public function ieitoc_create_admin_page()
  {
    // Set class property
    $this->options = get_option( 'ieitoc_opt' );
    $translate_text = __('Settings', 'ie-insert-toc');
    ?>
      <div class="wrap">
        <h1>ie Insert TOC <?php echo $translate_text  ?></h1>
        <form method="post" action="options.php">
        <?php
          // This prints out all hidden setting fields
          settings_fields( 'my_option_group' );
          do_settings_sections( 'ieitoc-setting-admin' );
          submit_button();
        ?>
        </form>
      </div>
    <?php
  }

  /**
   *  create option items & init======================
   */
  public function ieitoc_page_init()
  {
    $translate_text2 = __('<b>Tree</b>     : tree structure display' , 'ie-insert-toc');
    $translate_text3 = __('<b>Posts</b>    : Insert the TOC on the posted article' , 'ie-insert-toc');
    $translate_text4 = __('<b>Pages</b>   : Insert the TOC on the pages' , 'ie-insert-toc');
    $translate_text5 = __('<b>Home</b>   : Insert the TOC on the home' , 'ie-insert-toc');

    $translate_text6 = __('<b>TOC number</b>   : Insert the TOC number' , 'ie-insert-toc');
    $translate_text7 = __('<b>Headline number</b>   : Insert the headline number' , 'ie-insert-toc');
    $translate_text8 = __('<b>Non CSS</b>   : Non design' , 'ie-insert-toc');
    $translate_text9 = __('<b>Base CSS</b>   : Apply base design' , 'ie-insert-toc');

    $translate_text10 = __('<b>Special CSS</b>   : Apply Special design' , 'ie-insert-toc');

    $translate_text11 = __('<b>Title TEXT</b>   : TOC title' , 'ie-insert-toc');
    $translate_text12 = __('<b>Widget to headline number</b>   : Please checked if TOC show only Widget and show headline number' , 'ie-insert-toc');

    register_setting(
      'my_option_group', // Option group
      'ieitoc_opt'
      ,
      array($this, 'ieitoc_sanitize')//sanitaize
    );
    add_settings_section(
      'ieitoc_setting_section', // ID
      '', // Title
      array( $this, 'ieitoc_print_section_info' ), // Callback
      'ieitoc-setting-admin' // Page
    );
    add_settings_field(
      'ieitoc_opt',
      // $translate_text,
      '',
      array( $this, 'ieitoc_checkboxs_callback' ),
      'ieitoc-setting-admin',
      'ieitoc_setting_section',
    	array(
    		'options'	=> array(
          'add_level' => $translate_text2,
    			'postpage'	=> $translate_text3,
    			'fixedpage'	=> $translate_text4,
    			'home'	=> $translate_text5,
    			'number'	=> $translate_text6,
    			'head_number'	=> $translate_text7,
    			'non_design'	=> $translate_text8,
    			'base_design'	=> $translate_text9,
    			'special_design'	=> $translate_text10,
    			'toc_title'	=> $translate_text11,
    			'widget_h_number'	=> $translate_text12
    		)
    	)
    );


  }//end of page init
  /**
   * session Callback====================================
   */
  public function ieitoc_print_section_info(){
    $creator_url  = 'https://it-experience.tokyo';
		$logo_img_url = plugins_url('/images/IE_logo60-60.png',__FILE__);
		$logo_atr = 'iE-eXPERIENCE';
		$wordpress_page= 'https://wordpress.org/plugins/ie-insert-toc';
		$wordpress_img_url = plugins_url('/images/wordpress-logo.png',__FILE__);
		$wordpress_atr = 'WP Plugin page';
    $target_attr = '_blank';
		$translate_text1 = __('Automatically pick up the H tag and securely create and insert the table of contents', 'ie-insert-toc');
    $translate_text2 = __('Thank you for using this plugin! This plugin is free.', 'ie-insert-toc');
    $translate_text3 = __('If you like this, please give the tip to the producer!', 'ie-insert-toc');
    $translate_text4 = __('A new useful plug-in will be produced with your support!', 'ie-insert-toc');
    $translate_text5 = __('>>support this plugin!', 'ie-insert-toc');

    printf(
      '<p>%s</p>
      <div id="publicity">
        <p>
          <a href="%s" target ="%s">
            <img src="%s"><br>
						%s
          </a>
					<a href="%s" target="%s">
					<img src="%s"><br>
					%s
					</a>
          <span>
            %s<br>
            %s<br>
            %s<b><a href="%s" target="%s">%s</a></b><br></span>
        </p>
      </div>',
      $translate_text1, $creator_url, $target_attr, $logo_img_url,$logo_atr,$wordpress_page,  $target_attr,$wordpress_img_url, $wordpress_atr,$translate_text2,  $translate_text3, $translate_text4, self::PAY_URL, $target_attr, $translate_text5);


  }


  /**
   * checkboxs callback=================
   */
  public function ieitoc_checkboxs_callback( $args )
  {
    $optname	= 'ieitoc_opt';
    $html		= '';
    $checked = '';
		$imput_count = 0;

    foreach ( $args['options'] as $val => $title ) {
			if($imput_count < 6){
				// $checked sanitaize and set and print when checked
				if (isset($this->options) && is_array($this->options)) {
					$checked = in_array($val, $this->options) ? 'checked="checked"' : '';
					$html .= sprintf( '<input type="checkbox" id="%2$s" name="%1$s[%2$s]" value="%2$s" %3$s />', $optname, $val, $checked );
				} else {
					// $checked sanitaize and set and print when non checked
					$html .= sprintf( '<input type="checkbox" id="%2$s" name="%1$s[%2$s]" value="%2$s" />', $optname, $val );
				}
				$html .= sprintf( '<label for="%1$s[%2$s]"> %3$s</label><br />', $optname, $val, $title );

			}else if($imput_count < 9){

					$checked = ($val === $this->options['design']) ? 'checked="checked"' : '';
					$html .= sprintf( '<input type="radio" id="%2$s" name="%1$s[design]" value="%2$s" %3$s />', $optname, $val, $checked);

					$html .= sprintf( '<label for="%1$s[%2$s]"> %3$s</label><br />', $optname, $val, $title );
			}elseif($imput_count < 10){
				// if (isset($this->options) && is_array($this->options)) {
				//
				$html .= sprintf('<input type="text" id="%2$s" name="%1$s[%2$s]" value="%3$s">', $optname, $val, isset( $this->options['toc_title'] ) ? esc_attr ($this->options['toc_title']) : '');

				$html .= sprintf( '<label for="%1$s[%2$s]"> %3$s</label><br />', $optname, $val, $title );
				// }
			}elseif($imput_count < 11){
				if (isset($this->options) && is_array($this->options)) {
					$checked = in_array($val, $this->options) ? 'checked="checked"' : '';
					$html .= sprintf( '<input type="checkbox" id="%2$s" name="%1$s[%2$s]" value="%2$s" %3$s />', $optname, $val, $checked );
				} else {
					$html .= sprintf( '<input type="checkbox" id="%2$s" name="%1$s[%2$s]" value="%2$s" />', $optname, $val );
				}
				$html .= sprintf( '<label for="%1$s[%2$s]"> %3$s</label><br />', $optname, $val, $title );
			}


			if($imput_count === 0 || $imput_count === 3 || $imput_count === 5 || $imput_count === 8 || $imput_count === 9) {
				$html .= '<hr>';
			}
			$imput_count++;
    }//end of foreach


		$html .= '<p><b>Short Code for Widget</b> : [ieitoc] </p>';

		// print_r($this->options);
    echo $html;
  }

  /**
   * checkboxs sanitaize callback=================
   */
  public function ieitoc_sanitize($input) {

		// validation input text
			if( 20 < strlen( $input['toc_title'] ) ) {
				add_settings_error(
				'ieitoc_opt',
				'length-not-required',
				'Text must input within 20 characters.'
				);
				$input['toc_title'] = '';
			}
			$input['toc_title'] = htmlspecialchars($input['toc_title'], ENT_QUOTES, 'UTF-8');

			// validation radio button
			$check_radio = array('non_design', 'base_design', 'special_design');
			if(!in_array($input['design'], $check_radio)){
				add_settings_error(
				'ieitoc_opt',
				'error-required',
				'Fatal error'
				);
				exit('Fatal error : Please reinstall this plugin.');
			}
			return $input;
  }
}//end of class

if( is_admin() )
    $my_settings_page = new Ie_Toc_Setting();
endif;
