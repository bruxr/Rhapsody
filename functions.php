<?php
/**
 * Vanilla
 *
 * functions.php
 * Theme Framework Functions
 *
 * @package Vanilla
 * @author brux <brux.romuar@gmail.com>
 */

/**
 * Convenience function for var_dump().
 * Also wraps the output with <pre> tags
 * 
 * @param $var the variable
 */
if ( ! function_exists('d') ):
function d($var)
{

    echo '<pre>';
    var_dump($var);
    echo '</pre>';

}
endif;

/**
 * Registers and enqueues Vanilla built-in scripts.
 */
function vanilla_base_scripts()
{

    $js_url = get_template_directory_uri() . '/includes/rhapsody/js';

    // Modernizr
    wp_register_script('modernizr', "$js_url/modernizr.min.js", null, '2.6.2');
    wp_enqueue_script('modernizr');

    // Vanilla base
    wp_register_script('vanilla', "$js_url/vanilla.js", array('jquery'), VANILLA_VERSION, true);
    wp_enqueue_script('vanilla');

    // Bootstrap
    $bootstrap_js = array('alert', 'button', 'carousel', 'collapse', 'dropdown', 'modal',
        'popover', 'scrollspy', 'tab', 'tooltip', 'transition', 'typeahead');
    foreach ( $bootstrap_js as $js )
    {
        wp_register_script("bootstrap-$js", "$js_url/bootstrap-{$js}.js", array('jquery'), '2.2.2', true);
    }

}
add_action('wp_enqueue_scripts', 'vanilla_base_scripts', 9);

/**
 * Handles how each comment is displayed. 
 * Used as calback in wp_list_comments().
 * 
 * @param object $comment the comment
 * @param array $args display arguments
 * @param int $depth nested comment depth
 */
function vanilla_comment($comment, $args, $depth)
{

    $GLOBALS['comment'] = $comment;
?>

<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>

    <div class="comment-content">

        <div class="user-avatar">
            <a href="<?php echo comment_author_url(); ?>"><?php echo get_avatar($comment, 48); ?></a>
        </div>

        <div class="comment-body">
            
            <div class="comment-meta">
                <span class="comment-author"><?php comment_author_link(); ?></span> posted on 
                <a href="<?php comment_link(); ?>" title="Permalink to this Comment" class="comment-permalink"><time datetime="<?php comment_time('c'); ?>"><?php vanilla_comment_time(); ?></time></a>
            </div>

            <div class="comment-text">
                <?php comment_text(); ?>
            </div>

            <div class="comment-utils">
                <?php if ( $comment->comment_approved == '0' ): ?>
                    <span class="comment-awaiting-moderation">Your comment is awaiting moderation.</span>
                <?php else: ?>
                    <?php if ( current_user_can('edit_comment', $comment->comment_ID) ): edit_comment_link('Edit'); ?> &middot; <?php endif; ?>
                    <?php comment_reply_link(array_merge($args, array(
                        'reply_text' => 'Reply',
                        'depth' => $depth,
                        'max_depth' => $args['max_depth']
                    ))); ?>
                <?php endif; ?>
            </div>

        </div>

    </div>

<?php
}

/**
 * Prints the date & time the comment was posted using the date & time format
 * specified in the blog's settings panel.
 */
function vanilla_comment_time()
{

    comment_time(get_option('date_format') . ' ' . get_option('time_format'));

}

/**
 * Call this function to enable a framework feature.
 * Available features:
 * - opensearch
 * - settings
 * 
 * @param   string  $feature    the feature name
 * @param   mixed   $args       optional feature arguments
 * @return  void
 */
function vanilla_enable_feature($feature, $args = true)
{

    $vanilla_features = wp_cache_get('vanilla_features');
    if ( ! $vanilla_features ) $vanilla_features = array();

    if ( ! isset($vanilla_features[$feature]) )
    {
        $vanilla_features[$feature] = $args;
    }

    wp_cache_set('vanilla_features', $vanilla_features);

}

