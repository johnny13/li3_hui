<?php
/* hui command */
namespace app\extensions\command;

class hui extends \lithium\console\Command {
 
 public $webdir   = "/var/www/hui/app";  //change to your apps web directory
 public $huibasic = "/extensions/command/templates/basic.html.php"; //Need to set this up manually. 
 //TODO: Write Install Script for Templates

 //The rest of this should only be editited if you're really bored.
 public $recipient;
 public $cmd;
 public $rowHTML = array();
 public $hui_dir  = "/webroot/hui";
 public $view_dir = "/views";
 public $hui_plugindir = "/plugins";

 public function run() {
  global $hui_dir;
  $hui_globaldir = $this->webdir.$this->hui_dir;

  if(is_dir($hui_globaldir)===true) {
  $huiVersionFile = $hui_globaldir."/hui.json";
  $huijson = json_decode(file_get_contents($huiVersionFile));
  $this->out(' ');
  $this->header(" LI3-hui ".$huijson->{'version'}." - ".$huijson->{'description'});
  $this->out(' ');
  $this->out('   li3 hui bot  - Install & Update hui Files');
  $this->out('   li3 hui page - Generate Custom View HTML');
  $this->out(' ');
  } else {
  $this->header('LI3-hui Command -- hui directory not detected --');
  $this->out(' ');
  $this->out(' Run each command for all options & params.');
  $this->out(' ');
  $this->out('   li3 hui bot  - Install & Update hui Files');
  $this->out('   li3 hui page - Generate Custom View HTML');
  $this->out(' ');
  }

  }

  private function buildHTMLFile($targetDir,$targetName){
  global $webdir, $hui_dir, $rowHTML, $view_dir, $huibasic;
  $hui_globaldir = $this->webdir.$this->hui_dir;

  $viewFile = $this->webdir.$this->view_dir."/".$targetDir."/".$targetName.".php";

  if(empty($rowHTML)){
  $this->out("   defaulting HTML -- ".$this->huibasic);
  $file = $this->webdir.$this->huibasic;

  if (!copy($file, $viewFile)) {
  $this->out("   failed to Write $viewFile \n");
  } else {
  $this->out("   wrote HTML $viewFile \n");
  }

  } else {
  $the_html = file_get_contents($this->webdir."/extensions/command/templates/header.txt");

  foreach($rowHTML as $row){
  $the_html .= $row;
  $the_html .= "\n";
  }

  $the_html .= file_get_contents($this->webdir."/extensions/command/templates/footer.txt");

  file_put_contents($viewFile, $the_html);
  $this->out("   wrote Custom HTML $viewFile \n");
  }
  }

