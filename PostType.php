<?php
/**
 * Vanilla
 * 
 * Vanilla_PostType Class
 * Handles the creation of custom post types along with their
 * meta boxes and custom meta data.
 * 
 * @package Vanilla
 * @author brux <brux.romuar@gmail.com>
 */
final class Vanilla_PostType
{

	/**
	 * Contains WP's built-in post types.
	 * 
	 * @var array
	 */
	private static $wp_post_types = array('post', 'page', 'attachment', 'attachment', 'revision', 'nav_menu_item');

	/**
	 * Contains instances of this class for each post type.
	 *
	 * @var array
	 */
	private static $instances = array();
	
	/**
	 * Contains all the custom taxonomies.
	 *
	 * @var array
	 */
	private static $custom_taxonomies = array();
	
	/**
	 * The slug of this post type.
	 *
	 * @var string
	 */
	private $post_type;
	
	/**
	 * Custom post type args.
	 *
	 * @var array
	 */
	private $args = array();
	
	/**
	 * An array of this post type's taxonomy slugs.
	 *
	 * @var array
	 */
	private $taxonomies = array();

	/**
	 * Contains registered metadata.
	 * Used in doing the autosave meta magic :)
	 * 
	 * @var array
	 */
	private $metadata = array();

	/**
	 * Contains the post type's meta boxes.
	 * 
	 * @var array
	 */
	private $meta_boxes = array();
	
    /**
     * Returns an instance of this class for creating or modifying a post type.
     * 
     * @param string $label name of the post type in singular form
     * @param array $args optional post type arguments
     * @return Vanilla_PostType
     */
    public static function get($label, $args = array())
    {

        $plural = vanilla_pluralize($label);
        return self::create($label, $plural, $args);
        
    }

	/**
	 * Create a new custom post type.
     * It's basically the same as get() but with more options.
	 *
	 * @param string $singular name of your post type in singular form.
	 * @param string $plural optional. plural form of the name of your post type
	 * @param array $args optional. custom arguments. same as $args in register_post_type()
	 * @return Vanilla_PostType
	 */
	public static function create($singular, $plural = null, $args = array())
	{
		
        if ( isset($args['slug']) )
        {
            $slug = $args['slug'];
        }
        else
        {
            $slug = vanilla_slug($singular);
        }
		
		// Create a new instance of this class if one doesn't exist for this post type
		if ( ! isset(self::$instances[$slug]) )
		{
		
			// If we have an array $plural then it is the args, use $singular as the plural form
			if ( is_array($plural) )
			{
				$args = $plural;
				$plural = vanilla_pluralize($singular);
			}
			// If it is null then use the singular form
			elseif ( ! $plural )
			{
				$plural = vanilla_pluralize($singular);
			}
		
            $class = __CLASS__;
			self::$instances[$slug] = new $class($slug, $singular, $plural, $args);
			
		}
		
		return self::$instances[$slug];
		
	}
	
	/**
	 * Creates a new custom taxonomy. Returns the taxonomy's slug.
	 *
	 * @param string $singular name of your taxonomy in singular form.
	 * @param string $plural optional. plural form of the name of your taxonomy
	 * @param array $args optional. custom arguments. same as $args in register_taxonomy()
	 * @return string
	 */
	public function create_taxonomy($singular, $plural = null, $args = array())
	{
		
		$slug = vanilla_slug($singular);
		
		// Generate the taxonomy details if it doesn't exist yet.
		if ( ! isset(self::$custom_taxonomies[$slug]) )
		{
		
			// If we have an array $plural then those are args, use $singular as the plural form
			if ( is_array($plural) )
			{
				$args = $plural;
				$plural = vanilla_pluralize($singular);
			}
			// If it is null then use the singular form
			elseif ( ! $plural )
			{
				$plural = vanilla_pluralize($singular);
			}
			
			// Set the labels if it doesn't exist yet.
			if ( ! isset($args['label']) && ! isset($args['labels']) )
			{
				$args['label'] = $plural;
				$args['labels'] = array(
					'singular_name'					=> $singular,
					'search_items'					=> "Search $plural",
					'popular_items'					=> "Popular $plural",
					'all_items'						=> "All $plural",
					'parent_item'					=> "Parent $singular",
					'edit_item'						=> "Edit $singular",
					'update_item'					=> "Update $singular",
					'add_new_item'					=> "Add New $singular",
					'new_item_name'					=> "New $singular",
					'separate_items_with_commas'	=> "Separate $plural with commas",
					'add_or_remove_items'			=> "Add or remove $plural",
					'choose_from_most_used'			=> "Choose from most used $plural"
				);
			}
			
			self::$custom_taxonomies[$slug] = $args;
		
		}
		
		return $slug;
		
	}
	
