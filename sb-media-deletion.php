<?php
/**
 * Plugin Name: Sb Media Deletion
 * Plugin URI: https://themeforest.net/user/scriptsbundle/
 * Description: This plugin will allow the addition of photos to post categories while preventing administrators from removing any attached media from posts.
 * Version: 1.0
 * Author: Scripts Bundle
 * Author URI: https://themeforest.net/user/scriptsbundle/
 * License: GPL2
 * Text Domain: sb-media-deletion
 */

define('SB_DIR_PATH', plugin_dir_path(__FILE__));
define('SB_DIR_URL', plugin_dir_url(__FILE__));


if (!class_exists('Sb_media_deletion')) {

    class Sb_media_deletion {

        /**
         * Constructor
         */
        public function __construct() {
            $this->setup_actions();
            $this->require_plugin_files();
            add_action('init', array($this, 'Sb_media_deletion_plugin_textdomain'), 0);
        }

    
        public function Sb_media_deletion_plugin_textdomain() {
            $locale = apply_filters('plugin_locale', get_locale(), 'Sb_media_deletion');
            $dir = trailingslashit(WP_LANG_DIR);
            load_textdomain('Sb_media_deletion', plugin_dir_path(__FILE__) . "languages/Sb_media_deletion" . $locale . '.mo');
            load_plugin_textdomain('Sb_media_deletion', false, plugin_basename(dirname(__FILE__)) . '/languages');
        }

        /**
         * Setting up Hooks
         */
        public function setup_actions() {
            //Main plugin hooks
            register_activation_hook(SB_DIR_PATH, array($this, 'activate_Sb_media_deletion'));
            register_deactivation_hook(SB_DIR_PATH, array($this, 'deactivate_Sb_media_deletion'));
            add_action('wp_enqueue_scripts', array($this, 'sb_enqueue_scripts'));
        }

        public function sb_enqueue_scripts() {
        }

        /**
         * Activate callback
         */
        public static function activate_Sb_media_deletion() {
            //Activation code in here
        }

        /**
         * Deactivate callback
         */
        public static function deactivate_Sb_media_deletion() {
            //Deactivation code in here
        }

        private function require_plugin_files() {
            //Files to require
           require_once SB_DIR_PATH . '/inc/categories-images.php';
           require_once SB_DIR_PATH . '/inc/media-handler.php';
        }
    }
    // instantiate the plugin class
    $wp_plugin_template = new Sb_media_deletion();
}
