<?php
/* 
----------------------------------------------------------------------
.Nzr extra additional functions.
	The .Nzr class starts below.
----------------------------------------------------------------------
*/
function nzr_sort_latest($a, $b)
{
	/* 
	----------------------------------------------------------------------
	.Nzr Reverse natsort (latest first)
		This function is, outside the class unfortunatly, used in the sort
		function for using a similar sort to natsort(), but then in
		a little different (reverse).
	----------------------------------------------------------------------
	*/
	return -1 * strnatcmp ($a, $b);
}//End function

function nzr_sort_oldest($a, $b)
{
	/* 
	----------------------------------------------------------------------
	.Nzr Reverse natsort (oldest first)
		This function is, outside the class unfortunatly, used in the sort
		function for using a similar sort to natsort(), but then in
		a little different (revesve).
	----------------------------------------------------------------------
	*/
	return 1 * strnatcmp ($a, $b);
}//End function

class newanz_nzr
{
	/*
	----------------------------------------------------------------------
	.Nzr Class
		Reading .Nzr type files and returning usuable PHP arrays
		configured to the user's specifications.
	A Newanz project
	Copyright (c) Newanz.com
	----------------------------------------------------------------------
	Version: 1.0 Beta 2.2
	Author: Tom de Bruijn
	Created: 2008-08-24 (yyyy-mm-dd)
	Last updated: 2009-04-22 (yyyy-mm-dd)
	----------------------------------------------------------------------
	Not for personal usage outside NewsOffice. A fully supported
	stand-alone version of the class will be released later on.
	This version is modified to be integrated into NewsOffice more easy.
	----------------------------------------------------------------------
	1.0 Beta 2 changes:
	- In PHP class.
	- PHP 5+ only.
	- Cleaner coding, written on error_reporting(E_ALL); and no errors/
	  notices occured. (Before March 2009 that is.)
	- No longer fixed columns.
	- Easy to manipulate records by filter, sort, save and delete
	  functions.
	- .Nzr register and memory to make it easier to save, delete and
	  search results.
	- Add columns.
	- Change column names.
	- Use primary key (no real advantage yet, planning is to index these
	  values).
	- Primary auto keys; primary key with autoincrement functionality.
	- Extended error reporting.
	----------------------------------------------------------------------
	*/

	//Declare variables used inside the .Nzr class
	private $file;
	private $mode_read;
	private $mode_save;
	private $latest_action;
	private $record_save = false;
	public $errors_amount = 0;
	public $result = true;
	public $original = '';
	public $keys = array();
	public $content = array();
	public $amount_rows = 0;
	public $amount_columns = 0;
	private $max_amount = array();
	protected $register = array();
	protected $memory = array();
	private $parameters = array();
	
	private $primary_keys = array();
	private $auto_keys = array();
	
	protected $tmp_action;
	
	//Boot function: Check settings
	function __construct($file='',$mode='')
	{
		/*
		----------------------------------------------------------------------
		.Nzr Boot process
			Loads default settings.
			The file is not required. It's also possible to "hack" an
			preformated string into the class after starting the class.
			
			Selecting a mode will influence the handeling of certain functions
			inside the .Nzr class. See the mode(); function.
		----------------------------------------------------------------------
		*/
		$this->latest_action = 'start';
		$this->mode($mode);
		if(!empty($file))
		{
			$this->file = $file;
			if($this->mode_save!=='CREATE')
			{
				//Load file content
				if(($file_content = @file_get_contents($this->file))!==false)
				{
					//Everything went okay
					$this->original = $file_content;
				}
				else
				{
					//Something went wrong
					if(substr_count($this->file,'http://')>0 || substr_count($this->file,'https://')>0 || substr_count($this->file,'ftp://')>0)
					{
						//This is an online file
						$this->error(19);
					}
					elseif(file_exists($this->file)==false)
					{
						//Local file does not exists
						$this->error(20);
					}
				}
			}
		}
	}//End boot function
	
	public function create_file($keys,$override=false)
	{
		/* 
		----------------------------------------------------------------------
		.Nzr file creator
			Creates a .Nzr file.
			
			Select the keys with an array('<primarykey>','key2','key3') and
			this function will create it with the given keys, but without any
			rows.
			
			Override = Clean file even when it exists, not recommended, but 
			we needed it for the converter and it might be usefull. If it's
			not set to true it will give back an error.
		----------------------------------------------------------------------
		*/
		if(substr_count($this->file,'http://')>0 || substr_count($this->file,'https://')>0 || substr_count($this->file,'ftp://')>0)
		{
			//This is an online file, can not create such a file
			$this->error(25);
		}
		elseif(file_exists($this->file)==true && $override==false)
		{
			//File already exists and the override is not set to true
			$this->error(31);
		}
		else
		{
			$nzr_keys = '';
			foreach($keys as $key)
			{
				$nzr_keys .= '['.$key.']';
				if($key==$keys[count($keys)-1])
				{
					$nzr_keys .= ';';
				}
				$this->keys[] = $key;
			}
			//Save the new generated .Nzr file
			if(($nzr_file_open = @fopen($this->file, "w"))==true)
			{
				//File creation a succes
				fwrite($nzr_file_open, $nzr_keys);
				fclose($nzr_file_open);
				$this->saved_content = $nzr_keys;
			}
			else
			{
				//Could not save
				$this->error(28);
			}
		}
	}//End function
	
	public function set_store()
	{
		/* 
		----------------------------------------------------------------------
		.Nzr settings exporter
			Allows you to export all the usefull variables to an array so you
			can use them outside the class and/or import/"hack" them in another
			.Nzr object.
		----------------------------------------------------------------------
		*/
		foreach($this as $key=>$value)
		{
			$tmp[$key] = $value;
		}
		return $tmp;
	}//End function
	
	public function set_import($tmp)
	{
		/*
		----------------------------------------------------------------------
		.Nzr settings importer
			Allows you to import/"hack" all the usefull variables into this
			object so you can resume your actions in this object like you
			were still working in the other object you got these variables
			from.
		----------------------------------------------------------------------
		*/
		foreach($tmp as $key=>$value)
		{
			$this->$key = $value;
		}
	}//End function
	
