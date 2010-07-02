<?php 
/* Connection & Typical Settings. */
$conf['server']        = "";
$conf['port']          = 6667;
$conf['botname']       = "";
$conf['realname']      = "";
$conf['password']      = "";
$conf['emailadd']      = "";
$conf['bot_owner']     = "";
$conf['bot_owner_host'] = "";
$conf['user_modes']    = "+RB";
 
/* Do Not Edit This Line. */
$conf['authd_user']    = array();
$conf['authd_host']    = array(); 
/* Add Authd Users Here. */
$conf['authd_user'][1] = "";
$conf['authd_host'][1] = "";

$conf['authd_user'][2] = "";
$conf['authd_host'][2] = "";

$conf['authd_user'][3] = "";
$conf['authd_host'][3] = "";

$conf['authd_user'][4] = "";
$conf['authd_host'][4] = "";

$conf['authd_user'][5] = "";
$conf['authd_host'][5] = "";

$conf['authd_user'][6] = ""; 
$conf['authd_host'][6] = ""; 
 
/* Autojoin Rooms. */
$autojoin[1] = "";
$autojoin[2] = "";
$autojoin[3] = "";
$autojoin[4] = "";
$autojoin[5] = "";
 
/* Bot Commands Prefix. */
$pf = ".";
 
/* Max String Length The IRC Server Will Allow Per Line.
 * Its Anywhere Between 180-510 Characters. */
$MaxStrlen = 250;
 
 
/***** USER SETTINGS END *****/

$user = exec("whoami");          
if ($user == "root") { die("You can not run this as Root. It can cause serious problems.\r\n"); }
 
/* Prevent Script Stopping. */
set_time_limit(0);
 
$startup = 0;
 
/* Define The Socket. */
$socket = ircConnect();
 
/* Font Type Variables Array. */
$font['n'] = "\x0f";// normal font & color.
$font['b'] = "\x02";// bold.
$font['u'] = "\x1f";// underline.
$font['k'] = chr(3);// mIRC Equivalent Of Ctrl+K.
 