	/**
	 * Registers function hooks.
	 *
	 * @return void
	 */
	public static function _setup()
	{
		
		$taxonomies = self::$custom_taxonomies;
		$post_types = self::$instances;
		
		// Register our custom taxonomies and post types
		add_action('init', array(__CLASS__, '_setup_types'));
		add_action('add_meta_boxes', array(__CLASS__, '_add_meta_boxes'));
		add_action('post_updated', array(__CLASS__, '_save_meta'));
        add_action('admin_enqueue_scripts', array(__CLASS__, '_scripts'));
        add_action('wp_ajax_rhap_get_attachments', array(__CLASS__, '_get_attachments_ajax'));
		
	}
	
	/**
	 * Runs on 'init'. Registers custom post types and taxonomies.
	 *
	 * @return void
	 */
	public static function _setup_types()
	{
		
        // Start timing
        if ( WP_DEBUG ) vanilla_mark('post_type_setup_start'); 

		// Register taxonomies
		foreach ( self::$custom_taxonomies as $tax_slug => $tax_args )
		{
			register_taxonomy($tax_slug, null, $tax_args);
		}
		
        // Do not register built-in post types
        $instances = self::$instances;
        foreach ( self::$wp_post_types as $wp_post_type )
        {
            unset($instances[$wp_post_type]);
        }

		// Register post types and link them to their taxonomies
		foreach ( $instances as $post_type_slug => $post_type )
		{

			register_post_type($post_type_slug, $post_type->args);
			
			foreach ( $post_type->taxonomies as $tax_slug )
			{
				register_taxonomy_for_object_type($tax_slug, $post_type->post_type);
			}
			
		}

        // End timing
        if ( WP_DEBUG ) vanilla_mark('post_type_setup_end');
		
	}

	/**
	 * Adds all meta boxes under each post type.
	 * Do not call directly. Use $post_type->add_meta_box() to 
	 * add meta boxes instead.
	 */
	public static function _add_meta_boxes()
	{

		foreach ( self::$instances as $post_type )
		{
			foreach ( $post_type->meta_boxes as $meta_box_id => $meta_box )
			{
                if ( isset($meta_box['callback']) )
                {
                    add_meta_box($meta_box_id, $meta_box['title'], $meta_box['callback'], $post_type->post_type, $meta_box['context'], 'default', $meta_box['callback_args']);
                }
                else
                {
				    add_meta_box($meta_box_id, $meta_box['title'], array(__CLASS__, '_create_meta_box'), $post_type->post_type, $meta_box['context'], 'default', array($meta_box, $post_type));
                }
			}
		}

	}

