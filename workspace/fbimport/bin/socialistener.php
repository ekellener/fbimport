#!/usr/bin/php
<?php 

require_once './includes/app_config.php';
require_once './includes/MongoQueue.php';
require_once './includes/FBPostJob.php';
require_once './includes/FBPostGroupJob.php';



try{
	// DB connection
	$m = new Mongo($mongo_server);
	$db = $m->selectDB($mongo_db);
	$db->authenticate($mongo_db_uname,$mongo_db_pwd);
	
} catch(MongoException $e){
	error_log("Caught exception: ".$e->getMessage()." \n", 0);
    error_log("******** Fatal : Could not connect to MongoDB or (brand_trends & brands collections need to exist)\n", 0);
	error_log("*********** \n",0);
	exit;
}
 
 
 MongoQueue::$connection = $m;
 MongoQueue::$database = $mongo_db;
 MongoQueue::$environment = 'classes/jobs';
 MongoQueue::$context = array('context' => array('foo', 'bar'));

 

 while(true){
   MongoQueue::run();
//sleep(1);
 }
 echo "something died";
 
  
 