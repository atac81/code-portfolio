/* Disable the enter key so that the user can only use the tab key to get to the next field.         */
/* NOTE: This only applies to <input type="radio"> fields, and it does not apply to textarea fields. */
function stopRKey(evt) { 
  var evt = (evt) ? evt : ((event) ? event : null); 
  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
  if ((evt.keyCode == 13) && (node.type=="radio"))  {return false;} 
} 

document.onkeypress = stopRKey;
