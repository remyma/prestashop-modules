$(document).ready(function() {
	if(getE('id_webcontent').value) {
		$('#submitWebContentUpdate').show();
 		$('#submitWebContentAdd').hide();
	} else {
		$('#submitWebContentUpdate').hide();
 		$('#submitWebContentAdd').show();
	}
	
});

function webcontentEdition(webcontentId)
{ 
	// Update new web content block from web content list
	getE('id_webcontent').value = webcontentId;
	getE('titre').value = webcontents[webcontentId][1];
	getE('description').value = webcontents[webcontentId][2];
	getE('lien').value = webcontents[webcontentId][3];
 	$('#submitWebContentUpdate').show();
 	$('#submitWebContentAdd').hide();
 	getE('submitWebContentUpdate').setAttribute('class', 'button');
 	/* ##### IE */
 	getE('submitWebContentUpdate').setAttribute('className', 'button');
 	
 	$("#selectHook option[value='"+webcontents[webcontentId][0]+"']").attr('selected','selected');
 	$("#template option[value='"+webcontents[webcontentId][5]+"']").attr('selected','selected');
}

function webcontentDeletion(contentId, tokenModule)
{
 	document.location.replace(currentUrl+'&id_content='+contentId+'&token='+tokenModule);
}

