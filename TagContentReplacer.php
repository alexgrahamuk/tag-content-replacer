<?php
/*
AG - 04/04/08
Class to remove simple tags such as h or div etc and replace the data 
between them according to a user function.
*/
Class TagContentReplacer
{

	//Starting tag to look for
	protected $_start_tag;
	//Ending tag to look for
	protected $_end_tag;
	//Our initial content
	protected $_old_content;
	//Our parsed and replaced data
	protected $_new_content;
	//Holder for tag content
	protected $_tag_content;
	//Search position in the content
	protected $_needle;
	//String holding the user function to change the data
	protected $_user_callback;
	//Array of parameters to pass to the user function
	protected $_user_callback_params; 
	
	
	//function TagContentReplacer($start_tag, $end_tag, $content, $user_callback, $user_callback_parameters=array())
	public function __construct()	
	{
		$this->_init();
	}			

	
	protected function _init()
	{
		//Do anything extra or over ride this to save rewriting the constructor per extended class
	}
	
	
	//Pre prepare hook point
	protected function _pre_prepare()
	{
	}
	
	
	//Post prepare hook point
	protected function _post_prepare()
	{
	}
	
	
	//Reset function for use with code igniter so this can be a statically loaded library
	public function prepare($params)
	{
		//Set up our initial variables
		$this->_pre_prepare();
		$this->_start_tag = $params['start_tag'];
		$this->_end_tag = $params['end_tag'];
		$this->_old_content = $params['content'];
		$this->_new_content = "";
		$this->_tag_content = "";
		$this->_user_callback = $params['user_callback'];
		
		if (isset($params['user_callback_parameters']))
			$this->_user_callback_params = $params['user_callback_parameters'];
		else
			$this->_user_callback_params = array();
			
		$this->_needle = 0; //Set needle to start
		$this->_post_prepare();
	}
	
	
	//Function to replace the content of the tags
	protected function _replace_tag_content()
	{
		//Call the user function with the tag content and append the returned data to
		$this->_user_callback_params['tagData'] = $this->_tag_content;
		$this->_new_content = $this->_new_content.call_user_func($this->_user_callback, $this->_user_callback_params);
	}
	
	
	//Function to find the next open tag
	private function _find_open_tag()
	{
		//Set the needle to the opening tag position
		$this->_needle = strpos($this->_old_content, $this->_start_tag);
		
		//If theres no more tags return false;
		return ($this->_needle === false) ? false : true;
	}
	
	
	//Function to find the next ending tag
	private function _find_close_tag()
	{
		//Set the needle to the closing tag position
		$this->_needle = strpos($this->_old_content, $this->_end_tag);
	}
	
	
	//Special function to preserve non tag related content
	private function _preserve_content($endTag = false)
	{
		//Append pretag data to the new content
		$this->new_content = $this->new_content.substr($this->old_content, 0, $this->needle);
		
		//If we are doing an end tag remove it and chop anything before it out of the original content
		if ($endTag)
				$this->_old_content = substr($this->_old_content, $this->_needle + strlen($this->_end_tag));
		else
				$this->_old_content = substr($this->_old_content, $this->_needle + strlen($this->_start_tag)); //As above but if we are doing the openinig tag
	}
	
	
	//This takes care of grabbing the content of the tag
	private function get_tag_content()
	{
		//Store the content of the tag
		$this->_tag_content = substr($this->_old_content, 0, $this->_needle);
		//Strip the tag content from the original content
		$this->_old_content = substr($this->_old_content, $this->_needle);
		//Reset the needle
		$this->_needle = 0; 
	}
	
	
	//Publicly accessible method. Used to process data
	public function process_content()
	{
		//While there are more tags to process...
		while($this->find_open_tag())
		{
			$this->_preserve_content(); //Store anything before the start tag
			$this->_find_close_tag(); //Find the closing tag
			$this->_get_tag_content(); //Store the tag content
			$this->_replace_tag_content(); //Replace the tag content according to the user function
			$this->_preserve_content(true); //Preserve anything after the tag
		}
		
		//Append anything left after the last tag
		$this->_new_content = $this->_new_content.$this->_old_content;
		
		return $this->_new_content;
	}
	
}
?>