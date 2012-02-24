<?php

 require_once './includes/app_config.php';
 require_once './includes/MongoJob.php';
 require_once './includes/FBFQL.php';
 
     
 class FBPostGroupJob
   extends MongoJob
 {
 	
	//Not used
  public static $context;
  
  public static function processJob($fql,$buri,$bid)
  {
  
   try{
   	
	   
	   	$request = new FBFQL($fql);
		$posts = $request->getPosts();
		
	   	foreach($posts as $post){
	   		$post['fb_brand_uri'] =$buri; 
			$post['fb_brand_id'] =$bid; 
	  	   	MongoQueue::push('FBPostJob', 'processJob',$post , time());
		}
  	} catch (exception $e){
		error_log("Caught exception: ".$e->getMessage()." \n", 0);	
		error_log("Resubmitting job FBPostGroupJoB", 0);
		error_log("fql:".$fql." fb_brand_uri:".$buri." fb_brand_id:".$bid);
	
		$post = array('fql'=>$fql,'fb_brand_uri'=>$buri,'fb_brand_id'=>$bid);
	 	MongoQueue::push('FBPostJob', 'processJob',$post , time());	
//		For FB getting cranky
		sleep(60);
	 }
	
  }
  
 }