  private function addRow(){
  global $webdir, $hui_dir, $rowHTML;
  $hui_globaldir = $this->webdir.$this->hui_dir;

  $this->out(" Template Options");
  $this->out("     1. Solo Column  [12]");
  $this->out("     2. Two Column   [6 6]");
  $this->out("     3. Two Column   [8 4]");
  $this->out("     4. Three Column [3 6 3]");
  $this->out("     5. Three Column [4 4 4]");
  $this->out("     6. Four Column  [3 3 3 3]");
  $this->out("     7. Sidebar Flip [3 9]");
  $this->out(" ");
  $this->out("Select Template | ex 2 :");
  $line = trim(fgets(STDIN));
  $viewColumns = $line;
  $this->out(" ");

  $this->out("Fluid or Limit (adds optional 'limit' class to row) | F / L :");
  $line = trim(fgets(STDIN));
  $viewRows = $line;

  if($viewRows == "F" || $viewRows == "f"){
  $rowStart = '<div class="container"><div class="row">';
  } else {
  $rowStart = '<div class="container"><div class="row limit">';
  }

  if($viewColumns == 1){
  $rowRow = '<div class="twelvecol last"> /* content here */ </div>';
  $total = $viewColumns;
  } 
  if($viewColumns == 2){
  $rowRow = '<div class="sixcol"> /* content here */ </div>';
  $rowRow .= '<div class="sixcol last"> /* content here */ </div>';
  $total = $viewColumns;
  }
  if($viewColumns == 3){
  $rowRow = '<div class="eightcol"> /* content here */ </div>';
  $rowRow .= '<div class="fourcol last"> /* content here */ </div>';
  $total = $viewColumns;
  }
  if($viewColumns == 7){
  $rowRow = '<div class="threecol"> /* content here */ </div>';
  $rowRow .= '<div class="ninecol last"> /* content here */ </div>';
  $total = $viewColumns;
  }
  if($viewColumns == 4){
  $rowRow = '<div class="threecol"> /* content here */ </div>';
  $rowRow .= '<div class="threecol"> /* content here */ </div>';
  $rowRow .= '<div class="threecol last"> /* content here */ </div>';
  $total = $viewColumns;
  }
  if($viewColumns == 5){
  $rowRow= '<div class="fourcol"> /* content here */ </div>';
  $rowRow.= '<div class="fourcol"> /* content here */ </div>';
  $rowRow.= '<div class="fourcol last"> /* content here */ </div>';
  $total = $viewColumns;
  }
  if($viewColumns == 6){
  $rowRow= '<div class="threecol"> /* content here */ </div>';
  $rowRow.= '<div class="threecol"> /* content here */ </div>';
  $rowRow.= '<div class="threecol"> /* content here */ </div>';
  $rowRow.= '<div class="threecol last"> /* content here */ </div>';
  $total = $viewColumns;
  }

  if(isset($total)==false){
  $this->out("choice not recognized. defaulting to 2.");
  $rowRow= '<div class="sixcol"> /* content here */ </div>';
  $rowRow.= '<div class="sixcol last"> /* content here */ </div>';
  }

  $rowEnd = "</div></div>";

  $finalHTML = $rowStart.$rowRow.$rowEnd;
  $rowHTML[] = $finalHTML;

  $this->out(" "); $this->out("  --- Row Successfully Added ---"); $this->out(" ");

  //Repeat Until Satisfied
  $this->out("Add Another Row? | Y / N :");
  $line = trim(fgets(STDIN));

  if($line == "n" || $line == "N" || $line === "n" || $line === "N"){
  $this->out(" Ok!");
  $again = "not again";
  return $again;
  }

  if($line=="y"||$line=="Y"){
  $again = "again";
  return $again;
  } else {
  return true;
  }
 }

 public function page($row=null, $column=null) {
  global $webdir, $hui_dir, $rowHTML, $view_dir;
  $hui_globaldir = $this->webdir.$this->hui_dir; 

  if(is_dir($hui_globaldir)===true) {
  $huiVersionFile = $hui_globaldir."/hui.json";
  $huijson = json_decode(file_get_contents($huiVersionFile));
  $this->out(' ');
  $this->header("--- White Blank Page | hui ver".$huijson->{'version'}." - ".$huijson->{'description'}." ---");
  //print_r($huijson->{'title'});
  $this->out(" ");
  } else {
  $this->header('-- White Blank Page --');
  $this->out("hui not detected. run 'li3 hui bot' command to install.");
  }


  $this->out("Enter View (ie Posts): ");

  $line = trim(fgets(STDIN));
  //$this->out(STDOUT, $line . "\r\n");
  $viewTarget = $line;

  //$this->out(STDOUT,  );
  $this->out("Page Title (ie Index.html): ");
  $line = trim(fgets(STDIN));
  $viewTitle = $line;

  $viewFile = $this->webdir.$this->view_dir."/".$viewTarget."/".$viewTitle.".php";
  if(is_file($viewFile)==true){
  $this->out("Overwrite This File? | ( Y / N ) :");
  $line = trim(fgets(STDIN));
  if($line=="y"||$line=="Y"){
  //Do Nothing...
  $this->out("Carry On Then.");
  } elseif($line=="n"||$line=="N") {
  $this->out("Peace Out.");
  exit;
  }
  }

  $this->out("Customize Layout? | ( Y / N ) :");
  $line = trim(fgets(STDIN));
  if($line=="y"||$line=="Y"){
  $key = true;
  $im = 1;
  while($key){
  // Do stuff
  if($this->addRow() == "again") {
  $this->addRow();
  } else {
  $this->out(" --- ");
  $key = false;

  }
  $im++;
  }
  } else {
  $this->out("Using Default Template....");
  }

  $this->out(" ");
  $targetViewDir = "".$viewTarget;
  $this->buildHTMLFile($targetViewDir, $viewTitle);

  $this->out("Finished. Built in views/".$viewTarget."/".$viewTitle.".php");

 }

