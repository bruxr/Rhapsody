<?php
/**
 * Vanilla
 *
 * Vanilla_Settings Class
 * Provides a simple API for accessing WP's Settings API.
 *  
 * @package Vanilla
 * @author  brux <brux.romuar@gmail.com>
 */
class Vanilla_Settings
{

    /**
     * Array of registered setting fields.
     * 
     * @var array
     */
    private $fields = array();

    /**
     * The name of the option where 
     * settings are stored.
     *
     * @var string
     */
    private $option_name;

    /**
     * The "slug" of the settings page
     *
     * @var string
     */
    private $page_slug;

    /**
     * Array of registered setting sections.
     * 
     * @var array
     */
    private $sections = array();

    /**
     * Constructor, sets $option_name and $page_slug.
     *
     * @param string $option_name   name of option where settings are stored
     * @param string $page_slug     settings page "slug"
     */
    public function __construct($option_name, $page_slug)
    {

        $this->option_name = $option_name;
        $this->page_slug = $page_slug;

        add_action('admin_init', array($this, '_admin_init'));

    }

    /**
     * Transforms a string to a valid ID or slug.
     * 
     * @param  string $str input string
     * @return string
     */
    private function create_id($str)
    {

        $id = strtolower($str);
        $id = preg_replace('/[^a-z0-9_]/', '_', $id);
        $id = trim($id, '-');

        return $id;

    }

    /**
     * Runs on init when we are inside WP-admin.
     * Registers our setting, sections and fields.
     */
    public function _admin_init()
    {

        register_setting($this->option_name, $this->option_name, array($this, '_sanitize'));

        foreach ( $this->sections as $section_id => $section )
        {
            add_settings_section($section_id, $section['title'], array($this, '_section_callback'), $this->page_slug);
        }

        foreach ( $this->fields as $field_id => $field )
        {
            add_settings_field($field_id, $field['title'], $field['callback'], $this->page_slug, $field['section'], $field['args']);
        }

    }

    /**
     * Generates checkboxes for setting fields.
     * Used as callback for add_settings_field().
     *
     * $args should contain at least the elements 'id' which is
     * the ID of the fields option and 'options' which is an array
     * of value => label pairs of available options.
     *
     * $args can also contain 'description'.
     * 
     * @param  array $args add_settings_fields() args
     */
    public function _checkbox_field($args)
    {

        $option = get_option($this->option_name);
        extract($args);

        $name = sprintf('%s[%s]', $this->option_name, $id);

        if ( isset($option[$id]) )
        {
            $value = $option[$id];
        }
        else
        {
            $value = '';
        }

        $i = 0;
        $last = count($options) - 1;
        foreach ( $options as $option_value => $option_label ):
?>
        <label><input type="checkbox" name="<?php echo $name; ?>" value="<?php echo $option_value; ?>"<?php checked($option_value, $value); ?>> <?php echo $option_label; ?></label><?php if ( $i != $last ): ?><br><?php endif; ?>
<?php
        $i++;
        endforeach;
?>
        <?php if ( isset($args['description']) ): ?><p class="description"><?php echo $args['description']; ?></p><?php endif; ?>
<?php

    }

    /**
     * Generates radio buttons for setting fields.
     * Used as callback for add_settings_field().
     *
     * $args should contain at least the elements 'id' which is
     * the ID of the fields option and 'options' which is an array
     * of value => label pairs of available options.
     *
     * $args can also contain 'description'.
     * 
     * @param  array $args add_settings_fields() args
     */
    public function _radio_field($args)
    {

        $option = get_option($this->option_name);
        extract($args);

        $name = sprintf('%s[%s]', $this->option_name, $id);

        if ( isset($option[$id]) )
        {
            $value = $option[$id];
        }
        else
        {
            $value = '';
        }

        $i = 0;
        $last = count($options) - 1;
        foreach ( $options as $option_value => $option_label ):
?>
        <label><input type="radio" name="<?php echo $name; ?>" value="<?php echo $option_value; ?>"<?php checked($option_value, $value); ?>> <?php echo $option_label; ?></label><?php if ( $i != $last ): ?><br><?php endif; ?>
<?php
        $i++;
        endforeach;
?>
        <?php if ( isset($args['description']) ): ?><p class="description"><?php echo $args['description']; ?></p><?php endif; ?>
<?php

    }

    /**
     * Runs sanitation on POSTed form values.
     * 
     * @param  array $input form values
     * @return array
     */
    public function _sanitize($input)
    {

        if ( $input )
        {
            foreach ( $input as $name => $value )
            {
                $value = apply_filters("vanilla_sanitize_setting_$name", $value);
            }
        }
        else
        {
            $input = array();
        }

        return $input;
        
    }

