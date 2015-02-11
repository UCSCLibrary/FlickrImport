<?php
/**
 * Flickr plugin
 *
 * @package     FlickrImport
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * FlickrImport plugin class
 */
class FlickrImportPlugin extends Omeka_Plugin_AbstractPlugin
{
  /**
   * @var array Hooks for the plugin.
   */
  protected $_hooks = array('install','uninstall','initialize','define_acl','admin_head','config','config_form');

  /**
   * @var array Filters for the plugin.
   */
  protected $_filters = array('admin_navigation_main');

  /**
   * @var array Options and their default values.
   */
  protected $_options = array(
			      'flickrBackgroundErrors' => '',
			      'flickr_api_key' => ''
			      );

  /**
   * Require the job file
   *
   * @return void
   */
  public function hookInitialize()
  {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . 'import.php';

  }

  /**
   * Install the options
   *
   * @return void
   */
  public function hookInstall()
  {
    $this->_installOptions();
  }

  /**
   * Uninstall the options
   *
   * @return void
   */
  public function hookUninstall()
  {
    $this->_uninstallOptions();
  }


  /**
   * Queue the javascript and css files to help the form work.
   *
   * This function runs before the admin section of the sit loads.
   * It queues the javascript and css files which help the form work,
   * so that they are loaded before any html output.
   *
   * @return void
   */
  public function hookAdminHead()
  {
    queue_js_file('FlickrImport');
    queue_css_file('FlickrImport');
       
  }



  /**
   * Define the plugin's access control list.
   *
   * @param array $args This array contains a reference to
   * the zend ACL under it's 'acl' key.
   * @return void
   */
  public function hookDefineAcl($args)
  {
    $args['acl']->addResource('FlickrImport_Index');
    $args['acl']->allow('contributor','FlickrImport_Index');
  }

   
  /**
   * Add the SedMeta link to the admin main navigation.
   * 
   * @param array $nav Array of links for admin nav section
   * @return array $nav Updated array of links for admin nav section
   */
  public function filterAdminNavigationMain($nav)
  {
    $nav[] = array(
		   'label' => __('Flickr Import'),
		   'uri' => url('flickr-import'),
		   'resource' => 'FlickrImport_Index',
		   'privilege' => 'index'
		   );
    return $nav;
  }


    /**
     * Display the plugin config form.
     */
    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/forms/config_form.php';
    }

    /**
     * Set the options from the config form input.
     */
    public function hookConfig()
    {
        set_option('flickr_api_key', $_POST['flickr-api-key']);
    }
    
}
