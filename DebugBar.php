<?php
/**
 * Vanilla
 *
 * DebugBar.php
 * Defines the Vanilla_Debug_Bar class which outputs 
 * benchmark & performance status to Debug Bar
 *
 * @package Vanilla
 * @author brux <brux.romuar@gmail.com>
 */
class Vanilla_DebugBar extends Debug_Bar_Panel
{

    /**
     * Sets the panel's title.
     */
    public function init()
    {

        $this->title('Vanilla');

    }

    /**
     * Makes the panel visible.
     */
    public function prerender()
    {

        $this->set_visible(true);

    }

    /**
     * Renders the panel's content.
     */
    public function render()
    {

        $framework_setup_time = vanilla_elapsed_time('setup');
        $post_type_setup_time = vanilla_elapsed_time('post_type_setup');
        $setup_time = $framework_setup_time + $post_type_setup_time;

?>
        <div id="vanilla-benchmark">

            <h2>
                <span>Vanilla Version</span>
                <?php echo VANILLA_VERSION; ?>
            </h2>

            <h2>
                <span>Setup Time</span>
                <?php echo round($setup_time, 4); ?>ms
            </h2>

        </div>
<?php
    }

}