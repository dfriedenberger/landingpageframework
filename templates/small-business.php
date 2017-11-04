<?php

  class SmallBusiness implements iTheme {


    public function customize($template, $customize) {
		
		$template->load("index.html");
		
		foreach ($customize as $key => $value)
    		$template->assign($key, $value);

	}
	
  }

  $themes['small-business'] = new SmallBusiness();

?>