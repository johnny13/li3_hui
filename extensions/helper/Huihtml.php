<?php

namespace app\extensions\helper;

class huihtml extends \lithium\template\Helper {
	
	public function button($title=null,$gclass=null,$icon=null,$group=null){
		//Group, or custom class.
		if($gclass!=null){
			if($group==null){
				$btnHtml = "<button class='".$gclass."'>";
			} else {
				$btnHtml = "<button class='".$group." ".$gclass."'>";
			}
		} else {
			if($group!=null){
				$btnHtml = "<button class='".$group."'>";
			} else {
			$btnHtml = "<button>";
			}
		}
		//Icon?
		if($icon!=null){
			$btnHtml .= '<span class="icon '.$icon.'"></span>';
		}
		
			$buttonStart = $btnHtml . '<span class="label">'.$title."</span>";
			
		$buttonEnd = '</button>';
		
		return $buttonStart.$buttonEnd;
	}
	
	public function flapperGirl($linksarray,$ladyarray){
		//$ladyparts = array("title"=>".title_bar","loader"=>".pageloader","stage"=>".speakEasy");
		if(!isset($ladyarray)){
			$ladyarray = array("title"=>"title_bar","loader"=>"pageloader","stage"=>"speakEasy");
		}
		$girls = array("jessica","heather,","kasey","nicole","elizabeth","ann","sara");
		
		if(!empty($linksarray) && !empty($ladyarray)){
			$htmlData = '<div class="flapperStage"><div class="'.$ladyarray['stage'].'"><ul>';
			$linkHTML = "";
			foreach($linksarray as $itemArray){
				//Setup Trunk / Branches Class System
				shuffle($girls);
				$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			    $randomString = '';
				$length = 5;
			    for ($i = 0; $i < $length; $i++) {
			        $randomString .= $characters[rand(0, strlen($characters) - 1)];
			    }
	
				$girl = $girls[0]."_".$randomString;

				if(isset($itemArray["docked"])==false){
					$linkHTML .= '<li class="'.$ladyarray['title'].'" id="'.$girl.'">'.$itemArray["nav"].'</li>';
					if(!empty($itemArray["links"])){
						foreach($itemArray["links"] as $lkey=>$lvay){
							$linkHTML .= '<li class="basic '.$girl.'"><a href="'.$lvay.'" class="pageloader">'.$lkey.'</a></li>';
						}
					}
				} else {
					$linkHTML .= '<li class="'.$ladyarray['title'].' docked" id="'.$girl.'">'.$itemArray["nav"].'</li>';
					if(!empty($itemArray["links"])){
						foreach($itemArray["links"] as $lkey=>$lvay){
							$linkHTML .= '<li class="basic '.$girl.'" style="display: none;"><a href="'.$lvay.'" class="pageloader">'.$lkey.'</a></li>';
						}
					}
				}
			}
			
		}
		$htmlDataEnd = '</ul></div></div>';
		
		$htmlFinal = $htmlData.$linkHTML.$htmlDataEnd;
		return $htmlFinal;
	}
	
}

?>