/* Main Loop. */
while(1){
	/* Read Server Data. */
	$buffer = @socket_read($socket, 256, 0);
 
	/* Force Reconnection if Ping Timeout. */
	if ($buffer == false){ socket_close($socket); $socket = ircConnect(); }
 
	/* Seperate All Data */
	preg_match("/^:(.*?)!(.*?)@(.*?)[\s](.*?)[\s](.*?)[\s]:(.*?)$/",$buffer, $rawdata);
	$nick = $rawdata[1];
	$ident = $rawdata[2];
	$host = $rawdata[3];
	$msg_type = $rawdata[4];
	$chan = $rawdata[5]; 
	$args = trim($rawdata[6]);
 
	/* Split Data Into An Array. */
	$ex = explode(' ', $buffer);
 
	/* Send PONG Back To The Server. */
	if($ex[0] == "PING"){ SockSend($socket, "PONG ".trim($ex[1])); }
 
	/* Rejoin On Kick. */
	if ($ex[1] == "KICK"){ SockSend($socket, "JOIN ".trim($ex[2])); }
 
	/* Handle A Private Message 'PM'. */
	if ($chan == $conf['botname']){ $chan = $nick; }
 
	/* Create The Trigger. */
	$args = explode(" ", $args);
	$trigger = array_shift($args);
	$args = implode(" ", $args);
 
	/* Create A Switch For  The Trigger. */
	switch (strtolower($trigger)){
 
		/*** Owner Commands Start. ***/
 
		/* Quit Irc. */
		case $pf."quit":
			if ($nick == $conf['bot_owner']  || $host == $conf['bot_owner_host']){
				PrintData($trigger.' '.$args, $nick);
				if ($args == ''){
					SockSend($socket, "QUIT :".$conf['quit_message']);
				} else {
					SockSend($socket, "QUIT :".$args);
					}
				die('<b>Terminating Client Session . . .</b>');
				}
			break;
 
		/* Restart Client */
		case $pf."restart":
			if ($nick == $conf['bot_owner'] || $host == $conf['bot_owner_host']){
				PrintData($trigger.' '.$args, $nick);
				socket_close($socket); $socket = ircConnect();
				}
			break;

date_default_time_zone(-8);
 
		/* Add, Del, List, Authd User. */
		case $pf."auth":
			if ($nick == $conf['bot_owner'] || $host == $conf['bot_owner_host']){
				PrintData($trigger.' '.$args, $nick);
				$args = explode(" ", $args);
				$authcmd = array_shift($args);
				$args = implode(" ", $args);
				switch(strtolower($authcmd)){
					case "add":
						if ($args == '') {
							NOTICE($nick, $font['b']."You must supply a nick you want adding to the list");
						} elseif (in_array($args, $conf['authd_user'], true)){
							NOTICE($nick, $font['b']."".$args." is already in the authd user list.");
						} else {
							array_push($conf['authd_user'], $args);
							NOTICE($nick, $font['b']."".$args." added to temp authd user list.");
							}
						break;
					case "del":
						if ($args == '') {
							NOTICE($nick, $font['b']."You must supply a nick you want deleting from the list.");
						} elseif (in_array($args, $conf['authd_user'], true)){
							foreach($conf['authd_user'] as $k => $v){
								if ($v == $args){
									unset($conf['authd_user'][$k],$conf['authd_user'][$v]);
									NOTICE($nick, $font['b']."".$args." deleted from temp authd user list.");
								}
							}
						} else {
							NOTICE($nick, $font['b']."".$args." not found in temp authd user list.");
							}
						break;
					case "list":
						NOTICE($nick, $font['b']."".$conf['botname']."'s Auth'd User list.");
						NOTICE($nick, $font['b']."    Num  Nick");
						foreach($conf['authd_user'] as $k => $v){
							if ($v == ''){ $v = 'Not Set..'; }
							if ($k > 9){
								NOTICE($nick, $font['b']."     ".$k."  ".$v);
							} else {
								NOTICE($nick, $font['b']."      ".$k."  ".$v);
								}	
							}
						break;
					default:
						NOTICE($nick, $font['b']."Auth Commands Help.");
						NOTICE($nick, $font['b']."".$pf."auth add nick");
						NOTICE($nick, $font['b']."".$pf."auth del nick");
						NOTICE($nick, $font['b']."".$pf."auth list");
						break;
					}
				}
			break;
 
		/* Exec PHP code. Use 'Print'. */
		case $pf."eval":
			if ($nick == $conf['bot_owner'] || $host == $conf['bot_owner_host']){
				PrintData($trigger.' '.$args, $nick);
				$output = Eval($args);
				PRIVMSG($chan, $output);
				}
			break;


		case $pf."shell":
                        if ($nick == $conf['bot_owner'] || $host == $conf['bot_owner_host']){
                                PrintData($trigger.' '.$args, $nick);
  $ls = shell_exec($args); $ls = explode("\n", $ls); foreach ($ls as $ls) { privmsg($chan, $ls); time_nanosleep(0, 500000000); }       
                                }
                        break;

 
		/*** Owner Commands End. ***/
		/*** Auth'd & Owner Commands Start. ***/
 
		/* Join Room. */
		case $pf."join":
			if ($nick == $conf['bot_owner'] || $host == $conf['bot_owner_host']){
				PrintData($trigger.' '.$args, $nick);
				if (preg_match("%^#[A-Za-z0-9\\\/.\-_']+$%", $args)){
					PRIVMSG($chan, "Joining ".$args);
					SockSend($socket, "JOIN ".$args);
				} else {
					PRIVMSG($chan, $args." Invalid room name.");
				}
			}
			break;
 
		/* Part Room. */
		case $pf."part":
			if ($nick == $conf['bot_owner'] || $host == $conf['bot_owner_host']){
				PrintData($trigger.' '.$args, $nick);
				if ($args == ''){
					PRIVMSG($chan, "Parting ".$chan);
					SockSend($socket, "PART ".$chan);
				} else {
					if (preg_match("%^#[A-Za-z0-9\\\/.\-_']+$%", $args)){
						PRIVMSG($chan, "Parting ".$args);
						SockSend($socket, "PART ".$args);
					} else {
						PRIVMSG($chan, $args." Invalid room name.");
						}
					}
				}
			break;
 
		/* Change The Commands Trigger. */
		case $pf."trigger":
			if ($nick == $conf['bot_owner']){
				PrintData($trigger.' '.$args, $nick);
				if (strlen($args) === 1){
					$pf = $args;
					PRIVMSG($chan, "Trigger changed to: ".$pf);
				} else {
					NOTICE($nick, $font['b']."You may only set 1 character as the commands trigger.");
					}
				}
			break;
 
		/*** Auth'd & Owner Commands End. ***/
		/*** All User Commands Start. ***/

		/* Commands. */
		case $pf."commands":
			PrintData($trigger.' '.$args, $nick);
		PRIVMSG($chan, ".tinyurl <url> (reverse a tinyurl. | generate a tinyurl.) .chuck .vin .mrt .jack .weather <weather location.> .urban <term> (urban dictionary.) .wiki <term> (wikipedia.) .youtube <query> (3 results default.) .discogs <query> (3 results default.) .imdb <query> (3 results default.) .google <query> (3 results default.) .gs <query> (google search, 1 result.) .php <query> (php.net search, 1 result.) .acro <acronym> (acro definitions)");
                PRIVMSG($chan, ".calc <query> (google calculator.) .port <ip> <port> (port scan.) .translate langfrom langto query");
 		break;

		/* TinyUrl. */
		case $pf."tinyurl":
			PrintData($trigger.' '.$args, $nick);
			PRIVMSG($chan, TinyUrl($args));
			break;
 
		/* Random Facts. */
		case $pf."jack":
			PrintData($trigger.' '.$args, $nick);
			Truncate(JackBauer(), $chan);
			break;
		case $pf."chuck":
		case $pf."mrt":
		case $pf."vin":
			PrintData($trigger.' '.$args, $nick);
			$Person = ltrim($trigger, $pf);
			Truncate(RandomFacts($Person), $chan);
			break;
 
		/* Weather. */
		case $pf."weather":
			PrintData($trigger.' '.$args, $nick);
			PRIVMSG($chan, WunderGround($args, $nick));
			break;
		
		/* Imdb Quotes. */
		case $pf."quote":
			ImdbQuotes($args, $chan);
			break;

		/*Google Traslator. */
		case $pf."translate":
			$words = explode(" ", $args);
			if (count($words) < 3) {
				PRIVMSG($chan, "Please ".$pf."trans langfrom langto yourtextyouwanttranslating");
				break;
				}
			$from = array_shift($words);
			$to = array_shift($words);
			$words = implode(" ", $words);
			translate($from, $to, $words, $chan);
			break;
 
		default:
			if ($AIChat == true && $msg_type == 'PRIVMSG'){
				$chatter = $trigger.' '.$args;
				$message = aichat($chatter, $nick);
				if ($message){ PRIVMSG($chan, $message); }
				}
			break;

 
		/* Urban Dictionary. */
		case $pf."urban":
			PrintData($trigger.' '.$args, $nick);
			UrbanDict($args, $chan);
			break;
 
		/* Google search. */
		case $pf."gs":
		case $pf."google":
			PrintData($trigger.' '.$args, $nick);
			if($trigger == $pf."google"){ GoogleSearch($args, $chan, 3); } else { GoogleSearch($args, $chan, 1); }
			break;
 
		/* Site search. (3 results) */
		case $pf."youtube":
		case $pf."discogs":
		case $pf."imdb":
			PrintData($trigger.' '.$args, $nick);
			GoogleSearch($args, $chan, 3, $siteSearch = ltrim($trigger, $pf));
			break;
 
		/* Site Search. (1 result)  */
		case $pf."php":
			PrintData($trigger.' '.$args, $nick);
			GoogleSearch($args, $chan, 1, $siteSearch = ltrim($trigger, $pf));
			break;
 
		/* Acronyms. */
		case $pf."acro":
			PrintData($trigger.' '.$args, $nick);
			Acronyms($args, $chan);
			break;
 
		/* Port Scan. */
		case $pf."port":
			PrintData($trigger.' '.$args, $nick);
			list($host, $port) = explode(' ', $args);
			if(empty($port)){
				list($host, $port) = explode(':', $args);
				}
			Portscan($host, $port, $chan);
			break;
 
		/* Google Calculator. */
		case $pf."calc":
			PrintData($trigger.' '.$args, $nick);
			PRIVMSG($chan, GoogleCalc($args));
			break;
 
		/* Wikipedia. */
		case $pf."wiki":
			PrintData($trigger.' '.$args, $nick);
			Wikipedia($args, $chan);
			break;
 
		/*** All User Commands End. ***/
 
		} // End Of Switch.
	}
 