 public function bot() {

  //set this to your desired setup.
  global $hui_dir, $hui_plugindir;
  $hui_globaldir = $this->webdir.$this->hui_dir;
  $plugin_directory = $hui_globaldir.$this->hui_plugindir;

  if(isset($this->cmd)===true){
  //Lets Go.
  $hui_cmd = $this->cmd;
	if($hui_cmd == 1){
	 	 	//mk hui dir
			if(is_dir($hui_globaldir)!=true){
				mkdir($hui_globaldir);
			}
			$this->header('-- hui main installer --');
			$this->out("Select Theme [dark, li3, none] :");
			$line = trim(fgets(STDIN));
			$theme = $line;
			$this->out(" ");
			
			if(chmod($hui_globaldir, 0777)==true && $theme != "none"){
				$hui_theme_dir = $hui_globaldir."/".$theme;
				if(is_dir($hui_theme_dir)!=true){
					mkdir($hui_theme_dir);
				}
				chmod($hui_theme_dir, 0777);

				$hui_themeimg_dir = $hui_globaldir."/".$theme."/imgs";
				if(is_dir($hui_themeimg_dir)!=true){
				mkdir($hui_themeimg_dir);
				}
				chmod($hui_themeimg_dir, 0777);
				
				//Get All Image Files
				// TODO
				
			} elseif(chmod($hui_globaldir, 0777)==true){
				
				//Do Nothing
				
			} else {
				$this->out(' Error. Could not write to directory webroot/hui');
				exit;
			}
			if($theme == "none" || $theme == "NONE"){
			$hui_files = array(
			"https://raw.github.com/johnny13/hui/master/dist/hui-min.js"=>"hui-min.js",
			"https://raw.github.com/johnny13/hui/master/dist/ie-min.css"=>"ie-min.css",
			"http://huementui.s3.amazonaws.com/cdn/hui-base.css"=>"hui-base.css",
			"https://raw.github.com/johnny13/hui/master/dist/hui-base-min.css"=>"hui-base-min.css",
			"http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"=>"jquery.min.js",
			"https://raw.github.com/johnny13/hui/master/libs/jquery/jquery-1.8.3.min.js"=>"jquery-1.8.3.min.js",
			"http://huementui.s3.amazonaws.com/cdn/html5shiv.js"=>"html5shiv.js"
			);
			} else {
				if(is_dir($hui_globaldir."/".$theme)!=true){
				mkdir($hui_globaldir."/".$theme);
				}
			$hui_files = array(
			"https://raw.github.com/johnny13/hui/master/dist/hui-min.js"=>"hui-min.js",
			"https://raw.github.com/johnny13/hui/master/dist/ie-min.css"=>"ie-min.css",
			"http://huementui.s3.amazonaws.com/cdn/hui-base.css"=>"hui-base.css",
			"https://raw.github.com/johnny13/hui/master/dist/hui-base-min.css"=>"hui-base-min.css",
			"http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"=>"jquery.min.js",
			"https://raw.github.com/johnny13/hui/master/libs/jquery/jquery-1.8.3.min.js"=>"jquery-1.8.3.min.js",
			"http://huementui.s3.amazonaws.com/cdn/html5shiv.js"=>"html5shiv.js",
			"https://raw.github.com/johnny13/hui/master/src/themes/".$theme."/dev-theme.css"=> $theme."/dev-theme.css"
			);
			}
			$this->out('loading hui files via curl .....');
			$this->out(' ');
			foreach($hui_files as $url=>$file){
				$this->out('loading - '. $file);
				$ch = curl_init();
				$timeout = 5;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$data = curl_exec($ch);
				curl_close($ch);
				$hfile = $hui_globaldir."/".$file;
				file_put_contents($hfile, $data);
			}
		  
		  $this->out(' ');
		  $ch = curl_init();
		  $verfile = "hui.json";
		  $urlVersion = "https://raw.github.com/johnny13/hui/master/hui.jquery.json";

		  $timeout = 5;
		  curl_setopt($ch, CURLOPT_URL, $urlVersion);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		  $data = curl_exec($ch);
		  curl_close($ch);

		  $hfile = $hui_globaldir."/".$verfile;

		  //$current = file_get_contents($hfile);
		  file_put_contents($hfile, $data);
		  $this->out('loading - '. $verfile);
		  $this->out(' ');
		
		  $this->out('Kick Ass. hui has been setup.');
		  $this->out('install hui tests [ li3 hui bot --cmd=2 ] for a full run down.');
		  $this->out(' ');
		  exit;  
	  }
	
	  if($hui_cmd == 2){
		$this->header('-- hui test suite installer --');
		  $hui_themeimg_dir = $hui_globaldir;
		  if(is_dir($hui_themeimg_dir."/libs")!=true){
		  //mkdir($hui_themeimg_dir);
		  mkdir($hui_themeimg_dir."/libs");
		  mkdir($hui_themeimg_dir."/libs/qunit");
		  mkdir($hui_themeimg_dir."/libs/jquery");

		  chmod($hui_themeimg_dir."/libs/qunit", 0777);
		  chmod($hui_themeimg_dir."/libs/jquery", 0777);
		  chmod($hui_themeimg_dir."/libs", 0777);
		  }

		  $hui_files = array(
		  "https://huementui.s3.amazonaws.com/cdn/hui.js"=>"hui.js",
		  "https://raw.github.com/johnny13/hui/master/test/hui_test.js"=>"hui_test.js",
		  "https://raw.github.com/johnny13/hui/master/test/hui-test.css"=>"hui-test.css",
		  "https://huementui.s3.amazonaws.com/cdn/bootleg/index.html"=>"index.html",
		  "https://raw.github.com/johnny13/hui/master/libs/qunit/qunit.js"=>"libs/qunit/qunit.js",
		  "https://raw.github.com/johnny13/hui/master/libs/qunit/qunit.css"=>"libs/qunit/qunit.css",
		  "https://raw.github.com/johnny13/hui/master/libs/jquery/jquery-1.9.0b1.js"=>"libs/jquery/jquery-1.9.0b1.js",
		  "https://raw.github.com/johnny13/hui/master/libs/jquery/jquery-migrate-1.0.0b1.js"=>"libs/jquery/jquery-migrate-1.0.0b1.js"
		  );
		  $this->out(' ');  
		  $this->out('Fetching Github.com hui files.......');
		  $this->out(' ');
		  foreach($hui_files as $url=>$file){
		  $this->out('loading - '. $file);
		  $ch = curl_init();
		  $timeout = 5;
		  curl_setopt($ch, CURLOPT_URL, $url);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		  $data = curl_exec($ch);
		  curl_close($ch);

		  $hfile = $hui_globaldir."/".$file;

		  //$current = file_get_contents($hfile);
		  file_put_contents($hfile, $data);
		  sleep(0.5); //play nice
		  }
		  $this->out(' ');$this->out(' ');
		  $this->out('hui tests installed and ready!!');$this->out(' To use, Visit in a browser:');
		  $this->out('http://localhost/hui <- if modrewrite ');
		  $this->out('or http://192.whatever.1/app/webroot/hui');

		  $this->out(' ');
		  exit;
	  }
	
	  if($hui_cmd == 3){
		//Check Local hui.jquery.json version
		//against https://github.com/johnny13/hui version
		$ch = curl_init();
		$verfile = "hui.json";
		$urlVersion = "https://raw.github.com/johnny13/hui/master/hui.jquery.json";
		
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $urlVersion);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$rdata = curl_exec($ch);
		curl_close($ch);
		$remotedata = json_decode($rdata);
		//print_r(json_decode($remotedata));
		//exit;
		if(is_file($hui_globaldir."/hui.json")==true){
			$locald = file_get_contents($hui_globaldir."/hui.json");
			$localjson = json_decode($locald);
			$localdata = $localjson->version;
		} else {
			$localdata = "Error. Local Install not found.";
		}
		$this->header('-- hui version check --');
		$this->out("Latest Remote: ".$remotedata->version);
		$this->out(" ");
		$this->out("Your Version: ".$localdata);
		$this->out(" ");
		$this->out("If Updated required: li3 hui bot --cmd=4");
		$this->out(" ");
	  }
	
	  if($hui_cmd == 4){
		$this->header('-- hui updater --');
		if(is_dir($hui_globaldir)!=true){
		mkdir($hui_globaldir);
		chmod($hui_globaldir, 0777);
		}
		
		$url = "https://raw.github.com/johnny13/hui/master/dist/hui-min.js";
		$urlsix = "https://raw.github.com/johnny13/hui/master/hui.jquery.json";
		
		$urls = array(
			"hui-min.js"       => "https://raw.github.com/johnny13/hui/master/dist/hui-min.js",
			"hui-base-min.css" => "https://raw.github.com/johnny13/hui/master/dist/hui-base-min.css",
			"ie-min.css"       => "https://raw.github.com/johnny13/hui/master/dist/ie-min.css",
			"hui-base.css"     => "https://huementui.s3.amazonaws.com/cdn/hui-base.css",
			"hui.js"           => "https://huementui.s3.amazonaws.com/cdn/hui.js",
			"hui.json"         => "https://raw.github.com/johnny13/hui/master/hui.jquery.json"
		);
		
		$counter = 1;
		foreach($urls as $fkey=>$furl){
			$this->out(' .. '.$counter.' .. ');
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $furl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);
			$hfile = $hui_globaldir."/".$fkey;
			//$current = file_get_contents($hfile);
			$this->out('Updating - '. $fkey);
			$this->out(' ');
			file_put_contents($hfile, $data);
			sleep(1);
			$counter++;
		}
		
		$this->out('Finished - '. $hfile);
		$this->out(' ');
	}
	
