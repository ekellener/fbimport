<?php 

require './includes/app_config.php';
require_once './includes/FBPost.php';

/**
 * 
 */
class PostsStore extends MongoCollection {
	
	function __construct() {
		try {
				// DB connection
			$m = new Mongo($GLOBALS['mongo_server']);
			$db = $m->selectDB($GLOBALS['mongo_db']);
			$db->authenticate($GLOBALS['mongo_db_uname'],$GLOBALS['mongo_db_pwd']);
			$brand_posts = $GLOBALS['brand_posts'];
		
		} catch(MongoExcpetion $e){
			error_log("Caught exception: ".$e->getMessage()." \n", 0);
		    error_log("******** Fatal : Could not connect to MongoDB or (brand_trends & brands collections need to exist)\n", 0);
			error_log("*********** \n",0);
			throw new Exception('Mongo Issue');
		}

	parent::__construct($db ,$brand_posts);
	}
	


public function savePost($results)
{

		$ts = new MongoDate();
		$results['timestamp'] = $ts;
			
		// Clean up and remove unecessary array elements before storing
		$remarray = array('about','is_published', 'products','category','description','founded','location','mission','phone', 'company_overview');
		foreach($remarray as $remelem){
			unset($results[$remelem]);
		}

	
		$query = array('id' => $results['id']);		
	
		
		try {
		
		
		} catch (MongoException $e) {
    		error_log("********\n", 0);
			error_log("Caught exception: ".$e->getMessage()." \n", 0);
			error_log($query,0);
			error_log("*********** \n",0);
			throw new Exception('FB Issue: resubmit job');
			
		}
		
	}	
			
	
}


