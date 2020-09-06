<?php

function jsonDecrypt() 
{
	$jsonInput = "";
	$file = fopen('php://input', 'r');
	
	while (!feof($file)) 
	{
		$jsonInput .= fgets($file);
	}
	fclose($file);
	return ($jsonInput == "")? [] : json_decode($jsonInput, true);
}
	
function printResponse($data = array()) 
{
	header('Content-type: application/json');
	header("Access-Control-Allow-Origin: *");

	echo json_encode($data);
	// echo $data;
	exit();	
}

function printErrorResponse($message) 
{
	header('Content-type: application/json');
	echo json_encode(array("message"=>$message,"status"=>0));
	exit();	
}

function send_notification_ios($tokens, $message)
{
	
	$url = 'https://fcm.googleapis.com/fcm/send';
	$fields = array (
	'registration_ids' => $tokens,
	// 'notification' => $message,
	'data' => $message,
	'notification' => $message
	);

	// echo "<pre>"; print_r($fields); die();

	$headers = array(
	'Authorization: key=' . GOOGLE_API_KEY,
	'Content-Type: application/json'
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);

	if ($result === FALSE) 
	{
	//die('Curl failed: ' . curl_error($ch));
	}
	// echo "<pre>"; print_r($fields);
	curl_close($ch);
	// return $result; 
	// echo "<pre>";
	// print_r($result); die();
}
function send_notification_android($tokens, $message)
{
	
	// $GOOGLE_API_KEY = "AAAAPY3WWaU:APA91bG0_4AgLRV4Qk0_9rzC0f0AR-2Efrp5leYfD9AVIO5fOAxE4IgzatE8y7tRuI14xQ6hOkUtCpvKBM2HRkhWCq0fvQpgaDlHqBBo5wJNYYIIN7-kNY-dXd3htymDQJBhbK9NQ9eC";

	$url = 'https://fcm.googleapis.com/fcm/send';
	$fields = array (
	'registration_ids' => $tokens,
	// 'notification' => $message,
	'data' => $message,
	// 'notification' => $message
	);

//	 echo "<pre>"; print_r(json_encode($fields)); die();

	$headers = array(
	'Authorization: key=' . GOOGLE_API_KEY,
	'Content-Type: application/json'
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);

	if ($result === FALSE) 
	{
	//die('Curl failed: ' . curl_error($ch));
	}
	// echo "<pre>"; print_r($fields);
	curl_close($ch);
//	 echo "<pre>";
//	 print_r($result); die();
	
}

function audioVideoUpload( $path ,$base64_string, $commentId,$commentType) 
{
	// commentType 1 for Audio And 2 for video
	 if($commentType=='1')
	 {
		$output_file = $path.$commentId.".mp3"; 
	 }
	 if($commentType=='2')
	 {
		$output_file = $path.$commentId.".mp4"; 
	 }

	$ifp = fopen( $output_file, "wb" ); 
	stream_filter_append($ifp, "convert.base64-decode");
	fwrite( $ifp, $base64_string );
	fclose( $ifp );
	return( $output_file ); 
}

/**
 * @param $imagePath
 * @param $binaryCode
 * @param $userId
 * @param null $dimension
 * @param null $namePrefix
 * @param string $extension
 * @return string
 * @comment Function for Base64 Image Upload Dynamic
 */
function imageUpload($imagePath, $binaryCode, $userId = "", $dimension=null, $namePrefix=null, $extension="png")
{
    // Create User Id Folder
	if(!is_dir($imagePath))
	{
		$chmod = (is_dir($imagePath) === true) ? 644 : 777;
		if (in_array(get_current_user(), array('apache', 'httpd', 'nobody', 'system', 'webdaemon', 'www', 'www-data')) === true)
		{
			$chmod += 22;
		}
		mkdir($dirswfl);
		chmod($dirswfl, octdec(intval($chmod)));
	}

	$binaryCode = base64_decode($binaryCode);
	$binaryImage = imagecreatefromstring($binaryCode);
	$uri = 'data://application/octet-stream;base64,' . base64_encode($binaryCode);

	$info = getimagesize($uri);
	$width = $info[0];
	$height = $info[1];
	
	if(isset($info['mime'])) 
	{
		if($info['mime'] == "image/jpeg") 
		{
			$imageType = "jpeg";
		} 
		else 
		{
			$imageType = "png";
		}
	} 
	else 
	{
		$imageType = "png";
	}

	if(isset($dimension) && !empty($dimension))
	{
		$maxwidth = $dimension['width'];
		$maxheight = $dimension['height'];

		if ($maxwidth < $width && $width >= $height) 
		{
			$desired_width = $maxwidth;
			$desired_height = ($desired_width / $width) * $height;
		}
		elseif ($maxheight < $height && $height >= $width) 
		{
			$desired_height = $maxheight;
			$desired_width = ($desired_height /$height) * $width;
		}
		else 
		{
			$desired_height = $height;
			$desired_width = $width;
		}
	}
	else
	{
		$desired_height = $height;
		$desired_width = $width;
	}

	$binaryImage = imagecreatefromstring($binaryCode);
	$new = imagecreatetruecolor($desired_width, $desired_height);
	$x = imagesx($binaryImage);
	$y = imagesy($binaryImage);
	imagecopyresampled($new, $binaryImage, 0, 0, 0, 0, $desired_width, $desired_height, $x, $y);

	if($userId)
	{
		$newNameImage = $userId.$namePrefix.".".$extension;
	}
	else
	{
		$newNameImage = randomFix(8).$namePrefix.".".$extension;
	}

    //$newNameImage = $userId.$namePrefix.".".$extension;

	$binaryImageg_thumb = $imagePath.$newNameImage;

	$black = imagecolorallocatealpha($new, 0, 0, 0, 127);
	imagealphablending($new, false);
	imagecolortransparent($new, $black);

	if($imageType == "jpeg") 
	{
		$createImageSave=imagejpeg($new,$binaryImageg_thumb);
	} 
	else if($imageType == "gif") 
	{
		$createImageSave=imagegif($new,$binaryImageg_thumb);
	} 
	else 
	{
		$createImageSave=imagepng($new,$binaryImageg_thumb);
	}

	return $newNameImage;
}

