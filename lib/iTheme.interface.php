<?php

interface iTheme {
	
	public function customizeIndex($template, $customize);

	public function customizeImpressum($template, $impressum);

	public function customizeNotification($template, $customize);

	public function customizeSubscription($template, $sender, $customize, $link);

}
?>