/* Irc Connect. */
function ircConnect(){
	global $conf,$startup;
	if($startup == 1){ print nl2br("<b>Restarting Client . . .</b>\n"); }
	if($startup == 0){ print nl2br("<font color=\"#736F6E\"><i><b>Starting Client Session . . .\n".date('l jS \of F Y H:i:s')."</b>\n"); $startup = 1; }
 
	/* Create a TCP/IP Socket. */
	print nl2br(date('H:i:s')." - <b>Creating Socket...</b>\n");
	$socket = @socket_create( AF_INET, SOCK_STREAM, 0);
	if ($socket < 0) {
		print nl2br(date('H:i:s')." - <b>\"socket_create()\" failed.\nReason: ".socket_strerror($socket)."</b>\n");
		exit;
	} else {
		print nl2br(date('H:i:s')." - OK!\n");
	}
	flush();
 
	/* Attempt To Connect To Server. */
	print nl2br(date('H:i:s')." - <b>Attempting to Connect to ".$conf['server']." on Port ".$conf['port']."...</b>\n");
	$result = @socket_connect($socket, $conf['server'], $conf['port']);
	if ($result === FALSE) {
		print nl2br(date('H:i:s')." - <b>\"socket_connect()\" failed.\nReason: ".socket_strerror($result)."</b>\n");
		exit;
	} else {
		print nl2br(date('H:i:s')." - OK!\n");	
	}
	flush();
	ircLogin($socket);
 
	/* Return Socket. */
	return $socket;
	}
 
