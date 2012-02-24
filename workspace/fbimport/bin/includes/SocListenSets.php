<?php

Class SocListenSets extends MongoCursor {
	
	public function __construct($coll)
	{
	
		parent::__construct($coll->db,$coll->getName());
		return $coll->find();
	
	}



}
