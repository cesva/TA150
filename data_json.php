<?php

	function GravaFitxerLog($nserie, $frase){

		//creem el path general
		$path=dirname(__FILE__)."/log/".$nserie."/";
		if (!is_dir($path)) { mkdir($path, 0777, true); chmod($path, 0777); }
	
		$file2 = $path.$nserie."_".date("Y")."-".date("m")."-".date("d").".txt";
		
		if (!file_exists($file2)) {
				$fp = fopen($file2,"a"); 
				fclose ($fp);
		}
		$fp = fopen($file2,"a"); 
		$string = "[".date("H:i:s")." UTC] ";
		$string .= $frase;
		$string .= "\r";
		fseek($fp, 0, SEEK_END );
		fwrite($fp, $string);
		fclose($fp);
	}

	//token
		$token=md5("codi_token");  // --> dc52f5173f61a241bd55b96a7693988c (token de seguretat que cal posar al sensor)
	
	//agafem els parametres de la capsalera i del body
		foreach (getallheaders() as $name => $value) { $name= strtolower($name); $he_name[] = $name;  $he[$name] = $value;}
		foreach ($_GET as $name => $value) { $name= strtolower($name); $pget_name[] = $name;  $pget[$name] = $value;}
		$body=preg_replace('/[[:cntrl:]]/', '', trim(file_get_contents("php://input")));
		$ar_body=json_decode($body, true);

	//agafem numero de serie
		$nserie=$ar_body['serial'];
	
	//posem les verificacions
		//verifiquem el token
		if ( $he['api-key']!=$token ) {
		    header('WWW-Authenticate: Basic realm="My Realm"');
		    header('HTTP/1.0 401');
		    echo '{"code":01,"message":"Invalid credential"}';
		    GravaFitxerLog($he['serialnum'], "Invalid credential ".$he['API-KEY']);
		    exit;
		}
		//verifiquem num. serie
		if (strlen($ar_body['serial'])!=7) {
		    header('WWW-Authenticate: Basic realm="My Realm"');
		    header('HTTP/1.0 404');
		    echo '{"code":02,"message":"Invalid serial number"}';
				exit();
		}

		//verifiquem el model
		if ($ar_body['model']!="TA150") {
		    header('WWW-Authenticate: Basic realm="My Realm"');
		    header('HTTP/1.0 404');
		    echo '{"code":10,"message":"Invalid model"}';
		    GravaFitxerLog($he['serialnum'], "Invalid model");
				exit();
		}


	//posem tots els directoris
		$filename =$nserie."_".date("Y")."-".date("m")."-".date("d").".csv";
		$file = dirname(__FILE__)."/files/".$nserie."/data/".$filename;

	//creem el path general
		$dirname = dirname($file); 
		if (!is_dir($dirname)) { mkdir($dirname, 0777, true); chmod($dirname, 0777); GravaFitxerLog($nserie, "Directori: '".$dirname."' OK"); }

	//mirem si existeix el fitxer per crear-lo amb capsalera
		if (!file_exists($file)) {
			$fp = fopen($file,"a"); 
			
			$linia_cap="sep=|\r\n"; foreach ($ar_body as $name => $value) { if (is_array($value)) { foreach ($value as $subname => $subvalue) { $linia_cap.=$subname."|"; } } else { $linia_cap.=$name."|";} } $linia_cap.="\r\n";
			fseek($fp, 0, SEEK_END );
			fwrite($fp, $linia_cap);
			fclose ($fp);
			GravaFitxerLog($nserie, "Dia: '".$filename."' OK");
			echo "\r\n".$linia_cap;
		}

	//desem el registre
		$fp = fopen($file,"a"); 
		$linia_reg=""; foreach ($ar_body as $name => $value) { if (is_array($value)) { foreach ($value as $subname => $subvalue) { $linia_reg.=$subvalue."|"; } } else { $linia_reg.=$value."|";} } $linia_reg.="\r\n";
		fseek($fp, 0, SEEK_END );
		fwrite($fp, $linia_reg);
		fclose($fp);
		GravaFitxerLog($nserie, "Registre: '".$ar_body['timestamp']."' OK!");
		echo "\r\n".$linia_reg;


	//confirmem rebuda al sensor
		header('HTTP/1.1 200');
		exit();


?>
