<?php echo head(array('title' => 'Flickr Import')); ?>

<?php echo flash(); ?>

<?php

if(!isset($this->collection))
  $this->collection = 0;
?>

<form>

<div class="field">
 <div id="flickr-url-label" class="two columns alpha">
<label for="flickr-url"><?php echo __('Flickr URL'); ?></label>
</div>
   <div class="inputs five columns omega">
   <?php echo $this->formText('flickr-url',"",array()) ?>
<p class="explanation"><?php echo __( 'Paste the full url of the photo, photoset or gallery on Flickr which you would like to import (example: https://www.flickr.com/photos/sdasmarchives/sets/72157643807500044/)' ); ?></p>
   </div>
</div>

<div class="field">
 <div id="flickr-number-label" class="two columns alpha">
<label for="flickr-number"><?php echo __('Number of Photos'); ?></label>
</div>
<div class="inputs five columns omega">
  <label><input type="radio" name="flickr-number" value="single" id="flickr-single-radio">Single Photo</label><br>
  <label><input type="radio" name="flickr-number" value="multiple" id="flickr-multiple-radio" checked="checked">Multiple Photos (photoset or gallery)</label>
<p class="explanation"><?php echo __( 'Are you importing a single photo, or multiple photos in a photoset or gallery?' ); ?></p>
   </div>
</div>


<div class="field" id="flickr-select-div">
<div id="flickr-public-label" class="two columns alpha">
<label for="flickr-public">Select Items</label>
</div>
<div id="flickr-public-div" class="inputs five columns alpha">
<label><input type="radio" name="flickr-selecting" value="false" checked="checked"/> Import all items</label> <br>
<label><input id="flickr-select" type="radio" name="flickr-selecting" value="true" /> Select items to import</label>
<p class="explanation"><?php echo __( 'If you are importing photos from a photoset or gallery, this option allows you to select which photos to import from a list of thumbnails.' ); ?></p>
</div>
</div>

<div class="field">
 <div id="flickr-collection-label" class="two columns alpha">
<label for="flickr-collection"><?php echo __('Collection'); ?></label>
</div>
   <div class="inputs three columns omega">
   <?php echo $this->formSelect('flickr-collection',$this->collection,array('id' => 'flickr-collection'),$this->form_collection_options); ?>
<p class="explanation"><?php echo __( 'To which collection would you like to add the flickr photos?' ); ?></p>
   </div>
</div>

<div class="field">
 <div id="flickr-userrole-label" class="two columns alpha">
<label for="flickr-userrole"><?php echo __('User Role'); ?></label>
</div>
   <div class="inputs three columns omega">
   <?php echo $this->formSelect('flickr-userrole',"37",array('id' => 'flickr-userrole'),$this->form_userrole_options); ?>
<p class="explanation"><?php echo __( 'What role should the Flickr user have in the imported Omeka item metadata?' ); ?></p>
   </div>

</div>

<div class="field">
<div id="flickr-public-label" class="two columns alpha">
<label for="flickr-public">Privacy</label>
</div>
<div id="flickr-public-div" class="inputs three columns alpha">
<input type="checkbox" name="flickr-public" value="1" checked="checked"/>
<p class="explanation"><?php echo __( 'Would you like to make the new items public?' ); ?></p>
</div>
</div>


<div class="field" id="previewThumbs"></div>

<div class="field">
<button name="flickr-import-submit" type="submit">Import photos</button>
</div>


</form>
<?php echo foot(); ?>
  