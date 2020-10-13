<?php
/**
 * Plugin Name: SPINDOGS Bedrock Autoloader
 * Plugin URI: https://github.com/spindogs/bedrock/
 * Description: An autoloader that enables standard plugins to be required just like must-use plugins. The autoloaded plugins are included during mu-plugin loading. An asterisk (*) next to the name of the plugin designates the plugins that have been autoloaded.
 * Version: 1.0.1
 * Author: Roots
 * Author URI: https://spindogs.co.uk/
 * License: MIT License
 */

namespace Roots\Bedrock;

if (!is_blog_installed()) {
    return;
}

/**
 * Class Autoloader
 * @package Roots\Bedrock
 * @author Roots
 * @link https://roots.io/
 */
class Autoloader
{
    /** @var static Singleton instance */
    private static $instance;

    /** @var array Store Autoloader cache and site option */
    private $cache;

    /** @var array Autoloaded plugins */
    private $autoPlugins;

    /** @var array Autoloaded mu-plugins */
    private $muPlugins;

    /** @var int Number of plugins */
    private $count;

    /** @var array Newly activated plugins */
    private $activated;

    /**
     * Create singleton, populate vars, and set WordPress hooks
     */
    public function __construct()
    {
        if (isset(self::$instance)) {
            return;
        }

        self::$instance = $this;

        if (is_admin()) {
            add_filter('show_advanced_plugins', [$this, 'showInAdmin'], 0, 2);
        }

        $this->loadPlugins();
    }

   /**
    * Run some checks then autoload our plugins.
    */
    public function loadPlugins()
    {
        $this->checkCache();
        $this->validatePlugins();
        $this->countPlugins();

        array_map(static function () {
            include_once WPMU_PLUGIN_DIR . '/' . func_get_args()[0];
        }, array_keys($this->cache['plugins']));

        $this->pluginHooks();
    }

    /**
     * Filter show_advanced_plugins to display the autoloaded plugins.
     * @param $show bool Whether to show the advanced plugins for the specified plugin type.
     * @param $type string The plugin type, i.e., `mustuse` or `dropins`
     * @return bool We return `false` to prevent WordPress from overriding our work
     * {@internal We add the plugin details ourselves, so we return false to disable the filter.}
     */
    public function showInAdmin($show, $type)
    {
        $screen = get_current_screen();
        $current = is_multisite() ? 'plugins-network' : 'plugins';

        if ($screen->base !== $current || $type !== 'mustuse' || !current_user_can('activate_plugins')) {
            return $show;
        }

        $this->updateCache();

        $this->autoPlugins = array_map(function ($auto_plugin) {
            $auto_plugin['Name'] .= ' *';
            return $auto_plugin;
        }, $this->autoPlugins);

        $GLOBALS['plugins']['mustuse'] = array_unique(array_merge($this->autoPlugins, $this->muPlugins), SORT_REGULAR);

        return false;
    }

    /**
     * This sets the cache or calls for an update
     */
    private function checkCache()
    {
        $cache = get_site_option('bedrock_autoloader');

        if ($cache === false || (isset($cache['plugins'], $cache['count']) && count($cache['plugins']) !== $cache['count'])) {
            $this->updateCache();
            return;
        }

        $this->cache = $cache;
    }

    /**
     * Get the plugins and mu-plugins from the mu-plugin path and remove duplicates.
     * Check cache against current plugins for newly activated plugins.
     * After that, we can update the cache.
     */
    private function updateCache()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $this->autoPlugins = $this->getPlugins();
        $this->muPlugins   = get_mu_plugins();
        $plugins           = array_diff_key($this->autoPlugins, $this->muPlugins);
        $rebuild           = !is_array($this->cache['plugins']);
        $this->activated   = $rebuild ? $plugins : array_diff_key($plugins, $this->cache['plugins']);
        $this->cache       = ['plugins' => $plugins, 'count' => $this->countPlugins()];

