<?php
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author			Raymond Benc
 * @package 		Phpfox
 * @version 		$Id: image.php 7303 2014-05-07 17:43:19Z Fern $
 */

ob_start();

/**
 * Key to include phpFox
 *
 */
define('PHPFOX', true);

/**
 * Directory Seperator
 *
 */
define('PHPFOX_DS', DIRECTORY_SEPARATOR);

/**
 * phpFox Root Directory
 *
 */
define('PHPFOX_DIR', dirname(dirname(__FILE__)) . PHPFOX_DS);
define('PHPFOX_NO_MOBILE_CHECK', true);

// Require phpFox Init
require(PHPFOX_DIR . 'include' . PHPFOX_DS . 'init.inc.php');

if (isset($_GET['file']) && isset($_GET['ext']))
{
	$sFile = PHPFOX_DIR . 'file' . PHPFOX_DS . 'pic' . PHPFOX_DS . 'photo' . PHPFOX_DS . $_GET['file'] . '.' . $_GET['ext'];
}

if (!isset($_GET['pq']) && (!isset($_SESSION['core']['image']['photo_' . md5($sFile)]) || !isset($_SESSION['phpfox']['image']['photo_' . md5($sFile)])))
{	
	$sName = $_GET['file'] . '.' . $_GET['ext'];
	$sName = preg_replace('/(.*)_(.*)\.(.*)/i', '\\1.\\3', $sName);
	$sName = preg_replace('/(.*)\.(.*)/i', '\\1%s.\\2', $sName);
	$bCantViewImage = false;

	$aPhoto = Phpfox::getService('photo')->getPhotoByDestination($sName);

	if ($aPhoto === false)
	{
		$bCantViewImage = true;	
	}
	else 
	{
		// User can view this item because they are allowed to view all hidden items
		if (Phpfox::getUserParam('photo.can_view_hidden_photos'))
		{
			
		}
		// Viewer can view this item since it belongs to them
		elseif (Phpfox::getUserId() && Phpfox::getUserId() == $aPhoto['user_id'])
		{
			
		}
		else 
		{		
			// If the album is only for friends lets make sure the user is friends with the album owner
			if ($aPhoto['privacy'] == 2 && !Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aPhoto['user_id']))
			{
				$bCantViewImage = true;		
			}
			// If the album is only allowed to be viewed by certain users make sure they are in the "white" list
			elseif ($aPhoto['privacy'] == 3 && !Phpfox::getService('privacy')->verify('photo_album', $aPhoto['album_id'], $aPhoto['user_id']))
			{				
				$bCantViewImage = true;			
			}
		}
		
		// Check if this is a password protected album
		if (!empty($aPhoto['album_password']) && $aPhoto['user_id'] != Phpfox::getUserId())
		{
			$bCantViewImage = true;			
		}
	}	
	
	if ($bCantViewImage)
	{
		header("HTTP/1.0 404 Not Found");
		$sText = "Visit our site to view this image.\n" . Phpfox::getParam('core.path');	
		$nW = 400;
		$nH = 50;
		$nLeft = 5;
		$nTop = 5;
		$hImg = imageCreate($nW, $nH);
		$nBgColor  = imageColorAllocate($hImg, 0, 0, 0);
		$nTxtColor = imageColorAllocate($hImg, 255, 255, 255);
		$aLines = explode("\n", $sText);
		foreach ($aLines as $sLine)
		{
			imageString($hImg, 5, $nLeft, $nTop, $sLine, $nTxtColor);
			$nTop += 17;
		}
			        
		ob_clean();
		header('Content-Type: image/jpeg');
		imagejpeg($hImg);        
		exit;
	}
}


/* If its an image for the spam questions */
if (isset($_GET['pq']))
{
	$oDb = Phpfox::getLib('database');
	$oParse = Phpfox::getLib('parse.input');
	$sHash = $oParse->clean($_GET['pq']);
	$sPath = $oDb->select('hash')->from(Phpfox::getT('upload_track'))->where('user_hash = "' . $sHash .'"')->limit(1)->execute('getSlaveField');
	
	// Little check to make sure they cant get the folder
	if (empty($sPath))
	{
		$sPath = rand(0,999999);
	}
	// we cannot delete it here because we need to revershe check it once they submit the form
	//$oDb->delete(Phpfox::getT('upload_track'),'user_hash = "' . $sHash . '"');
	$sFile = Phpfox::getParam('user.dir_user_spam') . $sPath;

	if(Phpfox::getParam('core.allow_cdn'))
	{
		$sFile = Phpfox::getParam('user.url_user_spam') . $sPath;
		$sFile = Phpfox::getLib('cdn')->getUrl($sFile, Phpfox::getLib('cdn')->getServerId());
	}

	$_GET['ext'] = 'jpeg';
	
}
else
{
	preg_match('/_[0-9]+/', $_GET['file'], $aSize);
}

$sFile = preg_replace('/%s/', !empty($aSize[0]) ? $aSize[0] : '', $sFile); 
if (file_exists($sFile) || Phpfox::getParam('core.allow_cdn'))
{
	ob_clean();
	header('Content-Type: image/' . $_GET['ext']);
	echo file_get_contents($sFile);
	ob_end_flush();
	exit;
}
else 
{
	header("HTTP/1.0 404 Not Found");
	$sText = 'Not Found!';
	$nW = 100;
	$nH = 30;
	$nLeft = 5;
	$nTop = 5;
	$hImg = imageCreate($nW, $nH);
	$nBgColor  = imageColorAllocate($hImg, 0, 0, 0);
	$nTxtColor = imageColorAllocate($hImg, 255, 255, 255);
	imageString($hImg, 5, $nLeft, $nTop,  $sText, $nTxtColor);
		        
	ob_clean();
	header('Content-Type: image/jpeg');
	imagejpeg($hImg);        
	exit;
}

?>
