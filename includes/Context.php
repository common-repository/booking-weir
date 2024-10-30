<?php

namespace wsd\bw;

use  Exception ;
use  wsd\bw\util\helpers ;
/**
 * Class representing the context in which the plugin is running.
 */
final class Context
{
    /**
     * Absolute path to the plugin main file.
     *
     * @var string
     */
    private  $main_file ;
    /**
     * Plugin slug.
     *
     * @var string
     */
    private  $plugin_slug ;
    /**
     * Plugin basename.
     *
     * @var string
     */
    private  $plugin_basename ;
    /**
     * Plugin version.
     *
     * @var string
     */
    protected  $plugin_version ;
    /**
     * Instances of plugin modules.
     *
     * @var array
     */
    private  $instances ;
    /**
     * Sets the plugin main file.
     *
     * @param string $main_file Absolute path to the plugin main file.
     */
    public function __construct( $main_file )
    {
        $this->main_file = $main_file;
        $this->plugin_slug = 'booking-weir';
        $this->plugin_basename = plugin_basename( BOOKING_WEIR_FILE );
        $this->plugin_version = BOOKING_WEIR_VER;
        $this->instances = [];
    }
    
    /**
     * Store plugin module.
     *
     * @param string $id
     * @param mixed $instance
     */
    public function add( $id, $instance )
    {
        $this->instances[$id] = $instance;
    }
    
    /**
     * Retrieve plugin module.
     *
     * @param string $id
     * @return mixed
     */
    public function get( $id )
    {
        if ( !isset( $this->instances[$id] ) ) {
            throw new Exception( sprintf( 'Trying to retrieve unregistered module: %s', $id ) );
        }
        return $this->instances[$id];
    }
    
    /**
     * Gets the absolute path for a path relative to the plugin directory.
     *
     * @param string $relative_path Optional. Relative path. Default '/'.
     * @return string Absolute path.
     */
    public function path( $relative_path = '/' )
    {
        return plugin_dir_path( $this->main_file ) . ltrim( $relative_path, '/' );
    }
    
    /**
     * Gets the full URL for a path relative to the plugin directory.
     *
     * @param string $relative_path Optional. Relative path. Default '/'.
     * @return string Full URL.
     */
    public function url( $relative_path = '/' )
    {
        return plugin_dir_url( $this->main_file ) . ltrim( $relative_path, '/' );
    }
    
    /**
     * Gets the path to a plugin file in the build directory.
     *
     * @param string $file
     * @return string
     */
    public function build_path( $file )
    {
        return $this->path( 'dist/' . $file );
    }
    
    /**
     * Gets the URL to a plugin file in the build directory.
     *
     * @param string $file
     * @return string
     */
    public function build_url( $file )
    {
        return $this->url( 'dist/' . $file );
    }
    
    /**
     * Looks up files from plugin directory that match the selector.
     * Then look for child theme overrides.
     *
     * @param  string $dir      Subdirectory relative to (child)theme root.
     * @param  string $selector File name selector.
     * @return array            [basename => file]
     */
    public function files( $dir, $selector = '*.php' )
    {
        $dir = ( !empty($dir) ? $dir . DIRECTORY_SEPARATOR : '' );
        $files = [];
        $plugin_root = $this->path();
        $theme_root = get_template_directory();
        $child_root = get_stylesheet_directory();
        /**
         * Collect the files matching the selector from specified directory.
         */
        $glob = helpers\glob_maybe_brace( $plugin_root . $dir . $selector );
        foreach ( $glob as $file ) {
            $key = basename( $file );
            $files[$key] = $file;
        }
        /**
         * Override files if using Child theme.
         */
        
        if ( $child_root !== $theme_root ) {
            $child_dir = str_replace( [ 'includes/', 'templates/' ], $this->plugin_slug . '/', $dir );
            $glob = helpers\glob_maybe_brace( $child_root . DIRECTORY_SEPARATOR . $child_dir . $selector );
            foreach ( $glob as $file ) {
                $key = basename( $file );
                $files[$key] = $file;
            }
        }
        
        return $files;
    }
    
