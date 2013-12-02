<?php

namespace li3_hui\extensions\helper;

class mumford extends \lithium\template\Helper {
	
	private $file_size = 0;
	private $max_file_size = 5000;
	private $file_downloaded = "";
	private $notifyNmaOpened = false;

	public function __construct () {
		
	}

	/*
		get the IP address of the connected user
	*/
	public function getIP() {
		$ip="";
		if (getenv("HTTP_CLIENT_IP")) $ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR")) $ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR")) $ip = getenv("REMOTE_ADDR");
		else $ip = "";
		return $ip;
	}

	private function dayadd($days,$date=null , $format="d/m/Y"){
		// add days to a date
		return date($format,strtotime($days." days",strtotime( $date ? $date : date($format) )));
	}

	private function attr($s,$attrname) {
		//return html attribute
		preg_match_all('#\s*('.$attrname.')\s*=\s*["|\']([^"\']*)["|\']\s*#i', $s, $x); 
		if (count($x)>=3) return isset($x[2][0]) ? $x[2][0] : "";
		return "";
	}

	private function makeabsolute($url,$link) {
		$p = parse_url($url);
		if (strpos( $link,"http://")===0 ) return trim($link);
		if($p['scheme']."://".$p['host']==$url && $link[0]!="/" && $link!=$url) return trim($p['scheme']."://".$p['host']."/".$link);
		if (strpos( $link, "/")===0) return trim("http://".$p['host'].$link);
		return trim(str_replace(substr(strrchr($url, "/"), 1),"",$url).$link);
	}

	private function on_curl_header($ch, $header) {	// to handle file size check and prevent downloading too much
		$trimmed = rtrim($header);   
		if (preg_match('/^Content-Length: (\d+)$/i', $trimmed, $matches)) {
			$file_size = (float)$matches[1];
			if ($file_size > $this->max_file_size) {
				// stop if bigger
				return -1;
			}
		}
		return strlen($header);
	}

	private function on_curl_write($ch, $data) {	// to handle file size check and prevent downloading too much
		$bytes = strlen($data);
		$this->file_size += $bytes;
		$this->file_downloaded .= $data;
		if ($this->file_size > $this->max_file_size) {
			// stop if bigger
			return -1;
		}
		return $bytes;
	}

	private function getRemoteFileSize($url) {
		if (substr($url,0,4)=='http') {
			$x = array_change_key_case(get_headers($url, 1),CASE_LOWER);
			if ( strcasecmp($x[0], 'HTTP/1.1 200 OK') != 0 ) { $x = $x['content-length'][1]; }
			else { $x = $x['content-length']; }
		}
		else { $x = @filesize($url); }
		return $x;
	} 

	private function getHttpResponseCode($url) {
		if (!function_exists("curl_init")) die("getHttpResponseCode needs CURL module, please install CURL on your php.");
		// 404 not found, 403 forbidden...
		$ch = @curl_init($url);
		@curl_setopt($ch, CURLOPT_HEADER, TRUE);
		@curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$status = array();
		preg_match('/HTTP\/.* ([0-9]+) .*/', @curl_exec($ch) , $status);
		return isset($status[1]) ? $status[1] : null;
	}

	/*
		Copy a remote url to your local server
	*/
	public function copyFile($url,$filename){
		// copy remote file to server
		$file = fopen ($url, "rb");
		if (!$file) return false; else {
			$fc = fopen($filename, "wb");
			while (!feof ($file)) {
				$line = fread ($file, 1028);
				fwrite($fc,$line);
			}
			fclose($fc);
			return true;
		}
	}

	/*
		Google spell suggest:
		usage example:
		$obj = New Minibots();
		$word = $obj->doSpelling("wikipezia"); 
		--> wikipedia
	*/
	public function doSpelling($q) {
		// grab google page with search
		$web_page = file_get_contents( "http://www.google.it/search?q=" . urlencode($q) );
		// put anchors tag in an array
		preg_match_all('#<a([^>]*)?>(.*)</a>#Us', $web_page, $a_array);
		for($j=0;$j<count($a_array[0]);$j++) {
			// find link with spell suggestion and return it
			if(stristr($a_array[0][$j],"class=spell")) return strip_tags($a_array[0][$j]);
			if(stristr($a_array[0][$j],"class=\"spell\"")) return strip_tags($a_array[0][$j]);
		}
		return $q;	//if no results returns the q value
	}

	/*
		Make a tiny url:
		usage example:
		$obj = New Minibots();
		$short_url = $obj->doShortURL("http://www.this.is.a.long.url/words-words-words"); 
		--> http://tinyurl.com/aiIAa (fake values)
	*/
	public function doShortURL($longUrl) {
		//(thanks to tinyurl.com)
		$short_url= file_get_contents('http://tinyurl.com/api-create.php?url=' . $longUrl);
		return $short_url;
	}

	/*
		Convert back from a tiny url to a long url, work also with urls of other services like goo.gl, bit.ly and others:
		usage example:
		$obj = New Minibots();
		$long_url = $obj->doShortURLDecode("http://tinyurl.com/aiIAa"); 
		--> http://www.this.is.a.long.url/words-words-words (fake values)
	*/
	public function doShortURLDecode($url) {
		if (!function_exists("curl_init")) die("doShortURLDecode needs CURL module, please install CURL on your php.");
		$ch = @curl_init($url);
		@curl_setopt($ch, CURLOPT_HEADER, TRUE);
		@curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$out = @curl_exec($ch);
		preg_match('/Location: (.*)\n/', $out, $a);
		if (!isset($a[1])) return $url;
		return $a[1];
	}

	/*
		Check if an mp3 URL is an mp3:
		usage example:
		$obj = New Minibots();
		$check = $obj->checkMp3("http://www.artintent.it/Kalimba.mp3"); 
		--> true
	*/
	public function checkMp3($url) {
		if (!function_exists("curl_init")) die("getHttpResponseCode needs CURL module, please install CURL on your php.");
		$a = parse_url($url);
		if(checkdnsrr(str_replace("www.","",$a['host']),"A") || checkdnsrr(str_replace("www.","",$a['host']))) {
			$ch = @curl_init();
			@curl_setopt($ch, CURLOPT_URL, $url);
			@curl_setopt($ch, CURLOPT_HEADER, 1);
			@curl_setopt($ch, CURLOPT_NOBODY, 1);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			@curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			$results = explode("\n", trim(curl_exec($ch)));
			$mime = "";
			foreach($results as $line) {
				if (strtok($line, ':') == 'Content-Type') {
					$parts = explode(":", $line);
					$mime = trim($parts[1]);
				}
			}
			return $mime=="audio/mpeg";
		} else {
			return false;
		}
	}

	/*
		Check if a URL exists, like file_exists, but for remote urls:
		usage example:
		$obj = new Minibots();
		$check = $obj->url_exists("http://en.wikipedia.org/wiki/Barack_Obama"); 
		--> true
	*/
	public function url_exists($url) {
		return ($this->getHttpResponseCode($url) == 200);
	}

	/*
		Check if an email is correct, this function try to validate email address by connecting to the SMTP server.
		It returns true when email is ok or returns an array(msg, error code) when fails.
		The second parameter, $from_address should be an email with permission to send mail from your domain.
		usage example:
		$obj = new Minibots();
		$check = $obj->doSMTPValidation("pons@rockit.it","info@barattalo.com");
		--> true
	*/
	function doSMTPValidation($email, $from_address="", $debug=false) {
		if (!function_exists('checkdnsrr')) die("This function requires checkdnsrr function, check your Php version.");
		$output = "";
		// --------------------------------
		// Check email syntax with regular expression, for both destination and sender
		// --------------------------------
		if (!$from_address) $from_address = $_SERVER["SERVER_ADMIN"];
		if (!preg_match('/^([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))$/', $from_address)) {
			$error = "From email is wrong.";
		} elseif (preg_match('/^([a-zA-Z0-9\._\+-]+)\@((\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,7}|[0-9]{1,3})(\]?))$/', $email, $matches)) {
			$domain = $matches[2];
			// --------------------------------
			// get DNS MX records
			// --------------------------------
			if(getmxrr($domain, $mxhosts, $mxweight)) {
				for($i=0;$i<count($mxhosts);$i++){
					$mxs[$mxhosts[$i]] = $mxweight[$i];
				}
				asort($mxs);
				$mailers = array_keys($mxs);
			} elseif(checkdnsrr($domain, 'A')) {
				$mailers[0] = gethostbyname($domain);
			} else {
				$mailers=array();
			}
			$total = count($mailers);
			if($total > 0) {
				// --------------------------------
				// Check if mail servers accept email
				// --------------------------------
				for($n=0; $n < $total; $n++) {
					if($debug) { $output .= "Checking server $mailers[$n]...\n";}
					$connect_timeout = 2;
					$errno = 0;
					$errstr = 0;
					$from_address = str_replace("@","",strstr($from_address, '@'));

					// --------------------------------
					// Open socket
					// --------------------------------
					if($sock = @fsockopen($mailers[$n], 25, $errno , $errstr, $connect_timeout)) {
						$response = fgets($sock);
						if($debug) {$output .= "Opening up socket to $mailers[$n]... Success!\n";}
						stream_set_timeout($sock, 5);
						$meta = stream_get_meta_data($sock);
						if($debug) { $output .= "$mailers[$n] replied: $response\n";}
						// --------------------------------
						// Errors or time out
						// --------------------------------
						if(!$meta['timed_out'] && !preg_match('/^2\d\d[ -]/', $response)) {
							$code = trim(substr(trim($response),0,3));
							if ($code=="421") {
								// 421 #4.4.5 Too many connections to this host.
								$error = $response;
								break;
							} else {
								if($response=="" || $code=="") {
									// There was an error, but not clear
									$code = "0";
								}
								$error = "Error: $mailers[$n] said: $response\n";
								break;
							}
							break;
						}
						// talk to smtp server with its language
						// try to ask for recipient but don't send email
						$cmds = array(
							"HELO $from_address",
							"MAIL FROM: <$from_address>",
							"RCPT TO: <$email>",
							"QUIT",
						);
						foreach($cmds as $cmd) {
							$before = microtime(true);
							fputs($sock, "$cmd\r\n");
							$response = fgets($sock, 4096);
							$t = round(1000 * (microtime(true)-$before));
							if($debug) {$output .= $cmd."\n". "($t ms) ". $response;}
							if(!$meta['timed_out'] && preg_match('/^5\d\d[ -]/', $response)) {
								$code = trim(substr(trim($response),0,3));
								if ($code<>"552") {
									$error = "Unverified address: $mailers[$n] said: $response";
									break 2;
								} else {
									$error = $response;
									break 2;
								}
								// --------------------------------
								// Errors 554 and 552 are over quota, so the email is ok, but the full.
								// 554 Recipient address rejected: mailbox overquota
								// 552 RCPT TO: Mailbox disk quota exceeded
								// --------------------------------
							}
						}
						fclose($sock);
						if($debug) { $output .= "Succesful communication with $mailers[$n], no hard errors, assuming OK\n";}
						break;
					} elseif($n == $total-1) {
						$error = "None of the mailservers listed for $domain could be contacted";
						$code = "0";
					}
				}
			} elseif($total <= 0) {
				$error = "No usable DNS records found for domain '$domain'";
			}
			
		} else {
			$error = 'Email is wrong.';
		}
		if($debug) {
			print nl2br(htmlentities($output));
		}
		if(!isset($code)) $code="n.a.";
		if(isset($error)) return array($error,$code); else return true;
	}


	/*
		Fetch info for a specified URL, maximages and maxkbimg are usefull to get useful images,
		so if there is a small icon this image will be skipped, to find an image bigger.
		usage example:
		$obj = new Minibots();
		$infos = $obj->getUrlInfo("http://piccsy.com/2013/10/cute-dog"); 
		--> array(
			[keywords] => Piccsy, images, beautiful images, creative images, image discovery, discovery, browse, galleries, piccs
			[description] => Beautiful, inspirational and creative images from Piccsy. Thousands of Piccs from all our streams, for you to browse, enjoy and share with a friend.
			[title] => Piccsy :: cute dog
			[favicon] => http://piccsy.com/favicon.ico
			[images] => Array
				(
					[0] => http://img1.piccsy.com/cache/images/03/f1/69269c21__4500deb6_0ec40_cb4-post.jpg
					[1] => http://piccsy.com/piccsy/images/layout/logo/e02f43.200x200.jpg
				)
		)
	*/
	public function getUrlInfo($url,$maximages=5,$maxkbimg=10) {
		if (!function_exists("curl_init")) die("getUrlInfo needs CURL module, please install CURL on your php.");
		$url = $this->makeabsolute($url, $this->doShortURLDecode($url));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);       // Fail on errors
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // allow redirects
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);     // return into a variable
		curl_setopt($ch, CURLOPT_PORT, 80);             //Set the port number
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);          // times out after 15s
		if($maximages==0) {
			// if you don't want images from html 
			// use only first 5 kb to reduce band used and time
			$this->max_file_size = 5000;
			curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'on_curl_header'));
			curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'on_curl_write'));
		}
		$web_page = curl_exec($ch);
		if(strlen($web_page) <= 1 && $maximages==0) {
			$web_page = $this->file_downloaded;
		}

		//$web_page = file_get_contents($url);
		$data['keywords']="";
		$data['description']="";
		$data['title']="";
		$data['favicon']="";
		$data['images']=array();
		//search title
		preg_match_all('#<title([^>]*)?>(.*)</title>#Uis', $web_page, $title_array);
		$data['title'] = trim($title_array[2][0]);
		//search keywords and description
		preg_match_all('#<meta([^>]*)(.*)>#Uis', $web_page, $meta_array);
		//print_r($meta_array);
		for($i=0;$i<count($meta_array[0]);$i++) {
			if (strtolower($this->attr($meta_array[0][$i],"name"))=='description') 
				$data['description'] = trim($this->attr($meta_array[0][$i],"content"));
			if (strtolower($this->attr($meta_array[0][$i],"name"))=='keywords') 
				$data['keywords'] = trim($this->attr($meta_array[0][$i],"content"));
		}
		//search favicon
		preg_match_all('#<link([^>]*)(.*)>#Uis', $web_page, $link_array);
		for($i=0;$i<count($link_array[0]);$i++) {
			if (strtolower($this->attr($link_array[0][$i],"rel"))=='shortcut icon') 
				$data['favicon'] = $this->makeabsolute($url,$this->attr($link_array[0][$i],"href"));
		}

		// search images on open graph and schema org
		preg_match_all('#<meta([^>]*)(.*)/?>#Uis', $web_page, $imgs_array);
		$imgs = array();
		for($i=0;$i<count($imgs_array[0]);$i++) {
			$att1 = $this->attr($imgs_array[0][$i],"property");
			$att2 = $this->attr($imgs_array[0][$i],"itemprop");
			if ($att1 == "og:image" || $att2=="image") {
				$src = trim($this->attr($imgs_array[0][$i],"content"));
				array_push($imgs,$src);
				break;
			}
		}

		// search images big enough
		preg_match_all('#<img([^>]*)(.*)/?>#Uis', $web_page, $imgs_array);
		for($i=0;$i<count($imgs_array[0]);$i++) {
			if ($src = $this->attr($imgs_array[0][$i],"src")) {
				$src = $this->makeabsolute($url,$src);
				$kb = 1;
				if($maxkbimg>0) {
					$kb = $this->getRemoteFileSize($src);
				}
				if(!in_array($src,$imgs) && $kb>$maxkbimg*1000) array_push($imgs,$src);
			}
			if (count($imgs)>$maximages-1) break;
		}
		$data['images']=$imgs;

		return $data;
	}

	/*
		Get info for video on Youtube or on Vimeo
		return an array with title, descriptiom, thumb
		$obj = new Minibots();
		$infos = $obj->getVideoUrlInfo("http://www.youtube.com/watch?v=KUVlrdfKowk");
		---> Array
		(
			[title] => Lavoratooooooooori - YouTube
			[description] => Non sapete che mettere di carino nell 'out of office quando andate in ferie?? Ecco...
			[thumb] => http://img.youtube.com/vi/KUVlrdfKowk/1.jpg
		)
	*/
	public function getVideoUrlInfo($url) {
		if (!function_exists("curl_init")) die("getVideoUrlInfo needs CURL module, please install CURL on your php.");
		$url = $this->makeabsolute($url, $this->doShortURLDecode($url));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_FAILONERROR, 0);       // Fail on errors
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // allow redirects
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);     // return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);          // times out after 15s
		//curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'on_curl_header'));
		$web_page = curl_exec($ch);

		//search title
		preg_match_all('#<title([^>]*)?>(.*)</title>#Uis', $web_page, $title_array);
		$title = isset($title_array[2][0]) ? trim(preg_replace('/ +/', ' ', $title_array[2][0])) : "";

		//search keywords and description
		preg_match_all('#<meta([^>]*)(.*)>#Uis', $web_page, $meta_array);
		//print_r($meta_array);
		$description="";
		for($i=0;$i<count($meta_array[0]);$i++) 
			if (strtolower($this->attr($meta_array[0][$i],"name"))=='description') 
				$description = $this->attr($meta_array[0][$i],"content");

		//URL examples (Youtube):
		// http://www.youtube.com/v/Md1E_Rg4MGQ&hl=en&fs=1&
		// http://www.youtube.com/watch?v=Md1E_Rg4MGQ&feature=aso
		preg_match_all('/^https?:\/\/www.youtube.com\/(v\/|watch\?v=)([^&]*)(.*)$/', $url, $yarr);
		if(isset($yarr[2][0])) {
			$thumb = "http://img.youtube.com/vi/".$yarr[2][0]."/1.jpg";
		}
	
		// Check vor Vimeo urls:
		preg_match_all('/^https?:\/\/vimeo.com\/([0-9]*)$/', $url, $varr);
		if(isset($varr[1][0])) {
			$vimeoInfo = $this->getVimeoInfo($varr[1][0]);
			$thumb = $vimeoInfo["thumbnail_small"];
		}
		
		return array("title"=>$title,"description"=>$description,"thumb"=>$thumb);
	}

	/*
		Get Facebook counters for a url using Facebook Apis.
		return an array with title, descriptiom, thumb
		$obj = new Minibots();
		$infos = $obj->readFacebookCounters("http://www.dailybest.it/2013/03/05/vita-programmatore-gif-animate/");
		---> Array
		(
			[total] => 7109
			[likes] => 3438
			[shares] => 1937
			[clicks] => 0
			[comments] => 1734
		)
	*/
	public function readFacebookCounters($url) {
		// returns the counters of facebook likes + shares + comments...
		$query = "select total_count,like_count,share_count,click_count,comment_count from link_stat WHERE url ='" . $url ."'";
		$s = file_get_contents("https://api.facebook.com/method/fql.query?query=".urlencode($query)."&format=json");
		$ar = json_decode($s);
		if(isset($ar[0])) {
			return array(
				"total"=>$ar[0]->total_count,
				"likes"=>$ar[0]->like_count,
				"shares"=>$ar[0]->share_count,
				"clicks"=>$ar[0]->click_count,
				"comments"=>$ar[0]->comment_count
			);
		}
		return false;
	}

	/*
		Get number of tweets with the specified url counters for a url using Facebook Apis.
		return a number
		$obj = new Minibots();
		$infos = $obj->readTwitterCounters("http://www.dailybest.it/2013/03/05/vita-programmatore-gif-animate/");
		---> 175
	*/
	public function readTwitterCounter($url) {
		$s = file_get_contents("http://urls.api.twitter.com/1/urls/count.json?callback=?&url=".urlencode($url));
		$ar = json_decode($s);
		if(isset($ar->count)) return $ar->count; else return 0;
	}

	/*
		Get number of Google +1s with the specified url using hidden Google Apis.
		return a number
		$obj = new Minibots();
		$infos = $obj->readGooglePlusCounter("http://www.dailybest.it/2013/03/05/vita-programmatore-gif-animate/");
		---> 175
	*/
	public function readGooglePlusCounter($url) {
		if (!function_exists("curl_init")) die("readGooglePlusCounter needs CURL module, please install CURL on your php.");
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		$curl_results = curl_exec ($curl);
		curl_close ($curl);
		$json = json_decode($curl_results, true);
		return intval( $json[0]['result']['metadata']['globalCounts']['count'] );
	}


	/*
		Get the keyword suggestion from google for a word and return an array with suggested keywords.
		$obj = new Minibots();
		$infos = $obj->googleSuggestKeywords("berlusconi");
		---> Array
		(
			[0] => berlusconi
			[1] => berlusconi news
			[2] => berlusconi bunga bunga
			[3] => berlusconi bunga bunga party
			[4] => berlusconi net worth
			[5] => berlusconi quotes
			[6] => berlusconi ruby
			[7] => berlusconi hump
			[8] => berlusconi trial
			[9] => berlusconi jail
		)
	*/
	public function googleSuggestKeywords($k) {
		if (!function_exists("curl_init")) die("googleSuggestKeywords needs CURL module, please install CURL on your php.");
		$k = explode(" ",$k); $k = $k[0];
		$u = "http://google.com/complete/search?output=toolbar&q=" . $k;
		$xml = simplexml_load_string(file_get_contents($u));
		// Parse the keywords 
		$result = $xml->xpath('//@data');
		$ar = array();
		while (list($key, $value) = each($result)) $ar[] = (string)$value;
		return $ar;
	}

	/*
		Get the latitude and longitude from an address using Google. If succeed returns an array,
		else return false.
		$obj = new Minibots();
		$poi = $obj->getLatLong("milan, italy");
		---> Array
		(
			[lat] => 45.465454000000001
			[long] => 9.1865159999999992
		)
	*/
	public function getLatLong($address){
		$_url = sprintf('http://maps.google.com/maps?output=js&q=%s',rawurlencode($address));
		if($_result = file_get_contents($_url)) {
			if(strpos($_result,'errortips') > 1 || strpos($_result,'Did you mean:') !== false) return false;
			// search coordinates inside the answer with regular expression
			preg_match('!center:\s*{lat:\s*(-?\d+\.\d+),lng:\s*(-?\d+\.\d+)}!U', $_result, $_match);
			$coords['lat'] = $_match[1];
			$coords['long'] = $_match[2];
			return $coords;
		}
		return false;
	}

	/*
		Get the latitude and longitude from an address using Google. If succeeds returns an array,
		else return false.
		$obj = new Minibots();
		$poi = $obj->wikiDefinition("Barack Obama");
		---> Array
		(
			[0] => Barack Obama
			[1] => Barack Hussein Obama II (; born August 4, 1961) is the 44th and current President of the United States, the first African American to hold the office. 
			[2] => http://en.wikipedia.org/wiki/Barack_Obama
		)
	*/
	public function wikiDefinition($s,$wikilang="en") {
		$url = "http://".$wikilang.".wikipedia.org/w/api.php?action=opensearch&search=".urlencode($s)."&format=xml&limit=1";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
		curl_setopt($ch, CURLOPT_POST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, false);		// Include head as needed
		curl_setopt($ch, CURLOPT_NOBODY, FALSE);		// Return body
		curl_setopt($ch, CURLOPT_VERBOSE, FALSE);		// Minimize logs
		curl_setopt($ch, CURLOPT_REFERER, "");			// Referer value
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);// No certificate
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);	// Follow redirects
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);			// Limit redirections to four
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);	// Return in string
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; he; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");   // Webbot name
		$page = curl_exec($ch);
		$xml = simplexml_load_string($page);
		if((string)$xml->Section->Item->Description) {
			return array((string)$xml->Section->Item->Text, (string)$xml->Section->Item->Description, (string)$xml->Section->Item->Url);
		} else {
			return "";
		}
	}
	
	/*
		Get vimeo video info using the id of the video if success return an array with many infos
		else return false.
		$obj = new Minibots();
		$poi = $obj->getVimeoInfo("75976293");
		---> Array
	(
		[id] => 75976293
		[title] => AWAKEN
		[description] => Fort Myers and Sanibel [...LONG TEXT...]
		[url] => http://vimeo.com/75976293
		[upload_date] => 2013-10-02 12:13:39
		[mobile_url] => http://vimeo.com/m/75976293
		[thumbnail_small] => http://b.vimeocdn.com/ts/450/665/450665474_100.jpg
		[thumbnail_medium] => http://b.vimeocdn.com/ts/450/665/450665474_200.jpg
		[thumbnail_large] => http://b.vimeocdn.com/ts/450/665/450665474_640.jpg
		[user_id] => 9973169
		[user_name] => Cameron Michael
		[user_url] => http://vimeo.com/user9973169
		[user_portrait_small] => http://b.vimeocdn.com/ps/377/229/3772290_30.jpg
		[user_portrait_medium] => http://b.vimeocdn.com/ps/377/229/3772290_75.jpg
		[user_portrait_large] => http://b.vimeocdn.com/ps/377/229/3772290_100.jpg
		[user_portrait_huge] => http://b.vimeocdn.com/ps/377/229/3772290_300.jpg
		[stats_number_of_likes] => 2503
		[stats_number_of_plays] => 60265
		[stats_number_of_comments] => 88
		[duration] => 313
		[width] => 1920
		[height] => 1080
		[tags] => florida, timelapse, nature, birds, dolphin, thunder, lightning, 4k, stars, skies
		[embed_privacy] => anywhere
	)

	*/
	public function getVimeoInfo($id) {
		if (!function_exists('curl_init')) die('CURL is not installed!');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://vimeo.com/api/v2/video/$id.php");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = unserialize(curl_exec($ch));
		curl_close($ch);
		return isset($output[0]) && is_array($output[0]) ? $output[0] : false;
	}

	/*
		function get geographic information from ip address
	*/
	public function ipToGeo($ip="") {
		if(!$ip) $ip = $this->getIP();
		$ar = file_get_contents("http://freegeoip.net/json/".$ip);
		return json_decode($ar);
	}

	
	// ---------------------------------------- new methods --------------------------------------

	/*
		function to convert from euro to any currency, you must
		use the standard currency codes, list of codes and more informations
		here: http://www.ecb.europa.eu/stats/exchange/eurofxref/html/index.en.html
		echo $mb->getExchangeRateFromEurTo("USD"); ---> 1.3432
	*/
	public function getExchangeRateFromTo($from,$to) {
		$XMLContent=file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");
		if(!function_exists("ge")) {
			function ge($currency,$XMLContent) {
				foreach($XMLContent as $line){
					if(preg_match("/currency='([[:alpha:]]+)'/",$line,$currencyCode)){
						if(preg_match("/rate='([[:graph:]]+)'/",$line,$rate)){
							if($currencyCode[1]==$currency) return $rate[1];
						}
					}
				}
				return "";
			}
		}
		$FROM = $TO = 1;
		if($from!="EUR") {
			$temp = ge($from,$XMLContent);
			if($temp=="") return "";
			$FROM = 1/$temp;
		}
		if($to!="EUR") {
			$TO = ge($to,$XMLContent);
			if($TO=="") return "";
		}
		return $FROM*$TO;
	}

	/*
		function to search for images with a key or a phrase
		scraping contents from www.picsearch.com
		$pics = $mb->getImage("apple fruit");
		echo "<img src=\"".$pics[rand(0,count($pics)-1)]."\"/>";
	*/
	public function getImage($key) {
		//
		// scraping content from picsearch
		$temp = file_get_contents("http://www.picsearch.com/index.cgi?q=".urlencode($key));
		preg_match_all("/<img class=\"thumbnail\" src=\"([^\"]*)\"/",$temp,$ar);
		if(is_array($ar[1])) return $ar[1];
		return false;
	}


	/*
		function to open the connection with Notify My Android.
		need an apikey, which is free.
		you can find it here: https://www.notifymyandroid.com/
		you can download the free app to receive the notifications
		from the Google Play store.
		usage

	*/
	private function notifyNmaOpen($apikey) {
		if (!function_exists("curl_init")) die("notifyNmaOpen needs CURL module, please install CURL on your php.");
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://www.notifymyandroid.com/publicapi/verify?apikey=".$apikey);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		echo "notify open<br/>";
		$curl_results = curl_exec ($curl);
		echo(htmlspecialchars($curl_results));
		curl_close ($curl);
		return preg_match("/code=\"200\"/",$curl_results);
	}
	public function notifyNma($apikey,$title,$text,$link="",$application="minibots") {
		$check = false;
		if(!$this->notifyNmaOpened) {
			$check = $this->notifyNmaOpen($apikey);
			$this->notifyNmaOpened= $check;
		} else {
			$check = true;
		}
		if($check) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, "https://www.notifymyandroid.com/publicapi/notify");
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, 'apikey='.urlencode($apikey)."&application=".urlencode($application).'&event='.urlencode($title)."&description=".
				urlencode($text)."&url=".urlencode($link) );
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			echo "invio<br/>";
			$curl_results = curl_exec ($curl);
			echo(htmlspecialchars($curl_results));
			curl_close ($curl);
			return preg_match("/code=\"200\"/",$curl_results);
		}
	}
	
	/*
		send a ping to pingomatic services to help bloggers
		to index their posts in search engines;
	*/
	public function pingomatic($title,$url,$feed="") {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "http://pingomatic.com/ping/?title=".urlencode($title)."&blogurl=".urlencode($url)."&rssurl=".urlencode($feed)."&chk_weblogscom=on&chk_blogs=on&chk_feedburner=on&chk_newsgator=on&chk_myyahoo=on&chk_pubsubcom=on&chk_blogdigger=on&chk_weblogalot=on&chk_newsisfree=on&chk_topicexchange=on&chk_google=on&chk_tailrank=on&chk_skygrid=on&chk_collecta=on&chk_superfeedr=on");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; he; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");   // Webbot name
		$curl_results = curl_exec ($curl);
		curl_close ($curl);
		return preg_match("/(Pinging complete!)/",$curl_results);
	}

	
}

?>