function ircLogin($socket){
	global $conf,$autojoin;
	/* Login. */
	print nl2br(date('H:i:s')." - <b>Sending Login Data...</b>\n");
	SockSend($socket, "USER ".$conf['botname']." ".strtolower($conf['botname'])." ".$conf['botname']." :".$conf['realname'], "login");
	SockSend($socket, "NICK ".$conf['botname'], "login");
	if ($conf['password'] != ''){ SockSend($socket, "PRIVMSG NICKSERV :IDENTIFY ".$conf['password'], "login"); }
	if ($conf['user_modes'] != ''){ SockSend($socket, "MODE ".$conf['botname']." ".$conf['user_modes'], "login"); }
	flush();
	sleep(2);
 
	/* Join Autojoin Room/s. */
	print nl2br(date('H:i:s')." - <b>Attempting To Join Rooms...</b>\n");
	foreach ($autojoin as $key => $rooms){
		if ($rooms != ''){
			SockSend($socket,"JOIN ".$rooms, "login");
			}
		}
	flush();
	}
 
/* Print Infos @Browser. */
function PrintData($Message, $User = null){
	global $conf,$msg_type,$chan;
	$Time = date('H:i:s');
	if ($User === null){ 
		$User = $conf['botname'];
		$Print = $Time." - ".$User." ".$Message;
	} else {
		$Print = $Time." - ".$User." ".$msg_type." ".$chan." :".$Message."\n";
		}
	print nl2br($Print);
	flush();
	}
 
/* Socket Send: Login, Join, Parts, Quits, Etc. */
function SockSend($socket, $data, $login = null){
	$data = $data."\n";
	if(@socket_send($socket, $data , strlen($data), 0) == true){
	} else {
		if($login == 'login'){
			print nl2br(date('H:i:s')." - <b>\"socket_send()\" failed. (Throttled: Reconnecting too fast?)...\nTry Reconnecting In A Minute . . .</b>\n");
			exit;
		} else {
			print nl2br(date('H:i:s')." - <b>\"send failed.\nReason: ".socket_strerror($socket)."</b>\n");
			exit;
			}
		}
	PrintData($data);
	}
 
/* Privmsg. */
function PRIVMSG($msgTarget, $msgContents){
	global $socket;
	$msgData = "PRIVMSG ".$msgTarget." :".$msgContents."\r\n";
	socket_send($socket, $msgData, strlen($msgData), 0);
	PrintData($msgData);
	}
 
/* Notice. */
function NOTICE($msgTarget, $msgContents){
	global $socket;
	$msgData = "NOTICE ".$msgTarget." :".$msgContents."\n";
	socket_send($socket, $msgData, strlen($msgData), 0);
	PrintData($msgData);
	}
 
