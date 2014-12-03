<div class="field">
    <div id="flickr-api-key-label" class="two columns alpha">
        <label for="flickr-api-key"><?php echo __('Enter the API key obtained from your Flickr account'); ?></label>
    </div>
    <div class="inputs five columns omega">
   <?php echo get_view()->formText('flickr-api-key', get_option('flickr_api_key'), 
        array()); ?>
        <p class="explanation"><?php echo __(
            'Please obtain an API key from flickr.com and enter it here. ' 
          . 'FlickrImport may produce errors until the API key is properly set.' 
        ); ?></p>
    </div>
</div>