	/**
	 * Invoked as a callback for each auto meta box.
	 * Generates the HTML for each meta.
	 * Do not call directly.
	 * 
	 * @param  object $post     the post object
	 * @param  array $meta_box meta box args
	 */
	public static function _create_meta_box($post, $meta_box)
	{

        $textfield_like = array('email', 'number', 'text', 'textarea', 'url', 'date', 'time');

        // Loop through each field
		$fields = $meta_box['args'][0]['fields']; 
		foreach ( $fields as $field )
		{

            $placeholder = $maxlength = '';

            // Retrieve the value of our metadata
			$value = get_post_meta($post->ID, $field['id'], true);
            if ( empty($value) && isset($field['default']) ) $value = $field['default'];

            // and then pass it through a filter
            $value_filter = "vanilla_display_{$post->post_type}_meta";
			$value = apply_filters($value_filter, $value, $field['id'], $post);

            // Generate the ID
            $field_id = VANILLA_THEME_SLUG . '_' . $field['id'];

            // Print the container
            printf('<div class="%s_meta_field vanilla_meta_field" id="%s_container">', VANILLA_THEME_SLUG, $field_id);

            // If we have a textfield-like field then the label goes first and 
            // check if we have a placeholder & maxlength
            if ( in_array($field['type'], $textfield_like) )
            {

                if ( isset($field['placeholder']) ) $placeholder = ' placeholder="'. $field['placeholder'] .'"';

                if ( isset($field['maxlength']) ) $maxlength = ' maxlength="'. intval($field['maxlength']) .'"';

                printf('<p><label>%s: ', $field['label']);

            }

            // For numbers we have a size attribute
            if ( $field['type'] == 'number' )
            {
                $size = isset($field['size']) ? intval($field['size']) : 4;
            }

            // Generate the HTML for the field
			switch ( $field['type'] )
			{

                case 'date':
				case 'email':
                case 'text':
                case 'time':
                case 'url':
                    $width = ( $field['type'] == 'date' || $field['type'] == 'time' ) ? 95 : 100;
					printf('<br><input type="%s" name="%s" value="%s" id="%s" style="width: %d%%"%s%s></label></p>', $field['type'], $field_id, esc_attr($value), $field_id, $width, $maxlength, $placeholder);
					break;

                case 'number':
                    printf('<input type="%s" name="%s" value="%s" id="%s" size="%d"%s%s></label></p>', $field['type'], $field_id, esc_attr($value), $field_id, $size, $maxlength, $placeholder);
                    break;

                case 'checkbox':
                case 'radio':
                    printf('<p>%s:<br>', $field['label']);
                    $frags = array();
                    foreach ( $field['options'] as $label => $option_value )
                    {
                        $frags[] = sprintf('<label><input type="%s" name="%s" value="%s" id="%s" %s> %s</label>', $field['type'], $field_id, esc_attr($option_value), $field_id, checked($option_value, $value, false), $label);
                    }
                    echo implode("\n<br>\n", $frags);
                    echo '</p>';
                    break;

                case 'select':
                    printf('<p><label>%s:<br><select name="%s" id="%s">', $field['label'], $field_id, $field_id);
                    $frags = array();
                    foreach ( $field['options'] as $label => $option_value )
                    {
                        $frags[] = sprintf('<option value="%s" %s>%s</option>', $option_value, selected($option_value, $value, false), $label);
                    }
                    echo implode("\n<br>\n", $frags);
                    echo '</select></label></p>';
                    break;

                case 'textarea':
                    printf('<br><textarea name="%s" id="%s" rows="5" style="width: 100%%"%s>%s</textarea></label></p>', $field_id, $field_id, $placeholder, esc_html($value));
                    break;

                case 'toggle':
                    if ( ! empty($value) )
                        $checked = ' checked="checked"';
                    else
                        $checked = '';
                    printf('<p><label><input type="checkbox" name="%s" id="%s"%s> %s</label></p>', $field_id, $field_id, $checked, $field['label']);
                    break;

                case 'images':
                    
                    $attachments = get_posts(array(
                        'post_type'         => 'attachment',
                        'posts_per_page'    => -1,
                        'post_status'       => 'any',
                        'post_mime_type'    => 'image'
                    ));
                    $opts = '<option value="">Select an Image...</option>';
                    $attachs = array();
                    foreach ( $attachments as $attach ) {
                        $thumb = wp_get_attachment_image_src($attach->ID, 'thumbnail');
                        $opts .= sprintf('<option value="%s" data-thumb="%s"%s>%s</option>', $attach->ID, $thumb[0], selected($attach->ID, $value, false), esc_html(basename($attach->guid)));
                        $attachs[$attach->ID] = $thumb;
                    }
                    unset($attachments);
                    $value_img = '';
                    if ( $value != '' )
                        $value_img = sprintf('<img src="%s">', $attachs[$value][0]);
                    printf('<p><label>%s:<br><select name="%s" class="rhap-image-selector" data-value="%s">%s</select></label><br><span style="display:block;width:150px;height:150px;">%s</span></p>', $field['label'], $field_id, $value, $opts, $value_img);
                    break;

                case 'callback':
                default:
                	
                	// Be compatible with callback-based fields not using the "callback" arg
                	// but instead putting it in the "type" arg
                	$cb = $field['type'] == 'callback' ? $field['callback'] : $field['type'];

                    call_user_func($cb, array(
                        'id'    => $field['id'],
                        'label' => $field['label'],
                        'value' => $value
                    ));
                    break;

			}

            // Close container
            echo '</div>';

		}

        $nonce_action = sprintf("%s_%s_%s", VANILLA_THEME_SLUG, $post->post_type, $meta_box['args'][0]['slug']);
        $nonce_field = VANILLA_THEME_SLUG . '_' . $post->post_type . '_nonce';
        wp_nonce_field($nonce_action, $nonce_field);

	}

