<?php
	/**
	 * Fichier de callback utilisé comme interface du daemon VOD.
	 * Cela permet d'avoir immediatement accès aux vidéos qui viennent d'etre envoyés sur l'espace VOD.
	 * En cas de problemes ou de questions, veuillez contacter support-vod-wordpress@infomaniak.ch
	 *
	 * @author Destrem Kevin
	 * @link http://statslive.infomaniak.ch/vod/api/
	 * @version 1.2
	 * @copyright infomaniak.ch
	 *
	 */
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	if (current_user_can('edit_others_posts')) {
		
		$aOptions = get_option('vod_infomaniak');

		if ($aOptions['vod_api_callbackKey'] == $_REQUEST['key']) {
			$db = new EasyVod_db();

			$iVideo = intval(sanitize_key($_POST['iFileCode']));
			$iFolder = intval(sanitize_key($_POST['iFolderCode']));
			$sFileName = sanitize_file_name($_POST['sFileName']);
			$sServerCode = sanitize_text_field($_POST['sFileServerCode']);

			if (empty($iVideo) || empty($iFolder)) {
				die(__("Probleme avec les parametres"));
			}
			$oFolder = $db->getFolder($iFolder);
			if (empty($oFolder) || empty($oFolder->sName)) {
				die(__("Dossier inconnu"));
			}

//			$encodage = array_shift($_POST['files']);
			$path_tmp = explode('/redirect/' . $aOptions['vod_api_id'] . "/", sanitize_text_field($_POST['files'][0]['sFileUrl']));
			$sPath = "/" . dirname($path_tmp[1]) . "/";
			$sExtension = strtoupper(sanitize_text_field($_POST['files'][0]['sExtension']));
			$iDuration = intval(sanitize_key($_POST['iDuration']));
			$dUpload = date("Y-m-d H:i:s", strtotime(sanitize_text_field($_POST['dDateUpload'])));

			$oldVideo = $db->get_videos_byCodes($sServerCode, $iFolder);
			if (!empty($oldVideo)) {
				foreach ($oldVideo as $video) {
					$db->delete_video($video->iVideo);
				}
			}

			$db->insert_video($iVideo, $iFolder, $sFileName, $sServerCode, $sPath, $sExtension, $iDuration, $dUpload);

			if (!empty($_POST['sInfo'])) {
				$sParamInfo = sanitize_text_field($_POST['sInfo']);
				if (strpos($sParamInfo, "wp_upload_post_") !== false) {
					$sToken = str_replace("wp_upload_post_", "", $sParamInfo);
					$db->update_upload($sToken, $iVideo);
				}
			}
		}
	}
	die();
?>