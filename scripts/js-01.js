/*
	NewsOffice 2 JavaScript Libary
	Included since version 2.0.3 Beta
	Latest update: 2009-04-06 (yyyy-mm-dd)
*/

var xml_object;

//Allow easy acces to creating links in the editor
function add_link()
{
	var link_url = prompt('What is the url of the link?', 'http://');
	var link_title = prompt('What is the name of the link?', '');
	if(link_url!=null && link_title!=null)
	{
		var link_object = "<a href='" + link_url + "'>" + link_title + "</a>";
		document.getElementById('content').value += link_object;
	}
}//End function

//Allow easy acces to creating images in the editor
function add_image()
{
	var image_url = prompt('What is the url of the image?', 'http://');
	var image_title = prompt('What is the name of the image?', '');
	if(image_url!=null && image_title!=null)
	{
		var image_object = "<a href='" + image_url + "'><img src='" + image_url + "' alt='" + image_title + "'";
		if(set_html=='xhtml')
		{
			image_object += " /";
		}
		image_object += "></a>";
		document.getElementById('content').value += image_object;
	}
}//End function

/*
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	Hides (important) messages function through AJAX powered javascript.
	Added 2009-02-22 (NewsOffice 2.0.4 Beta)
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
*/
//Reset messages
function reset_messages()
{
	//Now, though AJAX, save the data to a php session to hide it for the rest of the pages, in this session.
	xml_object = load_xml_object();
	if(xml_object==null)
	{
		alert("Your browser does not support AJAX powered features! This action could not be preformed.");
		return;
	}
	//File which is requested
		var url = dir_scripts + "save_boxes.php?type=reset";
	//Request the box saver - Will put it in a session and the message(s) will not return untill the session is destroyed.
		xml_object.onreadystatechange = state_changed;
		xml_object.open("GET",url + "",true);
		xml_object.send(null);
} 

//The closer function
function important_message_hider(box_id)
{
	//Hide the message itself
	document.getElementById(box_id).style.display = 'none';
	//Now, though AJAX, save the data to a php session to hide it for the rest of the pages, in this session.
	xml_object = load_xml_object();
	if(xml_object==null)
	{
		alert("Your browser does not support AJAX powered features! This action could not be preformed.");
		return;
	}
	//File which is requested
		var url = dir_scripts + "save_boxes.php?box=" + box_id + "&type=important_message";
	//Request the box saver - Will put it in a session and the message(s) will not return untill the session is destroyed.
		xml_object.onreadystatechange = state_changed;
		xml_object.open("GET",url,true);
		xml_object.send(null);
}//End function

function message_hider(box_id)
{
	//Hide the message itself
	document.getElementById(box_id).style.display = 'none';
	//Now, though AJAX, save the data to a php session to hide it for the rest of the pages, in this session.
	xml_object = load_xml_object();
	if(xml_object==null)
	{
		alert("Your browser does not support AJAX powered features! This action could not be preformed.");
		return;
	}
	//File which is requested
		var url = dir_scripts + "save_boxes.php?box=" + box_id + "&type=message";
	//Request the box saver - Will put it in a session and the message(s) will not return untill the session is destroyed.
		xml_object.onreadystatechange = state_changed;
		xml_object.open("GET",url,true);
		xml_object.send(null);
}//End function

var set;
//Add to page
function state_changed() 
{
	if(xml_object.readyState==4)
	{
		document.getElementsByTagName("body").innerHTML = xml_object.responseText;
		//Hide the important message(s) container if no other messages are active. This will remove the 5px space above the title of the page.
		if(xml_object.responseText=='no-others')
		{
			document.getElementById('important_messages').style.display = 'none';
		}
		else if(xml_object.responseText=='reset')
		{
			if(set!='yes')
			{
				set = 'yes';
				//Set message
					var message = document.createElement('div');
					//Set style
						var attr_1 = document.createAttribute('class');
						attr_1.nodeValue = 'important';
						message.setAttributeNode(attr_1);
					//Set content
						var message_content = document.createTextNode('All your messages have succesfully been reset. They will re-appear on the next page.');
						message.appendChild(message_content);
				
				//Check if container already exists?
				var important_messages_container = document.getElementById('important_messages');
				if(important_messages_container==null)
				{
					//Create new container
					var container = document.createElement('div');
					var attr_1 = document.createAttribute('class');
					attr_1.nodeValue = 'important_messages';
					container.setAttributeNode(attr_1);
					container.appendChild(message); //Add message to container
					//Set on top of actual content
						var prev_content = document.getElementById('content_content').innerHTML;
						document.getElementById('content_content').innerHTML = '';
						document.getElementById('content_content').appendChild(container);
						document.getElementById('content_content').innerHTML += prev_content;
				}
				else
				{
					document.getElementById('important_messages').style.display = 'block';
					//Use current container
					var prev_content = document.getElementById('important_messages').innerHTML;
					document.getElementById('important_messages').innerHTML = '';
					document.getElementById('important_messages').appendChild(message);
					document.getElementById('important_messages').innerHTML += prev_content;
				}
			}
		}
	}
}//End function