        update_site_option('bedrock_autoloader', $this->cache);
    }

    /**
     * This accounts for the plugin hooks that would run if the plugins were
     * loaded as usual. Plugins are removed by deletion, so there's no way
     * to deactivate or uninstall.
     */
    private function pluginHooks()
    {
        if (!is_array($this->activated)) {
            return;
        }

        foreach ($this->activated as $plugin_file => $plugin_info) {
            do_action('activate_' . $plugin_file);
        }
    }

    /**
     * Check that the plugin file exists, if it doesn't update the cache.
     */
    private function validatePlugins()
    {
        foreach ($this->cache['plugins'] as $plugin_file => $plugin_info) {
            if (!file_exists(WPMU_PLUGIN_DIR . '/' . $plugin_file)) {
                $this->updateCache();
                break;
            }
        }
    }

    /**
     * Count the number of autoloaded plugins.
     *
     * Count our plugins (but only once) by counting the top level folders in the
     * mu-plugins dir. If it's more or less than last time, update the cache.
     *
     * @return int Number of autoloaded plugins.
     */
    private function countPlugins()
    {
        if (isset($this->count)) {
            return $this->count;
        }

        $count = count(glob(WPMU_PLUGIN_DIR . '/*/', GLOB_ONLYDIR | GLOB_NOSORT));

        if (!isset($this->cache['count']) || $count !== $this->cache['count']) {
            $this->count = $count;
            $this->updateCache();
        }

        return $this->count;
    }

    /**
     * Get plugins from mu-plugins
     *
     * Almost direct copy from wordpress source, only $plugin_root changed to
     * point to where this file lives, and cache names changed to mu-plugin
     *
     * @return array valid plugins
     */

    private function getPlugins( $plugin_folder  = ''){
        $cache_plugins = wp_cache_get( 'mu-plugins', 'mu-plugins' );
        if ( ! $cache_plugins ) {
            $cache_plugins = array();
        }

        if ( isset( $cache_plugins[ $plugin_folder ] ) ) {
            return $cache_plugins[ $plugin_folder ];
        }

        $wp_plugins  = array();
        $plugin_root = dirname(__FILE__);
        if ( ! empty( $plugin_folder ) ) {
            $plugin_root .= $plugin_folder;
        }

        // Files in app/mu-plugins directory
        $plugins_dir  = @ opendir( $plugin_root );
        $plugin_files = array();
        if ( $plugins_dir ) {
            while ( ( $file = readdir( $plugins_dir ) ) !== false ) {
                if ( substr( $file, 0, 1 ) == '.' ) {
                    continue;
                }
                if ( is_dir( $plugin_root . '/' . $file ) ) {
                    $plugins_subdir = @ opendir( $plugin_root . '/' . $file );
                    if ( $plugins_subdir ) {
                        while ( ( $subfile = readdir( $plugins_subdir ) ) !== false ) {
                            if ( substr( $subfile, 0, 1 ) == '.' ) {
                                continue;
                            }
                            if ( substr( $subfile, -4 ) == '.php' ) {
                                $plugin_files[] = "$file/$subfile";
                            }
                        }
                        closedir( $plugins_subdir );
                    }
                } else {
                    if ( substr( $file, -4 ) == '.php' ) {
                        $plugin_files[] = $file;
                    }
                }
            }
            closedir( $plugins_dir );
        }

        if ( empty( $plugin_files ) ) {
            return $wp_plugins;
        }

        foreach ( $plugin_files as $plugin_file ) {
            if ( ! is_readable( "$plugin_root/$plugin_file" ) ) {
                continue;
            }

            $plugin_data = get_plugin_data( "$plugin_root/$plugin_file", false, false ); //Do not apply markup/translate as it'll be cached.

            if ( empty( $plugin_data['Name'] ) ) {
                continue;
            }

            $wp_plugins[ plugin_basename( $plugin_file ) ] = $plugin_data;
        }

        uasort( $wp_plugins, '_sort_uname_callback' );

        $cache_plugins[ $plugin_folder ] = $wp_plugins;
        wp_cache_set( 'mu-plugins', $cache_plugins, 'mu-plugins' );

        return $wp_plugins;
    }
}

new Autoloader();