(function($){
  '$:nomunge'; // Used by YUI compressor.
  
  // Method: jQuery.fn.replaceText
  // 
  // Replace text in specified elements. Note that only text content will be
  // modified, leaving all tags and attributes untouched. The new text can be
  // either text or HTML.
  // 
  // Uses the String prototype replace method, full documentation on that method
  // can be found here: 
  // 
  // https://developer.mozilla.org/En/Core_JavaScript_1.5_Reference/Objects/String/Replace
  // 
  // Usage:
  // 
  // > jQuery('selector').replaceText( search, replace [, text_only ] );
  // 
  // Arguments:
  // 
  //  search - (RegExp|String) A RegExp object or substring to be replaced.
  //    Because the String prototype replace method is used internally, this
  //    argument should be specified accordingly.
  //  replace - (String|Function) The String that replaces the substring received
  //    from the search argument, or a function to be invoked to create the new
  //    substring. Because the String prototype replace method is used internally,
  //    this argument should be specified accordingly.
  //  text_only - (Boolean) If true, any HTML will be rendered as text. Defaults
  //    to false.
  // 
  // Returns:
  // 
  //  (jQuery) The initial jQuery collection of elements.
   
	$.fn.neverpo_replace_text = function( search, replace, target_tag, text_only ) {
		return this.each(function(){
		  var node = this.firstChild,
			val,
			new_val,
			
			// Elements to be removed at the end.
			remove = [];
		  
		  // Only continue if firstChild exists.
		  if ( node ) {
			
			// Loop over all childNodes.
			do {
			  
			  // Only process text nodes.
			  if ( node.nodeType === 3 && $(node).parent()[0].tagName == target_tag ) {
				
				// The original node value.
				//val = node.nodeValue;
				
				// The new value - заменяеся часть строки, тот кусок, который совпал со словарным значением
				//new_val = val.replace( search, replace );
				
				//-----------
				
				// The original node value.
				val = node.nodeValue.match(search);	//строка очищается regexp
				if( Array.isArray(val) === false ){
					return;
				}
				
				new_val = replace(val[0]); //получаем контенер с данными об объекте перевода
				//new_val = val.replace( search, replace );	//заменяем словарным выражением, как следствие значение совпадает с выводимым в консоли для исходного текста
				
				// Only replace text if the new value is actually different!
				if ( typeof new_val.translated_text != 'undefined' && new_val.translated_text !== val[0] ) {
				  
				  if ( !text_only && /</.test( new_val.translated_text ) ) {
					// The new value contains HTML, set it in a slower but far more
					// robust way.
					node.parentNode.classList.add('never-po-translated');
					if (typeof node.parentNode.neverpo_text_original == 'undefined') {
						node.parentNode.neverpo_text_original	= node.nodeValue;
						node.parentNode.use_in_all_blogs		= new_val.use_in_all_blogs;
						node.parentNode.use_in_all_pages		= new_val.use_in_all_pages;
					};
					$(node).before( new_val.translated_text );
					
					// Don't remove the node yet, or the loop will lose its place.
					remove.push( node );
				  } else {
					// The new value contains no HTML, so it can be set in this
					// very fast, simple way.
					node.parentNode.classList.add('never-po-translated');
					if (typeof node.parentNode.neverpo_text_original == 'undefined') {
						node.parentNode.neverpo_text_original	= node.nodeValue;
						node.parentNode.use_in_all_blogs		= new_val.use_in_all_blogs;
						node.parentNode.use_in_all_pages		= new_val.use_in_all_pages;
					};
					node.nodeValue = new_val.translated_text;
				  }
				}
			  }
			  
			} while ( node = node.nextSibling );
		  }
		  
		  // Time to remove those elements!
		  remove.length && $(remove).remove();
		});
	};	
	
})(jQuery);