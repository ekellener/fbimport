<?php

require_once './includes/FBPage.php';

Class FBPosts {
		
	private $fbpage;
		
	protected $timefilter;	
	 
	public function __construct($fbpage)
	{
	

	$this->fbpage = $fbpage;	
	
	}
	
	public function setTimeFilter($since = 0)
	{
		$this->timefilter = $since;	
	}	
	
	public function getPostJobList()
	{
	
	$ctr =0;
	// 3 months of Epoch
	$interval = 7776000;
	// Jan 1, 2009 - 1230851204
	// Jan 1, 2011 - 1293840000
	$pasttimelimit =1230851204;
	$currenttime	= time();
	
	while(true){
		
		
		// gone too far
		if($currenttime - $interval*$ctr <= $pasttimelimit)
			break;
		
		// gone back as far as we're going to go
		if($currenttime - $interval*($ctr+1) <= $pasttimelimit)
			$jobdata[$ctr]['fql'] = $this->getFQLString($pasttimelimit - ($interval*($ctr)),$currenttime,$this->fbpage->getPageID());
		else
			$jobdata[$ctr]['fql'] = $this->getFQLString($pasttimelimit - ($interval*($ctr)),$pasttimelimit-($interval*($ctr+1)),$this->fbpage->getPageID());



		$ctr++;
		
	}
	
	return $jobdata;
	}
	
	
	private function getFQLString($pasttimelimit, $currenttime, $pid)
	{
		return "select post_id from stream where source_id = ".$pid." and actor_id = ".$pid." and created_time > ".$pasttimelimit." and created_time <= ".$currenttime." ";
				
	}
	
			
	
	
}


