<?php


    require_once 'Template.class.php';
    require_once 'Tracking.class.php';

    function GUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

	$themes = array();
	require_once 'iTheme.interface.php';
    require_once __DIR__.'/../templates/parallax.php';	
    require_once __DIR__.'/../templates/small-business.php';

  


class Bootstrap {



    public static function start($publicPath) {
		
       global $themes;

	
	   $url = strtok($_SERVER["REQUEST_URI"],'?');	
	   
	   //User id
	   $guid = GUID();
	   
       if(isset($_COOKIE["userid"])) {
         $guid = $_COOKIE["userid"];
       }

	   
	   //Skin

	   $pageId = "abtest";
	   
	   if(isset($_COOKIE["pageid"])) {
         $pageId = $_COOKIE["pageid"];
       }
	   
	   foreach ($_GET as $key => $value) { 
			switch($key)
			{
				case "id":
				  $pageId = $value;
				  break;
			}
	   }
	   	
	   $configPath = realpath($publicPath . '/../config')."/";
	
	   $config = self::readPageConfig($configPath,$url,$pageId);
	 	   
	   $pageId = $config['id'];
	   $templateSkin = $config['template'];
	   
	   
   	   setcookie("userid", $guid, time() + (86400 * 30), "/"); // 86400 = 1 day
	   setcookie("pageid", $pageId, time() + (86400 * 30), "/"); // 86400 = 1 day

	
	
	
	   //Content ausliefern
	   $templatePath = realPath(dirname(__FILE__) . "/../templates/" . $templateSkin) . "/";
   
	   //static resources
	   self::staticResources($url,$publicPath,$templatePath);
	   

	   //Tracking
	   
   	   $logPath = realpath($publicPath . '/../log');

	   if($url == "/tracking")
	   {
		 if ( isset( $_POST['userid'] ) && isset( $_POST['event'] ) && isset( $_POST['args'] ))
		 {
			$userid = $_POST['userid'];
			$event  = $_POST['event'];
			$args   = $_POST['args'];

			$tracking = new Tracking($userid,$logPath);
			$tracking->raise($event,$args);
			
			echo "Raise ".$event." OK";

		 }
		 else if ( isset( $_POST['userid'] ) && isset( $_POST['key'] ) && isset( $_POST['value'] ))
		 {
			$userid = $_POST['userid'];
			$key  = $_POST['key'];
			$value   = $_POST['value'];

			$tracking = new Tracking($userid,$logPath);
			$tracking->set($key,$value);
			
			echo "Set ".$key." OK";

		 }
		 else
		 {
			 echo "Tracking - ERROR";
		 }
		 exit;
	   }
	   
	   

       $tracking = new Tracking($guid,$logPath);
	   

       $tracking->raise("REQUEST","request ".$_SERVER['REQUEST_URI']);
	   
	   if(isset($_SERVER['HTTP_REFERER'])) {
			$tracking->set("HTTP_REFERER",$_SERVER['HTTP_REFERER']);
	   }
	   
       $tracking->set("PAGE",$templateSkin);
       if(isset($_SERVER['HTTP_USER_AGENT'])) {
          $tracking->set("HTTP_USER_AGENT",$_SERVER['HTTP_USER_AGENT']);
       }
	  
	   
       //deliver Index
       $index = new Template($templatePath);
	   
	   $themes[$templateSkin]->customize($index,$config['customize']);
	   $index->assign("title",$config['title']);
	   $index->assign("copyright",$config['copyright']);

	   
	   
	   $analytics = "";
	   $analytics = file_get_contents($configPath . "analytics.txt");

	   $index->assign("analytics",$analytics);
	   
	   
       $index->assign( "guid", $guid );
       echo $index->html();
	
	
	
	    
		
	}
	
	
	 public static function staticResources($url,$publicPath,$templatePath) {
		 
		$ext = pathinfo($url, PATHINFO_EXTENSION);
		
		switch ($ext) {
			case "css":
				self::sendFile($url,$publicPath,"text/css"); //public path overwrites
				self::sendFile($url,$templatePath,"text/css");
				header("HTTP/1.0 404 Not Found");
				exit;
			case "js":
				self::sendFile($url,$publicPath,"application/javascript"); //public path overwrites
				self::sendFile($url,$templatePath,"application/javascript");
				self::sendFile($url,__DIR__ . "/","application/javascript"); //for tracking etc.
				header("HTTP/1.0 404 Not Found");
				exit;
	        case "jpg":
				self::sendFile($url,$publicPath,"image/jpeg"); //public path overwrites
				self::sendFile($url,$templatePath,"image/jpeg");
				header("HTTP/1.0 404 Not Found");
				exit;
			case "png":
				self::sendFile($url,$publicPath,"image/png"); //public path overwrites
				self::sendFile($url,$templatePath,"image/png");
				header("HTTP/1.0 404 Not Found");
				exit;
			case "ico":
			    self::sendFile($url,$publicPath,"image/x-icon");
				header("HTTP/1.0 404 Not Found");
				exit;
			case "woff2":
			    self::sendFile($url,$templatePath,"font/woff2");
				header("HTTP/1.0 404 Not Found");
				exit;
			default:
			    if(strlen($ext) > 0)
				{
					header("HTTP/1.0 404 Not Found");
					exit;
				}
				break;
			
		}
		 
	 }
	 
	  public static function sendFile($url,$path,$contentType) {
		
		
		$file = realpath($path . substr($url,1));
		if(!file_exists($file)) return;

		$rPath = realpath($path);
		$sPath = substr(realpath($file), 0, strlen($rPath));
		
		//Check if no relative path attack
		if(strcmp($sPath,$rPath) == 1)
		{
			header('HTTP/1.0 403 Forbidden');
			exit;
		}
		
		$size = filesize($file);
		header("Content-type: $contentType",true);
		header("Content-length: $size");
		readfile($file);
		exit;
		 
	 }

	 public static function readPageConfig($configPath,$url,$id)
	 {		
		$pages = array();

		$files = scandir($configPath);
		foreach ($files as $file)
		{
		  $ext = pathinfo($file, PATHINFO_EXTENSION);
		  if($ext != "json") continue;
			
		 
		  $content = file_get_contents($configPath . $file);
		  $json = json_decode($content, true);
		 
		  if(!array_key_exists ( 'type' , $json ))
		  {
			  print "Error in config";
			  print $content;
			  exit;
		  }
		  
		  if($json['type'] == "page")
		  {
			  if($json['id'] == $id)
				  return $json;
			  
			  array_push($pages, $json);
		  }
		 
		  
		}
		
		
		
		//random
		return $pages[mt_rand(0, count($pages) - 1)];
	 }
	 
}

?>