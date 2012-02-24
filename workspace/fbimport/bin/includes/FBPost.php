<?php

require_once './includes/facebook.php';
require_once './includes/FBPage.php';

Class FBPost {
		
	private $post_id;
	private $facebook;
	protected $results;
	private $fb_brand_id;
	private $fb_brand_name;
	private $fb_job_lastupdated;
	

	 
	public function __construct($pid, $buri,$bid)
	{	
	$this->post_id = $pid;	
	$this->fb_brand_id = $bid;	
	$this->fb_brand_uri = $buri;	

	}


public function getPost()
	{
		
	require './includes/app_config.php';
  
	$facebook = new Facebook($fb_params['initary']);
  	$facebook->setAccessToken($fb_params['access_key']);

	$this->facebook = $facebook;
		
	try{	
		$call = "/".$this->post_id;
		$response = $this->facebook->api($call);	  
	} catch (FacebookApiException $e) {
    	error_log("********\n", 0);
		error_log("Caught exception: ".$e->getMessage()." \n", 0);   		
		error_log($call);
		throw new Exception('FB Issue: resubmit job');
	
	}	
	
	$response['fb_brand_id'] = $this->fb_brand_id;
	$response['fb_brand_uri'] = $this->fb_brand_uri;
	$this->results = $response;
	
	 
	return $response;
	}	
			
	

	
}


