<?php


/*
 * Below is a bulkbookupload ajax function and callback, 
 * complete with console.logs and echos to verify functionality
 */


function wpbooklist_bulkbookupload_action_javascript() { 
	?>
  	<script type="text/javascript" >
  	"use strict";
  	jQuery(document).ready(function($) {
	  	$("#wpbooklist-bulkbookupload-button").click(function(event){

	  		// Calling the function that will supposedly prevent computers from sleeping to further ensure backup success
			sleep.prevent();

	  		// Gather info from page
	  		var amazonAuthYes = $( "input[name='authorize-amazon-yes']" ).prop('checked');
	  		var amazonAuthNo = $( "input[name='authorize-amazon-no']" ).prop('checked');
	  		var library = $('#wpbooklist-bulkbookupload-select-library').val();
	  		var createPost = $( "input[name='bulk-upload-create-post']" ).prop('checked');
	  		var createPage = $( "input[name='bulk-upload-create-page']" ).prop('checked');
	  		var woocommerce = $( "input[name='bulk-upload-create-woo']" ).prop('checked');
	  		var isbnString = $('#wpbooklist-bulkbookupload-textarea').val();
	  		var statusDiv = $('#wpbooklist-bulkbookupload-status-div');
	  		var spinner = $('#wpbooklist-spinner-bulkbookupload');
	  		var titleResponseDiv = $('#wpbooklist-bulkbookupload-title-response');
	  		var addButton = $('#wpbooklist-bulkbookupload-button');
	  		var smileIcon = $('#wpbooklist-smile-icon-1');
	  		var titleResponse = '';
	  		var totalAdded = 0;
	  		var scrollTop = 0;
	  		var amazonAuthQuestion = $("#auth-amazon-question-label");
	  		var errorFlag = false;
	  		var errorCounter = 0;
	  		var failedIsbns = '';
	  		var isbnIterator = 0;

	  		// Reset UI elements
	  		titleResponseDiv.animate({'opacity':'0', 'height':'0px'}, 1000);
	  		statusDiv.animate({'opacity':'0', 'margin-bottom':'0px'}, 1000);

	  		// Trim a possible comma and whitespace from the end of the string
	  		isbnString.replace(/,\s*$/, "");

	  		// Create ISBN array
	  		var isbnArray = isbnString.split(',');

	  		var totalIsbns = isbnArray.length;

	  		// Estimate completion time
	  		var estimateTime = totalIsbns*4
	  		estimateTime = Math.round(estimateTime/60);
	  		if(estimateTime < 1){
	  			estimateTime = 'Less Than 1 Minute';
	  		} 
	  		if(estimateTime == 1){
	  			estimateTime = '1 Minute';
	  		}
	  		if(estimateTime > 1){
	  			estimateTime = estimateTime+' Minutes';
	  		}

	  		// Check Amazon Authorization
		    if(amazonAuthYes === false && amazonAuthNo === false){
				amazonAuthQuestion.css({'font-weight':'bold','color':'red'});
				scrollTop = amazonAuthQuestion.offset().top-50;
				errorFlag = true;
				// Scroll the the highest flagged element 
				if(scrollTop != 0){
				  $('html, body').animate({
				    scrollTop: scrollTop
				  }, 500);
				  scrollTop = 0;
				}
		    }
	  		
	  		// If Amazon Auth has been selected (one way or another), then proceed.
		    if(errorFlag === false){
		    	addButton.prop('disabled', true);
		  		spinner.animate({'opacity':'1'}, 1000);
		  		statusDiv.animate({'opacity':'1', 'margin-bottom':'90px'}, 1000);
		  		statusDiv.html('<p>Adding <span class="wpbooklist-color-orange-italic">'+totalIsbns+'</span> Books...</p><p>Total Estimated Time: '+estimateTime+'</p>');

		  		// One Ajax call per ISBN number
		  		//for (var i = isbnArray.length - 1; i >= 0; i--) {
		  		(function wpbooklist_bulk_add_book_worker() {
		  			isbnArray[isbnIterator] = isbnArray[isbnIterator].replace(/-/g,'');
		  			
		  			var data = {
						'action': 'wpbooklist_bulkbookupload_action',
						'security': '<?php echo wp_create_nonce( "wpbooklist_bulkbookupload_action_callback" ); ?>',
						'amazonAuthYes':amazonAuthYes,
						'library':library,
						'createPost':createPost,
						'createPage':createPage,
						'woocommerce':woocommerce,
						'isbn':isbnArray[isbnIterator]
					};

					var request = $.ajax({
					    url: ajaxurl,
					    type: "POST",
					    data:data,
					    timeout: 0,
					    success: function(response) {
					    	response = response.split('---sep---sep---');
/*
					    	var apicallreport = response[2];
					    	console.log(apicallreport)


					    	var whichapifound = JSON.parse(response[3]);
					    	console.log("Here's the report for where the this book's data was obtained from:");
					    	console.log(whichapifound)

					    	var amazonapifailcount = response[4];
					    	console.log('The Amazon Fail Count was: '+amazonapifailcount);
*/
					    	//console.log(response);
					    	// If the ajax call was succesful but the book wasn't found or some other error retreiving the book information occurred (probably due to a bad ISBN number)
					    	if(response[0] == '' || response[0] == 'undefined' || response[0] == undefined){
					    		failedIsbns = failedIsbns+','+response[1];
					    		errorCounter++;
					    	} else {
					    		totalAdded++;
					    		// Handle UI progress updates
						    	titleResponseDiv.scrollTop(titleResponseDiv.prop("scrollHeight"));
						    	titleResponseDiv.animate({'opacity':'1'}, 1000);
						    	smileIcon.animate({'opacity':'1'}, 1000);
						   		titleResponseDiv.css({'height':'155px'});
						    	titleResponse = titleResponse+" Added<br/><span class='wpbooklist-bulkbookupload-response-span'>'"+response[0]+"'</span><br/>";
						    	titleResponseDiv.html(titleResponse);

						    	statusDiv.html('<p>Adding <span class="wpbooklist-color-orange-italic">'+totalIsbns+'</span> Books...</p><p>Total Estimated Time: '+estimateTime+'</p><p>Succesfully Added <span class="wpbooklist-color-orange-italic">'+totalAdded+'</span> books!<img id="wpbooklist-smile-icon-1" src="<?php echo ROOT_IMG_ICONS_URL; ?>smile.png" /><p>');
					    	}

					    	// handling UI stuff once all books have made an attempt to be added
					    	if(totalAdded == (totalIsbns-errorCounter)){
					    		spinner.animate({'opacity':'0'}, 1000);
					    		failedIsbns = failedIsbns.replace(/^,|,$/g,'');
					    		var failedIsbnsArray = failedIsbns.split(',');
					    		var failedIsbnsArrayUnique = [];

					    		// Making the failed ISBN unique array
								$.each(failedIsbnsArray, function(i, el){
								    if($.inArray(el, failedIsbnsArrayUnique) === -1) failedIsbnsArrayUnique.push(el);
								});

								// Creating ISBN error message
								var errorReportString = '';
								if(failedIsbnsArrayUnique.length > 0 && failedIsbnsArrayUnique[0] != ''){
									for (var i = failedIsbnsArrayUnique.length - 1; i >= 0; i--) {
										if(failedIsbnsArrayUnique[i] != 'undefined' && failedIsbnsArrayUnique[i] != undefined){
											errorReportString = errorReportString+'<p class="wpbooklist-bulkbookupload-error-isbn">'+failedIsbnsArrayUnique[i]+'</p>'
										}
									}
									titleResponseDiv.html('<p id="wpbooklist-bulkbookupload-isbn-error-message"><span class="wpbooklist-color-orange-italic">WPBookList</span> had trouble finding information for these ISBN Numbers:</p>'+errorReportString);
									titleResponseDiv.animate({ scrollTop: 0 }, "fast");
								}

								console.log('Here\'s an array of all the failed ISBN/ASIN numbers:')
					    		console.log(failedIsbnsArrayUnique);
					    		addButton.prop('disabled', false);
					    	}
					    	// TODO: Update UI here - create message like 'succesfully added x books', or 'add booktitle succesfully!'
					    },
						error: function(jqXHR, textStatus, errorThrown) {
							console.log(errorThrown);
				            console.log(textStatus);
				            console.log(jqXHR);
				            errorCounter++;
						},
						complete: function() {
							isbnIterator++;
					      	// Schedule the next request when the current one's complete, if we're not doen already
					      	if(totalAdded != (totalIsbns-errorCounter)){
								setTimeout(wpbooklist_bulk_add_book_worker, 1000);
							}
					    }
					});
				})();
		  		//}; 

		  		
	  		}

	     	

			event.preventDefault ? event.preventDefault() : event.returnValue = false;
	  	});
	});
	</script>
	<?php
}

