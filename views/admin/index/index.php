<?php

$head = array('bodyclass' => 'flickr-import', 
              'title' => html_escape(__('Flickr Import | Import Photos')));
echo head($head);
echo flash(); 

if(isset($successDialog)) 
  echo '<div id="flickr-success-dialog" title="&#x2714; SUCCESS"></div>';

echo $form; 
echo foot(); 

?>
