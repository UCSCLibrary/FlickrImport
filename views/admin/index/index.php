<?php

$head = array('bodyclass' => 'flickr-import', 
              'title' => html_escape(__('Flickr Import | Import Photos')));
echo head($head);
echo flash(); 

if(isset($successDialog)) {
  ?>
  <div id="flickr-success-dialog" title="&#x2714; SUCCESS"></div>
  <script>
   jQuery( function() {
       jQuery( "#flickr-success-dialog" ).dialog({
           height: 0,
           width: 250,
           resizeable: false,
           dialogClass: "flickr-success-dialog"
       });
   } );
  </script>
  <?php
}
echo $form; 
echo foot(); 

?>
