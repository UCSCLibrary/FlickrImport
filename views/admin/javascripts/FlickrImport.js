
/**
*This function parses the provided flickr url to distinguish between
*photosets, galleries, and photostreams. It grabs the identifying
*string for the appropriate collection type. When finished, it calls
*getPhotoIDs.
*/
function parseURL(url){

    var setSplit = url.split("sets");
    var galSplit = url.split("galler");

         //gallery
    if(galSplit.length >1 && setSplit.length==1)
    {
	var glpurl = "https://api.flickr.com/services/rest/?api_key=a664b4fdddb9e009f43e8a6012b1a392&format=json&jsoncallback=?&method=flickr.urls.lookupGallery&url="+encodeURIComponent(url);
console.log(glpurl);
	jQuery.getJSON( glpurl , {format: "json"})
	    .done(function( msg ) {
		getPhotoIDs([msg.gallery.id,"gallery"]);
	    });

	//photoset
    } else if (galSplit.length ==1 && setSplit.length > 1) {
	var setID = setSplit[setSplit.length-1];
	setID = setID.replace(/\//g,"");
	getPhotoIDs([setID,"photoset"]);

       //photostream
    } else if (galSplit.length==1 && setSplit.length ==1) {
	var glpurl = "https://api.flickr.com/services/rest/?api_key=a664b4fdddb9e009f43e8a6012b1a392&format=json&jsoncallback=?&method=flickr.urls.lookupUser&url="+encodeURIComponent(url);
	jQuery.getJSON( glpurl , {format: "json"})
	    .done(function( msg ) {
		getPhotoIDs([msg.user.id,"photostream"]);
	    });
    }
}

/*
*This function returns an array of photoIDs contained within
*the given Flickr collection. It takes one parameter which is
*an array containing the identifying string for the collection
*and the collection type.
*When finished, this function calls addPhoto, which adds the
*photo to the preview div.
*/
function getPhotoIDs(args){
    var setID = args[0];
    var type = args[1];
    console.log("setID: "+setID);
    console.log("type: "+type);

    var apiBase = "https://api.flickr.com/services/rest/?api_key=a664b4fdddb9e009f43e8a6012b1a392&format=json&jsoncallback=?&method=flickr.";

    if(type == "gallery")
	url = apiBase+"galleries.getPhotos&gallery_id="+setID;
    else if (type == "photoset")
	url = apiBase+"photosets.getPhotos&photoset_id="+setID;
    else if (type == "photostream")
	url = apiBase+"people.getPublicPhotos&user_id="+setID;
	
    console.log(url);
    jQuery.getJSON( url , {format: "json"})
	.done(function( msg ) {
	    if(type=="gallery")
		addPhoto(msg.photos.photo,0);
	    else if (type=="photoset")
		addPhoto(msg.photoset.photo,0);    
	    else if (type=="photostream")
		addPhoto(msg.photos.photo,0);
	});    
}

/*
*This function retrieves a thumbnail and title for the given
*Flickr photoID and adds them with a checkbox to the photo
*preview div
*/
function addPhoto(photos,i){

    var urlBase = "https://api.flickr.com/services/rest/?api_key=a664b4fdddb9e009f43e8a6012b1a392&format=json&jsoncallback=?&method=flickr.photos.getSizes&photo_id=";

    var htmlBegin = '<div class="previewPicDiv"><input type="checkbox" name="flickr-selected['+photos[i].id+']" class="previewCheck" checked="checked"/><img class="previewPic" src="';
    var htmlMiddle = '" ><label class="previewLabel">';
    var htmlEnd = "</previewLabel></div>";

    //console.log(photos);
    console.log("pulling: "+photos[i].id);
    jQuery.getJSON( urlBase+photos[i].id , {format: "json"})
	.done(function( msg ) {
	    console.log(msg.sizes.size[2].source);
	    console.log(photos[i].title);
	    jQuery('#previewThumbs').append(htmlBegin+msg.sizes.size[2].source+htmlMiddle+photos[i].title+htmlEnd);
	    addPhoto(photos,++i);
	}); 

}

/*
*This function returns a boolean value indicating whether the 
*text in the Flickr url input constitute a valid Flickr URL
*/
function validFlickrUrl(url){
    var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
    if(!regexp.test(url))
	return false;
    if(url.indexOf("flickr.com")<0)
	return false;
    return true;
}

//Interface functions
jQuery( document ).ready(function(){
    
    //If the user decides to select individual images to import from a collection,
    //parse the URL (which will initiate adding the thumbnail images to the 
    // preview div).
    jQuery('#flickrselecting-true').click(function(e){
	var url = jQuery("#flickrurl").val();
	if(!validFlickrUrl(url)){
	    alert('Invalid Flickr Url');
	    return;
	}
	jQuery('#previewThumbs').html('');
	jQuery('#previewThumbs').show();
	jQuery('#previewThumbs').append('<input type="hidden" name="flickr-selecting" value="true"/>');
	parseURL(url);
    });

    jQuery('#flickrselecting-false').click(function(e){
	jQuery('#previewThumbs').hide(400);
    });

    //Hide the options for selecting which images to import if you 
    //click the import single button
    jQuery('#flickrnumber-single').click(function(){
	jQuery('#flickrselectingfield').hide(400);
	jQuery('#previewThumbs').hide(400);
    });

    //Show the options for selecting which images to import if you
    //click the import multiple button
    jQuery('#flickrnumber-multiple').click(function(){
	jQuery('#flickrselectingfield').show(400);
    });

    //if you change the Flickr url while you are supposed to be selecting
    //which images to import, update the preview images.
    jQuery('#flickrurl').change(function(){
	var number = jQuery('input[name="flickrnumber"]:checked').val();
	var selecting = jQuery('input[name="flickrselecting"]:checked').val();
	var url = jQuery("#flickrurl").val();
	if(number == "multiple" && selecting == "true" && validFlickrUrl(url))
	    parseURL(url);
    });

    //if the "import single" option is checked when the page loads 
    //(only happens when the form repopulates after a submission)
    //hide the item selection options.
    var single = jQuery('input[name="flickrnumber"]:checked').val();
    if(single=='single')
	jQuery('#flickrselectingfield').hide();

    //if the "select items to import" option is checked when the page loads 
    //(only happens when the form repopulates after a submission)
    //go ahead and check the other one.
    var radios = jQuery('input[name="flickrselecting"]:checked');
    if(radios.val()=='true')
	jQuery('#flickrselecting-false').prop("checked",true);
});

