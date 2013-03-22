<?php
/*
	@author	:	Giriraj Namachivayam	
	@date 	:	Mar 21, 2013
	@demourl	:	http://ngiriraj.com/socialMedia/googleplus_idcard/
	@document	:	http://ngiriraj.com/work/
	@license	: 	Free to use
	@History	:	V1.0 - Google IDCard
*/

# Oauth Connect file
/* 
	FileName	:	socialmedia_oauth_connect.php
	Support	:	oAuth 2.0
	Provider(s)	:	'Google'
				'Bitly'
				'WordPress'
				'Paypal'
				'Facebook'
				'Microsoft'
				'Foursquare'
				'Box'
				'Yammer'
				'Reddit'
				'Yandex'
	Download	:	Payable Version; Contact kayalshri@gmail.com for further
*/

include "socialmedia_oauth_connect.php";

# Destination Folder Name
$idDir = 'gcards/'; 

# Facebook standared id card image template
$fbTemplate_img = 'gplus.png';

# Fonts - Download from http://www.google.com/webfonts
$font ="fonts/Merri/MerriweatherSans-Regular.ttf";
$headfont ="fonts/Spirax/Spirax-Regular.ttf";

# class instance
$oauth = new socialmedia_oauth_connect();

# Service Provider
$oauth->provider="Google";

# Google Clien ID
$oauth->client_id = "730362277469-tbeqm6l332n1al4pnfdgb83786a6g3f2.apps.googleusercontent.com";

# Google Secret Key
$oauth->client_secret = "xxxxxxxxxxxxxxxxxxxxxxxx";

# Scope
$oauth->scope="https://www.googleapis.com/auth/userinfo.email  https://www.googleapis.com/auth/userinfo.profile";

# Redirect uri
$oauth->redirect_uri  ="http://ngiriraj.com/socialMedia/googleplus_idcard/";

#Initialize the call
$oauth->Initialize();

# Response type=code
$code = ($_REQUEST["code"]) ?  ($_REQUEST["code"]) : "";


