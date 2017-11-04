<?php

  class Parallax implements iTheme {


    public function customize($template, $customize) {
		
		$template->load("index.html");

		foreach ($customize as $key => $value)
    		$template->assign($key, $value);
			
	}
	
  }

  $themes['parallax'] = new Parallax();

?>