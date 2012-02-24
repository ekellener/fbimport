#!/usr/bin/php
<?php 

require './includes/facebook.php';
require 'phpapp_config.php';

// Start main DB connection
try{
	// DB connection
	$m = new Mongo($mongo_server);
	$db = $m->selectDB($mongo_db);
	$db->authenticate($mongo_db_uname,$mongo_db_pwd);
	$m_brands_coll = new MongoCollection($db, $brands_coll);
	$m_brand_trends_coll = new MongoCollection($db, $trends_coll);
	
} catch(MongoExcpetion $e){
	error_log("Caught exception: ".$e->getMessage()." \n", 0);
    error_log("******** Fatal : Could not connect to MongoDB or (brand_trends & brands collections need to exist)\n", 0);
	error_log("cmd options: ".print_r($cmdoptions,1),0);
	error_log("*********** \n",0);
		exit;
}



// process script params
$cmdoptions = getopt("t::s::");

$forcetime=0;
if($cmdoptions)
	$forcetime = strtotime($cmdoptions['t']);

//Only process a specific competitor set
if(isset($cmdoptions['s'])) {
	$mqry = array('competitive_set'=>$cmdoptions['s']);
	print_r($mqry);
	$cursor = $m_brands_coll->find($mqry);	
} else {
	$cursor = $m_brands_coll->find();	
	}


// Pull in the list of competitive sets for capture -> $trend_coll, filter by -scompetitive_set
//Pull in main competitor set.
$brandconfig = iterator_to_array($cursor);


// Loop through each Competitive set in the brands collection
foreach($brandconfig as $brandset){

	// Assign the list of fb pages to be captured.
 	$brands = $brandset['fb_uri'];
	
	echo "\nBrand Config: ".$brandset['competitive_set']." \n";
	print_r($brandset['fb_uri']);

//Batch Process  each Brand page in current competitive set
	$batch_pages= array();
	foreach($brands as $fburi)
		$batch_pages[] = array('method' => 'GET','relative_url' =>"/".$fburi);
	try{
		$batchResponse_pages = $facebook->api('?batch='.json_encode($batch_pages), 'POST');
	}catch (FacebookApiException $e) {
		error_log("Caught exception: ".$e->getMessage()." \n", 0);
       	error_log("******** Fatal : couldn't get data from FB: \n", 0);
		error_log("brandset: ".print_r($brandset,1),0);
		error_log("cmd options: ".print_r($cmdoptions,1),0);
		error_log("*********** \n",0);
		exit;
	}
	
	
//  Parse Batchresponse results and assign query results for each post into brand_page_results[]	
	$brand_fb_id = array();
	$brand_page_results = array();
				
	foreach($batchResponse_pages as $brand_page){
		$result =  json_decode($brand_page['body'], true);
		$brand_page_results[$result['username']] = $result;
		$brand_fb_id[] = $result['id'];
	}

		
		
// Process 		
			
//assign a timestamp for logging and querying 
	if($forcetime)
		$ts = new MongoDate($forcetime);
	else 
		$ts = new MongoDate();

		
	foreach($brand_page_results as $brand_page){
		$stats = array();
		$store = array();
		$brand = $brand_page;

		//Build a usable timestamp range to check for an upsert (within the current day)
		$mstdte = new MongoDate($ts->sec - ($ts->sec % 86400));
		$menddte = new MongoDate($ts->sec - ($ts->sec % 86400) + 86399);
		$timerange = array("\$gte" => $mstdte,"\$lte" =>$menddte);
		$rangequery = array('timestamp' => $timerange, 'page_info.name' => $brand['name']);
 		echo "Update: ".$brand['name']." : (".date(DATE_RFC2822,$mstdte->sec)." ".date(DATE_RFC2822,$menddte->sec).") \n";


		//Construct $store array that merges previous array results sets into master array to be written to MongoDB
		$store = array_merge($store, array("competitive_set"=>$brandset['competitive_set']));
		$store['post_list'] =  getPostList($brand['id'],$facebook);
		$store['posts_raw'] =  getPostData($store['post_list'],$facebook);
		$store['posts_summary']  =   addSummaryData($store);
		$store['timestamp'] = $ts;
		$store['page_info'] =$brand;
		
			
		// Clean up and remove unecessary array elements before storing
//		$remarray = array('products','category','description','founded','location','mission','phone', 'company_overview');
//		foreach($remarray as $remelem){
//			unset($store[$remelem]);
//		}


// Insert into MongoDB , or update the existing one if it is already there for the current date.
		
		try{
//			print_r($rangequery);
//			exit;
		$m_brand_trends_coll->update($rangequery,$store, array("upsert" => true));
		} catch (MongoException $e) {
    		error_log("********\n", 0);
			error_log("Caught exception: ".$e->getMessage()." \n", 0);
			error_log($rangequery,0);
			error_log($store,0);
			error_log("*********** \n",0);
			exit;
		}
	}
		
}



/**
 * Creates, Batches and executes a series of FB graph calls.
 * @param array $fblist An array of facebook post ids.
 * @param array $facebook. Main Facebook object used to issue graph/fql calls.
 * @param integer $sts Lower time range for filtering the FQL query
 * @param integer $ets Upper time range for filtering FQL query
 * @param integer $fbid this is a test
 * 
 * @return array $results_post An array containing the results of the batched queries. post_id is the associative index..
 */


