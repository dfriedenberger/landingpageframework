<?php

  class SmallBusiness implements iTheme {


    public function customizeIndex($template, $customize) {
		
		$template->load("index.html");

		foreach ($customize as $key => $value)
    		$template->assign($key, $value);
			
	}
	
	public function customizeImpressum($template, $impressum)
	{
			$template->load("impressum.html");
			$template->assign("impressum", $impressum);
	}
	
	public function customizeNotification($template, $customize) {
		
		echo "customizeNotification not supported";
		exit;
		
	}
	
	public function customizeSubscription($template, $sender, $customize, $link) {
		
		echo "customizeSubscription not supported";
		exit;
		
	}

  }

  $themes['small-business'] = new SmallBusiness();

?>