<?php
/**
 * Flick import form 
 *
 * @package     FlickrImport
 * @copyright   2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * FlickrImport form class
 */
class Flickr_Form_Import extends Omeka_Form
{

    /**
     * Construct the import form.
     *
     *@return void
     */
    public function init()
    {
        parent::init();
        $this->_registerElements();
    }

    /**
     * Define the form elements.
     *
     *@return void
     */
    private function _registerElements()
    {

        //hashed anti-csrf nonce:
        //$this->addElement('hash', 'flickrNonce', array('salt'=>'oilyravinecakes'));
        
        
        //number (single or multiple):
        $this->addElement('radio', 'flickrnumber', array(
            'label'         => __('Number of Photos'),
            'value'         => 'multiple',
	    'order'         => 1,
	    'multiOptions'       => array(
		'single'=>'Single Image',
		'all'=>'All Images from a gallery, photostream, or album',
		'select'=>'Select Images from a gallery, photostream, or album'),
            'value' => 'single'
	));



        //URL:
        //for some reason the zend from element "url" isn't included with Omeka
        //so I validate with my own callback function defined below
        $this->addElement('text', 'flickrurl', array(
	    'label'         => __('Flickr URL'),
	    'description'   => __('Paste the full URL of the photo, gallery, photostream, or album from Flickr'),
	    'validators'    =>array(
		array('callback',false,array('callback'=>array($this,'validateFlickrUrl'),'options'=>array()))
	    ),
	    'order'         => 2,
	    'required'      => true,
	));
        
	// Collection:
        $this->addElement('select', 'flickrcollection', array(
	    'label'         => __('Collection'),
	    'description'   => __('To which collection would you like to add the Flickr photo(s)?'),
	    'value'         => '0',
	    'order'         => 4,
	    'multiOptions'       => $this->_getCollectionOptions()
	)
	);

	// User Role:
        $this->addElement('select', 'flickruserrole', array(
	    'label'         => __('Responsibility'),
	    'description'   => __('The Flickr user is the ____ of the image(s)'),
	    'value'         => 'Contributor',
	    'order'         => 5,
	    
	    'multiOptions'       => $this->_getRoleOptions()
	)
	);


        // Visibility (public vs private):
        $this->addElement('checkbox', 'flickrpublic', array(
            'label'         => __('Public Visibility'),
            'description'   => __('Would you like to make the imported photo(s) public on your Omeka site?'),
            //            'checked'         => 'checked',
	    'order'         => 6
	)
	);

        $apiKey = get_option('flickr_api_key');

        $this->addElement('hidden','apikey',array(
            'id' =>'apikey',
            'value' => $apiKey
        ));

	$this->addElement('hash','csrf_token');

        // Submit:
        $this->addElement('submit', 'flickrimportsubmit', array(
            'label' => __('Import Photo(s)')
        ));

	//Display Groups:
        $this->addDisplayGroup(
	    array(
		'flickrurl',
		'flickrnumber',
		'flickrcollection',
		'flickruserrole',
		'flickrpublic'
	    ),
	    'fields'
	);
	
        $this->addDisplayGroup(
	    array(
		'flickrimportsubmit'
	    ), 
	    'submit_buttons'
	);
    }