function getPostData($fblist, $facebook){
		
// Now batch all post graph calls (pulling details on each post)	
$batch_posts = array();
$result_posts = array();


foreach($fblist as $postid){
	$batch_posts[] = 	array('method' => 'GET','relative_url' =>"/".$postid);
//Process in batch sizes of 10
	if(count($batch_posts) ==20 || count($result_posts) + count($batch_posts) == count($fblist)){
		// sleep so fb doesn't get cranky on too many calls
		sleep(1);
		try{
			$batchresponse_posts = $facebook->api('?batch='.json_encode($batch_posts), 'POST');
		} catch (FacebookApiException $e){
	 		print("Caught exception: ".$e->getMessage()." \n");
   			print("ctr: ".count($batch_posts)."\n");
			exit;		
		}
		
				
		foreach($batchresponse_posts as $post){
			$j = json_decode($post['body'], true);
			//Ignore empty data records
			if(isset($j['id']))
				$result_posts[$j['id']] = $j;  
		}
		$batch_posts = array();	
	}
}

echo "size:".count($result_posts)." size: ".count($fblist)."\n";

return($result_posts);
}



/**
 * Creates, Batches and executes a series of FB graph calls.
 * @param integer $fbid Facebook Page Author/Source id (usually the  owner of the page)
 * @param array $facebook. Main Facebook object used to issue graph/fql calls.
 * 
 * @return array $batch_posts. An array containing posts ids for the $fbid wall.
 * 
 */
function getPostList($fbid,$facebook){
	
// First get a list of ALL posts since 1/1/2011 -129384000
// revised get a list from 1/1/2012 
// Force app to sleep to allow fb not to exceed calls - needs to be refactored into a processing queue.
// 
$fql = "select post_id, created_time, updated_time,likes from stream where source_id=".$fbid." and actor_id = ".$fbid." and created_time > 1325376000 limit 400 ";

try{
	$response = $facebook->api(array(
	'method' => 'fql.query',
	'query' =>$fql,));
	  
} catch (FacebookApiException $e) {
   print("Caught exception: ".$e->getMessage()." \n");
   print($fql);
	exit;
}	

$batch_posts = array();
foreach($response as $postid)
	$batch_posts[] = $postid['post_id']; 

return($batch_posts);
}

/**
 * Processes array of Facebook Graph/FQL results and adds a summary element.
 * @param array $branddata preliminary array to be stored. 
 * 
 * @return array $ret adjusted version of the brandata array (with summary data added)
 */

function addSummaryData($branddata){

// Init metrics
$ret['likes'] =0;			
$ret['shares'] =0;			
$ret['comments'] =0;
$ret['posts']= count($branddata['post_list']);		


foreach($branddata['post_list'] as $postid){
	
	if(isset($branddata['posts_raw'][$postid]['likes']['count']))
		$ret['likes'] += $branddata['posts_raw'][$postid]['likes']['count'];
	if(isset($branddata['posts_raw'][$postid]['shares']['count']))
		$ret['shares'] += $branddata['posts_raw'][$postid]['shares']['count'];
	if(isset($branddata['posts_raw'][$postid]['comments']['count']))
	$ret['comments'] += $branddata['posts_raw'][$postid]['comments']['count'];
	
}
 
return($ret);
	
}

/**
 * Issues a FB FQL query and returns an array of stats based on the results.
 * @param integer $fbid The facebook id of the brand page.
 * @param array $facebook. Main Facebook object used to issue graph/fql calls.
 * @param integer $sts Lower time range for filtering the FQL query
 * @param integer $ets Upper time range for filtering FQL query
 * @param integer $fbid this is a test
 * 
 * @return array An array containing the results in the FQL query.
 */

function getCommentStats($fbid, $facebook, $sts,$ets){
// Facebook FQL call to get 	
//hardcode Jan 1 2011
$fql = "SELECT actor_id,updated_time,message, permalink,attachment, comments FROM stream WHERE updated_time > ".$sts." AND updated_time <=".$ets." AND source_id = ".$fbid." and actor_id=".$fbid." limit 20";

$stats = array('posts_updated' =>0,'comments_updated' => 0, 'comments_updated_likes'=>0);

try{
	$response = $facebook->api(array(
	'method' => 'fql.query',
	'query' =>$fql,));
	

	  
} catch (FacebookApiException $e) {
    error_log("******** Error processing FB FQL ".date("r")." \n", 0);
	error_log("Caught exception: ".$e->getMessage()." \n", 0);
	error_log($fql,0);
	error_log("*********** \n",0);
	return $stats;
}
	

$stats['posts_updated'] = count($response);
$stats['posts_photo'] =0;
$stats['posts_video'] =0;
$stats['posts_link'] =0;
$stats['posts_other_type']=0;

foreach($response as $response_element){
	$stats['comments_updated'] += count($response_element['comments']['comment_list']);
	foreach($response_element['comments']['comment_list'] as $comments){
		$stats['comments_updated_likes'] += $comments['likes'];
	}
	if(isset($response_element['attachment']['media']))
	foreach($response_element['attachment']['media'] as $mediatype){
		switch($mediatype['type']){
			case "photo":
				$stats['posts_photo']++;
				break;
			case "video":
				$stats['posts_video']++;
				break;
			case "link":
				$stats['posts_link']++;
				break;
			default:
				$stats['posts_other_type']++;
			}			
		}
}	

$stats['raw'] = $response;
return $stats;
}




?>

