<?php

  class ParallaxEmail implements iTheme {


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
		
		$template->load("notification.html");

		foreach ($customize as $key => $value)
    		$template->assign($key, $value);
			
	}
	
	public function customizeSubscription($template, $sender, $customize, $link) {
		
			$template->load("email_subscription.htm");
			
			$template->assign( "link", $link );
			
			//Umbruch erzwingen
			$linktext = str_replace(array("&"), "&<br>", $link);
			
			$template->assign( "linktext", $linktext );
		
			foreach ($customize as $key => $value)
				$template->assign($key, $value);
		
			$sender->subject($customize['subject']);
			$sender->body($template->html());

	}
	
  }

  $themes['parallaxEmail'] = new ParallaxEmail();

?>