
//TODO no more javascript global variables
var flickr_preview_pagesize = 25;
var flickr_preview_photos; 
var flickr_current_page;
var flickr_selected;

/**
 *This function parses the provided flickr url to distinguish between
 *photosets, galleries, and photostreams. It grabs the identifying
 *string for the appropriate collection type. When finished, it calls
 *previewPhotos.
 */
function parseURL(url,userID){
  apikey = jQuery("input#apikey").val();
  if(userID) {
    apikey = jQuery("input#apikey").val();
    url = resolveShortUrl(url);

    var setSplit = url.split("sets");
    if(setSplit.length == 1)
      setSplit = url.split("albums");
    var galSplit = url.split("galler");

    //gallery
    if(galSplit.length >1 && setSplit.length==1)
    {
      var glpurl = "https://api.flickr.com/services/rest/?api_key="+apikey+"&format=json&jsoncallback=?&method=flickr.urls.lookupGallery&url="+encodeURIComponent(url);
      jQuery.getJSON( glpurl , {format: "json"})
        .done(function( msg ) {
	  previewPhotos(msg.gallery.id,userID,"gallery");
        });

      //photoset
    } else if (galSplit.length ==1 && setSplit.length > 1) {
      var setID = setSplit[setSplit.length-1];
      setID = setID.replace(/\//g,"");
      previewPhotos(setID,userID,"photoset");

      //photostream
    } else if (galSplit.length==1 && setSplit.length ==1) {
      previewPhotos(false,userID,"photostream");
    }

  } else {

    var glpurl = "https://api.flickr.com/services/rest/?api_key="+apikey+"&format=json&jsoncallback=?&method=flickr.urls.lookupUser&url="+encodeURIComponent(url);
    jQuery.getJSON( glpurl , {format: "json"})
      .done(function( msg ) {
        parseURL(url,msg.user.id);
      });
  }
}

/*
 *This function retrieves a list of photos in the given set
 * and calls the addPhotos function to generate a preview
 * of them, paginated as necessary.
 *When finished, this function calls addPhoto, which adds the
 *photo to the preview div.
 */
function previewPhotos(setID,userID,type){
  var apikey = jQuery('input#apikey').val();
  var apiBase = "https://api.flickr.com/services/rest/?api_key="+apikey+"&format=json&jsoncallback=?&method=flickr.";

  if(type == "gallery")
    url = apiBase+"galleries.getPhotos&gallery_id="+setID;
  else if (type == "photoset")
    url = apiBase+"photosets.getPhotos&photoset_id="+setID;
  else if (type == "photostream")
    url = apiBase+"people.getPublicPhotos";
  else 
    return;
  
  url += "&user_id="+userID;
  jQuery.getJSON( url , {format: "json"})
    .done(function( msg ) {
      flickr_preview_photos = (type == 'photoset') ? msg.photoset.photo : msg.photos.photo;
      showPage(1);
    });    
}

function resetPageButtons(numpages,currentPage=1) {
  var id;
  jQuery('#flickr-preview #page-numbers > a').remove();
  jQuery('#flickr-preview #page-numbers > div').remove();
  for (var page = 1; page <= numpages; page++) {
    id = 'flickr-preview-page-'+page;
    if (page == currentPage) 
      jQuery('#flickr-preview #page-numbers').append('<div id="'+id+'">'+page+'</div>');
    else
      jQuery('#flickr-preview #page-numbers').append('<a id="'+id+'">'+page+'</a>');
    
    jQuery('#flickr-preview #'+id).click(function(){
      jQuery('#flickr-preview a').unbind();
      showPage(parseInt(jQuery(this).html()));
    });
  }
}


function showPage(pagenum,pagesize = false) {
  if(pagesize === false)
    pagesize = flickr_preview_pagesize;
  var numPages = Math.ceil(flickr_preview_photos.length / pagesize);
  resetPageButtons(numPages,pagenum);
  jQuery('div.previewPicDiv').remove()  
  start = (pagenum-1)*pagesize;
  flickr_current_page = pagenum;
  addPhotos(start,start+pagesize);
}

/*
 *This function retrieves a thumbnail and title for the given
 *Flickr photoID and adds them with a checkbox to the photo
 *preview div
 */
function addPhotos(index,end){
  if(index >= end)
    return;
  var urlBase = "https://api.flickr.com/services/rest/?api_key=a664b4fdddb9e009f43e8a6012b1a392&format=json&jsoncallback=?&method=flickr.photos.getSizes&photo_id=";

  var htmlBegin = '<div class="previewPicDiv"><input type="checkbox" name="flickr-selected['+flickr_preview_photos[index].id+']" class="previewCheck" /><img class="previewPic" src="';
  var htmlMiddle = '" ><label class="previewLabel">';
  var htmlEnd = "</previewLabel></div>";

  jQuery.getJSON( urlBase+flickr_preview_photos[index].id , {format: "json"})
    .done(function( msg ) {
      jQuery('#flickr-preview #flickr-thumbs').append(htmlBegin+msg.sizes.size[2].source+htmlMiddle+flickr_preview_photos[index].title+htmlEnd);
      addPhotos(++index,end);
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
  if(url.indexOf("flickr.com")<0 && url.indexOf("flic.kr")<0)
    return false;
  return true;
}


/*
 *This function resolves short flickr URLs so that preview images can be 
 *generated
 */
function resolveShortUrl(url){
  if(url.indexOf("flic.kr")<0)
    return url;
}

//Interface functions
jQuery( document ).ready(function(){

  jQuery( document ).tooltip();

  jQuery('div#flickr-preview').append('<h3>Select images to import</h3><input type="hidden" name="flickr-selecting" value="true"/><div id="previewSelectButtons"><button id="previewSelectAll">Select All</button><button id="previewDeselectAll">Deselect All</button></div><div id="flickr-thumbs"></div><div id=preview-pagination><a id="flickr-preview-page-prev">&#60;</a><div id=page-numbers></div><a id="flickr-preview-page-next">&#62;</a></div><div id=preview-per-page>Per Page: <input type=text name=flickr-perpage size=4 id="flickr-perpage-input"></div>');

  //reset the form (not sure why the reset function isn't working)
  jQuery('body.flickr-import div#content form input:text').val("");

  //set the default max responses per page
  jQuery("#flickr-perpage-input").val(flickr_preview_pagesize);

  //trigger reload images on per-page change
  jQuery("#preview-per-page").change(function(){
    var newPageSize = parseInt(jQuery("#flickr-perpage-input").val());
    var newPage = Math.floor(flickr_current_page * flickr_preview_pagesize / newPageSize);
    flickr_preview_pagesize = newPageSize;
    showPage(newPage);
  });


  //If the user decides to select individual images 
  //to import from a collection,
  //parse the URL (which will initiate adding the 
  //thumbnail images to the  preview div).
  jQuery('input#flickrnumber-select').click(function(e){
    urlFieldElement = jQuery("#flickrurl").parents("div.field");
    var url = jQuery("#flickrurl").val();
    if (url==="")
      return;
    if(!validFlickrUrl(url)){
      alert('Invalid Flickr Url');
      return;
    }
    thumbs = jQuery('#flickr-preview');
    thumbs.insertAfter(urlFieldElement);
    thumbs.show();

    jQuery('#previewSelectAll').click(function(event){
      event.preventDefault();
      jQuery('.previewCheck').each(function() {
	jQuery(this).prop('checked','checked');
      });
    });

    jQuery('#previewDeselectAll').click(function(event){
      event.preventDefault();
      jQuery('.previewCheck').each(function() {
	jQuery(this).prop('checked',false);
      });
    });

    parseURL(url);
  });

  //Hide the options for selecting which images to import if you 
  //click the import single or import all buttons
  jQuery('#flickrnumber-all').click(function(e){
    jQuery('#flickr-preview').hide(400);
  });
  jQuery('#flickrnumber-single').click(function(){
    jQuery('#flickr-preview').hide(400);
  });


  //if you change the Flickr url while you are supposed to be selecting
  //which images to import, update the preview images.
  jQuery('#flickrurl').change(function(){
    var number = jQuery('input[name="flickrnumber"]:checked').val();
    var url = jQuery("#flickrurl").val();
    if(number == "select") {
      if (validFlickrUrl(url))
        parseURL(url);
      else
        alert('Invalid Flickr Url');
    }
  });
});