	/**
	 * Saves our post type's metadata field contents.
	 * 
	 * @param  integer $post_id the post ID
	 */
	public static function _save_meta($post_id)
	{

		// Skip autosaves
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

		// And quick-edits
		if ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) return;

        // Make sure we came from post.php and we are receiving a POST request
        $referer = explode('?', basename($_SERVER['HTTP_REFERER']));
        if ( ($referer[0] == 'post.php' || $referer[0] == 'post-new.php') && $_SERVER['REQUEST_METHOD'] == 'POST' )
        {

    		// Grab the post type object
    		$post_type = get_post_type($post_id);
    		if ( ! isset(self::$instances[$post_type]) ) return;
    		$post_type_obj = self::$instances[$post_type];

            // Loop through each of our meta boxes
    		foreach ( $post_type_obj->meta_boxes as $meta_box_id => $meta_box )
    		{

    			if ( ! empty($meta_box['save_handler']) )
    			{
    				call_user_func_array($meta_box['save_handler'], array($post_id, $meta_box_id, $meta_box));
    			}
    			else
    			{

	                do_action('vanilla_save_meta_box', $post_id, $meta_box_id, $post_type_obj);

	                // Skip callback-based meta boxes since they already did work on do_action
	                if ( isset($meta_box['callback']) ) continue;

	    			// Validate nonce
	    			$nonce_action = sprintf("%s_%s_%s", VANILLA_THEME_SLUG, $post_type, $meta_box['slug']);
	    			$nonce_field = VANILLA_THEME_SLUG . '_' . $post_type . '_nonce';;
	    			check_ajax_referer($nonce_action, $nonce_field);

	    			// Process the fields
	    			foreach ( $meta_box['fields'] as $field )
	    			{

	    				$value = isset($_POST[VANILLA_THEME_SLUG . '_' . $field['id']]) ? $_POST[VANILLA_THEME_SLUG . '_' . $field['id']] : '';

	    				// If we have a custom handler, just execute that instead
	    				if ( ! empty($field['handler']) )
	    				{
	    					$value = call_user_func_array($field['handler'], array($post_id, $value, $field, $post_type_obj));
	    				}
	    				else
	    				{
	    					$value = apply_filters('vanilla_save_meta_field', $value, $field['id'], $post_type_obj);
	    					update_post_meta($post_id, $field['id'], $value);
	    				}

	    			}

	    		}

    		}

        }

	}
	
	/**
	 * Constructor.
	 *
	 * @param string $slug the slug of this post type
	 * @param string $singular singular form of the name of this post type
	 * @param string $plural plural form of the name of this post type
	 * @param array $args optional. post type arguments.
	 */
	private function __construct($slug, $singular, $plural, $args = array())
	{
		
		$this->post_type = $slug;
		
		// Arguments
		$defaults = array(
			'public'			=> true,
			'menu_position'		=> 5
		);
		$this->args = array_merge($defaults, $args);
		
		// Process labels
		if ( ! isset($this->args['label']) && ! isset($this->args['labels']) )
		{
			
			$this->args['label'] = $plural;
			$this->args['labels'] = array(
				'singular_name'			=> $singular,
				'add_new_item'			=> "Add New $singular",
				'edit_item'				=> "Edit $singular",
				'new_item'				=> "New $singular",
				'view_item'				=> "View $singular",
				'search_items'			=> "Search $plural",
				'not_found'				=> "No $plural found",
				'not_found_in_trash'	=> "No $plural found in Trash"
			);
			
		}
		
	}
	
	/**
	 * Adds a new taxonomy and links it to this post type.
	 *
	 * @param string $singular singular form of the name of this taxonomy
	 * @param string $plural plural form of the name of this taxonomy
	 * @param array $args optional. taxonomy args.
	 */
	public function add_taxonomy($singular, $plural = null, $args = array())
	{
		
		$slug = self::create_taxonomy($singular, $plural, $args);
		if ( array_search($slug, $this->taxonomies) === false )
		{
			$this->taxonomies[] = $slug;
		}
		
	}
	
	/**
	 * Returns the slug of this post type.
	 *
	 * @return string
	 */
	public function get_slug()
	{
		
		return $this->post_type;
		
	}
	
	/**
	 * Return the arguments array for this post type.
	 *
	 * @return array
	 */
	public function get_args()
	{
		
		return $this->args;
		
	}

	/**
	 * Adds a metabox for that post type.
	 * 
	 * @param string $title   meta box title
	 * @param array $fields  array of fields
	 * @param string $context optional meta box position. can be 'normal', 'advanced' or 'side'
	 */
	public function add_meta_box($title, $fields, $context = 'advanced')
	{

		$slug = str_replace('-', '_', vanilla_slug($title));

        // Process each field
		$the_fields = array();
		foreach ( $fields as $label => $field )
		{
            $f = array(
                'id'    	=> str_replace('-', '_', vanilla_slug($label)),
                'label' 	=> $label,
                'type'  	=> is_array($field) ? $field['type'] : $field,
                'handler'	=> is_array($field) && isset($field['handler']) ? $field['handler'] : ''
            );

            if ( is_array($field) )
            {
                $f = array_merge($f, $field);
            }
			
            $the_fields[] = $f;

		}

        // Add this meta box to our this post type's collection
		$this->meta_boxes[$slug] = array(
            'title'     => $title,
            'fields'    => $the_fields,
            'context'   => $context,
            'slug'      => $slug
        );

	}

	/**
	 * Adds a metabox but specifying a custom callback.
     * 
	 * @param  string $title           meta box title
	 * @param  mixed  $callback        callback function
	 * @param  array  $callback_args   callback arguments
	 * @param  string $context         optional meta box position
     * @return string the meta box slug
	 */
	public function add_meta_box_cb($title, $callback, $args = '')
	{

		if ( $args == '' )
			$args = array();

		$default_args = array(
			'save_handler'		=> '',
			'callback_args'		=> null,
			'context'			=> 'advanced'
		);
		$args = array_merge($default_args, $args);

		$args['title'] = $title;
		$args['callback'] = $callback;

        $slug = str_replace('-', '_', vanilla_slug($title));
		$this->meta_boxes[$slug] = $args;

        return $slug;

	}

    public static function _scripts()
    {
        
        $screen = get_current_screen();
        if ( $screen->base != 'post' )
            return;

        wp_enqueue_script('rhap-posttype-scripts', get_template_directory_uri() . '/includes/rhapsody/js/post-type.js', array('jquery'));
        wp_localize_script('rhap-posttype-scripts', 'RHAPSODY', array(
            'getAttachmentsNonce' => wp_create_nonce('rhap-get-attach'),
            'postID'              => get_the_ID()
        ));

    }

    public static function _get_attachments_ajax()
    {

        check_ajax_referer('rhap-get-attach');

        $args = array(
            'post_type'         => 'attachment',
            'post_status'       => 'any',
            'post_mime_type'    => 'image',
            'posts_per_page'    => -1
        );

        if ( isset($_GET['post']) && is_numeric($_GET['post']) )
            $args['post_parent'] = intval($_GET['post']);

        $attachs = get_posts($args);
        $attachments = array();
        foreach ( $attachs as $attach )
        {
            $thumb = wp_get_attachment_image_src($attach->ID, 'thumbnail');
            $attach->thumb = $thumb[0];
            $attach->basename = basename($attach->guid);
            $attachments[$attach->ID] = $attach;
        }

        header('Content-type: application/json');
        echo json_encode(array(
            'results' => $attachments
        ));
        exit;

    }

}
Vanilla_PostType::_setup();