    /**
     * Returns a path to a plugin file which may be overridden
     * by a matching file in a child theme directory.
     *
     * @return string Path to file.
     */
    public function file( $dir, $filename )
    {
        $files = $this->files( $dir, $filename );
        if ( count( $files ) < 1 ) {
            return '';
        }
        /**
         * Return the path to first file in the (assoc) array.
         */
        foreach ( $files as $file ) {
            return $file;
        }
    }
    
    /**
     * Path for a configuration file.
     *
     * @param string $id
     * @return string
     */
    public function config_path( $id )
    {
        return $this->path( sprintf( '/includes/config/%s.php', $id ) );
    }
    
    /**
     * Path for `wp_set_script_translations`.
     *
     * @return string|null
     */
    public function languages_path()
    {
        return apply_filters( 'bw_languages_path', null );
    }
    
    /**
     * Return the plugin upload directory.
     * Example: `/var/www/wp-content/uploads/plugin-slug`
     *
     * @return string|bool
     */
    public function upload_dir()
    {
        $upload_dir = wp_upload_dir();
        if ( !isset( $upload_dir['basedir'] ) ) {
            return false;
        }
        $dir = $upload_dir['basedir'] . '/' . $this->plugin_slug();
        if ( validate_file( $dir ) !== 0 ) {
            return false;
        }
        return $dir;
    }
    
    /**
     * Returns the plugin upload URL.
     * Example: `http://127.0.0.1/wp-content/uploads/plugin-slug`
     *
     * @return string|bool
     */
    public function upload_url()
    {
        $upload_dir = wp_upload_dir();
        if ( !isset( $upload_dir['baseurl'] ) ) {
            return false;
        }
        return esc_url( $upload_dir['baseurl'] . '/' . $this->plugin_slug() );
    }
    
    /**
     * Return the plugin slug.
     */
    public function plugin_slug()
    {
        return $this->plugin_slug;
    }
    
    /**
     * Return the plugin basename.
     */
    public function plugin_basename()
    {
        return $this->plugin_basename;
    }
    
    /**
     * Return the plugin version.
     */
    public function plugin_version()
    {
        return $this->plugin_version;
    }
    
    /**
     * Capability required for full control of the plugin.
     *
     * @return string
     */
    public function get_admin_capability()
    {
        return apply_filters( 'bw_admin_capability', 'activate_plugins' );
        // 'administrator' user capability.
    }
    
    /**
     * Does the current user have full control of the plugin.
     *
     * @return boolean
     */
    public function is_admin()
    {
        return current_user_can( $this->get_admin_capability() );
    }
    
    /**
     * Capability required for managing the plugin.
     *
     * @return string
     */
    public function get_required_capability()
    {
        return apply_filters( 'bw_required_capability', 'edit_others_posts' );
        // 'editor' user capability.
    }
    
    /**
     * Can current user manage the plugin.
     *
     * @return boolean
     */
    public function is_elevated()
    {
        return current_user_can( $this->get_required_capability() );
    }
    
    /**
     * White label mode for the plugin.
     * Undocumented/WIP.
     *
     * @return boolean
     */
    public function is_white_label()
    {
        return defined( 'BOOKING_WEIR_WHITE_LABEL' ) && BOOKING_WEIR_WHITE_LABEL;
    }
    
    /**
     * Style asset handle.
     *
     * @param string $id
     * @return string
     */
    public function get_style_handle( $id )
    {
        return sprintf( '%s-%s-style', $this->plugin_slug(), $id );
    }
    
    /**
     * Script asset handle.
     *
     * @param string $id
     * @return string
     */
    public function get_script_handle( $id )
    {
        return sprintf( '%s-%s-script', $this->plugin_slug(), $id );
    }

}