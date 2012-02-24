#!/usr/local/php5/bin/php
<?php 

require 'facebook.php';
require 'pollfb_config.php';


date_default_timezone_set('America/Los_Angeles');

// script params
$cmdoptions = getopt("t::s::");
$forcetime=0;
if($cmdoptions)
	$forcetime = strtotime($cmdoptions['t']);

$m = new Mongo($mongo_server);
$db = $m->selectDB($mongo_db);

print_r($cmdoptions);


// Pull in the list of competitive sets for capture -> $trend_coll, filter by -scompetitive_set
$db_collection = new MongoCollection($db, $brands_coll);

if(isset($cmdoptions['s'])) {
	$mqry = array('competitive_set'=>$cmdoptions['s']);
	print_r($mqry);
	$cursor = $db_collection->find($mqry);	
} else {
	$cursor = $db_collection->find();	
	}

$brandconfig = iterator_to_array($cursor);

print_r($brandconfig);

// Test data
//$brands = array("/pirelli","yokohamatirecorp","hankooktire.global","KumhoTire","FirestoneTires","falkentire","ToyoTires","MichelinMan","GoodyearBlimp");

// Loop through each Competitive set in the brands collection
foreach($brandconfig as $brandset){
	// Assign the list of fb pages to be captured.
 	$brands = $brandset['fb_uri'];
	$fbcoll = $db->selectCollection($trend_coll);

//	echo "\nBrand Config: ".$brandset['competitive_set']." \n";
//	print_r($brandset['fb_uri']);

	
	//Build FB batch calls
	$batch = array();
	
	//Assign a last updated timestamp
		if($forcetime)
			$brand['ts'] = new MongoDate($forcetime);
		else 
			$brand['ts'] = new MongoDate();
		
		//Build a usable timestamp range to check for an upsert (within the current day)
		$mstdte = new MongoDate($brand['ts']->sec - ($brand['ts']->sec % 86400));
		$menddte = new MongoDate($brand['ts']->sec - ($brand['ts']->sec % 86400) + 86399);
	
	
	
//Batch Process Brand pages
	foreach($brands as $fburi){
		$batch_pages[] = 	array('method' => 'GET','relative_url' =>"/".$fburi);
		}
	$batchResponse_pages = $facebook->api('?batch='.json_encode($batch_pages), 'POST');
	foreach($batchResponse_pages as $brand_page){
		$result =  json_decode($brand_page['body'], true);
		$brand_page_results[$result['username']] = $result;
		$brand_fb_id[] = $result['id'];
		}

	
		$batch_posts = getCommentStatsBatch($brand_fb_id, $facebook, $mstdte->sec,$menddte->sec);

	print_r($batch_posts);
//	exit;
		
	//Call FB w/ batch
//	$batchResponse_pages = $facebook->api('?batch='.json_encode($batch_pages), 'POST');
	$batchResponse_posts = $facebook->api('?batch='.json_encode($batch_posts), 'POST');
   
	print_r($batchResponse_posts);
	exit;
	
	foreach($batchResponse as $brand_page){
		
		// Pull FB data for the Brand
		$stats = array();
		$store = array();
	
			// results of page call
		$brand = json_decode($brand_page['body'], true);
				
//		print_r($brand);
	
//d details on the recent 25 set of posts.
//		$brand_posts_url = "/".$brand['id']."/posts";
//		$brand_posts = $facebook->api($brand_posts_url,'GET');
		
		$timerange = array("\$gte" => $mstdte,"\$lte" =>$menddte);
		$rangequery = array('ts' => $timerange, 'name' => $brand['name']);

 		echo "Update: ".$brand['name']." : (".date(DATE_RFC2822,$mstdte->sec)." ".date(DATE_RFC2822,$menddte->sec).") \n";


 		$stats = getCommentStats($brand['id'], $facebook, $mstdte->sec,$menddte->sec);


		$store = array_merge($brand,$stats);
		$store = array_merge($store, array("competitive_set"=>$brandset['competitive_set']));
	//	echo "comm: ".$stats['comments_updated']." pos: ".$stats['comments_updated']."\n";
		
		// Clean up and remove unecessary array elements before storing
		$remarray = array('products','category','description','founded','location','mission','phone', 'company_overview');
		foreach($remarray as $remelem){
			unset($store[$remelem]);
		}
	
			
		// Insert an entry, or update the existing one if it is already there for the current date.
//	$fbcoll->update($rangequery,$store, array("upsert" => true));
		
	}

}



function getCommentStatsBatch($brand_uri, $facebook, $sts,$ets){
// Facebook FQL call to get 	
//hardcode Jan 1 2010

$batch = array();

foreach($brand_uri as $fbid){
$fql = "SELECT actor_id, updated_time,message, permalink,attachment, comments FROM stream WHERE updated_time > ".$sts." AND updated_time <=".$ets." AND source_id = ".$fbid." and actor_id=".$fbid." limit 20";
print($fql."\n");
$batch[] = array('method' => 'GET','relative_url' =>"method/fql.query?query=".$fql);
}


return($batch);
/*
$stats = array('posts_updated' =>0,'comments_updated' => 0, 'comments_updated_likes'=>0);
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
return($stats);*/

}



function getCommentStats($fbid, $facebook, $sts,$ets){
// Facebook FQL call to get 	
//hardcode Jan 1 2010
$fql = "SELECT actor_id,updated_time,message, permalink,attachment, comments FROM stream WHERE updated_time > ".$sts." AND updated_time <=".$ets." AND source_id = ".$fbid." and actor_id=".$fbid." limit 20";


$response = $facebook->api(array(
'method' => 'fql.query',
'query' =>$fql,
));
 

$stats = array('posts_updated' =>0,'comments_updated' => 0, 'comments_updated_likes'=>0);
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
return($stats);
}


?>