/* Chanserv. */
function CHANSERV($msgContents){
	global $socket;
	$msgData = "CHANSERV ".$msgContents."\n";
	socket_send($socket, $msgData, strlen($msgData), 0);
	PrintData($msgData);
	}
 
/* Nickserv. */
function NICKSERV($msgContents){
	global $socket;
	$msgData = "PRIVMSG NICKSERV ".$msgContents."\n";
	socket_send($socket, $msgData, strlen($msgData), 0);
	PrintData($msgData);
	}
 
/* Mode. */
function MODE($msgContents){
	global $socket;
	$msgData = "MODE ".$msgContents."\n";
	socket_send($socket, $msgData, strlen($msgData), 0);
	PrintData($msgData);
	}
 
 
/*** General Functions. ***/
 
 
/* Eval. */
function EvalBuffer($code){
	$output = eval($code);
		return $output;
		}
	
 
/* TinyUrl */
function TinyUrl($url){
	global $font;
	if(preg_match('/http:\/\/tinyurl\.com\/[a-zA-Z0-9-]{4,30}+$/', $url)){
		$key = explode('.com/', $url);
		preg_match('/<a id="redirecturl" href="(.*)">/', file_get_contents('http://preview.tinyurl.com/'.$key[1]), $reply);
		return 'TinyUrl: '.$font['u'].$reply[1];
	} else {
		return 'TinyUrl: '.$font['u'].file_get_contents('http://tinyurl.com/api-create.php?url='.$url);
		}
	}
 
/* Random Facts. Chuck, Vin, Mrt. */
function RandomFacts($Person){
	$url = "http://4q.cc/index.php?pid=atom&person=".$Person;
	preg_match_all('/<summary>(.*?)<\/summary>/', file_get_contents($url), $matches);
	return html_entity_decode($matches[1][array_rand($matches[1])]);
	}
 
/* Random Facts. Jack Bauer. */
function JackBauer(){
	$url = "http://www.jackbauerfacts.com/fact/random";
	preg_match('/<div style=".*">Fact ID #\d{1,5}:[\s](.*?)<\/div>/', file_get_contents($url), $matches);
	return html_entity_decode($matches['1']);
	}

/* IMDB Film Quotes. */
function ImdbQuotes($SearchQuery, $chan){
	if (!empty($SearchQuery)){
		$url = GoogleSearch($SearchQuery, $chan, 1, "quotes");
		$contents = file_get_contents($url);
		$contents = strstr($contents, '<!-- End TOP_RHS -->');
		$contents = preg_replace('/[\r\n\t ]+/', ' ', $contents);
		$contents = str_replace('&#x27;', "'", $contents);
		$array = explode('<hr width="30%">', trim($contents));
		$num = count($array);
		if ($num){
			$num = mt_rand(0,$num)-1;
			preg_match_all('/(.*?)<\/b>:(.*?)<br>/', $array[$num], $matches);
			$count = count($matches['0']);
			for($i=0; $i < $count; $i++){
				preg_match('/(.*?)<\/b>:(.*?)<br>/', $matches['0'][$i], $matches1);
				Truncate(trim(strip_tags($matches1['1'])).': '.trim(strip_tags($matches1['2'])), $chan);sleep(1);
			}
		} else {
			PRIVMSG($chan,'There were no results for "'.$SearchQuery.'".');
			}
	} else {
		PRIVMSG($chan,'Try searching a Film/Show title.');
		}
	}