	private function mode($given_mode='')
	{
		/* 
		----------------------------------------------------------------------
		.Nzr Mode selector
			Select the mode you want to handle this .Nzr file with. You can
			set this through the class opener:
			$nzr = new newanz_nzr($file, $mode);
		
		Access:
			Access from outside is not allowed, that will take away the whole use of modes.
			
		Modes:
			'STRICT'
				Do not continue on errors
			'LOOSE'
				Ignore errors
			'READ_ONLY'
				Only read files and do not allow modifications to the file.
				Filter and rekey functions are allowed on the returned
				content.
			'MULTIPLE_SAVES_FRIENDLY'
				When you delete or save a lot of records on one .Nzr file
				you might consider this mode as it will write the changes to
				the file when you close the class with the close(); function.
		---------------------------------------------------------------------
		*/
		$registered_modes = array('STRICT','LOOSE','CREATE','READ_ONLY','MULTIPLE_SAVES_FRIENDLY');
		$given_mode = strtoupper($given_mode); //Only working with UPPERCASE words here
		if(empty($given_mode)) { $given_mode = 'STRICT'; } //Default mode
		//Valid mode?
		if(in_array($given_mode,$registered_modes)==false)
		{
			//Error: Invalid mode
			$this->error(26);
			$given_mode = 'STRICT';
		}
		//Options for each mode
		if($given_mode=='MULTIPLE_SAVES_FRIENDLY')
		{
			$mode_read = 'STRICT';
			$mode_save = 'MSF';
		}
		elseif($given_mode=='CREATE')
		{
			$mode_read = 'NOTALLOWED';
			$mode_save = 'CREATE';
		}
		elseif($given_mode=='READ_ONLY')
		{
			$mode_read = 'STRICT';
			$mode_save = 'NOTALLOWED';
		}
		elseif($given_mode=='LOOSE')
		{
			$mode_read = 'LOOSE';
			$mode_save = 'NORMAL';
		}
		else
		{
			$mode_read = 'STRICT';
			$mode_save = 'NORMAL';
		}
		//Return to function
		$this->mode_read = $mode_read;
		$this->mode_save = $mode_save;
	}//End function

	private function params($array,$mode=true,$array_mode=true)
	{
		/*
		----------------------------------------------------------------------
		.Nzr Parameter converter.
			This function gives back an workable .Nzr parameter array.
		----------------------------------------------------------------------
		*/
		$params = array();
		if(is_array($array)==false)
		{
			$this->error(34);
		}
		else
		{
			foreach($array as $key=>$value)
			{
				if($mode==true && in_array($key,$this->keys)==false)
				{
					//Key does not exists. Show error and stop.
					$this->error(33);
					return $params;
				}
				else
				{
					if(is_array($value)==true)
					{
						foreach($value as $val)
						{
							$params[$key][] = $val;
						}
					}
					else
					{
						if($array_mode==true)
						{
							$params[$key] = array($value);
						}
						else
						{
							$params[$key] = $value;
						}
					}
				}
			}
		}
		$this->parameters = $params;
		return $params;
	}//End function

