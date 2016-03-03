(function($) {    
    
	var delay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout (timer);
			timer = setTimeout(callback, ms);
		};
	})();
	
	// sends the field data to module to check if there is duplicate
	var checkForDuplicates = (function(el, saveBlockerStatus){
		jQuery(".ms_duplicate_error").remove();
		jQuery("[type='submit']").attr("disabled", false);
		var val = encodeURIComponent(el.val());
		if(val.length > 0){
			var url = 'index.php?module=MSDuplicateCheck&action=MSDuplicateCheckAjax&mode=checkDuplicate&requestingModule='+app.getModuleName()+'&requestingField='+el.attr('id')+'&checkValue='+val;
	    	AppConnector.request(url).then(function(data) {
	    		if(data.result != null && data.result != ''){
	    			// found duplicate
	    			if(data.result.content.duplicate_ids.length > 0){
	    				// check if more than one ID or if the record is itself
	    				if(data.result.content.duplicate_ids.length > 1 || jQuery.inArray(app.getRecordId(), data.result.content.duplicate_ids)==-1){
	    					// append error div
	    					el.after("<div class='ms_duplicate_error' style='color:red;'>Duplicate content found!</div>");
	    					// if this should prevent saving the record, set disabled on save button
	    					if(saveBlockerStatus==1){
	    						jQuery("[type='submit']").attr("disabled", true);
	    					}
	    				}
	    			}
	    		}
	    	});
		}
	});
	
	// adds hook to all fields that are defined to be checked
    $(document).ready(function() {
    	var url = 'index.php?module=MSDuplicateCheck&action=MSDuplicateCheckAjax&mode=getDuplicateCheckFields&requestingModule='+app.getModuleName();
    	AppConnector.request(url).then(function(data) {
    		if(data.result != null && data.result != ''){
    			// check if fields in result data
    			jQuery.each( data.result.content.fields, function( i, val ) {
    				// register the keyup event
    				jQuery( "#"+val.field_htmlid ).keyup(function() {
    					// call 1000 ms after the keyup the function to check the duplicates
    					delay(function(){
    						checkForDuplicates(jQuery( "#"+val.field_htmlid ), val.save_blocker_status );
    					}, 500 );
    				});
    			});
    		}
    	});
    });
})(jQuery);