// Callback function for creating backups
function wpbooklist_bulkbookupload_action_callback(){
	global $wpdb;
	check_ajax_referer( 'wpbooklist_bulkbookupload_action_callback', 'security' );

	$isbn = '';
	$amazon_auth_yes = '';
	$library = '';
	$page_yes = '';
	$post_yes = '';
	$woocommerce = '';

	if(isset($_POST['isbn'])){
		$isbn = filter_var($_POST['isbn'],FILTER_SANITIZE_STRING);
	}

	if(isset($_POST['amazonAuthYes'])){
		$amazon_auth_yes = filter_var($_POST['amazonAuthYes'],FILTER_SANITIZE_STRING);
	}

	if(isset($_POST['library'])){
		$library = filter_var($_POST['library'],FILTER_SANITIZE_STRING);
	}

	if(isset($_POST['createPage'])){
		$page_yes = filter_var($_POST['createPage'],FILTER_SANITIZE_STRING);
	}

	if(isset($_POST['createPost'])){
		$post_yes = filter_var($_POST['createPost'],FILTER_SANITIZE_STRING);
	}

	if(isset($_POST['woocommerce'])){
		$woocommerce = filter_var($_POST['woocommerce'],FILTER_SANITIZE_STRING);
	}

	$book_array = array(
		'amazon_auth_yes' => $amazon_auth_yes,
		'library' => $library,
		'use_amazon_yes' => 'true',
		'isbn' => $isbn,
		'page_yes' => $page_yes,
		'post_yes' => $post_yes,
		'woocommerce' => $woocommerce
	);

	require_once(CLASS_BOOK_DIR.'class-wpbooklist-book.php');
	$book_class = new WPBookList_Book('addbulk', $book_array, null);
	$insert_result = $book_class->add_result;

	// If book added succesfully, get the ID of the book we just inserted, and return the result and that ID
	if($insert_result == 1){
		$book_table_name = $wpdb->prefix . 'wpbooklist_jre_user_options';
  		$id_result = $wpdb->get_var("SELECT MAX(ID) from $library");
  		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $library WHERE ID = %d", $id_result));


  		echo $book_class->title.'---sep---sep---'.$book_class->isbn;

/*
  		echo $book_class->title.'---sep---sep---'.$book_class->isbn.'---sep---sep---'.$book_class->apireport.'---sep---sep---'.json_encode($book_class->whichapifound).'---sep---sep---'.$book_class->apiamazonfailcount;
*/

	}
	wp_die();
}




?>