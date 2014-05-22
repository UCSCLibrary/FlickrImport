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
    $errors = unserialize(get_option('flickrBackgroundError'));
    $errors[] = $this->_getParam('error');
    set_option('flickrBackgroundError',serialize($errors));
  }


  /**
   * Log error from background job into error option
   *
   * @param string $errorString An error message supplied by the 
   * background job.
   * @return void
   */
  private function _handleBackgroundError($errorString)
  {
    $errors = unserialize(get_option('flickrBackgroundError'));
    $errors[]=$errorString;
    set_option('flickrBackgroundError',serialize($errors));
  }

}
