<?php
/**
 * Vanilla
 *
 * benchmarking.php
 * Framework benchmarking tools
 *
 * @package Vanilla
 * @author brux <brux.romuar@gmail.com>
 */

/**
 * Adds the Vanilla_DebugBar class to Debug Bar's panels.
 * 
 * @param   array   $panels     array of debug bar panels
 * @return  array
 */
function vanilla_debug_bar_panels($panels)
{
    
    require_once VANILLA_PATH . '/DebugBar.php';
    $panels[] = new Vanilla_DebugBar;
    return $panels;

}
add_filter('debug_bar_panels', 'vanilla_debug_bar_panels');

/**
 * Returns the elapsed time between two time marks.
 * 
 * You can only pass $point1 by suffixing both 
 * "_start" and "_end" to your time marks.
 * e.g. call vanilla_mark() using "setup_start" and "setup_end"
 * then vanilla_elapsed_time("setup") to return the
 * elapsed time between "setup_start" and "setup_end".
 *
 * Will return false if either of the two points
 * does not exist.
 * 
 * @param   string  $point1     start point
 * @param   string  $point2     optional end point.
 * @return  float|false
 */
function vanilla_elapsed_time($point1, $point2 = '')
{

    if ( ! $point2 )
    {
        $point2 = "{$point1}_end";
        $point1 = "{$point1}_start";
    }

    $times = wp_cache_get('vanilla_benchmark_times');
    if ( isset($times[$point1]) && isset($times[$point2]) )
    {
        $elasped_time = $times[$point2] - $times[$point1];
        return $elasped_time;
    }
    else
    {
        return false;
    }

}

/**
 * Marks this time with a label and then returns the current
 * time in microseconds.
 * Used in conjuction with vanilla_elapsed_time()
 * 
 * @param   string  $label  the label for this mark
 * @return  integer
 */
function vanilla_mark($label)
{

    $times = wp_cache_get('vanilla_benchmark_times');
    if ( ! $times ) $times = array();

    $times[$label] = microtime(true);

    wp_cache_set('vanilla_benchmark_times', $times);

}