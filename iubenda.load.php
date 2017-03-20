<?php
	
	function iubenda_load ($STR)
	{
		if(strpos($STR, "<html") === false) {
			
			return $STR;
			
		}
		else if(strpos($STR, "<html") > 200 ) {
			
			return $STR;
			
        }
        
        $STR = str_replace("</head>", "</head>".file_get_contents(__DIR__ . "/iubenda.cookies.js"), $STR);
        
		require __DIR__ . "/iubenda.class.faster.php";
		
		$faster = new iubendaFaster;
		
		return $faster -> isParse(mb_convert_encoding($STR, "HTML-ENTITIES", "UTF-8"));
	}
	
	ob_start("iubenda_load");
	
	
?>