/*Google Translator. (Language Check) */
function lang_check($lang, $languages) {
 
	/* Convert to lower case and trim. */
	$lang = trim(strtolower($lang));
 
	/* Check to see if the language is in the array. */
	if (array_key_exists($lang, $languages)) { return $lang; }
	foreach ($languages as $this_lang => $this_init) {
		if ($lang == $this_init) { return $this_lang; }
		}
 
	/* Check for abbreviations and mis-spellings. */
	switch ($lang) {
		case "sq":
		case "alb":
		case "alban":
		case "albanien":
		case "albanese":
			return "albanian";
			break;
		case "cs":
		case "check":
		case "chezc":
		case "cz":
			return "czech";
			break;
		case "da":
		case "dane":
		case "dan":
			return "danish";
			break;
		case "nl":
		case "ned":
		case "nederlands":
			return "dutch";
			break;
		case "en":
		case "eng":
		case "england":
		case "brit":
		case "british":
			return "english";
			break;
		case "et":
		case "est":
		case "estonien":
			return "estonian";
			break;
		case "tl":
		case "fil":
		case "filapino":
		case "philipino":
			return "filipino";
			break;
		case "fi":
		case "fin":
		case "finn":
		case "finish":
			return "finnish";
			break;
		case "fr":
		case "fra":
		case "fren":
		case "fre":
		case "francais":
			return "french";
			break;
		case "gl":
		case "gal":
		case "galicien":
			return "galician";
			break;
		case "de":
		case "germ":
		case "deutsche":
		case "deutch":
		case "ger":
			return "german";
			break;
		case "el":
		case "grek":
		case "greece":
		case "grk":
		case "gre":
			return "greek";
			break;
		case "hu":
		case "hun":
		case "hungary":
		case "hungarien":
			return "hungarian";
			break;
		case "in":
		case "ind":
		case "indonesien":
			return "indonesian";
			break;
		case "it":
		case "ita":
		case "italy":
			return "italian";
			break;
		case "ja":
		case "jap":
		case "japan":
		case "japenese":
			return "japanese";
			break;
		case "lv":
		case "lat":
		case "latvien":
			return "latvian";
			break;
		case "pt":
		case "por":
		case "port":
		case "portugal":
		case "portugese":
			return "portuguese";
			break;
		case "es":
		case "sp":
		case "spa":
		case "spain":
		case "spannish":
			return "spanish";
			break;
		case "ru":
		case "rus":
		case "russia":
		case "russien":
			return "russian";
			break;
		}
 
	/* Return FALSE if we havent found a match. */
	return FALSE;
 
	}

