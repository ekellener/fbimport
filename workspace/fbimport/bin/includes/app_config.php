<?php 

// Config stuff
// Use Facebook test app


//Global params.
$fb_params = array();
$fb_params['initary'] = array(
  'appId'  => '231852236850347',
  'secret' => '23dc7ea71b1a70dc224472bbd5921618',
  'cookie' => 'false'
);
$fb_params['access_key'] = 'AAADS3k43xKsBAJGL4cFDHBRbRktyEaGTkmeTsTx48V0lsNdWHFz1SjxoAR4sELqp2N1rVo82RWI1GjWZAwNlFlnAdJecZD';
$GLOBALS['fb_params'] = $fb_params;

//$access_key = 'AAACEdEose0cBANGJJhfzxSSRXLp1rkTFv1ooTxlp1aUV6QC28ZBP5f8UkHXZAj82xuSDxZCFCK1ELeYaXkXqZBuOdaezZBZAsZD';

//DB config


//default is 127.0.0.1
/* Stage version 
$mongo_server = "mongodb://ext-uf-main-stagemongo.uf-main.unitedfuture-uf.com";
$mongo_db = 	"socialistening";
$brands_coll = 	"brandsets";
$trends_coll = 	"brandtrends";
$brand_posts =  "brand_posts";
 
*/



$mongo_server = "mongodb://staff.mongohq.com:10069";
$mongo_db = 	"sandbox";
$brands_coll = 	"brands";
$trends_coll = 	"brand_trends";
$brand_posts =  "brand_posts";
$mongo_db_uname = "ekellener";
$mongo_db_pwd   = "sandb0x21";



$GLOBALS['mongo_server'] = $mongo_server;
$GLOBALS['mongo_db'] = $mongo_db;
$GLOBALS['brand_posts'] = $brand_posts;
$GLOBALS['mongo_db_uname'] = $mongo_db_uname;
$GLOBALS['mongo_db_pwd'] = $mongo_db_pwd;



/* Local version 
$mongo_server = "mongodb://127.0.0.1";
$mongo_db = 	"test";
$brands_coll = 	"brands";
$trends_coll = 	"brand_trends";
$brand_posts =  "brand_posts";
*/

ini_set('error_log','/tmp/pollfb.log');
date_default_timezone_set('America/Los_Angeles');

 
?>