/*
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	XML loading class used for dynamic loading of files..
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
*/
//Load object
function load_xml_object()
{
	var xml_object = null;
	//Attempt to request the file using the XMLHttpRequest method
	try
	{
		//Opera 8.0+, Firefox and Webkit based browers
		xml_object = new XMLHttpRequest();
	}
	//No luck, try this
	catch(e)
	{
		// Internet Explorer
		try
		{
			xml_object = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch(e)
		{
			xml_object = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xml_object;
}//End function

/*
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	Helpbox shower and hider
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
*/

var helpbox_set = 'no';
function helpbox_build()
{
	if(helpbox_set=='yes')
	{
		helpbox_destroy();
	}
	else
	{
		helpbox_set = 'yes';
		document.getElementById('helpbox_link').className = 'topnav_link_active';
		//Need help block
			var box = document.createElement('div');
			box.setAttribute('id','helpbox');
			
			//Overlay, so it will look better
				var box_overlay = document.createElement('div');
				box_overlay.setAttribute('id','helpbox_overlay');
				box.appendChild(box_overlay);
			
			//Close button
				var box_closer = document.createElement('a');
				box_closer.setAttribute('href','#helpbox_close');
				box_closer.setAttribute('id','helpbox_closer');
				box_closer.setAttribute('onclick','helpbox_destroy(); return false;');
				var closer_content = document.createTextNode('[Close]');
				box_closer.appendChild(closer_content);
				box.appendChild(box_closer);
			//Title
				var box_title = document.createElement('h1');
				var title_content = document.createTextNode('Need help?');
				box_title.appendChild(title_content);
				box.appendChild(box_title);
			//Content
				var box_content = document.createTextNode("Got some trouble understanding NewsOffice or you are having problems with you installation? Here is what you can do:");
				var list_object = document.createElement('ul');
					//LI1
						var li1 = document.createElement('li');
						//Link
							var li1_link = document.createElement('a');
							li1_link.setAttribute('id','helpbox_manuals');
							var li1_link_content = document.createTextNode("Read our manuals");
							li1_link.appendChild(li1_link_content);
						li1.appendChild(li1_link);
						list_object.appendChild(li1);
					//LI2
						var li2 = document.createElement('li');
						//Link
							var li2_link = document.createElement('a');
							li2_link.setAttribute('id','helpbox_support');
							var li2_link_content = document.createTextNode("Visit the support section");
							li2_link.appendChild(li2_link_content);
						li2.appendChild(li2_link);
						list_object.appendChild(li2);
					//LI3
						var li3 = document.createElement('li');
						//Link
							var li3_link = document.createElement('a');
							li3_link.setAttribute('id','helpbox_update');
							var li3_link_content = document.createTextNode("Check for updates");
							li3_link.appendChild(li3_link_content);
						li3.appendChild(li3_link);
						//Description
							var li3_des = document.createElement('div');
							li3_des.setAttribute('id','less');
							var li3_des_content = document.createTextNode("Updates might fix your problems.");
							li3_des.appendChild(li3_des_content);
						li3.appendChild(li3_des);
						list_object.appendChild(li3);
					//LI4
						var li4 = document.createElement('li');
						//Link
							var li4_link = document.createElement('a');
							li4_link.setAttribute('id','helpbox_website');
							var li4_link_content = document.createTextNode("Visit our website and ask for help");
							li4_link.appendChild(li4_link_content);
						li4.appendChild(li4_link);
						list_object.appendChild(li4);
				box.appendChild(box_content);
				box.appendChild(list_object);

		document.getElementById('body').appendChild(box);
		//Change description text to less_important class
		document.getElementById('less').className = 'less_important';
		//Set links
		helpbox_addlinks();
	}
}//End function

function helpbox_destroy()
{
	helpbox_set = 'no';
	if(document.getElementById('helpbox')!==null)
	{
		document.getElementById('helpbox_link').className = 'topnav_link';
		document.getElementById('body').removeChild(document.getElementById('helpbox'));
	}
}//End function

/*
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	Color preview
	Used to preview hex color codes in editors like the user group editor.
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
*/
function color_preview()
{
	//Get name
	if(typeof(document.getElementById('name'))!="undefined" && document.getElementById('name')!='null' && document.getElementById('name').value!='')
	{
		document.getElementById('color_preview').innerHTML = document.getElementById('name').value;
	}
	//Get color
	if(typeof(document.getElementById('color_preview'))!="undefined" && document.getElementById('color_preview')!='null')
	{
		//Set to a special color with additional css class
		if(document.getElementById('color').value!='')
		{
			document.getElementById('color_preview').setAttribute("class", "important");
			document.getElementById('color_preview').style.color = '#' + document.getElementById('color').value;
		}
		//Reset to non-special color
		else
		{
			document.getElementById('color_preview').style.color = '';
			document.getElementById('color_preview').setAttribute("class", "");
		}
	}
	else
	{
		document.getElementById('color_preview').style.color = '';
		document.getElementById('color_preview').setAttribute("class", "");
	}
}//End function

/*
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	Advanced options for editor
	Shows and hides the advanced options which can be activated.
	------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
*/
var registered_advanced_boxes = new Array();//Used to save every box id that is recorded
function advanced_options_show(box_id)
{
	var vmode;
	if(document.getElementById('advanced_box_content_'+box_id).style.display=='block')
	{//The block is already active, so hide it instead
		vmode = 'off';
		advanced_options_hider(box_id);
	}
	else
	{
		vmode = 'on';
		if(box_id=='description_editor') //Additional feature for description
		{
			document.getElementById('add_description_link').innerHTML = '&laquo; Hide description';
		}
		document.getElementById('advanced_box_content_'+box_id).style.display = 'block'; //Show content of block
		document.getElementById('advanced_box_hider_'+box_id).style.display = 'block';
	}
	//Now, though AJAX, save the data.
	xml_object = load_xml_object();
	if(xml_object==null)
	{
		alert("Your browser does not support AJAX powered features! This action could not be preformed.");
		return;
	}
	//File which is requested
		var url = dir_scripts + "save_boxes.php?box=" + box_id + "&type=advanced_editor&value=" + vmode;
	//Request the box saver
		xml_object.onreadystatechange = function() { if(xml_object.readyState==4) { } };
		xml_object.open("GET",url,true);
		xml_object.send(null);
}//End function
function advanced_options_hider(box_id)
{
	if(box_id=='description_editor') //Additional feature for description
	{
		document.getElementById('add_description_link').innerHTML = '&laquo; Add description';
	}
	document.getElementById('advanced_box_content_'+box_id).style.display = 'none'; //Hide content of block
	document.getElementById('advanced_box_hider_'+box_id).style.display = 'none';
}//End function

function advanced_boxes_hider(page_id)
{
	helpbox_destroy();
	if(document.getElementById('sidebar_' + page_id).style.display=='none')
	{
		//Activate
		var vmode = 'on';
		document.getElementById('sidebar_' + page_id).style.display = 'block';
		document.getElementById('mainsection_' + page_id).style.marginRight = sidebar_width;
		document.getElementById('advanced_link').innerHTML = 'Advanced';
		document.getElementById('advanced_link').className = 'topnavExp_link_active';
	}
	else
	{
		//De-activate
		var vmode = 'off';
		document.getElementById('sidebar_' + page_id).style.display = 'none';
		document.getElementById('mainsection_' + page_id).style.marginRight = '0px';
		document.getElementById('advanced_link').innerHTML = 'Simple';
		document.getElementById('advanced_link').className = 'topnavExp_link';
	}
	//Now, though AJAX, save the data.
	xml_object = load_xml_object();
	if(xml_object==null)
	{
		alert("Your browser does not support AJAX powered features! This action could not be preformed.");
		return;
	}
	//File which is requested
		var url = dir_scripts + "save_boxes.php?box=" + page_id + "&type=sidebar&value=" + vmode;
	//Request the box saver
		xml_object.onreadystatechange = function() { if(xml_object.readyState==4) { } };
		xml_object.open("GET",url,true);
		xml_object.send(null);
}//End function