	public function encode($object)
	{
		/*
		----------------------------------------------------------------------
		.Nzr Encoder
			Turns dirty variables (Exceptions) into .Nzr friendly variables
		----------------------------------------------------------------------
		*/
		$object = nl2br($object);
		$object = str_replace('<br />','<br>',$object);
		$object = htmlentities($object,ENT_QUOTES);
		$object = str_replace(';','<!--nzr-convert-dot-comma-->',$object);
		//Just in case
			$object = str_replace("\n",'',$object);
			$object = str_replace("
",'',$object);
			$object = preg_replace("/[\n\r]/",'',$object); //This removes line breaks
		if(function_exists('get_magic_quotes_gpc')==true && get_magic_quotes_gpc()==1)
		{
			$object = stripslashes($object);
		}
		return $object;
	}//End function

	public function decode($object)
	{
		/*
		----------------------------------------------------------------------
		.Nzr Decoder
			Filters out any exceptions for objects that aren't allowed in
			the .Nzr files.
			Works for arrays and strings.
		----------------------------------------------------------------------
		*/
		if(is_array($object)==false)
		{
			$object = str_replace('<!--nzr-convert-dot-comma-->',';',$object);
			$object = html_entity_decode($object,ENT_QUOTES);
		}
		else
		{
			//Execute every action for every value in this array and return the modified array.
			foreach($object as $key=>$obj)
			{
				$new_object[$key] = $obj;
				$new_object[$key] = str_replace('<!--nzr-convert-dot-comma-->',';',$new_object[$key]);
				$new_object[$key] = html_entity_decode($new_object[$key],ENT_QUOTES);
			}
			$object = $new_object;
		}
		return $object;
	}//End function

	public function readfile($limit=0)
	{
		/*
		----------------------------------------------------------------------
		.Nzr File reader: Converter
			Converts the .Nzr file content to a usuable PHP array.
			
		$limit = (type: int) Specify the amount of records that should
			be returned.
		----------------------------------------------------------------------
		*/
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			$filter_this = array(); //To avoid in_array error
			$line_number_fix = false; //Default value
			$i = 'empty';	//For first run
			$skip_error = false; //For first run
			$affected_records = 0; //Default value
			$this->comments = array(); //Avoid notice
			$done_limit = 0; //Default value
			$nzr_auto_keys = array();
			$nzr_primary_keys = array();

			if(empty($this->original) || strlen($this->original)<=1) {} //No content; do nothing, and no error, because .Nzr files can be totally empty
			else
			{
				$nzr_content = explode("\n",$this->original);
				if(empty($nzr_content))
				{
					//Error!: File empty
					$this->error(2);
				}
				else
				{
					foreach($nzr_content as $line_key=>$nzr_line)
					{
						if($i!=='empty' && $skip_error!==true) //Don't run on first go and skip this step when .Nzr just read the keys
						{
							if($i!==count($nzr_keys)) //The column count does not match
							{
								//Error!: Column count does not match, might break results.
								if($i<count($nzr_keys) && $i>0)
								{
									$tmp_extra = "More columns are found then were used. This doesn't break the results that much.";
								}
								else
								{
									$tmp_extra = "Less columns are found then were used. This breaks the returned results. Missing keys might be returned with an ID instead of a key. .Nzr Error 3 might show as well in the error report. This has the same origin.";
								}
								$this->error(9,"Found columns keys: \"".count($nzr_keys)."\". Columns used (Line \"".($line_number+1)."\"): \"".$i."\". ".$tmp_extra);
							}
						}
						if($skip_error==true) //Skip error reset
						{
							$skip_error = false;
						}
						$i = 0;
						//The obvious error; empty line can not contain .Nzr variables. We allow it, for now.
						if(!empty($nzr_line))
						{
							//The first line is a key line; specified by the user. So do a checkup as well: [ and ]; should exist
							if($nzr_line==$nzr_content[0] && substr_count($nzr_line,'];')>0 && substr_count($nzr_line,'[')>0)
							{
								//Removing comments
									$comment = strrchr($nzr_line,'];');
									$nzr_vars = str_replace($comment,']',$nzr_line);
									//Saving comments
										$comment = str_replace('];','',$comment); //Remove end of line
										if(!empty($comment))
										{
											$line_number = $line_key; //Current line number
											//Keys might be present
											if($line_number_fix==true)
											{
												//Fix the line number; the keys line is not counted as a record line.
												$line_number--;
											}
											//Save comments for later on
											$this->comments['keys'] = $comment;
										}
								//Remove comments
									$nzr_keys = str_replace(strrchr($nzr_line,'];'),']',$nzr_line);
								//Save to keys to array
									$nzr_keys = explode(']',$nzr_keys);
									if(!empty($nzr_keys))
									{
										//Clean up
											$nzr_keys = str_replace('[','',$nzr_keys);
										//Remove phantom last key
											unset($nzr_keys[count($nzr_keys)-1]);
										//Find primary keys
										foreach($nzr_keys as $nzr_key)
										{
											//Find primary auto keys
											if(preg_match("#\<(.*?)\>\+#",$nzr_key)>0)
											{
												$nzr_key = preg_replace("#\<(.*?)\>\+#",'\\1', $nzr_key);
												$nzr_auto_keys[] = $nzr_key;
												$nzr_primary_keys[] = $nzr_key;
											}
											//Find primary keys
											if(preg_match("#\<(.*?)\>#",$nzr_key)>0)
											{//! Save it to primary key array here
												$nzr_key = preg_replace("#\<(.*?)\>#",'\\1', $nzr_key);
												$nzr_primary_keys[] = $nzr_key;
											}
											$new_keys[] = $nzr_key;
										}
										$nzr_keys = $new_keys;
									}
								//Reset line numbering
									$line_number_fix = true;
									$skip_error = true;
							}
							//Normal
							elseif(substr_count($nzr_line,'";')>0 && substr_count($nzr_line,'"')>0)
							{
								//Removing comments
									$comment = strrchr($nzr_line,'";');
									$nzr_vars = str_replace($comment,'"',$nzr_line);
									//Saving comments
										$comment = str_replace('";','',$comment); //Remove end of line
										if(!empty($comment))
										{
											$line_number = $line_key; //Current line number
											//Keys might be present
											if($line_number_fix==true)
											{
												//Fix the line number; the keys line is not counted as a record line.
												$line_number--;
											}
											//Save comments for later on
											$this->comments[$line_number] = $comment;
										}
								//Make it easier to manage after explode
									$nzr_vars = preg_replace("#\"(.*?)\"#",'\\1"', $nzr_vars);
								//Break it down into values
									$nzr_vars = explode('"',$nzr_vars);
								//Remove phantom last variable
									if(empty($nzr_vars[count($nzr_vars)-1]))
									{
										unset($nzr_vars[count($nzr_vars)-1]);
									}
								//Set keys if they are found. Even when the mode is not selected to return them.
									if(!empty($nzr_keys))
									{
										foreach($nzr_vars as $nzr_var)
										{
											$nzr_var = $this->decode($nzr_var);
											$accept_variable = true;
											if(empty($nzr_keys[$i]))
											{
												$accept_variable = false;
												//Error!: no key
												$this->error(3,$line_number+2); //2 = 1 for the key line, 1 for the php array starts at 0
											}
											if($accept_variable==true)
											{
												$line_number = $line_key; //Current line number
												//Keys might be present
												if($line_number_fix==true)
												{
													//Fix the line number; the keys line is not counted.
													$line_number--;
												}
												//Save to array
												if($limit>$done_limit || $limit==0)
												{
													$nzr_keyed_vars[$line_number][$nzr_keys[$i]] = $nzr_var;
													//Save to register
													if(in_array($nzr_keys[$i],$nzr_primary_keys)==true)
													{
														//This is a primary key
														$this->register[$nzr_keys[$i].'='.$nzr_var][] = $line_number;
													}
												}
												if(!empty($nzr_auto_keys) && in_array($nzr_keys[$i],$nzr_auto_keys)==true) //Is there a primary key defined?
												{
													if(empty($max_amount) || $nzr_var>$max_amount[$nzr_keys[$i]])//Only save the highest value (integers)
													{
														$max_amount[$nzr_keys[$i]] = $this->decode($nzr_var); //Save highest value, but decode first, just in case.
													}
												}
												$i++; //Next value = Next key
											}
										}
									}
								//!Returning array with ID's, hmmm.. should we?
								if($limit>$done_limit || $limit==0)
								{
									$nzr_get[] = $this->decode($nzr_vars);
									$affected_records++;
								}
								$done_limit++;
							}
						}
					}
					//Cleanup
						unset($nzr_content);
					
					//Fix for empty files
					if(empty($nzr_keyed_vars))
					{
						$nzr_keyed_vars = array();
					}
					//Give back the keys and value records
					if(empty($nzr_keys))
					{
						//Keys are not found or the keyed output is empty.
						$this->error(8);
						$this->keys = array();
						$this->content = $nzr_get;
					}
					else
					{
						//Return as normal
						if(!empty($nzr_auto_keys))
						{
							$this->auto_keys = $nzr_auto_keys;
						}
						if(!empty($nzr_primary_keys))
						{
							$this->primary_keys = $nzr_primary_keys;
						}
						if(!empty($max_amount))
						{
							$this->max_amount = $max_amount;
						}
						$this->keys = $nzr_keys;
						$this->latest_action = 'read';
						$this->content = $nzr_keyed_vars;
						$this->memory = $nzr_keyed_vars;
					}
				}
			}
			$this->amount_rows = $affected_records;
			$this->auto_keys = $nzr_auto_keys;
		}
	}//End function