	if($hui_cmd == "5"){
		  $this->header('-- hui plugin manager --');
		  $this->out("Currently, only some hui plugins are supported.");
		  $this->out("Also, this requires the Git Command.");
		  $this->out(" ");
		  $this->out("more info here: github.com/johnny13/hui-plugins");
		  $this->out(" ");
		  $this->out("Select Plugin [Thimbleberry, Breakup] :");
		  $line = trim(fgets(STDIN));

		  if($line == "Thimbleberry" || $line == "thimbleberry"){
			//https://github.com/johnny13/Thimbleberry.git
			$output = shell_exec('git clone https://github.com/johnny13/Thimbleberry.git'.' '.$plugin_directory.'/'.$line);
			$this->out($output);
		  } elseif($line == "Breakup" || $line == "breakup"){
			//https://github.com/johnny13/breakup
			$output = shell_exec('git clone https://github.com/johnny13/breakup'.' '.$plugin_directory.'/'.$line);
			$this->out($output);
		  } else {
			$this->out("Didn't quite understand that... exiting.");
			exit;
		  }
	}
	  
	if($hui_cmd == "6"){
		$this->header('-- hui theme download --');
		$this->out("Select Theme [dark, li3] :");
		$line = trim(fgets(STDIN));
		$theme = $line;
		$this->out(" ");
		$themeurl = "https://raw.github.com/johnny13/hui/master/src/themes/".$theme."/dev-theme.css";
		$themefile = $theme."/dev-theme.css";
		$this->out('loading - '. $themefile);
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $themeurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		$hfile = $hui_globaldir."/".$themefile;
		if(is_dir($hui_globaldir."/".$theme)!=true){ 
			mkdir($hui_globaldir."/".$theme);
			chmod($hui_globaldir."/".$theme, 0777);
		}
		file_put_contents($hfile, $data);
		exit;
	}
	