if(empty($code)) {
	# Need to Authorize from Google via oAuth
	$oauth->Authorize();
}else{
	# Successfull Authorization
	$oauth->code = $code;
	
	# Get User Profile
	$user = json_decode($oauth->getUserProfile());
	#$oauth->debugJson($user);
	
	# Google+ user id
	$gid = $user->id;
	
	# Profile Photo
	$gp_user_profile= $user->link;
	
	# Google User Name
    	$gp_user_name      	= isset($user->name) ? ($user->name) : 'No Name';
	
	# Gender
    	$gp_user_gender    	= isset($user->gender) ? ($user->gender) : 'No gender';
	
	# Locale
    	$gp_user_locale		= isset($user->locale) ? ($user->locale) : 'Unknown';
	
	# Birthday
    	$gp_user_birth     	= isset($user->birthday) ? ($user->birthday) : '00/00/0000';
    
	# Birthday d-M format
    	$gp_birthdate = date($gp_user_birth);
    	$sort_birthdate = strtotime($gp_birthdate);
    	$for_birthdate = date('d M', $sort_birthdate);
    
	# Disclaimer msg
	$gplus_disclaimer 	= '* Un-official Google+ ID card from http://ngiriraj.com';
    
	#Alternative Image Saving by Using cURL seeing as allow_url_fopen is disabled
	function save_image($img,$fullpath='profile'){
		$ch = curl_init ($img);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		$rawdata=curl_exec($ch);
		curl_close ($ch);
		if(file_exists($fullpath)){
			unlink($fullpath);
		}
		$fp = fopen($fullpath,'x');
		fwrite($fp, $rawdata);
		fclose($fp);

	}

	#Download user profile image
	#copy($gplusProfileImagefile,$gplusTemplatefile);
	save_image($user->picture,$idDir.$gid.'.jpg');    
	
	# gplus Template - PNG file format
	$gplusTemplate = imagecreatefrompng($gplusTemplate_img); 
	
	# Profile image stored in ID Card folder - jpg file format
	$gplusProfileImage = imagecreatefromjpeg($idDir.$gid.'.jpg'); 
    
	# Profile Image Resize
	$image_gp = imagecreatetruecolor(100,150);
	list($width_orig, $height_orig) = getimagesize($idDir.$gid.'.jpg');
    	imagecopyresampled($image_gp, $gplusProfileImage , 0, 0, 0, 0,100, 150, $width_orig, $height_orig);
	
	# Profile Image merge with Template image
   	imagealphablending($gplusTemplate, false); 
   	imagesavealpha($gplusTemplate, true);
   	imagecopymerge($gplusTemplate, $image_gp, 332, 80, 0, 0, 100, 150, 100);  # profile image merge with gplus Template

	#Text color Theme
    	$Google_blue = imagecolorallocate($gplusTemplate, 81, 103, 147); // Create blue color
    	$Google_grey = imagecolorallocate($gplusTemplate, 74, 74, 74); // Create grey color
    
	imagealphablending($gplusTemplate, true);    
		
    	# Embed informations to gplus ID Card	
    	imagettftext($gplusTemplate, 12, 0, 15, 107, $Google_grey, $font, $gp_user_name); // Name
    	imagettftext($gplusTemplate, 12, 0, 15, 145, $Google_grey , $font, $gid); // ID    
    	imagettftext($gplusTemplate, 12, 0, 15, 180, $Google_grey, $font, $gp_user_gender); //Gender
    	imagettftext($gplusTemplate, 12, 0, 220, 180, $Google_grey, $font, $for_birthdate); //dob
    	imagettftext($gplusTemplate, 12, 0, 130, 180, $Google_grey, $font, $gp_user_locale); //Locale
    	imagettftext($gplusTemplate, 10, 0, 15, 219, $Google_grey, $font, $gp_user_profile); //Profile link
    	imagettftext($gplusTemplate, 6, 0, 15, 238, $Google_blue, $font, $gplus_disclaimer); //message
	
        
	# gplus ID Card save into idDir folder	
    	imagepng($gplusTemplate, $idDir.'id_'.$gid.'.jpg');
	
	# DELETE Profile image (due to space issue)
    	unlink($idDir.$gid.'.jpg');  
    
	# GOOGLE Plus API is readonly; So, we can post automatic.
	# Google ID card stored in different HTML file for sharing purpose
	
	# FILENAME
    	$myFile = $idDir."Google-idcard-".$gid.".html";
	$fh = fopen($myFile, 'w') or die("can't open file");
	
	# Share Button - Google+, Facebook, Twitter, LinkedIn
	$share = '<html><body bgcolor="#f2f2f2"><center><img src="id_'.$gid.'.jpg" /><BR><BR>';
	$share .= "Hurry up! Get your <a target='_blank' href='http://goo.gl/xFYlk'>Google+ IDcard</a> like ".$gp_user_name.".";
	$share .= '<BR><BR><BR><BR><BR>
		<div id="gplus-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.Google.net/en_GB/all.js#xgplusml=1&appId=321176691239227";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, "script", "Google-jssdk"));</script>

		<div class="gplus-like"  data-layout="button_count" data-width="450" data-show-faces="true" data-font="segoe ui" data-href="http://ngiriraj.com/socialMedia/googleplus_idcard/'.$myFile.'"></div>
		<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
		<script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
		<script type="IN/Share" data-counter="right" data-url="http://ngiriraj.com/socialMedia/googleplus_idcard/'.$myFile.'"></script>
		<!-- Place this tag where you want the share button to render. -->
		<div class="g-plus" data-action="share" data-annotation="bubble" data-href="<?php echo $myFile; ?>"></div>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		<a href="https://twitter.com/share" class="twitter-share-button" data-via="kayalshri" data-url="http://ngiriraj.com/socialMedia/googleplus_idcard/'.$myFile.'" data-text="Google+ IDCARD">Tweet</a>
		<BR>
		<div class="gplus-comments" data-href="http://ngiriraj.com/socialMedia/googleplus_idcard/'.$myFile.'" data-width="470" data-num-posts="10"></div>
		</center></body></html>';

	# File Write
	fwrite($fh, $share);
	
	# File close
	fclose($fh);  
				
	# Redirect to HTML Page
	echo("<script> top.location.href='http://ngiriraj.com/socialMedia/googleplus_idcard/".$myFile."'</script>");
}


?>