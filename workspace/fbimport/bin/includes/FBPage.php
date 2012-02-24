<?php



Class FBPage {
		
	protected $pagename;
	protected $facebook;

	private   $pageid; 
	private   $results;
		
	public function __construct($fbpagename,$facebook)
	{	
	$this->pagename = $fbpagename;
	$this->facebook = $facebook;	
	
	$this->processPage();	
	
	}

	
	public function getResults()
	{
		return $this->results;
	}
	
	public function getPageID()
	{
		return $this->pageid;
		
	}
			
	private function processPage()
	{
				
	try{	
		$call = "/".$this->pagename;
		$response = $this->facebook->api($call);	  
	} catch (FacebookApiException $e) {
		   error_log("********\n", 0);
			error_log("Caught exception: ".$e->getMessage()." \n", 0);   		
			error_log($this->pagename);
			throw new Exception('FB Issue: resubmit job');
	}	
	
	$this->results = $response;
	$this->pageid =  $response['id'];
	}
		
	public function save($m_brand_trends_coll)
	{
		//current time
		
				
		$ts = new MongoDate();
		$this->results['timestamp'] = $ts;
				
		//Build a usable timestamp range to check for an upsert (within the current day)
		$mstdte = new MongoDate($ts->sec - ($ts->sec % 86400));
		$menddte = new MongoDate($ts->sec - ($ts->sec % 86400) + 86399);
		$timerange = array("\$gte" => $mstdte,"\$lte" =>$menddte);
		$rangequery = array('timestamp' => $timerange, 'name' => $this->results['name']);
 		
		echo "Update: ".$this->results['name']." : (".date(DATE_RFC2822,$mstdte->sec)." ".date(DATE_RFC2822,$menddte->sec).") \n";
		// Clean up and remove unecessary array elements before storing
		$remarray = array('about','is_published', 'products','category','description','founded','location','mission','phone', 'company_overview');
		foreach($remarray as $remelem){
			unset($this->results[$remelem]);
		}
		
		
					
		try{
	
		
 		$m_brand_trends_coll->update($rangequery,$this->results, array("upsert" => true));
		} catch (MongoException $e) {
    		error_log("********\n", 0);
			error_log("Caught exception: ".$e->getMessage()." \n", 0);
			error_log($rangequery,0);
			error_log("*********** \n",0);
			throw new Exception('FB Issue: resubmit job');
		}
	
		
	}	
	
}