    /**
     *Process the form data and import the photos as necessary
     *
     *@return bool $success true if successful 
     */
    public static function ProcessPost()
    {
        //if we have a short url, expand it
        $_REQUEST['flickrurl'] = self::_resolveShortUrl($_REQUEST['flickrurl']);

        //if you're importing a single photo
        if(isset($_REQUEST['flickrnumber']) && $_REQUEST['flickrnumber']==='single')
	{
	    try {

	        if(self::_importSingle())
	            return('Import Successful. Go to Items or Collections to view imported image(s)');
	        else
	            throw new Exception('Import failed. You may have attempted to import a video, which are not supported.');

	    } catch(Exception $e) {
	        throw new Exception('Error importing photo. '.$e->getMessage());
	    }
	}

        //if you're importing multiple photos
        if(isset($_REQUEST['flickrnumber']) && $_REQUEST['flickrnumber']!=='single')
	    try {

	        if(self::_importMultiple())
	            return('Your Flickr photoset is now being imported. This process may take a few minutes. You may continue to work while the photos are imported in the background. You may notice some strange behavior while the photos are uploading, but it will all be over soon. In a minute, go to Items or Collections to view imported image(s).');

	    } catch(Exception $e) 
	{
	    throw new Exception('Error initializing photo import: '.$e->getMessage());
	}

        return(true);

    }


    
    /**
     * Start an asynchronous background job to import a set of photos
     *
     * @return void
     */
    private static function _importMultiple()
    {

        require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . 'import.php';

        if(isset($_REQUEST['flickrurl']))
            $url = $_REQUEST['flickrurl'];
        else
            throw new UnexpectedValueException('URL of Flickr photo was not set');

        //parse the type
        $type = self::_getType($_REQUEST['flickrurl']);

        //process optional values
        if(isset($_REQUEST['flickrcollection']))
            $collection = $_REQUEST['flickrcollection'];
        else
            $collection = 0;

        if(isset($_REQUEST['flickr-selecting'])) {
	    $selecting = true;
	    $selected = $_REQUEST['flickr-selected'];
        } else {
	    $selecting = false;
	    $selected = array();
        }

        if(isset($_REQUEST['flickrpublic']))
            $public = $_REQUEST['flickrpublic'];
        else 
            $public = false;


        if(isset($_REQUEST['flickruserrole']))
            $userRole = $_REQUEST['flickruserrole'];
        else
            $userRole = 0;

        //set up options to pass to background job
        $options = array(
	    'url'=>$url,
	    'type'=>$type,
	    'collection'=>$collection,
	    'selecting'=>$selecting,
	    'selected'=>$selected,
	    'public'=>$public,
	    'userRole'=>$userRole,
            'email'=>current_user()->email
	);

        //attempt to start the job
        try{
            $dispacher = Zend_Registry::get('job_dispatcher');
            $dispacher->sendLongRunning('FlickrImport_ImportJob',$options);
        } catch (Exception $e)
	{
	    throw($e);
	}

        return(true);
        
    }

    /**
     * Import a single photo in real time (not in the background).
     *
     * This function relies on the import form output being in the
     * $_POST variable. The form should be validated before calling this.
     *
     * @return bool $success true if no error, false otherwise
     */
    private static function _importSingle()
    {
        //include the import job class, whose static methods will import the photo
        require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . 'import.php';

        //include the phpFlickr library for interfacing with the Flickr API
        require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'phpFlickr' . DIRECTORY_SEPARATOR . 'phpFlickr.php';

        //initialize a Flickr API interface with this plugin's API key
        $f = new phpFlickr(get_option('flickr_api_key'));

        //pull the Flickr url from the form post 
        if(isset($_REQUEST['flickrurl']))
            $url = $_REQUEST['flickrurl'];
        else
            throw new UnexpectedValueException('URL of Flickr photo was not set');

        $photoID = self::_parsePhotoUrl($url);

        if(isset($_REQUEST['flickrcollection']))
            $collection = $_REQUEST['flickrcollection'];
        else
            $collection = 0;

        if(isset($_REQUEST['flickrpublic']))
            $public = $_REQUEST['flickrpublic'];
        else 
            $public = false;

        if(isset($_REQUEST['flickruserrole']))
            $userRole = $_REQUEST['flickruserrole'];
        else
            $userRole = 0;

        try{
            //retrive the photo information in the correct format to create a new Omeka item
            $post = FlickrImport_ImportJob::GetPhotoPost($photoID,$f,$collection,$userRole,$public);

            //retrieve the files associated with this photo (the photo itself, mainly)
            //in the correct format to attach to an omeka item
            $files = FlickrImport_ImportJob::GetPhotoFiles($photoID,$f);
        } catch(Exception $e) {
            throw $e;
        }

        if($post=="video")
            return(false);

        //create the item
        $record = new Item();
        $record->setPostData($post);

        if (!$record->save(false)) {
            throw new Exception($record->getErrors());
        }
        
        if(!insert_files_for_item($record,'Url',$files))
            throw new Exception("Error attaching files");
        
        return(true);

    }

    /**
     * Get an array to be used in formSelect() containing all collections.
     * 
     * @return array $options An associative array mapping collection IDs
     *to their titles for display in a dropdown menu
     */
    private function _getCollectionOptions()
    {
        $collectionTable = get_db()->getTable('Collection');
        $options = ['Assign No Collection'];
        $options = array_merge($options,$collectionTable->findPairsForSelectForm());

        return $options;
    }