/**
 * Returns TRUE if a framework features has been enabled.
 * 
 * @param   string  $feature    framework feature
 * @return  bool
 */
function vanilla_has_feature($feature)
{

    $vanilla_features = wp_cache_get('vanilla_features');
    
    if ( ! $vanilla_features )
    {
        return false;
    }

    return isset($vanilla_features[$feature]);

}

/**
 * Returns the arguments of a feature.
 * 
 * @param   string  $feature    feature name
 * @return  mixed
 */
function vanilla_get_feature($feature)
{

    $vanilla_features = wp_cache_get('vanilla_features');

    if ( ! $vanilla_features || ! isset($vanilla_features[$feature]) )
        return false;

    return $vanilla_features[$feature];

}

/**
 * Returns TRUE if the current request is made through XMLHttpRequest/AJAX
 * 
 * @return  bool
 */
function vanilla_is_ajax()
{

    return ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' );

}

/**
 * Wrapper to wp_localize_script() - allows multiple calls with the
 * same $handle and $object_name merging the $l10n array to the
 * previously stored one.
 * 
 * @param  string $handle      script name
 * @param  string $object_name javascript object name
 * @param  array  $l10n        the data
 * @since  0.3
 */
function vanilla_localize_script($handle, $object_name, $l10n)
{

    $l10n = wp_cache_get('vanilla_scripts_l10n');

    if ( isset($l10n[$handle][$object_name]) )
    {
        $l10n[$handle][$object_name] = array_merge($l10n[$handle][$object_name], $l10n);
        wp_cache_set('vanilla_scripts_l10n', $l10n);
    }
    else
    {
        $this->scripts_l10n[$handle][$object_name] = $l10n;
    }

}

/**
 * Prints the value of a post's custom field/metadata.
 * 
 * @param string $key meta key
 */
function vanilla_meta($key, $post_id = '')
{

    if ( ! $post_id )
        $post_id = get_the_ID(); 

    echo get_post_meta($post_id, $key, true);

}

/**
 * A function that does nothing :)
 */
function vanilla_noop()
{

}

/**
 * Normalizes a URL by automatically adding http:// if 
 * it is missing.
 * 
 * @param string $url input URL
 * @return string
 */
function vanilla_normalize_url($url)
{

    $parts = parse_url($url);
    if ( $parts && ! isset($parts['scheme']) )
    {
        $url = "http://$url";
    }
    else
    {
        return false;
    }

}

/**
 * Fallback function for wp_nav_menu() calls. Prints a menu containing
 * all of the blog's pages.
 *
 * @param array $args menu arguments
 */
function vanilla_page_menu($args)
{
    
    extract($args);

    // Do we have a container?
    $container_end = '';
    if ( $container !== false )
    {
        
        $container_end = '</'. $container .'>';
        
        $container = "<$container";

        // Container ID
        if ( $container_id )
            $container .= ' id="'. $container_id .'"';

        // Container class
        if ( $container_class )
            $container .= ' class="'. $container_class .'"';

        $container .= '>';

    }

    // Do we have a menu ID?
    if ( ! $menu_id )
    {
        $menu_id = $theme_location . '-menu';
    }

    // or menu class?
    if ( ! $menu_class )
    {
        $menu_class = '';
    }
    
    if ( ! $menu_id ) $menu_id = $theme_location . '-menu';

    $pages = get_pages();

?>
<?php echo $container; ?>
        <ul id="<?php echo $args['theme_location']; ?>-menu"<?php if ( $menu_class ): ?> class="<?php echo $menu_class; ?>"<?php endif; ?>>
            <li<?php if ( is_home() ): ?> class="current-menu-item"<?php endif; ?>><?php echo $before; ?><a href="<?php bloginfo('url') ?>"><?php echo $link_before; ?>Home<?php echo $link_after; ?></a><?php echo $after; ?></li>
            <?php foreach ( $pages as $page ): ?>
            <li<?php if ( is_page($page->ID) ): ?> class="current-menu-item"<?php endif; ?>><?php echo $before; ?><a href="<?php echo get_permalink($page->ID); ?>"><?php echo $link_before . apply_filters('the_title', $page->post_title) . $link_after; ?></a><?php echo $after; ?></li>
            <?php endforeach; ?>
        </ul>
<?php echo $container_end; ?>
<?php
}