	if($hui_cmd == "7"){
		  $this->header(" Keep Calm. Party On. ");
	}
  } else {

  if(is_dir($hui_globaldir)!=true){
  $this->out(' ');
  $this->header('hui Not detected.');
  $this->out('Hola! If this is your first time, Run the #1 Install Command.');
  $hui_globaldir_result = "like this: li3 hui bot --cmd=1";
  $this->out($hui_globaldir_result);
  $this->out(' ');
  $this->header('       --- hui bot ---');
  } else {
  //$this->out('\n'.'hui Directory is Set & Writeable.');
  $huiVersionFile = $hui_globaldir."/hui.json";
  $huijson = json_decode(file_get_contents($huiVersionFile));

  $this->out(' ');

  $this->header($huijson->{'title'}."   - version: ".$huijson->{'version'}." - ".$huijson->{'description'});
  //print_r($huijson->{'title'});
  $this->out(" ");
  }
  
  $this->out('Add or Update hui, plugins, themes, and QUnit Tests.');
  $this->out(' ');
  $this->out('  ex: li3 hui bot --cmd=2');
  $this->out(' ');
  $this->out('  1.  Install /hui/ folder in webroot. loads html js, & css files.');
  $this->out('  2.  Load QUnit Test suite in /hui/ [Optional]');
  $this->out('  3.  Check github for hui updates');
  $this->out('  4.  Curl the latest stable hui | Updates hui.js & base css files');
  $this->out('  5.  Git Clone a hui plugin repo (prompted for choice)');
  $this->out('  6.  Curl hui theme.css (prompted for choice)');
  $this->out('  7.   --- empty ---');
  $this->out(' ');
  $this->out(' ');
  }

 }
}

?>
