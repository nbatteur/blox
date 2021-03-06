<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the slideshow content section within the content tab and loads in all available options
 *
 * @since 	1.0.0
 *
 * @package	Blox
 * @author 	Nick Diego
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Blox_Content_Slideshow {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;


    /**
     * Path to the file.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $file = __FILE__;


    /**
     * Holds the base class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $base;


    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the base class object.
        $this->base = Blox_Main::get_instance();

		add_filter( 'blox_content_type', array( $this, 'add_slideshow_content' ), 16 );
		add_action( 'blox_get_content_slideshow', array( $this, 'get_slideshow_content' ), 10, 4 );
		add_filter( 'blox_save_content_slideshow', array( $this, 'save_slideshow_content' ), 10, 3 );
		add_action( 'blox_print_content_slideshow', array( $this, 'print_slideshow_content' ), 10, 4 );

		// Add the slideshow modal to the admin page
        add_action( 'blox_metabox_modals', array( $this, 'add_slideshow_modal' ), 10, 1 );

    	// Add required slideshow scripts to the front-end
    	add_action( 'blox_frontend_slideshow_scripts_styles', array( $this, 'slideshow_scripts_styles' ) );
    }


	/**
	 * Add required slideshow scripts to the front-end
     *
     * @since 1.0.0
     */
	public function slideshow_scripts_styles() {

		// Load flexslider js
        wp_enqueue_script( $this->base->plugin_slug . '-flexslider-scripts', plugins_url( 'assets/plugins/flexslider/jquery.flexslider-min.js', $this->base->file ), array( 'jquery' ), $this->base->version );

		// Load base flexslider styles
        wp_register_style( $this->base->plugin_slug . '-flexslider-styles', plugins_url( 'assets/plugins/flexslider/flexslider.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-flexslider-styles' );

        // Load slick js
        wp_enqueue_script( $this->base->plugin_slug . '-slick-scripts', plugins_url( 'assets/plugins/slick/slick.min.js', $this->base->file ), array( 'jquery' ), $this->base->version );

        // Load base slick styles
        wp_register_style( $this->base->plugin_slug . '-slick-styles', plugins_url( 'assets/plugins/slick/slick.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-slick-styles' );

        // Load default slick theme
        wp_register_style( $this->base->plugin_slug . '-slick-default-theme', plugins_url( 'assets/plugins/slick/slick-theme.css', $this->base->file ), array(), $this->base->version );
        wp_enqueue_style( $this->base->plugin_slug . '-slick-default-theme' );
	}


	/**
	 * Enable the "slideshow" content option in the plugin
     *
     * @since 1.0.0
     *
     * @param array $content_types  An array of the content types available
     */
	public function add_slideshow_content( $content_types ) {
		$content_types['slideshow'] = __( 'Slideshow', 'blox' );
		return $content_types;
	}


	/**
	 * Prints all of the image ralated settings fields
     *
     * @since 1.0.0
     *
     * @param int $id             The block id
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param bool $global        The block state
     */
	public function get_slideshow_content( $id, $name_prefix, $get_prefix, $global ) {
		?>

		<!-- Slideshow Settings -->
		<table class="form-table blox-content-slideshow blox-hidden">
			<tbody>
				<tr class="blox-slideshow-type-container">
					<th scope="row"><?php _e( 'Slideshow Type', 'blox' ); ?></th>
					<td>
						<select name="<?php echo $name_prefix; ?>[slideshow][slideshow_type]" class="blox-slideshow-type">
							<?php foreach ( $this->get_slideshow_types() as $type => $title ) { ?>
								<option value="<?php echo $type; ?>" <?php echo ! empty( $get_prefix['slideshow']['slideshow_type'] ) ? selected( $get_prefix['slideshow']['slideshow_type'], $type ) : ''; ?>><?php echo $title; ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<tr class="blox-slideshow-option blox-content-slideshow-builtin">
					<th scope="row"><?php _e( 'Builtin Slides' ); ?></th>
					<td>
						<input type="submit" class="button button-primary" name="blox_slideshow_upload_button" id="blox_slideshow_upload_button" value="<?php _e( 'Select Image(s)'); ?>" onclick="blox_builtinSlideshowUpload.uploader('<?php echo $name_prefix; ?>'); return false;" />

						<ul class="blox-slides-container">

						<?php if ( ! empty( $get_prefix['slideshow']['builtin']['slides'] ) ) { ?>

							<?php foreach ( $get_prefix['slideshow']['builtin']['slides'] as $key => $slides ) {

                                // Set the disabled flag if needed
                                $disabled = ! empty( $slides['visibility']['disable'] ) ? 'disabled' : '';


                                /* <input type="text" class="slide-image-caption blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][caption]" value="<?php echo isset( $slides['image']['caption'] ) ? esc_attr( $slides['image']['caption'] ) : ''; ?>" />*/

                                ?>
                                <li id="<?php echo $key; ?>" class="blox-slideshow-item <?php echo $disabled; ?>" >
									<div class="blox-slide-container">
										<img class="slide-image-thumbnail" src="<?php echo isset( $slides['image']['id'] ) ? wp_get_attachment_thumb_url( esc_attr( $slides['image']['id'] ) ) : ''; ?>" alt="<?php echo esc_attr( $slides['image']['alt'] ); ?>" />
									</div>
									<input type="text" class="slide-type blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][slide_type]" value="image" /> <!-- possibly more slide types in the future -->
                                    <input type="checkbox" class="slide-visibility-disable blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][visibility][disable]" value="1" <?php ! empty( $slides['visibility']['disable'] ) ? checked( $slides['visibility']['disable'] ) : ''; ?> />

									<input type="text" class="slide-image-id blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][id]" value="<?php echo isset( $slides['image']['id'] ) ? esc_attr( $slides['image']['id'] ) : ''; ?>" />
									<input type="text" class="slide-image-url blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][url]" value="<?php echo esc_attr( $slides['image']['url'] ); ?>" />
									<input type="text" class="slide-image-title blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][title]" value="<?php echo isset( $slides['image']['title'] ) ? esc_attr( $slides['image']['title'] ) : ''; ?>" />
									<input type="text" class="slide-image-alt blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][alt]" value="<?php echo isset( $slides['image']['alt'] ) ? esc_attr( $slides['image']['alt'] ) : ''; ?>" />
                                    <input type="text" class="slide-image-size blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][size]" value="<?php echo isset( $slides['image']['size'] ) ? esc_attr( $slides['image']['size'] ) : ''; ?>" />
                                    <input type="checkbox" class="slide-image-link-enable blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][link][enable]" value="1" <?php ! empty( $slides['image']['link']['enable'] ) ? checked( $slides['image']['link']['enable'] ) : ''; ?> />
									<input type="text" class="slide-image-link-url blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][link][url]" value="<?php echo ! empty( $slides['image']['link']['url'] ) ? esc_attr( $slides['image']['link']['url'] ) : 'http://'; ?>" />
									<input type="text" class="slide-image-link-title blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][link][title]" value="<?php echo ! empty( $slides['image']['link']['title'] ) ? esc_attr( $slides['image']['link']['title'] ) : ''; ?>" />
									<input type="checkbox" class="slide-image-link-target blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][link][target]" value="1" <?php ! empty( $slides['image']['link']['target'] ) ? checked( $slides['image']['link']['target'] ) : ''; ?> />

                                    <textarea class="slide-image-caption blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][caption]" ><?php echo isset( $slides['image']['caption'] ) ? esc_attr( $slides['image']['caption'] ) : ''; ?></textarea>

									<input type="text" class="slide-image-classes blox-force-hidden" name="<?php echo $name_prefix; ?>[slideshow][builtin][slides][<?php echo $key; ?>][image][classes]" value="<?php echo ! empty( $slides['image']['classes'] ) ? esc_attr( $slides['image']['classes'] ) : ''; ?>" />

									<div class="blox-slide-tools-container">
										<a class="blox-slide-edit dashicons" href="#blox_slide_details" title="<?php _e( 'Edit Slide', 'blox' );?>"></a>
                                        <a class="blox-slide-visibility dashicons" href="#" title="<?php _e( 'Toggle Slide Visibility', 'blox' );?>"></a>
                                        <a class="blox-slide-delete dashicons right" href="#" title="<?php _e( 'Delete Slide', 'blox' );?>"></a>
                                        <a class="blox-slide-copy dashicons right" href="#" title="<?php _e( 'Copy Slide', 'blox' );?>" data-name-prefix="<?php echo $name_prefix; ?>"></a>
									</div>
								</li>
							<?php } ?>

							<?php } else { ?>
								<li class="blox-filler">
									<div class="blox-filler-container"></div>
									<div class="blox-filler-tools">
										<span class="edit dashicons"></span>
                                        <span class="visibility dashicons"></span>
                                        <span class="delete dashicons right"></span>
                                        <span class="copy dashicons right"></span>
									</div>
								</li>
							<?php } ?>
						</ul>

					</td>
				</tr>
                <?php

                $bs_prefix = $get_prefix['slideshow']['builtin']['settings'];

                $animation      = $this->get_values( $bs_prefix, 'animation', 'builtin_slideshow_animation' );

                $slideshowSpeed = $this->get_values( $bs_prefix, 'slideshowSpeed', 'builtin_slideshow_slideshowSpeed' );
                $animationSpeed = $this->get_values( $bs_prefix, 'animationSpeed', 'builtin_slideshow_animationSpeed' );

                $slideshow      = $this->get_values( $bs_prefix, 'slideshow', 'builtin_slideshow_slideshow' );
                $animationLoop  = $this->get_values( $bs_prefix, 'animationLoop', 'builtin_slideshow_animationLoop' );
                $pauseOnHover   = $this->get_values( $bs_prefix, 'pauseOnHover', 'builtin_slideshow_pauseOnHover' );
                $smoothHeight   = $this->get_values( $bs_prefix, 'smoothHeight', 'builtin_slideshow_smoothHeight' );
                $directionNav   = $this->get_values( $bs_prefix, 'directionNav', 'builtin_slideshow_directionNav' );
                $controlNav     = $this->get_values( $bs_prefix, 'controlNav', 'builtin_slideshow_controlNav' );
                $caption        = $this->get_values( $bs_prefix, 'caption', 'builtin_slideshow_caption' );

                $background_images = $this->get_values( $bs_prefix, 'background_images', 'builtin_slideshow_background_images' );
                ?>
				<tr class="blox-slideshow-option blox-content-slideshow-builtin">
					<th scope="row"><?php _e( 'Control Settings' ); ?></th>
					<td>
						<div class="blox-standard-settings">
							<select name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][animation]" id="blox_builtin_settings_animation">
								<option value="slide" <?php echo selected( $animation, 'slide' ); ?> ><?php _e( 'Slide', 'blox' ); ?></option>
								<option value="fade" <?php echo selected( $animation, 'fade' ); ?> ><?php _e( 'Fade', 'blox' ); ?></option>
							</select>
							<label for="blox_builtin_settings_animation"><?php _e( 'Slideshow Animation', 'blox' ); ?></label><br>
							<input type="text" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][slideshowSpeed]" id="blox_builtin_settings_slideshowSpeed" class="blox-small-text" value="<?php echo esc_attr( $slideshowSpeed ); ?>" />
							<label for="blox_builtin_settings_slideshowSpeed"><?php _e( 'Slideshow Speed (milliseconds)', 'blox' ); ?></label><br>
							<input type="text" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][animationSpeed]" id="blox_builtin_settings_animationSpeed" class="blox-small-text" value="<?php echo esc_attr( $animationSpeed ); ?>" />
							<label for="blox_builtin_settings_animationSpeed"><?php _e( 'Animation Speed (milliseconds)', 'blox' ); ?></label>
						</div>
						<div class="blox-advanced-settings">
							<label><input type="checkbox" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][slideshow]" id="blox_builtin_settings_slideshow" value="1" <?php checked( $slideshow ); ?> /> <?php _e( 'Start Slideshow Automatically', 'blox' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][animationLoop]" id="blox_builtin_settings_animationLoop" value="1" <?php checked( $animationLoop ); ?> /> <?php _e( 'Loop Slideshow', 'blox' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][pauseOnHover]" id="blox_builtin_settings_pauseOnHover" value="1" <?php checked( $pauseOnHover ); ?> /> <?php _e( 'Enable Pause On Hover', 'blox' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][smoothHeight]" id="blox_builtin_settings_smoothHeight" value="1" <?php checked( $smoothHeight ); ?> /> <?php _e( 'Enable Slideshow Height Resizing', 'blox' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][directionNav]" id="blox_builtin_settings_directionNav" value="1" <?php checked( $directionNav ); ?> /> <?php _e( 'Disable Directional Navigation (i.e. arrows)', 'blox' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][controlNav]" id="blox_builtin_settings_controlNav" value="1" <?php checked( $controlNav ); ?> /> <?php _e( 'Disable Control Navigation (i.e. dots)', 'blox' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][caption]" id="blox_builtin_settings_caption" value="1" <?php checked( $caption ); ?> /> <?php _e( 'Disable Captions ', 'blox' ); ?></label><br>
						</div>
					</td>
				</tr>
                <tr class="blox-slideshow-option blox-content-slideshow-builtin">
					<th scope="row"><?php _e( 'Display Settings' ); ?></th>
					<td>
						<label>
                            <input type="checkbox" name="<?php echo $name_prefix; ?>[slideshow][builtin][settings][background_images]" id="blox_builtin_settings_background_images" value="1" <?php checked( $background_images ); ?> />
                            <?php _e( 'Set images as background images', 'blox' ); ?>
                        </label>
                        <span class="blox-help-text-icon">
                            <a href="#" class="dashicons dashicons-editor-help" onclick="helpIcon.toggleHelp(this);return false;"></a>
                        </span>
                        <div class="blox-help-text top">
                            <?php echo sprintf( __( 'When this setting is enabled, images are displayed as a background images in each slide. Additional custom CSS may be required to attain your desired effect. For additional information on how effectively to use this setting, view the builtin slideshow %sdocumentation%s.', 'blox' ), '<a href="https://www.bloxwp.com/documentation/slideshow/" target="_blank">', '</a>' ); ?>
                        </div>
                    </td>
				</tr>

				<?php
				// If Soliloquy is active, add the pertinant settings
				if ( is_plugin_active( 'soliloquy/soliloquy.php' ) ) {
					$this->get_slideshow_soliloquy( $name_prefix, $get_prefix );
				}

				// If Revolution is active, add the pertinant settings
				if ( is_plugin_active( 'revslider/revslider.php' ) ) {
					$this->get_slideshow_revolution( $name_prefix, $get_prefix );
				}

				// Load settings for any additional slideshows we might want to add
				do_action( 'blox_additional_slideshow_options', $name_prefix, $get_prefix );

				?>

				<tr class="blox-slideshow-option blox-content-slideshow-shortcode blox-hidden">
					<th scope="row"><?php _e( 'Slideshow Shortcode', 'blox' ); ?></th>
					<td>
						<input type="text" class="blox-full-text" name="<?php echo $name_prefix; ?>[slideshow][shortcode]" value="<?php echo ! empty( $get_prefix['slideshow']['shortcode'] ) ? esc_attr( $get_prefix['slideshow']['shortcode'] ) : ''; ?>" />
						<div class="blox-description">
							<?php _e( 'This serves as an alternate method for adding a slideshow to a content block. If the slideshow plugin you are using includes a shortcode, simply paste it above.', 'blox' ); ?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}


    public function get_values( $prefix, $setting, $default, $type = null ) {

        if ( ! empty( $prefix ) ) {
            $value = ! empty( $prefix[$setting] ) ? $prefix[$setting] : '';
            return $value;
        } else {
            return blox_get_option( $default, '' );
        }
    }

	/**
	 * Saves all of the slideshow ralated settings
     *
     * @since 1.0.0
     *
     * @param string $name_prefix The prefix for saving each setting (this brings ...['slideshow'] with it)
     * @param int $id             The block id
     * @param bool $global        The block state
     *
     * @return array $settings    Return an array of updated slideshow settings
     */
	public function save_slideshow_content( $name_prefix, $id, $global ) {

		$settings = array();

		$settings['slideshow_type']	= esc_attr( $name_prefix['slideshow_type'] );

		// Save the builtin slides
		if ( isset( $name_prefix['builtin']['slides'] ) ){
			foreach ( $name_prefix['builtin']['slides'] as $key => $slides ) {

				// Only slide type currently (v1.0.0) is "image"
				$settings['builtin']['slides'][$key]['slide_type'] 				= 'image';

                $settings['builtin']['slides'][$key]['visibility']['disable'] 	= isset( $name_prefix['builtin']['slides'][$key]['visibility']['disable'] ) ? 1 : 0;

				$settings['builtin']['slides'][$key]['image']['id'] 			= trim( strip_tags( $name_prefix['builtin']['slides'][$key]['image']['id'] ) );
				$settings['builtin']['slides'][$key]['image']['url']    		= esc_url( $name_prefix['builtin']['slides'][$key]['image']['url'] );
				$settings['builtin']['slides'][$key]['image']['title']    		= trim( strip_tags( $name_prefix['builtin']['slides'][$key]['image']['title'] ) );
				$settings['builtin']['slides'][$key]['image']['alt'] 	   		= trim( strip_tags( $name_prefix['builtin']['slides'][$key]['image']['alt'] ) );
                $settings['builtin']['slides'][$key]['image']['size'] 	   		= ! empty( $name_prefix['builtin']['slides'][$key]['image']['size'] ) ? esc_attr( $name_prefix['builtin']['slides'][$key]['image']['size'] ) : 'full'; // If no size is set, default to full

				$settings['builtin']['slides'][$key]['image']['link']['enable']	= isset( $name_prefix['builtin']['slides'][$key]['image']['link']['enable'] ) ? 1 : 0;
				$settings['builtin']['slides'][$key]['image']['link']['url']	= isset( $name_prefix['builtin']['slides'][$key]['image']['link']['url'] ) ? ( $name_prefix['builtin']['slides'][$key]['image']['link']['url'] == 'http://' ? '' : esc_url( $name_prefix['builtin']['slides'][$key]['image']['link']['url'] ) ) : '';
				$settings['builtin']['slides'][$key]['image']['link']['title']	= isset( $name_prefix['builtin']['slides'][$key]['image']['link']['title'] ) ? trim( strip_tags( $name_prefix['builtin']['slides'][$key]['image']['link']['title'] ) ) : '';
				$settings['builtin']['slides'][$key]['image']['link']['target']	= isset( $name_prefix['builtin']['slides'][$key]['image']['link']['target'] ) ? 1 : 0;

                $settings['builtin']['slides'][$key]['image']['caption']  		= wp_kses_post( $name_prefix['builtin']['slides'][$key]['image']['caption'] );
                $settings['builtin']['slides'][$key]['image']['classes'] 	 	= trim( strip_tags( $name_prefix['builtin']['slides'][$key]['image']['classes'] ) );
			}
		} else {
			$settings['builtin']['slides'] = '';
		}

		// Save the control settings
		$settings['builtin']['settings']['animation']      	= esc_attr( $name_prefix['builtin']['settings']['animation'] );
		$settings['builtin']['settings']['slideshowSpeed'] 	= absint( $name_prefix['builtin']['settings']['slideshowSpeed'] );
		$settings['builtin']['settings']['animationSpeed'] 	= absint( $name_prefix['builtin']['settings']['animationSpeed'] );
		$settings['builtin']['settings']['slideshow'] 		= isset( $name_prefix['builtin']['settings']['slideshow'] ) ? 1 : 0;
		$settings['builtin']['settings']['animationLoop'] 	= isset( $name_prefix['builtin']['settings']['animationLoop'] ) ? 1 : 0;
		$settings['builtin']['settings']['pauseOnHover'] 	= isset( $name_prefix['builtin']['settings']['pauseOnHover'] ) ? 1 : 0;
		$settings['builtin']['settings']['smoothHeight'] 	= isset( $name_prefix['builtin']['settings']['smoothHeight'] ) ? 1 : 0;
		$settings['builtin']['settings']['directionNav'] 	= isset( $name_prefix['builtin']['settings']['directionNav'] ) ? 1 : 0;
		$settings['builtin']['settings']['controlNav']		= isset( $name_prefix['builtin']['settings']['controlNav'] ) ? 1 : 0;
		$settings['builtin']['settings']['caption']  		= isset( $name_prefix['builtin']['settings']['caption'] ) ? 1 : 0;

        // Save the display settings
        $settings['builtin']['settings']['background_images']  	= isset( $name_prefix['builtin']['settings']['background_images'] ) ? 1 : 0;


		// Save all of the additional slideshow option ids (i.e. Soliloquy, Revolution Slider, Meta Slider, etc.)
		foreach( $this->get_slideshow_types() as $type => $title ){
			if ( $type != 'builtin' && $type != 'shortcode' ) {
				$settings[$type]['id'] = trim( strip_tags( $name_prefix[$type]['id'] ) );
			}
		}

		// Save slideshow shortcode. Ensure that the string begins with [ and ends with ]
		if ( preg_match( "/(^[\[]).*([\]]$)/", $name_prefix['shortcode'] ) == 1 ){
			$settings['shortcode'] = $name_prefix['shortcode'];
		}

		return $settings;
	}


	/**
	 * Adds the builtin slideshow modal to the page
	 *
	 * @since 1.0.0
	 *
	 * @param bool $global The block state
     */
	public function add_slideshow_modal( $global ) {
		?>
		<!--Slideshow Image Settings Modal-->
		<div id="blox_slide_details" class='blox-hidden blox-modal prev-next'>

			<!-- Header -->
			<div class="blox-modal-titlebar">
				<span class="blox-modal-title"><?php _e( 'Edit Slide', 'blox' ); ?></span>
                <button type="button" class="blox-modal-prev">
                    <span class="blox-modal-icon">
                        <span class="screen-reader-text"><?php _e( 'Previous', 'blox' ); ?></span>
                    </span>
                </button>
                <button type="button" class="blox-modal-next">
                    <span class="blox-modal-icon">
                        <span class="screen-reader-text"><?php _e( 'Next', 'blox' ); ?></span>
                    </span>
                </button>
				<button type="button" class="blox-modal-close">
					<span class="blox-modal-icon">
                        <span class="screen-reader-text"><?php _e( 'Close', 'blox' ); ?></span>
                    </span>
				</button>
			</div>

			<!-- Body -->
            <div class="modal-slide-image-details">

                <input type="text" class="modal-slide-id blox-force-hidden" value="" />
                <input type="text" class="modal-slide-image-id blox-force-hidden" value="" />
                <input type="text" class="modal-slide-image-url blox-force-hidden" value="" />
                <input type="text" class="modal-slide-image-thumbnail blox-force-hidden" value="" />

                <div class="modal-slide-image-view">
                    <img class="modal-slide-image-preview" src="" />
                    <a class="button" name="blox_upload_button" id="blox_upload_button"  onclick="blox_slideshow_change_image.uploader(); return false;"><?php _e( 'Select New Image', 'blox' );?></a>
                </div>

                <div class="modal-slide-image-settings">

                    <span class="name"><?php _e( 'Image Settings', 'blox' ); ?></span>
                    <div class="blox-modal-subsettings">
                        <label class="blox-modal-subsetting">
                            <span><?php _e( 'Title', 'blox' ); ?></span>
                            <div>
                                <input type="text" class="modal-slide-image-title" value="" />
                            </div>
                        </label>
                        <label class="blox-modal-subsetting">
                            <span><?php _e( 'Alt Text', 'blox' ); ?></span>
                            <div>
                                <input type="text" class="modal-slide-image-alt" value="" />
                            </div>
                        </label>
                        <label class="blox-modal-subsetting">
                            <span><?php _e( 'Size', 'blox' ); ?></span>
                            <div>
            					<select class="modal-slide-image-size">
            						<?php foreach ( (array) $this->get_image_sizes() as $i => $size ) {

            							// Remove the new Custom option added in WP 4.4 for now. Could cause confusion...
            							if ( $size['value'] != 'custom' ) {
            							?>
            							    <option value="<?php echo $size['value']; ?>"><?php echo $size['name']; ?></option>
            							<?php
            							}
            						} ?>
            					</select>
                                <div class="blox-description">
                                    <?php _e( 'Note the selected image size is not reflected in the preview.', 'blox' ); ?>
                                </div>
                            </div>
                        </label>
                    </div>

                    <span class="name"><?php _e( 'Image Link', 'blox' ); ?></span>
					<label class="blox-image-link-enable">
						<input type="checkbox" class="modal-slide-image-link-enable" value="1" />
						<?php _e( 'Check to enable', 'blox' ); ?>
					</label>
					<div class="blox-modal-subsettings image-link">
						<label class="blox-modal-subsetting">
							<span><?php _e( 'URL', 'blox' ); ?></span>
							<div><input type="text" class="modal-slide-image-link-url" value="" /></div>
						</label>
						<label class="blox-modal-subsetting">
							<span><?php _e( 'Title', 'blox' ); ?></span>
							<div><input type="text" class="modal-slide-image-link-title" value="" /></div>
						</label>
						<label>
							<input type="checkbox" class="modal-slide-image-link-target" value="1" />
							<?php _e( 'Open link in new window/tab', 'blox' ); ?>
						</label>
					</div>

                    <span class="name"><?php _e( 'Slide Caption', 'blox' ); ?></span>
					<textarea class="modal-slide-image-caption blox-textarea-code" type="text" rows="3" ></textarea>
					<div class="blox-description">
						<?php _e( 'Only basic HTML is accepted.', 'blox' ); ?>
					</div>

                    <span class="name"><?php _e( 'Visibility', 'blox' ); ?></span>
                    <label class="blox-visibility-disable">
						<input type="checkbox" class="modal-slide-visibility-disable" value="1" />
						<?php _e( 'Check to disable this slide', 'blox' ); ?>
					</label>
                    <div class="blox-description">
						<?php _e( 'Disabled slides will not show up in the slideshow. Simply uncheck to begin displaying again.', 'blox' ); ?>
					</div>

                    <span class="name"><?php _e( 'Slide Classes', 'blox' ); ?></span>
					<input type="text" class="modal-slide-image-classes" value="" />
					<div class="blox-description">
						<?php _e( 'Enter a space separated list of custom CSS classes to add to this image slide.', 'blox' ); ?>
					</div>

                </div>

            </div>

            <!-- Footer -->
            <div class="blox-modal-footer">
                <div class="blox-modal-buttonpane">

                    <div class="modal-slide-apply-settings-container">
                        <span class="blox-modal-spinner"></span>
                        <div id="blox-slide-apply-settings-message" class="blox-message success">
                            <p class="main"><?php _e( 'Settings successfully applied. Publish/Update to fully save your changes.', 'blox' ); ?></p>
                            <span class="dashicons dashicons-yes mobile"></span>
                        </div>
                        <button id="blox-slide-apply-settings" type="button" class="button button-primary blox-modal-button">
                            <?php _e( 'Apply Settings', 'blox' ); ?>
                        </button>
                    </div>

                </div>
            </div>

		</div> <!-- end blox_slide_details -->
		<?php
	}


	/**
	 * Prints all of the slideshow content to the frontend
     *
     * @since 1.0.0
     *
     * @param int $id             The block id
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     * @param string $global      The block state
     */
	public function print_slideshow_content( $content_data, $block_id, $block, $global ) {

		// Allows us to use is_plugin_active on the frontend
		include_once ( ABSPATH . 'wp-admin/includes/plugin.php' );

		// Get the type of block we are working with
		$block_scope = $global ? 'global' : 'local';

		// Array of additional CSS classes
		$classes = array();

		if ( $content_data['slideshow']['slideshow_type'] == 'shortcode' ) {

			//Display generic slider using a slider shortcode
			if ( ! empty( $content_data['shortcode'] ) ) {
				?>
				<div class="blox-slideshow-container shortcode <?php echo implode( ' ', apply_filters( 'blox_content_slideshow_classes', $classes ) ); ?>">
					<div class="blox-slideshow-wrap">
						<?php echo do_shortcode( $content_data['slideshow']['shortcode'] ); ?>
					</div>
				</div>
				<?php
			}
		} else if ( $content_data['slideshow']['slideshow_type'] == 'builtin' ) {

            if ( apply_filters( 'blox_builtin_slideshow_use_slick', true ) ) {
                $this->print_slideshow_builtin_slick( $content_data, $block_id, $block_scope );
            } else {
                $this->print_slideshow_builtin_flexslider( $content_data, $block_id, $block_scope );
            }

		} else if ( is_plugin_active( 'soliloquy/soliloquy.php' ) && $content_data['slideshow']['slideshow_type'] == 'soliloquy' )  {

			// If Soliloquy is active, display the selected slideshow
			if ( ! empty( $content_data['slideshow']['soliloquy'] ) ) {
				$this->display_slideshow_soliloquy( $content_data['slideshow']['soliloquy']['id'] );
			}
		} else if ( is_plugin_active( 'revslider/revslider.php' ) && $content_data['slideshow']['slideshow_type'] == 'revolution' ) {

			// If Revolution is active, display the selected slideshow
			if ( ! empty( $content_data['slideshow']['revolution'] ) ) {
				$this->display_slideshow_revolution( $content_data['slideshow']['revolution']['id'] );
			}
		} else if ( is_plugin_active( 'ml-slider/ml-slider.php' ) && $content_data['slideshow']['slideshow_type'] == 'metaslider' ) {

			// If Meta Slider is active, display the selected slideshow
			if ( ! empty( $content_data['slideshow']['metaslider'] ) ) {
				$this->display_slideshow_metaslider( $content_data['slideshow']['metaslider']['id'] );
			}
		}
	}

    /**
     * Print the builtin slideshow's frontend markup using flexslider
     * ALERT: This function will be depracated in v1.5.0 which should provide enough time for people to transition to slick
     *
     * @since 1.0.0
     *
     * @param array $content_data Array of all block data
     * @param string $block_id    The block id
     * @param string $block_scope The scope of the block, either global or local
     */
    public function print_slideshow_builtin_flexslider( $content_data, $block_id, $block_scope ) {

        // Check to make sure slides have been added to the builtin slideshow
        if ( ! empty( $content_data['slideshow']['builtin'] ) ) { ?>
            <div class="blox-slideshow-container builtin flexslider <?php echo implode( ' ', apply_filters( 'blox_content_slideshow_classes', $classes ) ); ?>">
                <ul class="blox-slideshow-wrap slides">

                    <?php foreach ( $content_data['slideshow']['builtin']['slides'] as $key => $slides ) { ?>
                        <li id="<?php echo $key; ?>" class="blox-slideshow-item <?php echo $slides['slide_type']; ?> <?php echo $slides['image']['classes']; ?>" >
                            <?php
                                // Get our image link if enabled
                                if ( ! empty( $slides['image']['link']['url'] ) && $slides['image']['link']['enable'] ) {
                                    $target = $slides['image']['link']['target'] == 1 ? '_blank' : '_self';
                                    $link_start = '<a href="' . $slides['image']['link']['url'] . '" target="' . $target . '" title="' . $slides['image']['link']['title'] . '">';
                                    $link_end   = '</a>';
                                } else {
                                    $link_start = '';
                                    $link_end   = '';
                                }
                            ?>

                            <?php echo $link_start; ?>
                                <img src="<?php echo ! empty( $slides['image']['url'] ) ? esc_attr( $slides['image']['url'] ) : ''; ?>" alt="<?php echo ! empty( $slides['image']['alt'] ) ? esc_attr( $slides['image']['alt'] ) : ''; ?>" title="<?php echo ! empty( $slides['image']['title'] ) ? esc_attr( $slides['image']['title'] ) : ''; ?>" />
                            <?php echo $link_end; ?>
                            <?php if ( empty( $content_data['slideshow']['builtin']['settings']['caption'] ) && ! empty( $slides['image']['caption'] ) ) {  ?>
                                <div class="blox-caption-container">
                                    <div class="blox-caption-wrap">
                                        <?php echo wp_kses_post( $slides['image']['caption'] ); ?>
                                    </div>
                                </div>
                            <?php }  ?>

                        </li>
                    <?php } ?>
                </ul>
            </div>

            <script type="text/javascript">
                jQuery(document).ready(function($){

                    // Set all of our slider settings
                    $(window).load(function() {
                        $('#blox_<?php echo $block_scope . "_" . $block_id;?> .blox-slideshow-container.builtin').flexslider({
                            animation: "<?php echo ! empty( $content_data['slideshow']['builtin']['settings']['animation'] ) ? $content_data['slideshow']['builtin']['settings']['animation'] : 'fade'; ?>",
                            animationLoop: <?php echo ! empty( $content_data['slideshow']['builtin']['settings']['animationLoop'] ) ? 'true' : 'false'; ?>,
                            slideshow: <?php echo ! empty( $content_data['slideshow']['builtin']['settings']['slideshow'] ) ? 'true' : 'false'; ?>,
                            pauseOnHover: <?php echo ! empty( $content_data['slideshow']['builtin']['settings']['pauseOnHover'] ) ? 'true' : 'false'; ?>,
                            directionNav: <?php echo ! empty( $content_data['slideshow']['builtin']['settings']['directionNav'] ) ? 'false' : 'true'; ?>,
                            controlNav: <?php echo ! empty( $content_data['slideshow']['builtin']['settings']['controlNav'] ) ? 'false' : 'true'; ?>,
                            slideshowSpeed: <?php echo ! empty( $content_data['slideshow']['builtin']['settings']['slideshowSpeed'] ) ? esc_attr( $content_data['slideshow']['builtin']['settings']['slideshowSpeed'] ) : 7000; ?>,
                            animationSpeed: <?php echo ! empty( $content_data['slideshow']['builtin']['settings']['animationSpeed'] ) ? esc_attr( $content_data['slideshow']['builtin']['settings']['animationSpeed'] ) : 600; ?>,
                            smoothHeight: <?php echo ! empty( $content_data['slideshow']['builtin']['settings']['smoothHeight'] ) ? 'true' : 'false'; ?>,
                        });
                    });

                });
            </script>

        <?php } else { ?>
            <div class="media-error">
                <p><?php _e( 'You haven\'t added any slides to the slideshow!' ); ?></p>
            </div>
        <?php }
    }


    /**
     * Print the builtin slideshow's frontend markup using slick
     *
     * @since 1.3.0
     *
     * @param array $content_data Array of all block data
     * @param string $block_id    The block id
     * @param string $block_scope The scope of the block, either global or local
     */
    public function print_slideshow_builtin_slick( $content_data, $block_id, $block_scope ) {

        // Check to make sure slides have been added to the builtin slideshow
        if ( ! empty( $content_data['slideshow']['builtin'] ) && ! empty( $content_data['slideshow']['builtin']['slides'] ) ) {

            $html = '<div class="blox-slideshow-container builtin">';

            foreach ( $content_data['slideshow']['builtin']['slides'] as $key => $slides ) {

                // Are we using background images?
                $background_images  = ! empty( $content_data['slideshow']['builtin']['settings']['background_images'] ) ? true : false;

                // Setup the image
                $image_id    = esc_attr( $slides['image']['id'] );
                $image_size  = ! empty( $slides['image']['size'] ) ? esc_attr( $slides['image']['size'] ) : 'full';
                $image_alt   = ! empty( $slides['image']['alt'] ) ? esc_attr( $slides['image']['alt'] ) : '';
                $image_title = ! empty( $slides['image']['title'] ) ? esc_attr( $slides['image']['title'] ) : '';

                // Get the image
                if ( $background_images ) {

                    // We only need the src for background images
                    $image = wp_get_attachment_image_src( $image_id, $image_size. '' );
                    $image = $image[0];

                } else {
                    $image_atts = array (
                        'class' => '',
                        'title' => $image_title,
                        'alt'   => $image_alt,
                    );

                    // The 3rd param is used to determine is image should be an icon, and thus is not used
                    $image = wp_get_attachment_image( $image_id, $image_size, '', $image_atts );
                }

                // Setup the slide link if there is one
                $link_start = '';
                $link_end   = '';

                // Get our image link if enabled
                if ( ! empty( $slides['image']['link']['url'] ) && $slides['image']['link']['enable'] ) {
                    $target    = $slides['image']['link']['target'] == 1 ? '_blank' : '_self';
                    $link_start = '<a href="' . $slides['image']['link']['url'] . '" target="' . $target . '" title="' . $slides['image']['link']['title'] . '">';
                    $link_end   = '</a>';
                }

                // Setup the image caption
                $caption = '';

                if ( empty( $content_data['slideshow']['builtin']['settings']['caption'] ) && ! empty( $slides['image']['caption'] ) ) {
                    $caption .= '<div class="blox-caption-container">';
                    $caption .= '<div class="blox-caption-wrap">';
                    $caption .= wp_kses_post( $slides['image']['caption'] );
                    $caption .= '</div>';
                    $caption .= '</div>';
                }

                // Get visbility settings
                $disabled = ! empty( $slides['visibility']['disable'] ) ? esc_attr( $slides['visibility']['disable'] ) : 0;

                // Maybe show the slide
                if ( ! $disabled ) {

                    // Final markup
                    $html .= '<div id="' . $key . '" class="blox-slideshow-item ' . $slides['slide_type'] . ' ' . $slides['image']['classes'] . '">';

                    if ( $background_images ) {
                        $html .= '<div class="blox-image-background" style="background-image: url(' . $image . ')">';
                        $html .= $link_start . $link_end;
                        $html .= $caption;
                        $html .= '</div>';
                    } else {
                        $html .= $link_start . $image . $link_end;
                        $html .= $caption;
                    }

                    $html .= '</div>';
                }
            }

            $html .= '</div>';

            echo $html;

            $this->print_slideshow_js( $content_data, $block_id, $block_scope );

         } else {
            ?>
            <div class="media-error">
                <p><?php _e( 'You haven\'t added any slides to the slideshow!' ); ?></p>
            </div>
            <?php
        }
    }


    public function print_slideshow_js( $content_data, $block_id, $block_scope ) {

        $settings = $content_data['slideshow']['builtin']['settings'];

        $fade           = $content_data['slideshow']['builtin']['settings']['animation'] == 'fade' ? 'true' : 'false';
        $infinite       = ! empty( $settings['animationLoop'] ) ? 'true' : 'false';
        $autoplay       = ! empty( $settings['slideshow'] ) ? 'true' : 'false';
        $pauseOnHover   = ! empty( $settings['pauseOnHover'] ) ? 'true' : 'false';
        $arrows         = ! empty( $settings['directionNav'] ) ? 'false' : 'true';
        $dots           = ! empty( $settings['controlNav'] ) ? 'false' : 'true';
        $autoplaySpeed  = ! empty( $settings['slideshowSpeed'] ) ? esc_attr( $settings['slideshowSpeed'] ) : 7000;
        $speed          = ! empty( $settings['animationSpeed'] ) ? esc_attr( $settings['animationSpeed'] ) : 600;
        $adaptiveHeight = ! empty( $settings['smoothHeight'] ) ? 'true' : 'false';

        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $( '#blox_<?php echo $block_scope . "_" . $block_id;?> .blox-slideshow-container.builtin').slick({
                adaptiveHeight: <?php echo $adaptiveHeight;?>,
                autoplay: <?php echo $autoplay;?>,
                autoplaySpeed: <?php echo $autoplaySpeed;?>,
                arrows: <?php echo $arrows;?>,
                dots: <?php echo $dots;?>,
                fade: <?php echo $fade;?>,
                infinite: <?php echo $infinite;?>,
                pauseOnHover: <?php echo $pauseOnHover;?>,
                speed: <?php echo $speed;?>,
            });
        });
        </script>
        <?php
    }


	/**
     * Helper function that returns a list of all available slideshow types
     *
     * @since 1.0.0
     *
     * @return array $slideshow_types An array of available slideshow types
     */
	public function get_slideshow_types() {

		// Add our builtin option
		$slideshow_types['builtin'] =  __( 'Builtin Slideshow', 'blox' );

		// If the Soliloquy Slideshow plugin is active, add this option
		if ( is_plugin_active( 'soliloquy/soliloquy.php' ) ) {
			$slideshow_types['soliloquy'] =  __( 'Soliloquy Slider', 'blox' );
		}

		// If the Revolution Slideshow plugin is active, add this option
		if ( is_plugin_active( 'revslider/revslider.php' ) ) {
			$slideshow_types['revolution'] =  __( 'Revolution Slider', 'blox' );
		}

		// If the Meta Slider plugin is active, add this option
		if ( is_plugin_active( 'ml-slider/ml-slider.php' ) ) {
			$slideshow_types['metaslider'] =  __( 'Meta Slider', 'blox' );
		}

    	// Apply filter for any additional slideshow types that need to be added
    	$slideshow_types = apply_filters( 'blox_slideshow_type', $slideshow_types );

		// Finally add the shortcode option
		$slideshow_types['shortcode'] =  __( 'Shortcode', 'blox' );

		return $slideshow_types;
	}



	// SOLILOQUY HELPER FUNCTIONS

	/**
     * Helper function for generating a dropdown of all available Soliloquy sliders in admin area
     *
     * @since 1.0.0
     *
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     */
    public function get_slideshow_soliloquy( $name_prefix, $get_prefix ) {

		// Grab id and title of all available sliders
		$posts = get_posts( array( 'post_type' => 'soliloquy', 'posts_per_page' => -1, 'post_status' => 'publish' ) );

		foreach( $posts as $post ) {
			$soliliquysliders[] = array(
				'title' => $post->post_title,
				'id' => $post->ID
			);
		}

		// Display all available sliders by title in dropdown for selection
		?>
		<tr class="blox-slideshow-option blox-content-slideshow-soliloquy blox-hidden">
			<th scope="row"><label><strong><?php _e( 'Soliloquy Slider', 'genesis-custom-header' ); ?></strong></label></th>
			<td>
				<select name="<?php echo $name_prefix; ?>[slideshow][soliloquy][id]">
					<option value="none" <?php ! empty( $get_prefix['slideshow']['soliloquy']['id'] ) ? selected( $get_prefix['slideshow']['soliloquy']['id'], 'none' ) : ''; ?> ><?php _e( 'Display None', 'genesis-custom-header' ); ?></option>
					<?php foreach ( (array) $soliliquysliders as $soliliquyslider ) { ?>
						<option value="<?php echo esc_attr( $soliliquyslider['id'] ); ?>" <?php ! empty( $get_prefix['slideshow']['soliloquy']['id'] ) ? selected( $get_prefix['slideshow']['soliloquy']['id'], $soliliquyslider['id'] ) : ''; ?>><?php echo esc_html( $soliliquyslider['title'] ); ?></option>
					<?php } ?>
				</select>

				<?php if ( empty( $soliliquysliders ) ) { ?>
					<div class="blox-error"><?php _e( 'You have not created any Soliloquy sliders yet.', 'genesis-custom-header' ); ?></div>
				<?php } ?>
			</td>
		</tr>
		<?php
	}


	/**
     * Helper function for displaying the Soliloquy slider on the frontend
     *
     * @since 1.0.0
     *
     * @param string $slideshow_id The Soliloquy slideshow id
     */
	public function display_slideshow_soliloquy( $slideshow_id ) {

		if ( ! empty( $slideshow_id ) && $slideshow_id != 'none' ) {
			?>
			<div class="blox-slideshow-container soliloquy <?php echo implode( ' ', apply_filters( 'blox_content_slideshow_classes', $classes ) ); ?>">
				<div class="blox-slideshow-wrap">
					<?php echo do_shortcode( '[soliloquy id="' . $slideshow_id . '"]' ); ?>
				</div>
			</div>
			<?php
		}
	}



	// REVOLUTION SLIDER HELPER FUNCTIONS

	/**
     * Helper function for generating a dropdown of all available Revolution sliders in admin area
     *
     * @since 1.0.0
     *
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     */
	public function get_slideshow_revolution( $name_prefix, $get_prefix ) {

		// Grab id and title of all available sliders
		$revolutionsliders = array();

		if ( class_exists( 'RevSlider' ) ){
			$slider 	= new RevSlider();
			$arrSliders = $slider->getArrSliders();

			foreach( $arrSliders as $revSlider ) {
				$revolutionsliders[$revSlider->getAlias()] = $revSlider->getTitle();
			}
		}

		// Display all available sliders by title in dropdown for selection
		?>
		<tr class="blox-slideshow-option blox-content-slideshow-revolution blox-hidden">
			<th scope="row"><label><strong><?php _e( 'Revolution Slider', 'genesis-custom-header' ); ?></strong></label></th>
			<td>
				<select name="<?php echo $name_prefix; ?>[slideshow][revolution][id]">
					<option value="none" <?php ! empty( $get_prefix['slideshow']['revolution']['id'] ) ? selected( $get_prefix['slideshow']['revolution']['id'], 'none' ) : ''; ?> ><?php _e( 'Display None', 'blox' ); ?></option>
					<?php foreach ( $revolutionsliders as $revolutionslider => $title ) { ?>
						<option value="<?php echo esc_attr( $revolutionslider ); ?>" <?php ! empty( $get_prefix['slideshow']['revolution']['id'] ) ? selected( $get_prefix['slideshow']['revolution']['id'], $revolutionslider ) : ''; ?>><?php echo esc_html( $title ); ?></option>
					<?php } ?>
				</select>

				<?php if ( empty( $revolutionsliders ) ) { ?>
					<div class="blox-error"><?php _e( 'You have not created any Revolution sliders yet.', 'blox' ); ?></div>
				<?php } ?>
			</td>
		</tr>
		<?php
	}


	/**
     * Helper function for displaying the Revolution slider on the frontend
     *
     * @since 1.0.0
     *
     * @param string $slideshow_id The Revolution slideshow id
     */
	public function display_slideshow_revolution( $slideshow_id ) {

		if ( ! empty( $slideshow_id ) && $slideshow_id != 'none' ) {
			?>
			<div class="blox-slideshow-container revolution <?php echo implode( ' ', apply_filters( 'blox_content_slideshow_classes', $classes ) ); ?>">
				<div class="blox-slideshow-wrap">
					<?php putRevSlider( $slideshow_id ); ?>
				</div>
			</div>
			<?php
		}
	}



	// META SLIDER HELPER FUNCTIONS

	/**
     * Helper function for generating a dropdown of all available Meta sliders in admin area
     *
     * @since 1.0.0
     *
     * @param string $name_prefix The prefix for saving each setting
     * @param string $get_prefix  The prefix for retrieving each setting
     */
	public function get_slideshow_metaslider( $name_prefix, $get_prefix ) {

		// Grab id and title of all available sliders
		$posts = get_posts( array( 'post_type' => 'ml-slider', 'posts_per_page' => -1, 'post_status' => 'publish' ) );

		foreach( $posts as $post ) {
			$metasliders[] = array(
				'title' => $post->post_title,
				'id' => $post->ID
			);
		}

		// Display all available sliders by title in dropdown for selection
		?>
		<tr class="blox-slideshow-option blox-content-slideshow-metaslider blox-hidden">
			<th scope="row"><label><strong><?php _e( 'Meta Slider', 'genesis-custom-header' ); ?></strong></label></th>
			<td>
				<select name="<?php echo $name_prefix; ?>[slideshow][revolution][id]">
					<option value="none" <?php ! empty( $get_prefix['slideshow']['metaslider']['id'] ) ? selected( $get_prefix['slideshow']['metaslider']['id'], 'none' ) : ''; ?> ><?php _e( 'Display None', 'blox' ); ?></option>
					<?php foreach ( (array) $metasliders as $metaslider ) { ?>
						<option value="<?php echo esc_attr( $metaslider['id'] ); ?>" <?php ! empty( $get_prefix['slideshow']['metaslider']['id'] ) ? selected( $get_prefix['slideshow']['metaslider']['id'], $metaslider['id'] ) : ''; ?>><?php echo esc_html( $metaslider['title'] ); ?></option>
					<?php } ?>
				</select>

				<?php if ( empty( $metasliders ) ) { ?>
					<div class="blox-error"><?php _e( 'You have not created any Meta sliders yet.', 'blox' ); ?></div>
				<?php } ?>
			</td>
		</tr>
		<?php
	}


	/**
     * Helper function for displaying the Meta Slider on the frontend
     *
     * @since 1.0.0
     *
     * @param string $slideshow_id The Meta Slider id
     */
	public function display_slideshow_metaslider( $slideshow_id ) {

		if ( ! empty( $slideshow_id ) && $slideshow_id != 'none' ) {
			?>
			<div class="blox-slideshow-container metaslider <?php echo implode( ' ', apply_filters( 'blox_content_slideshow_classes', $classes ) ); ?>">
				<div class="blox-slideshow-wrap">
					<?php echo do_shortcode( '[metaslider id="' . $slideshow_id . '"]' ); ?>
				</div>
			</div>
			<?php
		}
	}


    /**
     * Helper method for retrieving image sizes.
     *
     * @since 1.0.0
     *
     * @return array Array of image size data.
     */
    public function get_image_sizes() {

        $instance = Blox_Common::get_instance();
        return $instance->get_image_sizes();
    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     *
     * @return object The class object.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Blox_Content_Slideshow ) ) {
            self::$instance = new Blox_Content_Slideshow();
        }

        return self::$instance;
    }
}

// Load the slideshow content class.
$blox_content_slideshow = Blox_Content_Slideshow::get_instance();
