<?php 

// Config stuff
// Use Facebook test app
$facebook = new Facebook(array(
  'appId'  => '231852236850347',
  'secret' => '23dc7ea71b1a70dc224472bbd5921618',
  'cookie' => 'false'
));

//Perm key 
$access_key = 'AAADS3k43xKsBAJGL4cFDHBRbRktyEaGTkmeTsTx48V0lsNdWHFz1SjxoAR4sELqp2N1rVo82RWI1GjWZAwNlFlnAdJecZD';
$facebook->setAccessToken($access_key);


ini_set('error_log','/tmp/pollfb.log');
date_default_timezone_set('America/Los_Angeles');

//$access_key = 'AAACEdEose0cBANGJJhfzxSSRXLp1rkTFv1ooTxlp1aUV6QC28ZBP5f8UkHXZAj82xuSDxZCFCK1ELeYaXkXqZBuOdaezZBZAsZD';

//DB config


//default is 127.0.0.1
/* Stage version 
$mongo_server = "mongodb://ext-uf-main-stagemongo.uf-main.unitedfuture-uf.com";
$mongo_db = 	"socialistening";
$brands_coll = 	"brandsets";
$trends_coll = 	"brandtrends";
*/


/* Local version */
$mongo_server = "mongodb://staff.mongohq.com:10069";
$mongo_db = 	"sandbox";
$brands_coll = 	"brands";
$trends_coll = 	"brand_trends";
$mongo_db_uname = "ekellener";
$mongo_db_pwd   = "sandb0x21";


?>