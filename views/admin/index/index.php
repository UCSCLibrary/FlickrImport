<?php

$head = array('bodyclass' => 'flickr-import', 
              'title' => html_escape(__('Flickr Import | Import Photos')));
echo head($head);
?>
<?php echo flash(); ?>
<?php echo $form; ?>
<?php echo foot(); ?>