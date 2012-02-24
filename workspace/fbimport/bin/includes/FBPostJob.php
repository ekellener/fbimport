<?php

 require_once './includes/app_config.php';
 require_once './includes/MongoJob.php';
 require_once './includes/FBPost.php';
 require_once './includes/PostsStore.php';
     
 class FBPostJob
   extends MongoJob
 {
 	
	//Not used
   public static $context;
  
   public static function processJob($postdata,$buri,$bid)
   {
  
  
 	 try{
  	  	$fbpost = new FBPost($postdata,$buri,$bid);
		$results = $fbpost->getPost();
		$pstore = new PostsStore();		
		$pstore->savePost($results);
	
		
	 } catch (exception $e){
		error_log("Caught exception: ".$e->getMessage()." \n", 0);	
		error_log("Resubmitting job", 0);
		error_log("post_id:".$postdata." fb_brand_uri:".$buri." fb_brand_id:".$bid);

		$post = array('post_id'=>$postdata,'fb_brand_uri'=>$buri,'fb_brand_id'=>$bid);
	 	MongoQueue::push('FBPostJob', 'processJob',$post , time());	
//		For FB getting cranky
		sleep(60);
	 }
	 
   }
  
 }