/**
 * Returns the plural version of $str.
 *
 * @param string $word input string
 * @return string
 * @link http://www.phpclasses.org/browse/file/12343.html
 */
function vanilla_pluralize($word)
{

    $plural = array(
    '/(quiz)$/i' => '\1zes',
    '/^(ox)$/i' => '\1en',
    '/([m|l])ouse$/i' => '\1ice',
    '/(matr|vert|ind)ix|ex$/i' => '\1ices',
    '/(x|ch|ss|sh)$/i' => '\1es',
    '/([^aeiouy]|qu)ies$/i' => '\1y',
    '/([^aeiouy]|qu)y$/i' => '\1ies',
    '/(hive)$/i' => '\1s',
    '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
    '/sis$/i' => 'ses',
    '/([ti])um$/i' => '\1a',
    '/(buffal|tomat)o$/i' => '\1oes',
    '/(bu)s$/i' => '\1ses',
    '/(alias|status)/i'=> '\1es',
    '/(octop|vir)us$/i'=> '\1i',
    '/(ax|test)is$/i'=> '\1es',
    '/s$/i'=> 's',
    '/$/'=> 's');

    $uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

    $irregular = array(
    'person' => 'people',
    'man' => 'men',
    'child' => 'children',
    'sex' => 'sexes',
    'move' => 'moves');

    $lowercased_word = strtolower($word);

    foreach ($uncountable as $_uncountable){
        if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable){
            return $word;
        }
    }

    foreach ($irregular as $_plural=> $_singular){
        if (preg_match('/('.$_plural.')$/i', $word, $arr)) {
            return preg_replace('/('.$_plural.')$/i', substr($arr[0],0,1).substr($_singular,1), $word);
        }
    }

    foreach ($plural as $rule => $replacement) {
        if (preg_match($rule, $word)) {
            return preg_replace($rule, $replacement, $word);
        }
    }
    return false;

}

/**
 * Registers a sidebar.
 * This is just a wrapper of register_sidebar() but makes wraps each sidebar
 * widget with <aside> for HTML 5.
 * 
 * @param  string $name sidebar name
 * @param  string $desc optional sidebar description
 * @param  string $id   optional sidebar id/slug
 * @param  array  $args optional args
 * @since  0.3
 */
function vanilla_register_sidebar($name, $desc = '', $id = '', $args = array())
{

    $default_args = array(
        'name'          => $name,
        'description'   => $desc,
        'id'            => $id ? $id : vanilla_slug($name),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widgettitle">',
        'after_title'   => '</h3>'
    );
    $args = array_merge($default_args, $args);

    register_sidebar($args);

}

/**
 * Returns the "slug" version of $str.
 * A slug is basically a lowercase version of $str but with
 * characters that are not alphanumeric & not a dash or underscore
 * replaced with a dash (override by passing $replace_with)
 * 
 * @param   string $str             input string
 * @param   string $replace_with    optional. replace non-alphanumeric & not an underscore with this
 * @return  string
 */
function vanilla_slug($str, $replace_with = '-')
{

    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9-_]/i', $replace_with, $str);

    return $str;

}

/**
 * Prints the date and time the post was created.
 * This will use the date & time format specified in the blog's settings. 
 */
function vanilla_the_time()
{

    the_time(get_option('date_format') . ' ' . get_option('time_format'));

}