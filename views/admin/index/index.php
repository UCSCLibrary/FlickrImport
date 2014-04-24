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
   <?php echo $this->formText('flickr-url',"",array('id'=>'flickr-url')) ?>
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
<p class="explanation"><?php echo __( 'Please indicate whether the URL entered above points to a single image, or to a photoset or gallery containing multiple images.' ); ?></p>
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
   <div class="inputs five columns omega">
   <?php echo $this->formSelect('flickr-collection',$this->collection,array('id' => 'flickr-collection'),$this->form_collection_options); ?>
<p class="explanation"><?php echo __( 'Please indicate to which collection you would like to add the photo or photos, or (in the case of multiple photos) whether you would like to create a new collection based on the photoset information. New collections cannot be created based on individual images at this time.' ); ?></p>
   </div>
</div>

<div class="field">
 <div id="flickr-userrole-label" class="two columns alpha">
<label for="flickr-userrole"><?php echo __('User Role'); ?></label>
</div>
   <div class="inputs five columns omega">
   <?php echo $this->formSelect('flickr-userrole',"37",array('id' => 'flickr-userrole'),$this->form_userrole_options); ?>
<p class="explanation"><?php echo __( 'Please indicate which role in the new Omeka object metadata you would like to assign to the Flickr user from whom you are downloading this.' ); ?></p>
   </div>

</div>

<div class="field">
<div id="flickr-public-label" class="two columns alpha">
<label for="flickr-public">Visibility</label>
</div>
<div id="flickr-public-div" class="inputs five columns alpha">
<input type="checkbox" name="flickr-public" value="1" checked="checked"/>
<p class="explanation"><?php echo __( 'Pleas indicate whether you would like the new items to be public.' ); ?></p>
</div>
</div>


<div class="field" id="previewThumbs"></div>

<div class="field">
<button name="flickr-import-submit" type="submit">Import photos</button>
</div>


</form>
<?php echo foot(); ?>
  