<?php
/*
AG - 04/04/08
Class to remove simple tags such as h or div etc and replace the data 
between them according to a user function.
*/
Class TagContentReplacer
{

	//AG: Protect all vars till 3.1 till we decide the scope at which this class may be used as a superclass
	protected $start_tag; //Starting tag to look for
	protected $end_tag; //Ending tag to look for
	protected $old_content; //Our initial content
	protected $new_content; //Our parsed and replaced data
	protected $tag_content; //Holder for tag content
	protected $needle; //Search position in the content
	protected $user_callback; //String holding the user function to change the data
	protected $user_callback_params; //Array of parameters to pass to the user function
	
	//function TagContentReplacer($start_tag, $end_tag, $content, $user_callback, $user_callback_parameters=array())
	public function __construct()	
	{
		$this->init();
	}			

	protected function init()
	{
		//Do anything extra or over ride this to save rewriting the constructor per extended class
	}
	
	//Pre prepare hook point
	protected function pre_prepare()
	{
	}
	
	//Post prepare hook point
	protected function post_prepare()
	{
	}
	
	//Reset function for use with code igniter so this can be a statically loaded library
	public function prepare($params)
	{
		//Set up our initial variables
		$this->pre_prepare();
		$this->start_tag = $params['start_tag'];
		$this->end_tag = $params['end_tag'];
		$this->old_content = $params['content'];
		$this->new_content = "";
		$this->tag_content = "";
		$this->user_callback = $params['user_callback'];
		if (isset($params['user_callback_parameters'])) $this->user_callback_params = $params['user_callback_parameters'];
		else $this->user_callback_params = array();
		$this->needle = 0; //Set needle to start
		$this->post_prepare();
	}
	
	//Function to replace the content of the tags
	protected function replace_tag_content()
	{
		//Call the user function with the tag content and append the returned data to
		$this->user_callback_params['tagData'] = $this->tag_content;
		$this->new_content = $this->new_content.call_user_func($this->user_callback, $this->user_callback_params);
	}
	
	//Function to find the next open tag
	private function find_open_tag()
	{
		//Set the needle to the opening tag position
		$this->needle = strpos($this->old_content, $this->start_tag);
		//If theres no more tags return false;
		if ($this->needle === false) return false;
		else return true;
	}
	
	//Function to find the next ending tag
	private function find_close_tag()
	{
		//Set the needle to the closing tag position
		$this->needle = strpos($this->old_content, $this->end_tag);
	}
	
	//Special function to preserve non tag related content
	private function preserve_content($endTag = false)
	{
		//Append pretag data to the new content
		$this->new_content = $this->new_content.substr($this->old_content, 0, $this->needle);
		//If we are doing an end tag remove it and chop anything before it out of the original content
		if ($endTag) $this->old_content = substr($this->old_content, $this->needle + strlen($this->end_tag));
		else $this->old_content = substr($this->old_content, $this->needle + strlen($this->start_tag)); //As above but if we are doing the openinig tag
	}
	
	//This takes care of grabbing the content of the tag
	private function get_tag_content()
	{
		//Store the content of the tag
		$this->tag_content = substr($this->old_content, 0, $this->needle);
		//Strip the tag content from the original content
		$this->old_content = substr($this->old_content, $this->needle);
		$this->needle = 0; //Reset the needle
	}
	
	//Publicly accessible method. Used to process data
	public function process_content()
	{
		//While there are more tags to process...
		while($this->find_open_tag())
		{
			$this->preserve_content(); //Store anything before the start tag
			$this->find_close_tag(); //Find the closing tag
			$this->get_tag_content(); //Store the tag content
			$this->replace_tag_content(); //Replace the tag content according to the user function
			$this->preserve_content(true); //Preserve anything after the tag
		}
		//Append anything left after the last tag
		$this->new_content = $this->new_content.$this->old_content;
		return $this->new_content;
	}
	
}
?>