    /**
     * Prints section descriptions if they are available.
     * 
     * @param  array $args  add_settings_section() parameters
     */
    public function _section_callback($args)
    {
        
        $section_id = $args['id'];
        if ( ! empty($this->sections[$section_id]['description']) )
        {
            echo $this->sections[$section_id]['description'];
        }

    }

    /**
     * Generates a select dropdown for setting fields.
     * Used as callback for add_settings_field().
     *
     * $args should contain at least the elements 'id' which is
     * the ID of the fields option and 'options' which is an array
     * of value => label pairs of available options.
     *
     * $args can also contain 'description'.
     * 
     * @param  array $args add_settings_fields() args
     */
    public function _select_field($args)
    {

        $option = get_option($this->option_name);
        extract($args);

        $name = sprintf('%s[%s]', $this->option_name, $id);

        if ( isset($option[$id]) )
        {
            $value = $option[$id];
        }
        else
        {
            $value = '';
        }
?>
        <select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
<?php
        $i = 0;
        $last = count($options) - 1;
        foreach ( $options as $option_value => $option_label ):
?>
        <option value="<?php echo $option_value; ?>"<?php selected($option_value, $value); ?>> <?php echo $option_label; ?></option>
<?php
        $i++;
        endforeach;
?>
        </select>
        <?php if ( isset($args['description']) ): ?><p class="description"><?php echo $args['description']; ?></p><?php endif; ?>
<?php

    }

    /**
     * Generates text input fields.
     * Used as callback for add_settings_field().
     *
     * $args should contain at least the element 'id' which is
     * the ID of the fields option.
     *
     * $args can also contain 'placeholder' and 'description'.
     * 
     * @param  array $args add_settings_fields() args
     */
    public function _text_field($args)
    {
        
        $option = get_option($this->option_name);
        extract($args);

        $name = sprintf('%s[%s]', $this->option_name, $id);

        if ( isset($option[$id]) )
        {
            $value = $option[$id];
        }
        else
        {
            $value = '';
        }

        if ( isset($args['placeholder']) )
        {
            $placeholder = ' placeholder="'. $placeholder .'"';
        }
        else
        {
            $placeholder = '';
        }

?>
        <input type="<?php echo $text_type; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" id="<?php echo $name; ?>" class="regular-text"<?php echo $placeholder; ?>>
        <?php if ( isset($args['description']) ): ?><p class="description"><?php echo $args['description']; ?></p><?php endif; ?>
<?php
    }

    /**
     * Invokes settings_fields() and do_settings_section() which generates
     * the settings input fields.
     */
    public function do_settings()
    {
        
        settings_fields($this->option_name);
        do_settings_sections($this->page_slug);

    }

    /**
     * Adds a setting section. 
     * Throws an Exception if a section with the same $title already exists.
     * 
     * @param   string $title       section title
     * @param   string $description optional description for the section
     * @return  string section ID
     */
    public function add_section($title, $description = '')
    {

        $id = $this->create_id($title);

        if ( isset($this->sections[$id]) )
        {
            throw new Exception('Section already exists.');
        }
        
        $this->sections[$id] = compact('title', 'description');

    }

    /**
     * Adds a setting field.
     *
     * $type can be:
     *  'text' for regular text fields
     *  'email' for email fields
     *  'url' for URL input fields
     *  'checkbox' checkbox multi-selects
     *  'dropdown' or 'select' for dropdown menus
     *  'radio' for radio buttons
     * 
     * @param   string $title   field title/label
     * @param   string $type    field type
     * @param   string $section the name of the section where to put this field
     * @param   array $args     optional arguments
     * @return  string field ID
     */
    public function add_field($title, $type, $section, $args = null)
    {

        if ( ! $args )
        {
            $args = array();
        }

        $section = $this->create_id($section);

        // Generate a unique ID
        if ( isset($args['id']) )
        {
            $id = $args['id'];
        }
        else
        {
            $id = $this->create_id($title);
            $i = 1;
            while ( isset($this->fields[$id]) )
            {
                $id = $this->create_id("$title-$i");
                $i++;
            }
            $args['id'] = $id;
        }

        // Determine the callback by the field type
        if ( is_array($type) )
        {
            $callback = $type;
        }
        else
        {
            switch ( $type )
            {

                case 'email':
                case 'text':
                case 'url':
                    $callback = array($this, "_text_field");
                    $args['text_type'] = $type;
                    break;

                case 'checkbox':
                case 'dropdown':
                case 'radio':
                case 'select':

                    if ( $type == 'dropdown' )
                    {
                        $type = 'select';
                    }

                    $func_args = func_get_args();
                    if ( ! isset($args['options']) )
                    {
                        throw new BadMethodCallException('Options array missing.');
                    }
                    else
                    {
                        $callback = array($this, "_{$type}_field");
                    }
                    break;

                default:
                    $callback = $type;

            }
        }

        $this->fields[$id] = compact('title', 'callback', 'type', 'section', 'args');

    }

}
?>