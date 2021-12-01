<?php

namespace wp_exavault_conf
{

/**
 * Class Updater
 * ========================================================================================================
 * Responsible for handling remote updates of the plugin
 *
 * See: https://www.smashingmagazine.com/2015/08/deploy-wordpress-plugins-with-github-using-transients/
 *
 * @package lm360_sitelink_ext
 */
class Updater
{
    // PLUGIN PROPERTIES
    /**
     * file paths are the primary key for plugins in wordpress - therefore $file is this entity's pk
     * ````````````````````````````````````````````````````````````````````````````````````````````````````
     * @var
     */
    protected $file;

    /**
     * plugin metadata from the main file's PHPDoc that is captured by Wordpress
     * ````````````````````````````````````````````````````````````````````````````````````````````````````
     * @var
     */
    protected $plugin;

    /**
     * slug of the plugin path - used to check version number and to help ensure files get to their
     * proper destination
     * ````````````````````````````````````````````````````````````````````````````````````````````````````
     * @var
     */
    protected $basename;

    /**
     * whether the plugin is activated or not - this attribute is set from Wordpress
     * ````````````````````````````````````````````````````````````````````````````````````````````````````
     * @var
     */
    protected $active;

    // REPOSITORY PROPERTIES
    /**
     * repository username used for authentication
     * ````````````````````````````````````````````````````````````````````````````````````````````````````
     * @var
     */
    private $username;

    /**
     * name of the repository whose source code makes up this plugin
     * ````````````````````````````````````````````````````````````````````````````````````````````````````
     * @var
     */
    private $repository;

    /**
     * auth token returned (set) from the github server
     * ````````````````````````````````````````````````````````````````````````````````````````````````````
     * @var
     */
    private $authorize_token;

    /**
     * raw http response object returned (set) from the github server
     * ````````````````````````````````````````````````````````````````````````````````````````````````````
     * @var
     */
    private $github_response;

    /**
     * Updater constructor.
     * ====================================================================================================
     * Register the appropriate actions and attributes to allow automated update checking and ultimately,
     * secure remote update installation
     *
     * @param $file
     */
    public function __construct($file)
    {
        // store the unique identifier, the plugin's path, as a protected attribute
        $this->file = $file;

        // get the plugin data and store it accordingly
        // use admin_init to ensure the plugin functions are available in time for this code to be called
        add_action(
            'admin_init',
            array(
                $this,
                'set_plugin_properties'
            )
        );
        return $this;
    }

    /**
     * set_plugin_properties
     * ====================================================================================================
     * Get basic information about the plugin, including whether it is currently active or not
     *
     * This method is referenced in __construct() in an added action
     *
     * @noinspection PhpUnused
     */
    public function set_plugin_properties() {
        $this->plugin = get_plugin_data($this->file);
        $this->basename = plugin_basename($this->file);
        $this->active = is_plugin_active($this->file);
    }

    /**
     * setUsername
     * ====================================================================================================
     * Setter method for the username attribute
     *
     * @param $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * setRepository
     * ====================================================================================================
     * Setter method for the repository attribute
     *
     * @param $repository
     */
    public function setRepository($repository) {
        $this->repository = $repository;
    }


    /**
     * authorize
     * ===================================================================================================
     * Get the update_key from the Sitelink Authorization Service
     */
    private function authorize() {
        $acct = new Account();
        $token = $acct->authorize()['update_key'];
        $this->authorize_token = $token;
    }

    /**
     * getRepositoryInfo
     * ====================================================================================================
     * Make an api request to get the metadata regarding this plugin's repository. Depends on a valid
     * authentication.
     */
    public function getRepositoryInfo() {
        $this->authorize();
        $request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository ); // Build URI
        $response = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_uri ) ), true ); // Get JSON and parse it
        if( is_array( $response ) ) { // If it is an array
            $response = current( $response ); // Get the first item
        }
        if(!is_array($response)) {
            $this->github_response = json_decode($response);
        }
        $this->github_response = $response; // Set it to our property
        return $response;
    }

    public function initialize() {
        // modify the transient
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modifyTransient' ), 10, 1 );

        // ensure our plugin data get passed into the wordpress interface
        add_filter( 'plugins_api', array( $this, 'pluginPopup' ), 10, 3);

        // ensure plugin is activated after the update
        add_filter( 'upgrader_post_install', array( $this, 'afterInstall' ), 10, 3 );
    }

    /**
     * modifyTransient
     * ====================================================================================================
     * During the plugin update-check workflow, see if there is a new version of this plugin.
     * If there is, download the files and set them as an attribute of the given transient.
     *
     * This method is referenced in an action set during initialize()
     *
     * @param $transient
     * @return mixed
     * @noinspection PhpUnused
     */
    public function modifyTransient($transient ) {
        if( property_exists( $transient, 'checked') ) { // Check if transient has a checked property
            if( $checked = $transient->checked ) { // Did WordPress check for updates?
                $this->getRepositoryInfo(); // Get the repo info
                if(array_key_exists('tag_name', $this->github_response)) {
                    $out_of_date = version_compare( $this->github_response['tag_name'], $checked[$this->basename], 'gt' ); // Check if we're out of date
                    if( $out_of_date ) {
                        $new_files = $this->github_response['zipball_url']; // Get the ZIP
                        $slug = current( explode('/', $this->basename ) ); // Create valid slug
                        $plugin = array( // setup our plugin info
                            'url' => $this->plugin["PluginURI"],
                            'slug' => $slug,
                            'package' => $new_files,
                            'new_version' => $this->github_response['tag_name']
                        );
                        $transient->response[ $this->basename ] = (object) $plugin; // Return it in response
                    }
                } else {
                    Utilities::log('Invalid GitHub Response: ' . json_encode($this->github_response));
                }
            }
        }
        return $transient; // Return filtered transient
    }

    /**
     * pluginPopup
     * ====================================================================================================
     * Pop-up in wp-admin screen that provides details about the update / plugin. We reference plugin
     * metadata and information from the .git repository for this.
     *
     * This method is referenced in an action set during initialize()
     *
     * @param $result
     * @param $action
     * @param $args
     * @return object
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    public function pluginPopup($result, $action, $args ) {
        if( ! empty( $args->slug ) ) { // If there is a slug
            if( $args->slug == current( explode( '/' , $this->basename ) ) ) { // And it's our slug
                $this->getRepositoryInfo(); // Get our repo info
                // Set it to an array
                $plugin = array(
                    'name'              => $this->plugin["Name"],
                    'slug'              => $this->basename,
                    'version'           => $this->github_response['tag_name'],
                    'author'            => $this->plugin["AuthorName"],
                    'author_profile'    => $this->plugin["AuthorURI"],
                    'last_updated'      => $this->github_response['published_at'],
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
     * afterInstall
     * ====================================================================================================
     * Post-processing steps after the new plugin version has been pulled down and extracted.
     * Reactivates plugin after installation
     *
     * This method is referenced in an action set during initialize()
     *
     * @param $response
     * @param $hook_extra
     * @param $result
     * @return mixed
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterInstall($response, $hook_extra, $result ) {
        global $wp_filesystem; // Get global FS object

        $install_directory = plugin_dir_path( $this->file ); // Our plugin directory
        $wp_filesystem->move( $result['destination'], $install_directory ); // Move files to the plugin dir
        $result['destination'] = $install_directory; // Set the destination for the rest of the stack

        if ( $this->active ) { // If it was active
            activate_plugin( $this->basename ); // Reactivate
        }
        return $result;
    }

}

}

