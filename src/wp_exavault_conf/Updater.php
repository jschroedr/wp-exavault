<?php

/**
 * PHP version 7.4
 * 
 * @category Configuration
 * @package  WP_Exavault
 * @author   Jake Schroeder <jake_schroeder@outlook.com>
 * @license  GNU v3
 * @link     https://github.com/jschroedr/wp-exavault/
 * @since    1.1.0
 */

namespace wp_exavault_conf {

    /**
     * Updater Class
     * 
     * Until this plugin is on the WP Repository, we will reference the public
     * GitHub repository for updates / releases.
     * 
     * PHP version 7.4
     * 
     * @category Configuration
     * @package  WP_Exavault
     * @author   Jake Schroeder <jake_schroeder@outlook.com>
     * @license  GNU v3
     * @link     https://github.com/jschroedr/wp-exavault/
     * @since    1.1.0
     */
    class Updater
    {

        protected $file;
        protected $plugin;
        protected $basename;
        protected $active;

        private $_username;
        private $_repository;
        private $_github_response;

        /**
         * Construct a new Updater instance for WP Exavault
         * 
         * @param string $file the plugin base file to update from
         * 
         * @return Updater
         */
        public function __construct(string $file)
        {
            $this->file = $file;

            // get the plugin data and store it accordingly
            // use admin_init to ensure the plugin functions
            // are available in time for this code to be called
            add_action(
                'admin_init',
                array(
                    $this,
                    'setPluginProperties'
                )
            );
            return $this;
        }

        /**
         * Set the properties of the plugin so we may reference
         * them during the update hook from WP
         * 
         * @return void
         */
        public function setPluginProperties(): void
        {
            $this->plugin = get_plugin_data($this->file);
            $this->basename = plugin_basename($this->file);
            $this->active = is_plugin_active($this->file);
        }

        /**
         * Setter for userame attribute
         * 
         * @param string $username the github username the repo belongs to
         * 
         * @return void
         */
        public function setUsername(string $username): void
        {
            $this->username = $username;
        }

        /**
         * Setter for the repository attribute
         * 
         * @param string $repository the github repository name
         * 
         * @return void
         */
        public function setRepository($repository): void
        {
            $this->repository = $repository;
        }

        /**
         * Get and set the repository's latest release
         * 
         * @return array the latest release if available
         */
        public function getRepositoryInfo(): array
        {
            // build the url, make the request, and decode the response JSON
            $url = 'https://api.github.com/repos/' .
                $this->username . '/' .
                $this->_repository . '/releases';
            $response = wp_remote_get($url);
            $body = wp_remote_retrieve_body($response);
            $decoded = json_decode($body, true);
            // set the github response to the first release returned
            // (in other words, the most recent release)
            $currentRelease = current($decoded);
            // handle when there are not any releases available
            if (!is_array($currentRelease)) {
                $currentRelease = [];
            }
            $this->_github_response = $currentRelease;
            return $decoded;
        }

        /**
         * Set the WP hooks this class needs to listen to
         * in order to perform the update.
         * 
         * @return void
         */
        public function initialize(): void
        {
            // modify the transient
            add_filter(
                'pre_set_site_transient_update_plugins',
                [
                    $this,
                    'modifyTransient'
                ],
                10,
                1
            );

            // ensure our plugin data get passed into the wordpress interface
            add_filter(
                'plugins_api',
                [
                    $this,
                    'pluginPopup'
                ],
                10,
                3
            );

            // ensure plugin is activated after the update
            add_filter(
                'upgrader_post_install',
                [
                    $this,
                    'afterInstall'
                ],
                10,
                3
            );
        }

        /**
         * Intercept the check for a new version
         * 
         * @param object $transient the WP Transient data
         * 
         * @return object
         */
        public function modifyTransient(object $transient) : object
        {
            if (property_exists($transient, 'checked')) {
                if ($transient->checked) {
                    $this->getRepositoryInfo(); // Get the repo info
                    if (array_key_exists('tag_name', $this->github_response)) {
                        // check if we are out of date
                        $out_of_date = version_compare(
                            $this->github_response['tag_name'], 
                            $transient->checked[$this->basename], 
                            'gt'
                        );
                        if ($out_of_date) {
                            $new_files = $this->github_response['zipball_url'];
                            $slug = current(explode('/', $this->basename));
                            $plugin = array( // setup our plugin info
                                'url' => $this->plugin["PluginURI"],
                                'slug' => $slug,
                                'package' => $new_files,
                                'new_version' => $this->github_response['tag_name']
                            );
                            $transient->response[$this->basename] = (object) $plugin;
                        }
                    }
                }
            }
            return $transient; // Return filtered transient
        }

        /**
         * Provide plugin details during the update pop-up
         * 
         * @return object plugin details
         */
        public function pluginPopup() : object
        {
            $argumentArray = func_get_args();
            $result = $argumentArray[0];
            $args = $argumentArray[2];
            if (!empty($args->slug)) {
                if ($args->slug == current(explode('/', $this->basename))) {
                    $this->getRepositoryInfo(); // Get our repo info
                    // Set it to an array
                    $publishedAt = $this->github_response['published_at'];
                    $plugin = array(
                        'name'              => $this->plugin["Name"],
                        'slug'              => $this->basename,
                        'version'           => $this->github_response['tag_name'],
                        'author'            => $this->plugin["AuthorName"],
                        'author_profile'    => $this->plugin["AuthorURI"],
                        'last_updated'      => $publishedAt,
                        'homepage'          => $this->plugin["PluginURI"],
                        'short_description' => $this->plugin["Description"],
                        'sections'          => array(
                            'Description'   => $this->plugin["Description"],
                            'Updates'       => $this->github_response['body'],
                        ),
                        'download_link'     => $this->github_response['zipball_url']
                    );
                    return (object) $plugin; // Return the data
                }
            }
            return $result; // Otherwise return default
        }

        /**
         * Post-processing steps after the new plugin version has been pulled down
         * and extracted.
         * 
         * Reactivates plugin after installation
         *
         * This method is referenced in an action set during initialize()
         * 
         * @return array the installation $result data
         */
        public function afterInstall(): array
        {

            // get the third argument, which should be the result object
            $result = func_get_arg(2);

            // get the plugin directory
            $install_directory = plugin_dir_path($this->file);

            // get a global filesystem object
            // and move the new files to the plugin directory
            global $wp_filesystem;
            $wp_filesystem->move(
                $result['destination'],
                $install_directory
            );
            // set the destination for the rest of the update stack
            $result['destination'] = $install_directory;

            // activate the plugin if it was previously active
            if ($this->active) {
                activate_plugin($this->basename);
            }
            return $result;
        }
    }
}
