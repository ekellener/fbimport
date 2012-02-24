<?php
require      './includes/app_config.php';
require_once './includes/facebook.php';
require_once './includes/FBPage.php';

Class FBFQL {
		
	private $bid;
	private $facebook;
	private $fql;	
	protected $timefilter;	
	 
	public function __construct($fql)
	{


	$this->fql = $fql;
	
	$facebook = new Facebook($GLOBALS['fb_params']['initary']);
  	$facebook->setAccessToken($GLOBALS['fb_params']['access_key']);
	$this->facebook = $facebook;
	
	
	}
	
	public function setTimeFilter($since = 0)
	{
		$this->timefilter = $since;	
	}	
		
	public function getPosts()
	{

		

	try{
		
		 $response = $this->facebook->api(array(	
		'method' => 'fql.query',
		'query' =>$this->fql));
		
	  
	} catch (FacebookApiException $e) {
   		
		error_log("********\n", 0);
		error_log("Caught exception: ".$e->getMessage()." \n", 0);   		
		error_log($this->fql,0);
		throw new Exception('FB Issue: resubmit job');
				
	}

	return $response;
	}

	
}