/*Google Translator. (Main) */
function translate($from, $to, $TextToTranslate, $chan) {
 
	// Define languages and their initials.
	$languages = array(
		"albanian"=>"sq",
		"czech"=>"cs",
		"danish"=>"da",
		"dutch"=>"nl",
		"english"=>"en",
		"estonian"=>"et",
		"filipino"=>"tl",
		"finnish"=>"fi",
		"french"=>"fr",
		"galician"=>"gl",
		"german"=>"de",
		"greek"=>"el",
		"hungarian"=>"hu",
		"indonesian"=>"id",
		"italian"=>"it",
		"latvian"=>"lv",
		"japanese"=>"ja",
		"portuguese"=>"pt",
		"spanish"=>"es",
		"russian"=>"ru",
		);
 
	// Parse for alternative spellings.
	$to = lang_check($to, $languages);
	$from = lang_check($from, $languages);
 
	// Check to and from languages.
	if ($to == "" || $from == "" || !array_key_exists($to, $languages)
	|| !array_key_exists($from, $languages) || $to === FALSE || $from === FALSE) {
		return FALSE;
		}
	$url = "http://www.google.com/translate_t?text=".urlencode($TextToTranslate)."&langpair=".$languages[$from]."|".$languages[$to]."#";
	$contents = @file_get_contents($url);
	if ($contents){
		preg_match('/<input type=hidden name=gtrans value="(.*?)">/', $contents, $match);
		$result = Translation . ' ('.ucfirst($from).' to '.ucfirst($to).'): ' . trim(preg_replace('/[\r\n\t ]+/', ' ', $match['1']));
		PRIVMSG($chan, $result);
	} else {
		PRIVMSG($chan, $http_response_header[0]);
		}
 
/* Urban Dictionary. */
function UrbanDict($urban_query, $chan){
	global $font;
	$url = "http://www.urbandictionary.com/define.php?term=" . urlencode($urban_query);
	$contents = file_get_contents($url);
	if (empty($urban_query)){
		PRIVMSG($chan, "Please provide a search query.");
	} elseif (strpos($contents, "<div id='not_defined_yet'>")){
		PRIVMSG($chan, $font['b'].$urban_query.$font['n']." isn't defined yet.");
	} elseif (strpos($contents, "Service Temporarily Unavailable")) {
		PRIVMSG($chan, "Service temporarily unavailable. Please try again later.");
	} else {
		preg_match_all("/<a.*href=.*defin.*term=.*>(.*?)<\/a>/", $contents, $matches);
		$limit = count($matches['0']) < 18 ? count($matches['0']) : 18;
		for($i=0; $i < $limit; $i++){
			preg_match("/<a.*href=.*defin.*term=.*>(.*?)<\/a>/", $matches['0'][$i], $titles);
			$urban_titles .= ", ".$titles['1'];
			}
		$contents = trim(preg_replace('/[\r\n\t ]+/', ' ', $contents));
		preg_match_all("/<div class='definition'>(.*?)<div class='example'>/", $contents, $matches1);
		preg_match_all("/<div class='example'>(.*?)<div class='greenery'>/", $contents, $matches2);
		$num = array_rand($matches1[1]);
		PRIVMSG($chan, $font['b']."Urban Dictionary:".$font['n']." ".ucwords(strtolower($urban_query)));
		sleep(1);
		Truncate($font['b']."Definition:".$font['n']." ".html_entity_decode(strip_tags(trim($matches1[1][$num]))), $chan);
		sleep(1);
		Truncate($font['b']."Example:".$font['n']." ".html_entity_decode(strip_tags(trim($matches2[1][$num]))), $chan);
		sleep(1);
		PRIVMSG($chan, $font['b']."Nearby Titles:".$font['n']." ".substr($urban_titles, 2));
		}
	}
 
/* Weather Underground. */
function WunderGround($WeatherLocation, $user){
	$url = 'http://api.wunderground.com/auto/wui/geo/WXCurrentObXML/index.xml?query='.urlencode($WeatherLocation);
	$s = @simplexml_load_file($url);
	if ($s){
		if ($s->display_location->city != ''){
			if ($s->observation_time != 'Last Updated on , '){ $lupd = ' Last Updated On ('.str_replace('Last Updated on ', '', $s->observation_time).')'; } else { $lupd = ''; }
			if ($s->windchill_f != 'NA'){ $feelslike = ' feels like '.$s->windchill_f.'°F/'.$s->windchill_c.'°C'; } else { $feelslike = ''; }
			if ($s->temp_f != ''){ $temp = ' Temperature is ('.$s->temp_f.'°F/'.$s->temp_c.'°C'.$feelslike.')'; } else { $temp = ''; }
			if ($s->weather != ''){ $cond = 'Conditions ('.$s->weather.')'; } else { $cond = ''; }
			if ($s->wind_string != ''){ $wind = ' Wind Temperature and Speed ('.trim($s->wind_string).')'; } else { $wind = ''; }
			if ($s->relative_humidity != ''){ $hum = ' Humidity ('.$s->relative_humidity.')'; } else { $hum = ''; }
			if ($s->dewpoint_f != ''){ $dewpnt = ' Dewpoint ('.$s->dewpoint_f.'°F/'.$s->dewpoint_c.'°C)'; } else { $dewpnt = ''; }
			return $user.': from '.$s->display_location->full.'.'.$lupd.''.$temp.''.$cond.''.$wind.''.$hum.''.$dewpnt;
		} else {
			return $user.': City Not Found.';
			}
	} else {
		return $user.': '.$http_response_header[0];
		}
	}
 
/* Acronyms. */ 
function Acronyms($query, $chan){
	if ($query == null){ PRIVMSG($chan, "Please provide a search query.");
	} else{
		$url = "http://acronyms.thefreedictionary.com/".$query;
		preg_match_all('/<*td><td>(.*?)<\/td>/', file_get_contents($url), $matches);
		if (!$matches[1][0]){
			PRIVMSG($chan, "There were no results for $query");
		} else {
			$limit1 = count($matches['0']) < 5 ? count($matches['0']) : 5;
			$limit2 = count($matches['0']) < 10 ? count($matches['0']) : 10;
			for($i=0; $i < $limit1; $i++){ $result1 .= " | ".html_entity_decode(strip_tags($matches[1][$i])); }
			for($i=$limit1; $i < $limit2; $i++){ $result2 .= " | ".html_entity_decode(strip_tags($matches[1][$i])); }
			PRIVMSG($chan, substr($result1, 3)); if ($limit2 > 5) { PRIVMSG($chan, substr($result2, 3)); }
			}
		}
	}
 
/* Google & Site Search. */
function GoogleSearch($query, $chan, $limit, $siteSearch = null){
	if ($query == null){ PRIVMSG($chan, "Please provide a search query.");
	} else {
		switch($siteSearch){
			case "discogs":
				$site = '+site%3Adiscogs.com';
				break;
			case "youtube":
				$site = '+site%3Ayoutube.com';
				break;
			case "imdb":
				$site = '+site%3Aimdb.com';
				break;
			case "php":
				$site = '+site%3Aphp.net';
				break;
			default:
				$site = '';
				break;
			}
		$url = 'http://www.google.com/search?q='.urlencode($query).$site;
		preg_match_all('/<h3 class=r>(.|[\r\n])*?<\/h3>/', file_get_contents($url), $matches);
		$limit = count($matches[0]) < $limit ? count($matches[0]) : $limit;
		for($i=0; $i < $limit; $i++){
			preg_match('/href="(.*?)"/', $matches[0][$i], $matches1);
			preg_match('/<h3 class=r>(.*?)<\/a>/', $matches[0][$i], $matches2);
			PRIVMSG($chan , html_entity_decode(strip_tags(str_replace("&#39;", "'", $matches2[1])))." -> \x1f".$matches1[1]);
			}
		}
	}
 
/* Port Scan. */
function Portscan($host, $port, $chan){
	$fp = @fsockopen($host, $port, $errno, $errstr, 10);
	if($fp){
		PRIVMSG($chan, $host.':'.$port.' OPEN'); 
	} else { 
		PRIVMSG($chan, $host.':'.$port.' CLOSED');
		}
	}
 
/* Google Calculator. */
function GoogleCalc($query){
	if (!empty($query)){
		$url = "http://www.google.com/search?q=".urlencode($query);
		preg_match('/<h2 class=r style="font-size:138%"><b>(.*?)<\/b><\/h2>/', file_get_contents($url), $matches);
		if (!$matches['1']){
			return 'Your input could not be processed..';
		} else {
			return str_replace(array("Â", "<font size=-2> </font>", " &#215; 10", "<sup>", "</sup>"), array("", "", "e", "^", ""), $matches['1']);
			}
		}
	}
 
/* Wikipedia. */
function Wikipedia($query, $chan){
	global $font;
	$url = "http://www.google.com/search?q=en.wikipedia.org+".urlencode($query);
	preg_match_all('/<h3 class=r>(.|[\r\n])*?<\/h3>/', @file_get_contents($url), $match);
	for($i=0; $i < 1; $i++){
	    preg_match('/href="(.*?)"/', $match['0'][$i], $f_match);
		}
	if(strstr($f_match['1'], 'en.wikipedia.org') == true){ // else we didnt find a match
		$contents = @file_get_contents($f_match['1']);
		preg_match_all('/<p>(.*?)<\/p>/', $contents, $matches);
		$l=8;
		for($i=0; $i < $l; $i++){
			preg_match('/<p>(.*?)<\/p>/', $matches['0'][$i], $matches1);
			$matches1[$i] = strip_tags(str_replace(array("<b>", "</b>"), array($font['b'], $font['n']), $matches1[$i]));
			$matches1[$i] = html_entity_decode(preg_replace("/\[\d{1,2}\]/", "", $matches1[$i]));
			if (strlen($matches1[$i]) > 110){
				$l=$i;
				Truncate($matches1[$i], $chan);
				}
			}
		sleep(1);
		PRIVMSG($chan, $font['u']."".$f_match['1']);
	} else {
		PRIVMSG($chan, "No page with that title exists.");
		}
	}
 
/* Split Any Long Message Into Chunks. */
function Truncate($string, $chan, $order = null){
	global $MaxStrlen;
	if($order == 1){ $string = "..." . $string; }
	if (strlen($string) > $MaxStrlen){
		$msg1 = substr($string, 0, $MaxStrlen);
		$end = strrpos($msg1, " ");
		$msg1 = substr($msg1, 0, $end);
		$msg2 = substr($string, $end);
		PRIVMSG($chan, $msg1);
	} else {
		PRIVMSG($chan, $string);
		}
	if (strlen($msg2) > $MaxStrlen){
		Truncate($msg2, $chan, 1);
	} elseif (!empty($msg2)) {
		PRIVMSG($chan, "..." . trim($msg2));
		}
	}
 }
?>