function send_notification($tokens, $message)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array (
                'registration_ids' => $tokens,
                'notification' => $message
            );

        $headers = array(
        'Authorization:key = AAAAvbFHtUU:APA91bFOvbxBJEfiKistyE9UjNcywYbOvvtzr3LgqruP63NjnNrymnNuy1IunFCsawvVBsZOIshRwK1-C_SXVxGJdxZ43om4F02lFc78AYHLFlJwKC2a7Ud0KP06Do2dyyPw3CehUpnb',
        'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        
        if ($result === FALSE) 
        {
            // die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    function send_ios_push_specific($push_arr)
    {
        $passphrase = '';
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'assets/upload/pemfile/');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        $fp = stream_socket_client(
            'ssl://gateway.push.apple.com:2195', $err,
            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        $body['aps']= $push_arr;
        $payload = json_encode($body);
        
        foreach($push_arr['dt'] as $device_token)
        {
            // Build the binary notification
            $msg = chr(0) . pack('n', 32) . pack('H*', $device_token) . pack('n', strlen($payload)) . $payload;     
            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
        }
        // Close the connection to the server
        fclose($fp);
    }

function write_media_file($path, $fileid, $content)
{
	//echo $path.$fileid.'.txt'; 
	if(file_exists($path.$fileid.'.txt')) {
		$fp = fopen ($path.$fileid.'.txt', 'a+') or die('Error'); // file append
	}
	else {
		$fp = fopen ($path.$fileid.'.txt', 'w+')or die(print_r(error_get_last(),true));; // file create and write
	}
	
	fwrite($fp, $content);
	fclose($fp);
}

// function for generate random string 
function random_string_datetime() {
	$today = date('Ym');
	$startDate = date('Ym', strtotime('-10 days'));
	$range = $today - $startDate;
	$rand = rand(0, $range);
	$final_string = ($startDate + $rand);
	
	$final_string = $final_string.'1';
	return $final_string;
}

function randomFix($length)
{
	$random= "";
	srand((double)microtime()*1000000);	
	//$data = "AbcDE123IJKLMN67QRSTUVWXYZ";
	//$data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
	$data = "1234567890";
	for($i = 0; $i < $length; $i++) {
		$random .= substr($data, (rand()%(strlen($data))), 1);
	}
	return time();
	//return $random;
	//return strtotime(date('Y-m-d'));
}

//function for break large string
function break_str($string)
{
	// strip tags to avoid breaking any html
	$string = strip_tags($string);

	if (strlen($string) > 30) {

	    // truncate string
	    $stringCut = substr($string, 0, 30);

	    // make sure it ends in a word so assassinate doesn't become ass...
	    $string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
	}
	return $string;
}

// NEW FUNCTION ADDED

function send_notification_ios_old($tokens, $message)
{
		// print_r($message);die;
		// $GOOGLE_API_KEY = "AAAAuGkcXVM:APA91bHXlLEInjlu8hJ0ZiDRk1aNrMsE6_RXwdBVB_fQgVeueXuu-zCFmiRton_i6gBg6UMt3ZA4G6HEnlg7ExJw_pBg_ccMp0RQF3xpqoYeSROgo-1iI5teaYi265Ps2JVIElEu6suo";
		// $GOOGLE_API_KEY = "AAAA3LT0Ebs:APA91bFBJyLgwOOuTHJW54Me6a0_vH-mU6XOiXxf-DgLGVSgwlui0q5GzmLde9025SkU1MY8wHV4XHJ7FeNfQF-aOtOpxLfoG3IS6kXkSf6L5Oa-ugxn9jeHqhsvLlerQ_T5HoPkMmWMhQfPAVSlIdJxVhw8pghd2Q";

		$GOOGLE_API_KEY = "AAAA0tFjcyo:APA91bH7NBierw42IeCVUvcwOvuUULgQrkB297Ua5Hlbn54VeMFQdEFNRt0wkbmWDYWtXtyaY5BismkfE8oU1RkE_Izi8bmNcU_EuWocoZmJsZWrd3eEflPVu6fvDirdcglNr3nqHbu3";

		$url = 'https://fcm.googleapis.com/fcm/send';
		$fields = array (
		'registration_ids' => $tokens,
		//'notification' => $message
		'data' => $message,
		'notification' => $message
		);

		// echo "<pre>"; print_r($fields); die();

		$headers = array(
		'Authorization: key=' . GOOGLE_API_KEY,
		'Content-Type: application/json'
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);

		if ($result === FALSE) 
		{
		//die('Curl failed: ' . curl_error($ch));
		}
		// echo "<pre>"; print_r($fields);
		curl_close($ch);
		// return $result; 
}
