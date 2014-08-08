<?php
/**
 * FlickrImport
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The FlickrImport index controller class.
 *
 * @package FlickrImport
 */
class FlickrImport_IndexController extends Omeka_Controller_AbstractActionController
{    
 
/**
 * The default action to display the import from and process it.
 *
 * This action runs before loading the main import form. It 
 * processes the form output if there is any, and populates
 * some variables used by the form.
 *
 * @param void
 * @return void
 */
  public function indexAction()
  {
    include_once(dirname(dirname(__FILE__))."/forms/ImportForm.php");
    $form = new Flickr_Form_Import();

    //initialize flash messenger for success or fail messages
    $flashMessenger = $this->_helper->FlashMessenger;

    $flickr_api_key = get_option('flickr_api_key');
    if(empty($flickr_api_key)) {
      $flashMessenger->addMessage('Your Flickr API key has not been set. Please configure the FlickrImport plugin and set your API keys before attempting to import any photos.','error');
      $this->view->form = "";
      return;
    }

    try{
      if ($this->getRequest()->isPost()){
	if($form->isValid($this->getRequest()->getPost()))
	  $successMessage = Flickr_Form_Import::ProcessPost();
	else 
	  $flashMessenger->addMessage('Invalid Flickr photo data! Check your form entries.','error');
      } 
    } catch (Exception $e){
      $flashMessenger->addMessage($e->getMessage(),'error');
    }

    $backgroundErrors = unserialize(get_option('flickrBackgroundErrors'));
    if(is_array($backgroundErrors))
      foreach($backgroundErrors as $backgroundError)
	{
	  $flashMessenger->addMessage($backgroundError,'error');
	}
    set_option('flickrBackgroundErrors',"");

    if(isset($successMessage))
      $flashMessenger->addMessage($successMessage,'success');
    $this->view->form = $form;
      
  }

/**
   * An action called by the background import job to log errors
   * for later display.
   *
   * @param void
   * @return void
   */
  public function errorAction()
  {
    $errors = unserialize(get_option('flickrBackgroundErrors'));
    $errors[] = $this->_getParam('error');
    set_option('flickrBackgroundErrors',serialize($errors));
  }

}