    /**
     * Get an array to be used in formSelect() containing possible user roles.
     * 
     * @return array $options An associative array mapping dublin core elements
     * which could be associated with the Flickr usernames, to their display 
     * values in a dropdown menu.
     */
    private function _getRoleOptions()
    {
        $options = array(
	    '0'=>'No Role',
	    'Contributor'=>'Contributor',
	    'Creator'=>'Creator',
	    'Publisher'=>'Publisher'
	);
        return $options;
    }

    /**
     * Overrides standard omeka form behavior to fix radio display issue
     * 
     * @return void
     */
    public function applyOmekaStyles()
    {
        foreach ($this->getElements() as $element) {
            if ($element instanceof Zend_Form_Element_Submit) {
                // All submit form elements should be wrapped in a div with 
                // no class.
                $element->setDecorators(array(
                    'ViewHelper', 
                    array('HtmlTag', array('tag' => 'div')),
		    array('HtmlTag',array(
			'tag'=>'div',
			'placement' => Zend_Form_Decorator_Abstract::PREPEND,
			'id'=>"previewThumbs",
			'class'=>"field"
		    )
		    )
		));
            } else if($element instanceof Zend_Form_Element_Radio) {
                // Radio buttons must have a 'radio' class on the div wrapper.
                $element->getDecorator('InputsTag')->setOption('class', 'inputs radio five columns alpha');
		$element->getDecorator('FieldTag')->setOption('id', $element->getName().'field');
                $element->setSeparator('');
            } else if ($element instanceof Zend_Form_Element_Hidden 
                    || $element instanceof Zend_Form_Element_Hash) {
                $element->setDecorators(array('ViewHelper'));
            }
        }
    }


    /**
     * Get an array to be used in formSelect() containing possible roles for users.
     * 
     * @param string $url The Flickr url of the photos to import.
     * @return string $type The type of photo collection at the given url.
     * Possible return values are "photoset", "gallery", or "photostream".
     */
    private static function _getType($url)
    {
        $rv="";
        
        if (strpos($url, 'sets'))
            $rv="photoset";
        else if (strpos($url,'album'))
            $rv="photoset";
        else if (strpos($url,'galleries'))
            $rv="gallery";
        else if (strpos($url,'flickr.com/photos'))
            $rv = 'photostream';
        
        return $rv;
    }

    /**
     * Parse a single photo url and return the photo ID
     * 
     * @param string $url The Flickr url of the photo to import.
     * @return string $photoID The ID of the photo at the given URL
     */
    private static function _parsePhotoUrl($url)
    {
        $expUrl = explode("/",$url);

        if(count($expUrl)>1)
            $photoID = $expUrl[5];
        else
            $photoID = $url;

        return $photoID;
    }

    /**
     * Resolve a shortened URL and return the full url
     * 
     * @param string $shortUrl The shortened Flickr url of the photo to import.
     * @return string $fullUrl The full Flickr url of the photo to import.
     */
    private static function _resolveShortUrl($shortUrl)
    {
        //if the url contains 'flickr.com', it's not a short url. just return it.
        if(strpos($shortUrl,'flickr.com'))
            return($shortUrl);

        $fullUrl = self::_resolveRedirect($shortUrl);
        return $fullUrl;
    }

    /**
     * Resolve a redirect and return the redirected url
     * 
     * @param string $url The url which is redirected.
     * @return string $fullUrl The destination url.
     */
    private static function _resolveRedirect($url)
    {
        $headers = get_headers($url);
        $headers = array_reverse($headers);
        foreach($headers as $header) {
            if (strpos($header, 'location: ') === 0) {
	        $fullUrl = "https://flickr.com".str_replace('location: ', '', $header);
	        break;
            }
        }
        return $fullUrl;
    }


    /**
     * Validate the flickr url
     *
     *@param string $url The url to be validated
     *@param array $args An empty options array for now.
     *@return bool $valid Indicates whether this url points to a valid Flickr photo
     */
    public function validateFlickrUrl($url,$args){
        if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) {
            return false;
        }
        if(strpos($url,'//flic.kr'))
            $url = $this->_resolveShortUrl($url);

        if(!strpos($url,'flickr.com'))
            return false;
        
        return true;
    }


}
