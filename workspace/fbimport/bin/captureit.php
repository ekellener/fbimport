#!/usr/bin/php
<?php 

require './includes/facebook.php';
require 'phpapp_config.php';
require './includes/MongoQueue.php';
require_once './includes/FBPosts.php';
require_once './includes/FBPage.php';

//comment


// Start main DB connection

	// DB connection
	$m = new Mongo($mongo_server);
	$db = $m->selectDB($mongo_db);
	$db->authenticate($mongo_db_uname,$mongo_db_pwd);
	$m_brands_coll = new MongoCollection($db, $brands_coll);
	$m_brand_trends_coll = new MongoCollection($db,"brand_trends_data");

//Initialize MongoQueue
 	MongoQueue::$connection = $m;
 	MongoQueue::$database = $mongo_db;

	// Build an array that mimics the Competitive set collection
	
	$since = 0;
	$sets = iterator_to_array($m_brands_coll->find());
			
	foreach($sets as $set){


		foreach($set['fb_uri'] as $fbpagename){
		
		/* For testing
		if($fbpagename = 'KumhoTires'){
*/			$joblist = array();
			$page 	   = new FBPage($fbpagename,$facebook);	
			$pageposts = new FBPosts($page);
			$page->save($m_brand_trends_coll);
			$pageposts->setTimeFilter();
			$joblist = $pageposts->getPostJobList();
			
			// Create a Job for each of the joblist intervals.				
			foreach($joblist as $job){
				$job['fb_brand_uri'] =$fbpagename; 
				$job['fb_brand_id'] = $page->getPageID();
			 	MongoQueue::push('FBPostGroupJob', 'processJob',$job , time());
			}
		
		/*}*/
		}

	}	
		 	
		





