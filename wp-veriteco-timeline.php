<?php
/*
Plugin Name: WP VeriteCo Timeline
Plugin URI: http://www.josheaton.org/wordpress-plugins/wp-veriteco-timeline
Description: Internalizes VeriteCo Timeline Management into WordPress
Author: Josh Eaton, Young J. Yoon
Version: 1.1.2
Author URI: http://www.josheaton.org/
*/
/*  Copyright 2014  Josh Eaton  (email : josh@josheaton.org)

	Original code by Young J. Yoon.

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

	/* TIMELINE ENTRY CLASS */
	class wpvtEntry
	{
		private $post_id;

		public $startDate;
		public $endDate;
		public $headline;
		public $text;
		public $asset;

		public function __construct( $post ) {
			$this->post_id = $post->ID;
			$meta = get_post_meta( $this->post_id );

			$this->startDate = $meta['wpvt_start_date'][0];
			$this->endDate = $meta['wpvt_end_date'][0];
			$this->headline = get_the_title( $this->post_id );

			$text = apply_filters('the_content', $post->post_content);
			$text = str_replace( array( "\r", "\n" ), '', $text );
			$text = $this->undoTexturize($text);

			$this->text = $text;

			$thumbnail_id = get_post_thumbnail_id( $this->post_id );

			if( $thumbnail_id ) {
				// if there is featured image
				$img = wp_get_attachment_image_src( $thumbnail_id, 'full' );
				$thumbnail_image = get_post( $thumbnail_id, 'OBJECT' );
				if ($thumbnail_image && isset($thumbnail_image)) {
					$this->asset->media = $img[0];
					$this->asset->caption = $thumbnail_image->post_excerpt;
				}
			} else if( $meta['wpvt_video'][0] ) {
				// otherwise, look for youtube link
				$this->asset->media = $meta['wpvt_video'][0];
				$this->asset->caption = $meta['wpvt_video_caption'][0];
			}
		}

		public function toJSON() {
			return json_encode($this);
		}

		public function undoTexturize($content, $deprecated = '') {
			if ( !empty( $deprecated ) )
				_deprecated_argument( __FUNCTION__, '0.71' );

			// Translation of invalid Unicode references range to valid range
			$wp_htmltranswinuni = array(
				'&#8211;' => '-',
				'&#8212;' => '—',
				'&#8217;' => '\'',
				'&#8218;' => ',',
				'&#8220;' => '\"',
				'&#8221;' => '\"'
			);
			// Fix Word pasting
			$content = strtr($content, $wp_htmltranswinuni);
			return $content;
		}
	}


	/* Initailaize Back-end */
	function wpvt_admin_init() {
		wp_register_script( 'veriteco', plugins_url('js/timeline-min.js', __FILE__) );

		wp_register_script( 'wpvt_custom', plugins_url('js/wpvt_custom.js', __FILE__) );
		wp_register_style( 'wpvt_css', plugins_url('css/wpvt.css', __FILE__) );

		$page_title = "WP VeriteCo Timeline Configuration";
		$menu_title = "WP Timeline";
		$capability = "publish_posts";
		$menu_slug = "wpvt_config";
		$function = "wpvt_config_page";
		$icon_url = "";
		$position = "";

		add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function );

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('veriteco');
		wp_enqueue_script('wpvt_custom');

		wp_enqueue_style('wpvt_css');
	}
	add_action('admin_menu', 'wpvt_admin_init');


	/* Load Default Settings */
	function wpvt_default_settings() {
		$tmp = get_option('wpvt_options');
		if(!is_array($tmp)) {
			$arr = array(
				'width' => '900',
				'height' => '600',
				'maptype' => 'toner',
				'font' => 'Bevan-PotanoSans'
			);
			update_option('wpvt_options', $arr);
		}
	}
	register_activation_hook(__FILE__, 'wpvt_default_settings');


	/* Settings */
	function wpvt_settings_init() {
		$maptypes = array(
			'toner' => 'Stamen Maps: Toner',
			'toner-lines' => 'Stamen Maps: Toner Lines',
			'toner-labels' => 'Stamen Maps: Toner Labels',
			'watercolor' => 'Stamen Maps: Watercolor',
			'sterrain' => 'Stamen Maps: Terrain',
			'ROADMAP' => 'Google Maps: Roadmap',
			'TERRAIN' => 'Google Maps: Terrain',
			'HYBRID' => 'Google Maps: Hybrid',
			'SATELLITE' => 'Google Maps: Satellite'
		);

		$fonts = array(
			'Bevan-PotanoSans' => 'Bevan &amp; Potano Sans',
			'Merriweather-NewsCycle' => 'Merriweather &amp; News Cycle',
			'PoiretOne-Molengo' => 'Poiret One &amp; Molengo',
			'Arvo-PTSans' => 'Arvo &amp; PTSans',
			'PTSerif-PTSans' => 'PTSerif &amp; PTSans',
			'DroidSerif-DroidSans' => 'Droid Serif &amp; Droid Sans',
			'Lekton-Molengo' => 'Lekton &amp; Molengo',
			'NixieOne-Ledger' => 'NixieOne &amp; Ledger',
			'AbrilFatface-Average' => 'Abril Fatface &amp; Average',
			'PlayfairDisplay-Muli' => 'Playfair Display &amp; Muli',
			'Rancho-Gudea' => 'Rancho &amp; Gudea',
			'BreeSerif-OpenSans' => 'Bree Serif &amp; Open Sans',
			'SansitaOne-Kameron' => 'Sansita One &amp; Kameron',
			'Pacifico-Arimo' => 'Pacifico &amp; Arimo',
			'PT' => 'PT Sans &amp; PT Narrow &amp; PT Serif'
		);

		$types = array(
			'default' => 'Default'
		);

		add_settings_section('wpvt_id', '', 'wpvt_callback', 'wpvt_page');

		register_setting( 'wpvt_optiongroup', 'wpvt_options' ); // General Settings

		/* Add fields to cover page settings */
		add_settings_field('headline', 'Cover Headline', 'wpvt_setting_string', 'wpvt_page', 'wpvt_id', array('id' => 'headline', 'type' => 'text') );
		add_settings_field('text', 'Cover Text', 'wpvt_setting_string', 'wpvt_page', 'wpvt_id', array('id' => 'text', 'type' => 'text') );
		add_settings_field('type', 'Timeline Type', 'wpvt_setting_string', 'wpvt_page', 'wpvt_id', array('id' => 'type', 'type' => 'select', 'options' => $types ) );

		/* Add fields */
		add_settings_field('width', 'Width', 'wpvt_setting_string', 'wpvt_page', 'wpvt_id', array('id' => 'width', 'type' => 'text') );
		add_settings_field('height', 'Height', 'wpvt_setting_string', 'wpvt_page', 'wpvt_id', array('id' => 'height', 'type' => 'text') );
		add_settings_field('maptype', 'Map Type', 'wpvt_setting_string', 'wpvt_page', 'wpvt_id', array('id' => 'map', 'type' => 'select', 'options' => $maptypes ) );
		add_settings_field('font', 'Fonts', 'wpvt_setting_string', 'wpvt_page', 'wpvt_id', array('id' => 'fonts', 'type' =>'select', 'options' => $fonts ) );
		add_settings_field('start_at_end', 'Start at the end?', 'wpvt_setting_string', 'wpvt_page', 'wpvt_id', array('id' => 'start_at_end', 'type' => 'checkbox', 'label' => 'Yes') );
		add_settings_field('hash_bookmark', 'Hash Bookmarks?', 'wpvt_setting_string', 'wpvt_page', 'wpvt_id', array('id' => 'hash_bookmark', 'type' => 'checkbox', 'label' => 'Yes') );
	}
	add_action('admin_init', 'wpvt_settings_init');

		function wpvt_callback() { echo '<p>Adjust settings for the Timeline here.</p>'; }

		function wpvt_setting_string( $args ) {
			$options = get_option('wpvt_options');
			$id = $args['id'];
			$type = $args['type'];

			switch($type) {
				case 'text':
					$class = ($args['class']) ? ' class="'.$args['class'].'"' : '';
					echo "<input id='wpvt_".$id."' name='wpvt_options[".$id."]' type='text'". $class ." value='".$options[$id]."' />";
					break;
				case 'select':
					$choices = $args['options'];
					echo '<select id="wpvt_'.$id.'" name="wpvt_options['.$id.']">';
					foreach($choices as $value => $label) {
						$selected = ($options[$id] == $value) ? ' selected' : '';
						echo '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
					}
					echo '</select>';
					break;
				case 'checkbox':
					$checked = ($options[$id] == '1') ? ' checked' : '';
					echo '<input id="wpvt_'.$id.'" name="wpvt_options['.$id.']" type="checkbox" value="1" class="code" ' . $checked . ' /> '.$args['label'];
					break;
				default:
					break;
			}
		}

	/* Back-end Interface */
	function wpvt_config_page() { ?>
		<div class="wrap">
			<div id="poststuff">
				<div id="wpvt-icon"><br /></div>
				<?php echo '<h1 class="wpvt-title">' . __( 'WP Veriteco Timeline Configuration', 'wpvt-config' ) . '</h1>'; ?>
				<div class="clear"></div>

				<div class="postbox timeline-postbox">
					<h3>Timeline Settings</h3>

					<div class="inside">
						<form method="post" action="options.php">
							<?php settings_fields( 'wpvt_optiongroup' ); ?>
							<?php do_settings_sections( 'wpvt_page' ); ?>
							<?php submit_button(); ?>
						</form>
					</div>
				</div><!-- #postbox -->
			</div><!-- #poststuff -->
		</div>
	<?php }

	/* Register custom post type */
	function wpvt_post_type_init() {
		$labels = array(
			'name' => _x('Timeline Entries', 'post type general name'),
			'singular_name' => _x('Timeline Entry', 'post type singular name'),
			'add_new' => _x('Add New', 'timeline'),
			'add_new_item' => __('Add New Timeline Entry'),
			'edit_item' => __('Edit Timeline Entry'),
			'new_item' => __('New Timeline Entry'),
			'all_items' => __('All Timeline Entries'),
			'view_item' => __('View Timeline Entry'),
			'search_items' => __('Search Timeline Entries'),
			'not_found' =>  __('No Timeline Entries found'),
			'not_found_in_trash' => __('No Timeline Entries found in Trash'),
			'parent_item_colon' => '',
			'menu_name' => __('Timeline')
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title', 'editor', 'thumbnail' ),
			'register_meta_box_cb' => 'wpvt_meta_boxes'
		);
		register_post_type( 'timeline' , $args );

		wp_register_style( 'veriteco_css', plugins_url('css/timeline.css', __FILE__) );
		wp_enqueue_style('veriteco_css');
	}
	add_action( 'init', 'wpvt_post_type_init' );

	/* Metaboxes for Timeline Post Type */
	function wpvt_meta_boxes() {
		add_meta_box( 'timeline-meta', 'Timeline Meta Data', 'wpvt_meta_boxes_inner', 'timeline' );
	}
	//add_action( 'add_meta_boxes', 'wpvt_meta_boxes' );

	/* Prints the box content */
	function wpvt_meta_boxes_inner() {
		global $post;
		wp_nonce_field( plugin_basename( __FILE__ ), 'wpvt_noncename' ); // Use nonce for verification

		$meta = get_post_meta($post->ID);
		?>
		<div class="wpvt-metabox">
			<div class="wpvt-metabox-item">
				<label for="wpvt_start_date">Start Date:</label>
				<input type="text" id="wpvt_start_date" name="wpvtmeta[wpvt_start_date]" class="datepicker" value="<?php echo $meta['wpvt_start_date'][0]; ?>" />
			</div>
			<div class="wpvt-metabox-item">
				<label for="wpvt_end_date">End Date:</label>
				<input type="text" id="wpvt_end_date" name="wpvtmeta[wpvt_end_date]" class="datepicker" value="<?php echo $meta['wpvt_end_date'][0]; ?>" />
			</div>
			<div class="wpvt-metabox-item">
				<label for="wpvt_video">Video Embed:</label>
				<input type="text" id="wpvt_video" class="longinput" name="wpvtmeta[wpvt_video]" value="<?php echo $meta['wpvt_video'][0]; ?>" />
			</div>
			<div class="wpvt-metabox-item">
				<label for="wpvt_video_caption">Video Caption:</label>
				<input type="text" id="wpvt_video_caption" class="longinput" name="wpvtmeta[wpvt_video_caption]" value="<?php echo $meta['wpvt_video_caption'][0]; ?>" />
			</div>

			<input type="submit" class="button" name="wpvt_meta_submit" value="Save Timeline Data" />
		</div>
		<?php
	}


	/* Save Meta Data */
	function wpvt_save_wpvt_meta($post_id, $post) {
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['wpvt_noncename'], plugin_basename(__FILE__) )) {
			return $post->ID;
		}
		// Is the user allowed to edit the post or page?
		if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID;
		// OK, we're authenticated: we need to find and save the data
		// We'll put it into an array to make it easier to loop though.
		// Serialize and save.
		$wpvt_meta = $_POST['wpvtmeta'];

		// Add values of $events_meta as custom fields
		foreach ($wpvt_meta as $key => $value) { // Cycle through the $events_meta array!
			if( $post->post_type == 'revision' ) return; // Don't store custom data twice
			if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // If the custom field doesn't have a value
				add_post_meta($post->ID, $key, $value);
			}
			if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
		}
	}
	add_action('save_post', 'wpvt_save_wpvt_meta', 1, 2); // save the custom fields


	/* Save JSON file */
	function wpvt_update_json( $post_id ) {
		global $post;
		$post = get_post( $post_id );
		if($post->post_type == 'timeline') {
			$options = get_option('wpvt_options');

			$string = '
				{
					"timeline":
					{
						"headline":"' . $options['headline'] . '",
						"type":"' . $options['type'] . '",
						"text":"' . $options['text'] . '",
						"date": [
			';

			// TODO: APPEND DATE ENTRIES
			$args = array(
				'post_type' => 'timeline',
				'nopaging'  => true,
			);

			$loop = new WP_Query( $args );

			while ( $loop->have_posts() ) :
				$loop->the_post();
				$entry = new wpvtEntry( $post );

				$string .= $entry->toJSON();

				if($loop->current_post < $loop->post_count - 1) {
					$string .= ',';
				}

				wp_reset_postdata();
			endwhile;

			$string .= '
					]
					}
				}
			';

			$jsonFile = plugin_dir_path( __FILE__ ) . "/timeline.json";
			file_put_contents($jsonFile, $string);
		}
	}
	add_action('save_post', 'wpvt_update_json');


	/* Shortcodes */
	function wpvt_sc_func($atts) {
		global $post;
		$options = get_option('wpvt_options');
		$start_at_end = ($options['start_at_end'] == 1) ? 'true' : 'false';
		$hash_bookmark = ($options['hash_bookmark'] == 1) ? 'true' : 'false';

		// NOW I JUST NEED TO FETCH ALL THE POSTS, ARRANGE THE INFO INTO JSON THEN PRINT THE JAVASCRIPT CALL.
		// MAYBE GO WITH THE OPTION OF WRITING INTO A SEPARATE JSON FILE SO WE DON'T QUERY EVERY TIME.

		echo '
			<div id="timeline-embed"></div>
			<script type="text/javascript">
		    var timeline_config = {
				width: "'.$options['width'].'",
				height: "'.$options['height'].'",
				source: "'.plugins_url( 'timeline.json', __FILE__ ).'",
				start_at_end: '.$start_at_end.',
				hash_bookmark: '.$hash_bookmark.',
				css: "'.plugins_url( 'css/themes/font/'.$options['fonts'].'.css', __FILE__ ).'"	//OPTIONAL
			}
			</script>
			<script type="text/javascript" src="' . plugins_url( 'js/timeline-embed.js', __FILE__ ).'"></script>
		';

	}
	add_shortcode('WPVT', 'wpvt_sc_func');


?>