	public function search($select='',$mode=false)
	{
		/*
		----------------------------------------------------------------------
		.Nzr record searcher - Added: 2009-04-21
			Searches for matches based on the $select array:
			(Array('key1'=>'value1','key2'=>'value2'))
			
			Only searches on primary keys. Which are saved in the
			$this->register array.
			
			$mode = true/false;
			If true only the line numbers are returned, which COULD be
			usefull for a function like delete or update (save).
		----------------------------------------------------------------------
		*/
		$result = array();
		$matches = array();
		$return_matches = array();
		$this->latest_action = 'search';
		$affected_rows = 0;
		
		if(empty($select))
		{
			return $this->memory;
		}
		$filter = $this->params($select);
		$required_matches = count($filter);
		
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			//Get correct search mode
			$search_mode = 'primary_keys';
			foreach($filter as $key=>$value)
			{
				if(in_array($key,$this->primary_keys)==false)
				{//Non primary key found
					$search_mode = 'all_keys';
				}
			}
			//Do the easy and fast search on primary keys
			if($search_mode=='primary_keys')
			{
				//Go through each filter option
				foreach($filter as $key=>$values)
				{
					foreach($values as $value)
					{
						//Does the key exists in the register?
						if(!empty($this->register) && array_key_exists($key.'='.$value,$this->register)==true)
						{
							//Loop through the register
							foreach($this->register[$key.'='.$value] as $fkey=>$fvalue)
							{
								//Record line number matches
									$matches[$fvalue]++; //Add another match to this line
								//Enough matches according to filter?
								if($matches[$fvalue]==$required_matches)
								{//Yes, save this line.
									$return_matches[] = $fvalue;
								}
							}
						}
					}
				}
			}
			//Do the more extensive search on all values
			elseif($search_mode=='all_keys')
			{
				foreach($this->memory as $line_key=>$values)
				{
					foreach($filter as $fkey=>$fvalues)
					{
						//Does the key exists and does the value in the recorded values match the one that the user specified in the select command?
						if(in_array($values[$fkey],$fvalues)==true)
						{
							//Record line number matches
								$matches[$line_key]++; //Add another match to this line
							//Enough matches according to filter?
							if($matches[$line_key]==$required_matches)
							{//Yes, save this line.
								$return_matches[] = $line_key;
							}
						}
					}
				}
			}
			$affected_rows = count($return_matches);
			//Return results
			if($mode==true)
			{//Only return line numbers
				$result = $return_matches;
			}
			else
			{//Return values
				foreach($return_matches as $match)
				{
					$result[] = $this->memory[$match];
				}
				$this->content = $result;
			}
		}
		$this->amount_rows = $affected_rows;
		return $result;
	}//End function

	public function sort($keys,$sort_type='',$limit=0)
	{
		/*
		----------------------------------------------------------------------
		.Nzr content sorter
			Sorts a .Nzr content array in the order you want
			
		$sort_type = character/numbers/dates and ascending/descending?
			Default: Character ascending = char_asc
			Order types:
				- char_asc
				- char_desc
				- num_asc
				- num_desc
				- date_asc
				- date_desc
			
		$limit = (type: int) Specify the amount of records that should be
			returned.
		----------------------------------------------------------------------
		*/
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			$nzr_content = array();
			//Check keys
			foreach($keys as $key)
			{
				if(in_array($key,$this->keys)==false)
				{
					//Key does not exist in the registered keys array
					$this->error(22);
				}
			}
			//Sort you can use
			$avaliable_sorts = array('char_asc','char_desc','num_asc','num_desc','date_asc','date_desc');
			
			//Default vallue sort type
			if(empty($sort_type))
			{
				$sort_type = 'char_asc';
			}
			//Convert sort type
				//Fix for forgotten extension
				if(substr_count($sort_type,'_')<=0)
				{
					$sort_type .= '_asc';
				}
			if(in_array($sort_type,$avaliable_sorts)==false)
			{
				//Sort option does not exists
				$this->error(24);
				$nzr_content = $this->content; //Return unmodified array.
			}
			else
			{
				$sort_type = explode('_',$sort_type);
				$sort_type = array('object' => $sort_type[0], 'order' => $sort_type[1]);
			
				if(!empty($this->content))
				{
					foreach($this->content as $line_key=>$nzr_line)
					{
						$new_key = '';
						foreach($keys as $key)
						{
							if(array_key_exists($key,$nzr_line)==false)
							{
								//Key does not exist in line
								$this->error(23);
							}
							else
							{
								$new_key .= $nzr_line[$key];
							}
							if($key!==$keys[count($keys)-1])
							{
								$new_key .= ' ';
							}
						}
						//To avoid double keys and loosing data
						$i = '';
						while(array_key_exists($new_key.$i,$nzr_content)==true)
						{
							$i++;
						}
						if(!empty($i))
						{
							$new_key .= $i;
						}
						$nzr_content[$new_key] = $nzr_line;
					}
					//Actual sorting of the array
					if($sort_type['object']=='char')
					{
						if($sort_type['order']=='desc')
						{
							krsort($nzr_content, SORT_STRING);
						}
						elseif($sort_type['order']=='asc')
						{
							ksort($nzr_content, SORT_STRING);
						}
					}
					elseif($sort_type['object']=='num')
					{
						if($sort_type['order']=='desc')
						{
							krsort($nzr_content, SORT_NUMERIC);
						}
						elseif($sort_type['order']=='asc')
						{
							ksort($nzr_content, SORT_NUMERIC);
						}
					}
					elseif($sort_type['object']=='date')
					{
						if($sort_type['order']=='desc')
						{
							uksort($nzr_content, "nzr_sort_latest");
						}
						elseif($sort_type['order']=='asc')
						{
							uksort($nzr_content, "nzr_sort_oldest");
						}
					}
					
					if(!empty($limit))
					{
						//Return only X amount
						$limit = explode(',',$limit);
						$nzr_content = array_slice($nzr_content,$limit[0],$limit[1],true);
						$this->amount_rows = count($nzr_content);
					}
				}
			}
			$this->latest_action = 'sort';
			$this->content = $nzr_content;
		}
	}//End function

	public function filter($select,$limit=0)
	{
		/*
		----------------------------------------------------------------------
		.Nzr Filter results
			Filter the returned results.
			Removes records (in the returned results) that match the given
			$select statement.
		----------------------------------------------------------------------
		*/
		$this->latest_action = 'filter';
		$affected_records = 0;
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			//Filter?
			$affected_records = $this->search($select,true);
			if(!empty($affected_records))
			{
				$done_records = 0;
				$this->content = $this->memory;
				foreach($affected_records as $affected_record)
				{
					$done_records++;
					if($limit==0 || ($limit>0 && $done_records<=$limit))
					{
						unset($this->content[$affected_record]);
					}
				}
			}
		}
		$this->amount_rows = $affected_records;
		return $this->content;
	}//End function

	public function rekey($keys)
	{
		/*
		----------------------------------------------------------------------
		.Nzr Re-key:
			Leftover from .Nzr Beta 1. But still quite usefull.
			Reset line keys to specified value or values from the record.
			Returning first array keys similiar towards: "value1" or
			"value1_value2".

			Important!:
			Make sure the keys you select are columns where unique values
			are saved or multiple columns that make a unique new key. 
			Not unique generated keys will be overwritten with the last
			found record.
		----------------------------------------------------------------------
		*/
		$nzr_content = array();
		if($this->mode_read=='STRICT' && $this->result==false) { } //Can not continue if the result is false
		elseif(empty($this->keys))
		{
			//Error!: Not the right mode selected
			$this->error(7);
		}
		else
		{
			//Keys are given?
			if(empty($keys))
			{
				//Error!: Select keys are empty. Can't Re-key
				$this->error(6);
			}
			elseif(is_array($keys)==false)
			{
				$this->error(29);
			}
			else
			{
				//Do the keys exist in the given .Nzr file keys
				if(!empty($keys))
				{
					foreach($keys as $key)
					{
						if(in_array($key, $this->keys)==true)
						{
							$new_keys[] = $key;
						}
						else
						{
							$this->error(30);
						}
					}
				}
				if(!empty($new_keys))
				{
					$nzr_content = array();
					//Keys are good
					foreach($this->content as $nzr_line)
					{
						$new_key = '';
						foreach($new_keys as $object)
						{
							$new_key .= $nzr_line[$object];
							if($object!==$new_keys[count($new_keys)-1])
							{
								$new_key .= '_';
							}
						}
						$nzr_content[$new_key] = $nzr_line;
					}
					$this->latest_action = 'rekey';
					$this->content = $nzr_content;
				}
			}
		}
		//Fix
		if(empty($this->content))
		{
			$this->content = array();
		}
		return $nzr_content;
	}//End function
	
	public function save($change,$select='',$limit=0)
	{
		/*
		----------------------------------------------------------------------
		.Nzr Saver
			This function allows you to change the content of an .Nzr file and
			automaticly save the changes to it. New records can also be added
			with this function.
			
			Make changes to this/these key(s) with this value(s)
			$change = array('key1=value1')
			$change = array('key1=value1','key2=value2') 
			
			$select
				Add a new record to this file
					$select = 'new'
				Select records on which you like to execute
					$select = Array('key1'=>'value1')
				Execute action on all records in this file. All files will
				undergo the change specified in the $change array.
					$select = Array()
		----------------------------------------------------------------------
		*/
		$this->record_save = true; //For MSFRIENDLY mode
		$affected_columns = 0;
		$affected_records = 0;
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			if(empty($this->original) && !empty($select) && $select!=='new')
			{
				//Error!: No file content found and a select statement was specified.
				$this->error(11);
			}
			elseif(empty($change))
			{
				//Error!: No change specified
				$this->error(12);
			}
			elseif(is_writeable($this->file)==false)
			{
				//Error!: File is not writable
				$this->error(13);
			}
			elseif(substr_count($this->file,'http://')>0 || substr_count($this->file,'https://')>0 || substr_count($this->file,'ftp://')>0)
			{
				//Error!: File is an online file, can not save.
				$this->error(14);
			}
			else
			{
				//Everything okay and read changes
					$changes = $this->params($change,'',false);
				//Create new record
				if($select=='new')
				{
					$new_record = count($this->memory);
					foreach($this->keys as $change_key)
					{
						//Change selected values in returned records
						$change_value = $changes[$change_key];
						
						//Is this an auto key?
						if(in_array($change_key,$this->auto_keys)==true)
						{
							$this->max_amount[$change_key] += 1; //Make mulitple inserts possible
							$change_value = $this->max_amount[$change_key];
							$this->insert_id = $change_value;
						}
						//Save value to memory
						$this->memory[$new_record][$change_key] = $change_value;
						$affected_columns++;
					}
					$affected_records++;
				}
				//Update existing record(s)
				else
				{
					//Get affected records
						$affected_records = $this->search($select,true);
					//Loop affected records
					foreach($affected_records as $affected_record)
					{
						foreach($changes as $change_key=>$change_value)
						{
							//Change selected values in returned records
							$this->memory[$affected_record][$change_key] = $change_value;
							$affected_columns++;
						}
						$affected_records++;
					}
				}
				
				if($affected_records==0)
				{
					//Error!: No changes
						//! Should this error even be shown?
						//$this->error(16);
				}
				else
				{
					//Save the new generated .Nzr file
						$this->latest_action = 'save';
						$this->tmp_action = 'save';
						$this->content = $this->memory;
						if($this->mode_save!=='MSF')
						{
							$this->save_file();
						}
				}
			}
		}
		$this->amount_columns = $affected_columns;
		$this->amount_rows = $affected_records;
	}//End function
	
	public function delete($change,$limit=0)
	{
		/*
		----------------------------------------------------------------------
		.Nzr Record deleter
			Delete the $limit amount of records that all have the values 
			specified in the $keys array. 
			$change = (key1 => 'value'),(key2 => 'value')
		----------------------------------------------------------------------
		*/
		$this->record_save = true; //For MSFRIENDLY mode
		$affected_records = 0;
		$this->amount_columns = count($this->keys);
		
		if(($this->result==true && $this->mode_read=='STRICT') || ($this->mode_read!=='STRICT'))
		{
			//Get working array with changes
			$changes = $this->params($change);
			$done_records = 0;
			//Get affected records by the change array
			$affected_records = $this->search($changes,true);
			foreach($affected_records as $affected_record)
			{//Unset records in the memory
				$done_records++;
				if($limit==0 || ($limit>0 && $done_records<=$limit))
				{
					unset($this->memory[$affected_record]);
				}
			}
		
			//Save the new generated .Nzr file
			$this->latest_action = 'delete';
			$this->tmp_action = 'delete';
			$this->content = $this->memory;
			if($this->mode_save!=='MSF')
			{
				$this->save_file();
			}
		}
		$this->amount_rows = $affected_records;
	}//End function
	
	public function array_convert($nzr_array='')
	{
		/*
		----------------------------------------------------------------------
		.Nzr Array converter
			This function converts an .Nzr generated array to an .Nzr file
			based upon the (modified) keys and values read from the selected
			file.
		----------------------------------------------------------------------
		*/
		//Recommended to keep empty
		if(empty($nzr_array))
		{
			$nzr_array = $this->memory;
		}
		$nzr_content = '';
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			foreach($this->keys as $key)
			{
				$primary_key = false;
				$nzr_content .= '[';
				if(in_array($key,$this->primary_keys)==true)
				{
					$nzr_content .= '<';
					$primary_key = true;
				}
				$nzr_content .= $key;
				if(in_array($key,$this->primary_keys)==true)
				{
					$nzr_content .= '>';
				}
				if(in_array($key,$this->auto_keys)==true && $primary_key==true)
				{
					if($primary_key==false)
					{//Future development
						$nzr_content .= '|';
					}
					$nzr_content .= '+';
				}
				$nzr_content .= ']';
			}
			$nzr_content .= ";";
			if(!empty($this->comments['keys']))
			{
				$nzr_content .= $this->comments['keys'];
			}
			if(!empty($nzr_array))
			{
				//Fix for finding last line
				if($this->tmp_action=='delete')
				{
					$hold = 2;
				}
				elseif($this->tmp_action=='save')
				{
					$hold = 1;
				}
				//Here with the hold thing the latest bug was found.
				//!? BUG EXISTS HERE!
				//! on the save function I guess, there it already adds an enter too many.
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				$hold = 0;
				unset($this->tmp_action);//No longer needed, so clean up
				$last_line = count($nzr_array)-$hold;
				$nzr_content .= "\n";
				$i_line = 0;
				foreach($nzr_array as $line_key=>$nzr_values)
				{
					$i_line++;
					foreach($this->keys as $key)
					{
						$nzr_content .= '"'.$this->encode($nzr_values[$key]).'"';
					}
					$nzr_content .= ";";
					//Returning comments
						if(!empty($this->comments) && array_key_exists($line_key,$this->comments)==true) //Does a comment exists on this line?
						{
							//Comment exists, so add it to this line
							$nzr_content .= $this->comments[$line_key];
						}
					if($i_line!==$last_line) //Add a line break to the end of the line, except for the last line
					{
						$nzr_content .= "\n";
					}
				}
			}
			return $nzr_content;
		}
	}//End function
	
	public function save_file($new_content='')
	{
		/*
		----------------------------------------------------------------------
		.Nzr File saver
			This function saves a .Nzr file when this is requested.
			Used by the save and delete function.
		----------------------------------------------------------------------
		*/
		$result = false;
		if($this->mode_read=='READ_ONLY')
		{
			//Not allowed to save the file
			$this->error(27);
		}
		elseif(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			if(empty($new_content))
			{
				$new_content = $this->array_convert();
			}
			//Save the new generated .Nzr file
			if(($nzr_file_open = @fopen($this->file, "w+"))==true)
			{
				fwrite($nzr_file_open, $new_content);
				fclose($nzr_file_open);
				$this->saved_content = $new_content;
				$result = true;
			}
			else
			{
				//Could not save
				$this->error(15);
			}
		}
		return $result;
	}//End function
	
	public function return_primary_keys()
	{
		//Return the primary keys to the user
		$this->latest_action = 'return_primary_keys';
		return $this->auto_keys;
	}//End function
	
	public function set_primary_keys($objects)
	{
		//Let users specify primary keys for a .Nzr file.
		$this->record_save = true; //For MSFRIENDLY mode
		$this->latest_action = 'set_primary_keys';
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			//Set a new primary key
			foreach($objects as $object)
			{
				if(in_array($object,$this->primary_keys)==true){}
				elseif(in_array($object,$this->keys)==true)
				{//Add to array with primary keys
					$this->primary_keys[] = $object;
				}
				else
				{
					//Key does not exists
					$this->error(32);
				}
			}
			if($this->mode_save!=='MSF')
			{
				$this->save_file();
			}
		}
	}//End function
	
	public function return_auto_keys()
	{
		$this->latest_action = 'return_auto_keys';
		//Return the primary key to the user
		return $this->auto_keys;
	}//End function
	
	public function set_auto_keys($objects)
	{
		$this->record_save = true; //For MSFRIENDLY mode
		$this->latest_action = 'set_auto_keys';
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			//Set a new primary key
			foreach($objects as $object)
			{
				if(in_array($object,$this->auto_keys)==true){}
				elseif(in_array($object,$this->keys)==true)
				{
					$this->set_primary_keys(array($object));
					$this->auto_keys[] = $object;
				}
				else
				{
					//Key does not exists
					$this->error(32);
				}
			}
			if($this->mode_save!=='MSF')
			{
				$this->save_file($this->array_convert());
			}
		}
	}//End function
	
	public function add_keys($keys)
	{
		/*
		----------------------------------------------------------------------
		.Nzr Ad Keys
			Add new columns to a .Nzr file.
			Format: $keys = array('new_key'=>'default_value');
		----------------------------------------------------------------------
		*/
		$this->record_save = true; //For MSFRIENDLY mode
		$this->latest_action = 'add_keys';
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			$original_keys = $this->keys;
			foreach($keys as $key=>$default_value)
			{
				$this->keys[] = $key;
				$new_keys[] = array('key'=>$key,'value'=>$default_value);
			}
			if(!empty($this->memory))
			{
				foreach($this->memory as $line_key=>$values)
				{
					foreach($new_keys as $key)
					{
						$this->memory[$line_key][$key['key']] = $key['value'];
					}
				}
			}
			$this->tmp_action = 'save';
			if($this->mode_save!=='MSF')
			{
				$this->save_file();
			}
		}
	}//End function
	
	public function alter_keys($keys)
	{
		/*
		----------------------------------------------------------------------
		.Nzr Alter Keys
			Changes key names.
			Format: $keys = array('current_name'=>'new_name');
		----------------------------------------------------------------------
		*/
		$this->record_save = true; //For MSFRIENDLY mode
		$this->latest_action = 'alter_keys';
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			foreach($keys as $old_key=>$new_key)
			{
				if(in_array($old_key,$this->keys)==false)
				{
					//Key does not exists
					$this->error(33);
				}
			}
			//Go on only when the status is alright 
			if($this->result==true)
			{
				//Rename key names
				foreach($this->keys as $key=>$existing_key)
				{//Set new key names
					if(array_key_exists($existing_key,$keys)==true)
					{
						$this->keys[$key] = $keys[$existing_key];
					}
				}
				//Rename primary keys
				foreach($this->primary_keys as $key=>$existing_key)
				{//Set new key names
					if(array_key_exists($existing_key,$keys)==true)
					{
						$this->primary_keys[$key] = $keys[$existing_key];
					}
				}
				//Rename auto keys
				foreach($this->auto_keys as $key=>$existing_key)
				{//Set new key names
					if(array_key_exists($existing_key,$keys)==true)
					{
						$this->auto_keys[$key] = $keys[$existing_key];
					}
				}
				$nzr_content = array();
				if(!empty($this->memory))
				{
					foreach($this->memory as $line_key=>$values)
					{
						foreach($values as $key=>$value)
						{
							if(array_key_exists($key,$keys)==true)
							{//Change in Key name
								$nzr_content[$line_key][$keys[$key]] = $value;
							}
							else
							{//No change
								$nzr_content[$line_key][$key] = $value;
							}
						}
					}
				}
				$this->memory = $nzr_content;
				$this->tmp_action = 'save';
				$this->content = $this->memory;
				if($this->mode_save!=='MSF')
				{
					$this->save_file();
				}
			}
		}
	}//End function
	
	public function set_comment($select,$comment)
	{
		/*
		----------------------------------------------------------------------
		.Nzr set comment
			Sets a comment on a specific .Nzr record
			Format:
				$select = array('key'=>'value');
				OR
				$select = 'keys';
			'keys' will put the comment behind the .Nzr keys line.
		----------------------------------------------------------------------
		*/
		$this->record_save = true; //For MSFRIENDLY mode
		$this->latest_action = 'set_comment';
		if(($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT'))
		{
			if($select=='keys')
			{//Set a comment on the first line where the keys are set.
				$this->comments['keys'] = $comment;
				$this->tmp_action = 'save';
				if($this->mode_save!=='MSF')
				{
					$this->save_file();
				}
			}
			else
			{
				//Not yet made, want to make it yourself? Send it to us at contact@newanz.com
			}
		}
	}//End function

	public function close()
	{
		/*
		----------------------------------------------------------------------
		.Nzr closer
			Handles all unfinished business that is left for .Nzr to do.
			We recommend using this function even when you don't use the 
			'MULTIPLE_SAVES_FRIENDLY' mode.
		----------------------------------------------------------------------
		*/
		$result = true;
		if($this->mode_save=='MSF' && $this->record_save==true && (($this->mode_read=='STRICT' && $this->result==true) || ($this->mode_read!=='STRICT')))
		{//Save changes to file at once instead of writing changes to the file a dozen times.
			$result = $this->save_file();
		}
		$this->__destruct(); //Destruct class
		return $result;
	}//End function
	
	function __destruct()
	{
		/*
		----------------------------------------------------------------------
		.Nzr closer/destructor
			This cleans all the used variables by the class.
		----------------------------------------------------------------------
		*/
		foreach($this as $key=>$object)
		{
			//Unset $this variable with the designated key.
			unset($this->$key);
		}
	}

	private function error($error_id, $error_extra='')
	{
		/*
		----------------------------------------------------------------------
		.Nzr Error reporter
			Inform the user that there went something wrong with loading
			the .Nzr Class.

			Variables:
			- The error id is required. Else the user wouldn't get any
			  further in his/her search for the cause of the problem.
			- Using the extra error variable you can send usefull
			  information along with the error. Like a line number.
		----------------------------------------------------------------------
		*/
		if($this->mode_read!=='LOOSE')
		{
			//If the error function is called something is wrong, so the result is set to false.
			$this->result = false;
			//Define the nzr_error constant which can be used to display errors (when there are some).
			if(defined('nzr_error')==false)
			{
				define('nzr_error', 'TRUE');
			}
			//Find error
			switch($error_id)
			{
			case 1:
				$string = "File can not be found (1) or it is empty (2).\n\t\t1 = It is an online file and your computer/server is offline or the file does not exists.\n\t\t2 = File is not configured for the .Nzr reader.";
				break;
			case 2:
				$string = "The file is found empty. No content was found at all, not even keys. Could not execute .Nzr converter.";
				break;
			case 3:
				$string = "Column count for the keys are wrong on line: \"".$error_extra."\". The .Nzr reader found an column without a key or an undefined column. Even when a column is empty it has to be defined with quotes.\n\t\tThis error usually occurs when a records holds more columns than there are keys.";
				break;
			case 4:
				$string = "Error while filtering your results. The key \"".$error_extra."\" you specified was not found by the key finder. Filtering on this key has been skipped.";
				break;
			//5 is missing due to a dropped functionality
			case 6:
				$string = "To \"Re-key\" you have to specify the keys you want to \"Re-key\" towards.";
				break;
			case 7:
				$string = "To \"Re-key\" your selected file should have .Nzr column keys. Else it will not know what values to key.";
				break;
			case 8:
				$string = "You have selected the \"Key\" mode, but this .Nzr file doesn't uses any column names (keys). This file's values are returned with id's instead of column's names (keys) now.\n\t\tThis error usually occurs when there are more keys specified then there are columns in records.";
				break;
			case 9:
				$string = "Column count does not match. Found when trying to add column names to values. ".$error_extra;
				break;
			case 10:
				$string = "While filtering .Nzr found out that there is nothing to filter. No content was returned. The readfile(); function must have run before the filter() function.";
				break;
			case 11:
				$string = "Could not save your change as the .Nzr file you specified, because it is empty and you entered a select statement.";
				break;
			case 12:
				$string = "Could not save your change as the .Nzr file you specified, because you did not enter a change for the .Nzr file.";
				break;
			case 13:
				$string = "The file you selected for the save function is not writable. The function stopped.";
				break;
			case 14:
				$string = "The file you selected for the save function is an online file. .Nzr can not write to online locations. The function stopped.";
				break;
			case 15:
				$string = "Something went wrong saving this file. The save function returned back an error while writing to this file.";
				break;
			case 16:
				$string = "You entered a filter statement, but no records with these values found. So nothing is modified. The .Nzr file is not changed.";
				break;
			case 17:
				$string = "The selection key to filter on during the save is not found. No changes on this key were made.";
				break;
			case 18:
				$string = "The key you specified, to filter the results on, \"".$error_extra."\" does not exist in the .Nzr file.";
				break;
			case 19:
				$string = "The file you requested could not be loaded. We have seen it's an online file (prefixed with: http://, https:// or ftp://). Check if you have an internet connection and try again.";
			case 20:
				$string = "The file you requested could not be loaded. We can not see if it's an online file (prefixed with: http://, https:// or ftp://). Check the path of your file and if it exists, then try again.";
				break;
			case 21:
				$string = "The delete function does not work on files not using column names (keys).";
				break;
			case 22:
				$string = "The key you selected to sort on does not exist.";
				break;
			case 23:
				$string = "One or more keys you have selected to sort on does not exist.";
				break;
			case 24:
				$string = "The selected sort option does not exist.";
				break;
			case 25:
				$string = ".Nzr can not create an online file. Make sure you are creating a local file.";
				break;
			//28 is here to keep some functionality together
			case 28:
				$string = "Could not create the new .Nzr file. Permissions might not be set correctly (write-access) for the parent directory.";
				break;
			case 26:
				$string = "Selected .Nzr mode does not exists.";
				break;
			case 27:
				$string = "Not allowed to save the .Nzr file. The mode is set to \"Read only\" and no saves and deletes are allowed.";
				break;
			case 29:
				$string = "Invalid variable entered for the rekey function. You should enter an array, even when it only contains one object.";
				break;
			case 30:
				$string = "One or more keys you have selected to rekey on does not exist.";
				break;
			case 31:
				$string = "File already exists, can not create an .Nzr file here. If you want to overwrite this file anyway you should specify \"true\" as the second argument on the create function.";
				break;
			case 32:
				$string = "The key you selected does not exists. .Nzr can not make a primary key of a key that does not exists. Please select another (existing) key.";
				break;
			case 33:
				$string = "The key you selected does not exists. Please select another (existing) key.";
				break;
			case 34:
				$string = "The paramenter(s) you specified are/is not an array. Format is like this: Array('key'=>'value','key2'=>'value2')";
				break;	
			default:
				$string = "Error unknown. Could not return a readable format.";
			}

			$error_content = ".Nzr Error ";
			if(empty($error_id))
			{
				$error_content .= "?";
			}
			else
			{
				$error_content .= $error_id;
			}
			$error_content .= ":\n\t\t".$string;
			if(!empty($this->file))
			{
				$error_content .= "\n\t-----------\n\tFile: ".$this->file." (The error occured while reading this file.)";
			}
			$error_content .= "\n\t-----------\n\tRead mode: ".$this->mode_read;
			$error_content .= "\n\tSave mode: ".$this->mode_save;
			$error_content .= "\n\tLast preformed action: ".$this->latest_action;
			//Set to return values
				$this->errors_amount++;
				$this->errors .= $error_content;
			//Make readable in browser
				$this->errors = str_replace("\n",'<br>',$this->errors);
				$this->errors = str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$this->errors);
				$this->errors = preg_replace("#\"(.*?)\"#",'<strong>\\1</strong>', $this->errors);
				$this->errors .= '<br>------------------------------------<br><br>';
			//Return to user, in the source code
				echo "\n<!--\n\t".$error_content."\n-->\n";
		}
	}//End Newanz .Nzr error reporter
}//End Newanz .Nzr class
?>