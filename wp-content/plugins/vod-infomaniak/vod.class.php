<?php
	/**
	 * Classe generale regroupant les differentes fonctions du plugin wordpress.
	 * En cas de problemes ou de questions, veuillez contacter support-vod-wordpress@infomaniak.ch
	 *
	 * @author Destrem Kevin + Davide Rubini + Arnaud Toullieux
	 * @link http://statslive.infomaniak.ch/vod/api/
	 * @version 1.4.4
	 * @copyright infomaniak.ch
	 */
	define('VOD_RIGHT_CONTRIBUTOR', 1);
	define('VOD_RIGHT_AUTHOR', 2);
	define('VOD_RIGHT_EDITOR', 3);
	define('VOD_RIGHT_ADMIN', 4);

	class EasyVod {
		public $version = "1.4.4";
		private $local_version;
		private $plugin_url;
		private $options;
		private $key;
		private $db;

		function __construct() {
			$this->local_version = $this->version;
			$this->key = 'vod_infomaniak';
			$this->options = $this->get_options();
			$this->add_filters_and_hooks();
			$this->db = new EasyVod_db();
			$this->auto_sync = true;
			$this->auto_sync_delay = 3600;
			define("VOD_IK_SALT", "2saLsNw4s4MCG9BVjXCKxvZA");
			// Declare variables needed for encryption
			if (extension_loaded('openssl')) {
				$GLOBALS['encryptCipher'] = 'aes-256-cfb';
				$GLOBALS['encryptPassword'] = 'xx187rsd!ze$ze11ld8';
				$GLOBALS['encryptIv'] = '4zEz7z5!4a1s2e$s';
			}
		}
		function add_filters_and_hooks() {

			register_activation_hook(__FILE__, array(&$this, 'install_db'));
			add_action('plugins_loaded', array(&$this, 'update_db'));

			load_plugin_textdomain('vod_infomaniak', FALSE, basename(dirname(__FILE__)) . '/languages');

			add_action('template_redirect', array(&$this, 'vod_template_redirect'));
			add_filter('query_vars', 'vod_query_vars');
			add_filter('the_content', array(&$this, 'check'), 100);
			add_filter('the_excerpt', array(&$this, 'check'), 100);

			if (get_site_option('vod_db_version') == "1.0.5"){
				$this->options['vod_api_connected'] = 'off';
				update_option('vod_infomaniak', $this->options);
				update_option("vod_db_version",$this->db_version);			
			}



			if (is_admin()) {
				add_action('admin_menu', array(&$this, 'add_menu_items'));
				add_action('edit_form_advanced', array(&$this, 'buildForm'));
				add_action('edit_page_form', array(&$this, 'buildForm'));

				add_action('wp_ajax_importvod', array(&$this, 'printLastImport'));
				add_action('wp_ajax_vodsearchvideo', array(&$this, 'searchVideo'));
				add_action('wp_ajax_vodsearchplaylist', array(&$this, 'searchPlaylist'));
				add_action('wp_ajax_vodimportvideo', array(&$this, 'importPostVideo'));

				add_action('plugins_loaded', array(&$this, 'init_mce_video'));

				//On load css et js pour l'admin
				add_action('admin_enqueue_scripts', array(&$this, 'register_scripts'));
			}
		}

		function register_scripts(){
			//styles
			wp_register_style('ui-tabs', plugins_url('vod-infomaniak/css/jquery.ui.tabs.css'));
			wp_enqueue_style('vod-jquery-ui', plugins_url('vod-infomaniak/css/jquery-ui.css'), array(), $this->version, 'screen');
			wp_enqueue_style('ui-tabs');
			//scripts
			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_script('jquery-ui-tabs');
			wp_enqueue_script('suggest');
		}

		function init_mce_video() {
			if ($this->isCurrentUserCan('gestion')) {
				add_filter('mce_external_plugins', array(&$this, 'mce_register'));
				add_filter('mce_buttons', array(&$this, 'mce_add_button'), 0);
			}
		}

		function isCurrentUserCan($page) {
			$user = wp_get_current_user(); // pour l'utilisateur courant
			$iUserValue = $this->getRightValue($user->roles);

			switch ($page) {
				case 'plugin':
					$value = $this->options['vod_right_integration'] <= $iUserValue ||
						$this->options['vod_right_upload'] <= $iUserValue ||
						$this->options['vod_right_player'] <= $iUserValue ||
						$this->options['vod_right_playlist'] <= $iUserValue;
					return $value;
				case 'importation':
					return $this->options['vod_right_upload'] <= $iUserValue;
				case 'player':
					return $this->options['vod_right_player'] <= $iUserValue;
				case 'playlist':
					return $this->options['vod_right_playlist'] <= $iUserValue;
				case 'gestion':
					return $this->options['vod_right_integration'] <= $iUserValue;
				case 'configuration':
					return $iUserValue == VOD_RIGHT_ADMIN;
				default :
					return true;
			}
		}

		function getRightValue($sValue) {
			if (is_array($sValue)) {
				$sValue = reset($sValue);
			}
			switch ($sValue) {
				case 'contributor':
					return VOD_RIGHT_CONTRIBUTOR;
				case 'author':
					return VOD_RIGHT_AUTHOR;
				case 'editor':
					return VOD_RIGHT_EDITOR;
				case 'administrator':
					return VOD_RIGHT_ADMIN;
				default:
					return VOD_RIGHT_CONTRIBUTOR;
			}
		}

		function install_db() {
			$this->db->install_db();
			$this->fastSynchro();
		}

		function update_db() {
			if (get_site_option('vod_db_version') != $this->db->db_version) {
				$this->install_db();
			}
		}

		function add_menu_items() {
			if ($this->auto_sync) {
				$this->checkAutoUpdate();
			}

			if ($this->isCurrentUserCan('plugin')) {
				if (function_exists('add_menu_page')) {
					add_menu_page(__('Videos', 'vod_infomaniak'), __('Videos', 'vod_infomaniak'), 'edit_posts', __FILE__, array(&$this, 'vod_management_menu'));
				}

				if (function_exists('add_submenu_page')) {
					if ($this->isCurrentUserCan('gestion')) {
						add_submenu_page(__FILE__, __('Gestionnaire', 'vod_infomaniak'), __('Gestionnaire', 'vod_infomaniak'), 'edit_posts', __FILE__, array(&$this, 'vod_management_menu'));
					}
					if ($this->isCurrentUserCan('importation')) {
						add_submenu_page(__FILE__, __('Importation', 'vod_infomaniak'), __('Importation', 'vod_infomaniak'), 'edit_posts', 'import', array(&$this, 'vod_upload_menu'));
					}
					if ($this->isCurrentUserCan('player')) {
						add_submenu_page(__FILE__, __('Player video', 'vod_infomaniak'), __('Player video', 'vod_infomaniak'), 'edit_posts', 'Player', array(&$this, 'vod_implementation_menu'));
					}
					if ($this->isCurrentUserCan('playlist')) {
						add_submenu_page(__FILE__, __('Playlist', 'vod_infomaniak'), __('Playlist', 'vod_infomaniak'), 'edit_posts', 'Playlist', array(&$this, 'vod_playlist_menu'));
					}
					if ($this->isCurrentUserCan('configuration')) {
						add_submenu_page(__FILE__, __('Configuration', 'vod_infomaniak'), __('Configuration', 'vod_infomaniak'), 'edit_plugins', 'configuration', array(&$this, 'vod_admin_menu'));
					}
				}
			}
		}

		function importPostVideo() {
			if (!empty($_REQUEST['upload']) && $_REQUEST['upload'] == "finish") {
				echo $this->db->insert_upload($_REQUEST['sToken'], $_REQUEST['post']);
			} else {
				if (!empty($_REQUEST['iFolder'])) {
					$oFolder = $this->db->getFolder($_REQUEST['iFolder']);
					if (empty($oFolder) || empty($oFolder->sName)) {
						die(__("Il n'est pas possible d'uploader dans ce dossier.", 'vod_infomaniak'));
					}
					$oApi = $this->getAPI();
					$sToken = $oApi->initUpload($oFolder->sPath);
					$oApi->addInfo($sToken, "wp_upload_post_" . $sToken);
					delete_transient('vod_last_import');
					echo $sToken;
				}
			}
			die();
		}

		function searchPlaylist() {
			$aResult = $this->db->search_playlist($_REQUEST['q'], 100);
			$tReturn = "";
			if (!empty($aResult)) {
				foreach ($aResult as $oPlaylist) {
					$sDuration = "";
					if(!empty($oPlaylist->iTotalDuration)){
						$iDuration = intval($oPlaylist->iTotalDuration/100);
						$iHours = intval($iDuration/3600);
						$iMinutes = intval($iDuration/60) % 60;
						$iSecondes = intval($iDuration) % 60;
						$sDuration .= $iHours > 0 ? $iHours."h. " : '';
						$sDuration .= $iMinutes > 0 ? $iMinutes."m. " : '';
						$sDuration .= $iSecondes > 0 ? $iSecondes."s. " : '';
					}

					$tReturn .= '<tr class="vod_element_select" onclick="Vod_selectVideo(this, \''.$oPlaylist->iPlaylistCode.'\',\'\',\'\');">';
						$tReturn .= '<td>'.ucfirst(stripslashes($oPlaylist->sPlaylistName))."</td>";
						$tReturn .= '<td>'.stripslashes($oPlaylist->sPlaylistDescription)."</td>";
						$tReturn .= '<td align="center">'.$oPlaylist->iTotal.'</td>';
						$tReturn .= '<td align="right">'.$sDuration.'</td>';
					$tReturn .= '</tr>';
				}
			}
			echo $tReturn;
			die();
		}

		function searchVideo() {
			$aResult = $this->db->search_videos($_REQUEST['q'], 12, $this->options['vod_filter_folder']);
			$tReturn = "";
			if (!empty($aResult)) {
				foreach ($aResult as $oVideo) {
					$tReturn .= '<tr class="vod_element_select" onclick="Vod_selectVideo(this, \''.$oVideo->sPath . $oVideo->sServerCode . '.' . strtolower($oVideo->sExtension).'\',\''.$oVideo->sToken.'\',\''.$oVideo->iFolder.'\');">';
						$tReturn .= '<td><img width="100" src="http://vod.infomaniak.com/redirect/' . $this->options['vod_api_id'] . $oVideo->sPath . $oVideo->sServerCode . '.mini.jpg"/></td>';
						$tReturn .= '<td>';
							$tReturn .= ucfirst(stripslashes($oVideo->sName));
							$tReturn .= '<br/><br/>';
							$tReturn .= '<img src="'.plugins_url('vod-infomaniak/img/ico-folder-open-16x16.png').'" style="vertical-align:bottom"/>';
							$tReturn .= $oVideo->sPath;
						$tReturn .= '</td>';
						$tReturn .= '<td>'.$oVideo->dUpload.'</td>';
					$tReturn .= '</tr>';
				}
			}else{
				$tReturn .= "<tr><td colspan='3'>Aucune Resultat</td></tr>";
			}
			echo $tReturn;
			die();
		}

		function check($the_content, $side = 0) {
			$tag = $this->options['tag'];
			if ($tag != '' && strpos($the_content, "[" . $tag) !== false) {
				preg_match_all("/\[$tag([^`]*?)\]([^`]*?)\[\/$tag\]/", $the_content, $matches, PREG_SET_ORDER);
				foreach ($matches as $match) {
					$the_content = preg_replace("/\[$tag([^`]*?)\]([^`]*?)\[\/$tag\]/", $this->tag($match[2], $match[1], '', '', $side), $the_content, 1);
				}
			}
			if (strpos($the_content, "[upload-vod") !== false) {
				$tag = "upload-vod";
				preg_match_all("/\[$tag([^`]*?)\]([^`]*?)\[\/$tag\]/", $the_content, $matches, PREG_SET_ORDER);
				foreach ($matches as $match) {
					$the_content = preg_replace("/\[$tag([^`]*?)\]([^`]*?)\[\/$tag\]/", $this->tag_upload($match[2], $match[1], '', '', $side), $the_content, 1);
				}
			}
			return $the_content;
		}

		function tag_upload($file, $params, $high = 'v', $time = '', $side = 0) {
			//On check que le tag upload ne doit pas etre remplacer par un tag vod
			if (!empty($file) && strpos($file, ':') !== false) {
				$decoupage = explode(":", $file);
				$sToken = $decoupage[0];
				$aUpload = $this->db->get_upload_video($sToken);
				if (intval($aUpload->iVideo) > 0) {
					$video = $this->db->getVideo($aUpload->iVideo);
					if (!empty($video)) {
						global $post;
						$sVideoPath = $video->sPath . $video->sServerCode . "." . strtolower($video->sExtension);
						$update_post = array();
						$update_post['ID'] = $post->ID;
						$result = str_replace("[upload-vod]" . $file . "[/upload-vod]", "[vod]" . $sVideoPath . "[/vod]", $post->post_content);
						if ($result != $post->post_content) {
							$post->post_content = $result;
							$update_post['post_content'] = $post->post_content;
							// Sans le @, warning suivant la config apache. Probleme soumis sur le forum officiel
							@wp_update_post($update_post);
						}
						return $this->tag($sVideoPath, $params, $high, $time, $side);
					}
				}
			}


			$width = empty($aTagParam['width']) ? $this->options['width'] : intval($aTagParam['width']);
			$height = empty($aTagParam['height']) ? $this->options['height'] : intval($aTagParam['height']);
			return "<div style='background: url(\"" . plugins_url('vod-infomaniak/img/topbg10.png') . "\") repeat;border-radius: 8px; text-align:center; color: #DDDDDD; font-weight: bold; background-color: #222222; width: " . $width . "px; height: " . $height . "px;'>
			<div style='font-size: 150%;padding-top: 100px;line-height:" . (($height - 200) / 2) . "px;vertical-align: middle;'>
				<span style='display:block;'>" . __("Video en cours de conversion", 'vod_infomaniak') . " ...</span>
				<img src='" . plugins_url('vod-infomaniak/img/ico-vod-64.png') . "' style='vertical-align:middle'/>
			</div>
		</div>";
		}

		function removeSmartQuotes($sContent){
			$sContent = htmlentities($sContent);
			$sContent = str_replace(array(
				                        "'", '"', '’',
				                        '&amp;laquo;', '&amp;raquo;',
				                        '&amp;lsquo;', '&amp;rsquo;',
				                        '&amp;prime;', '&amp;Prime;',
				                        '&amp;nbsp;',),
			                        '"', $sContent);
			$sContent = str_replace(array("&amp;#8217;", "&amp;#8242;"), "", $sContent);
			$sContent = preg_replace('/"+/mi', '"', $sContent); // remplace les xquotes

			return $sContent;
		}

		function tag($file, $params, $high = 'v', $time = '', $side = 0) {
			//Recuperation des parametres optionnels des tags
			$aTagParam = array();
			if (!empty($params)) {

				$params = $this->removeSmartQuotes($params); // remplace les xquotes

				$params = html_entity_decode($params);
                $params = strtolower(str_replace('"', "", $params));

                $aList = explode(" ", $params);
				foreach ($aList as $param) {
					if (strpos($param, "=") !== false) {
                        $aCut = explode("=", $param);
						if (in_array($aCut[0], array("width", "height", "stretch", "autoplay", "loop", "player", "videoimage", "tokenfolder"))) {
							$aTagParam[$aCut[0]] = $aCut[1];
						}
					}
				}
			}

			//Recuperation des differents parametres
			$iVod = $this->options['vod_api_icodeservice'];
			if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
				$sProtocol = "https";
			}else{
				$sProtocol = "http";
			}

			$sUrl = $sProtocol."://vod.infomaniak.com/iframe.php";

			$sAccountBase = $this->options['vod_api_id'];
			$sKey = "";
			if (!empty($aTagParam['tokenfolder']) && !is_numeric($file)) {
				$oFolder = $this->db->getFolder($aTagParam['tokenfolder']);
				if (!empty($oFolder)) {
					$fileInfo = pathinfo($file);
					$sFileName = basename($file, '.' . $fileInfo['extension']);
					$sKey = "&sKey=" . $this->getTemporaryKey($oFolder->sToken, $sFileName);
				}
			}
			$videoimage = empty($aTagParam['videoimage']) ? 1 : intval($aTagParam['videoimage']);
			$player = empty($aTagParam['player']) ? $this->options['player'] : intval($aTagParam['player']);
			$autoplay = empty($aTagParam['autoplay']) ? $this->options['autoplay'] : intval($aTagParam['autoplay']);
			$loop = empty($aTagParam['loop']) ? $this->options['loop'] : intval($aTagParam['loop']);
			$width = empty($aTagParam['width']) ? $this->options['width'] : trim($aTagParam['width']);
			$height = empty($aTagParam['height']) ? $this->options['height'] : trim($aTagParam['height']);
			$stretch = empty($aTagParam['stretch']) ? $this->options['stretch'] : intval($aTagParam['stretch']);

            if (is_numeric($file)) {
				$video_url = $sUrl . "?url=&playlist=" . $file;
			} else {
				//Build de l'url finale
                $file = ltrim($file);
                if (!preg_match('/^http(s)?:\/\//', $file) ) {
					$sFile = $sAccountBase . "/" . $file;
				} else {
					$sFile = $file;
				}
				$video_url = $sUrl . "?url=" . $sFile;
				if ($videoimage) {
					$video_url .= "&preloadImage=" . str_replace(array(".flv", ".mp4"), ".jpg", $sFile);
				}
			}
			if (!empty($player)) {
				$video_url .= "&player=$player";
			} else {
				$video_url .= "&player=576";
			}
			if ($iVod) {
				$video_url .= "&vod=$iVod";
			}
			if (isset($aTagParam['autoplay'])) {
				$video_url .= "&autostart=$autoplay";
			}
			if (isset($aTagParam['loop'])) {
				$video_url .= "&loop=$loop";
			}
			if (isset($aTagParam['stretch'])) {
				$video_url .= "&strechheight=$stretch";
			}
            if (!empty($sKey)) {
                $video_url .= $sKey;
            }
            if (empty($width)) {
                $width = 480;
            }
            if (empty($height) || $height == 288 || strpos($width,'%') > 1) {
                $height = 288;
				$iPercentRatio = 56.25;
            }else{
				$iPercentRatio = (100/intval($width)*($height));
            }
	

			// Build de la balise, ancienne methode
			/**$html_tag = '<span class="youtube">
			***			<iframe title="Vod Player" class="vod-player" width="' . $width . '" height="' . $height . '" src="' . $video_url . '" frameborder="0" allowfullscreen></iframe>
			***			</span>';
			**/
			if (strpos($width,'%') == false && strpos($width,'px') == false){
				$width = $width."px";
			}

			$html_tag = '<div style="width:100%;max-width:'.$width.';">
							<div class="videoWrapper" style="position: relative;padding-bottom: '.$iPercentRatio.'%;height: 0;">
								<iframe style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" src="' . $video_url . '" frameborder="0" allowfullscreen></iframe>
							</div>
						</div>';

			return $html_tag;
		}

		function get_options() {
			$options = array(
				'width' => 480,
				'height' => 360,
				'template' => '{video}',
				'stretch' => 0,
				'loop' => 0,
				'autoplay' => 0,
				'privacy' => 0,
				'wtext' => '',
				'wtitle' => '',
				'tag' => 'vod',
				'iframe' => 'on',
				'updateFilterFolder' => '',
				'vod_api_connected' => 'off',
				'vod_right_integration' => VOD_RIGHT_CONTRIBUTOR,
				'vod_right_upload' => VOD_RIGHT_CONTRIBUTOR,
				'vod_right_player' => VOD_RIGHT_CONTRIBUTOR,
				'vod_right_playlist' => VOD_RIGHT_CONTRIBUTOR,
				'vod_api_valid_callback' => 'off'
			);
			$saved = get_option($this->key);

			if (!empty($saved)) {
				foreach ($saved as $key => $option) {
					$options[$key] = $option;
				}
			}

			if ($saved != $options) {
				update_option($this->key, $options);
			}

			return $options;
		}

		function mce_add_button($buttons) {
			array_push($buttons, "vodplugin");
			return $buttons;
		}

		function mce_register($plugin_array) {
			$plugin_array["swfobject"] = plugins_url('vod-infomaniak/js/swfobject.js');
			$plugin_array["vodplugin"] = plugins_url('vod-infomaniak/js/editor_plugin.js');
			return $plugin_array;
		}

		function buildForm() {
			require_once("vod.template.php");
            if (!empty($this->options['vod_api_connected']) && $this->options['vod_api_connected'] == 'on') {
				$aPlayers = $this->db->get_players();
				$aVideos = $this->db->get_videos_byPage(0, 50, $this->options['vod_filter_folder']);
				$aPlaylists = $this->db->get_playlists();
				$aFolders = $this->db->get_folders($this->options['vod_filter_folder']);
				$bCanUpload = false;
				if ($this->isCurrentUserCan('importation')) {
					$bCanUpload = true;
				}
				EasyVod_Display::buildForm($this->options, $aPlayers, $aVideos, $aPlaylists, $aFolders, $bCanUpload);
			} else {
                EasyVod_Display::buildFormNoConfig();
            }
		}

		function checkAutoUpdate() {
			$gmtime = time() - (int)substr(date('O'), 0, 3) * 60 * 60;
			if (!isset($this->options['vod_api_lastUpdate']) || $this->options['vod_api_lastUpdate'] < $gmtime - $this->auto_sync_delay) {
				$this->fastSynchro();
			}
		}

		function fastSynchro($updateVideo = true) {
			if (!isset($this->options['vod_api_connected']) || $this->options['vod_api_connected'] != 'on') {
				return false;
			}
			$oApi = $this->getAPI();

			//Update des players
			if ($oApi->playerModifiedSince($this->options['vod_api_lastUpdate'])) {
				$this->db->clean_players();
				$aListPlayer = $oApi->getPlayers();
				if (!empty($aListPlayer)) {
					foreach ($aListPlayer as $oPlayer) {
						if (empty($this->options['player'])) {
							$this->options['player'] = $oPlayer['iPlayerCode'];
						} else {
							if ($this->options['player'] == $oPlayer['iPlayerCode']) {
								$this->options['player'] = $oPlayer['iPlayerCode'];
								$this->options['width'] = $oPlayer['iWidth'];
								$this->options['height'] = $oPlayer['iHeight'];
								update_option($this->key, $this->options);
							}
						}
						$this->db->insert_player($oPlayer['iPlayerCode'], $oPlayer['sName'], $oPlayer['iWidth'], $oPlayer['iHeight'], $oPlayer['bAutoStart'], $oPlayer['bLoop'], $oPlayer['dEdit'], $oPlayer['bSwitchQuality']);
					}
				}
			}

			//Update des folders
			if ($oApi->folderModifiedSince($this->options['vod_api_lastUpdate'])) {
				$this->db->clean_folders();
				$aListFolder = $oApi->getFolders();
				if (!empty($aListFolder)) {
					foreach ($aListFolder as $oFolder) {
						$this->db->insert_folder($oFolder['iFolderCode'], $oFolder['sFolderPath'], $oFolder['sFolderName'], $oFolder['sAccess'], $oFolder['sToken']);
					}
				}
			}

			//Update des playlist
			if ($oApi->playlistModifiedSince($this->options['vod_api_lastUpdate'])) {
				$this->db->clean_playlists();
				$aListPlaylist = $oApi->getPlaylists();
				if (!empty($aListPlaylist)) {
					foreach ($aListPlaylist as $oPlaylist) {
						$this->db->insert_playlist($oPlaylist['iPlaylistCode'], $oPlaylist['sPlaylistName'], $oPlaylist['sPlaylistDescription'], $oPlaylist['iTotal'], $oPlaylist['sMode'], $oPlaylist['dCreated'], $oPlaylist['iTotalDuration']);
					}
				}
			}

			//Update de la synchro video
			if ($updateVideo) {
				$lastVideo = $this->db->getLastVideo();
				if (!empty($lastVideo)) {
					$lastDateImport = strtotime($lastVideo->dUpload);
					$isSynchro = false;
					$iPage = 0;
					while (!$isSynchro) {
						$aVideos = $oApi->getLastVideo(10, $iPage * 10);
						$iVideo = 0;
						while (!$isSynchro && $iVideo < count($aVideos)) {
							$oVideo = $aVideos[$iVideo];
							if ($lastDateImport < strtotime($oVideo['dFileUpload'])) {
								$this->db->insert_video($oVideo['iFileCode'], $oVideo['iFolder'], $oVideo['sFileName'], $oVideo['sFileServerCode'], $oVideo['aEncodes'][0]['sPath'], $oVideo['aEncodes'][0]['eConteneur'], $oVideo['fFileDuration'], $oVideo['dFileUpload']);
								$iVideo++;
							} else {
								$isSynchro = true;
							}
						}
						$iPage++;
					}
				}
			}

			//Verification s'il y a des upload en attente
			$aProcessing = $this->db->get_upload_process();
			if (!empty($aProcessing)) {
				$aLastImportation = $oApi->getLastImportation(50);
				foreach ($aLastImportation as $oImport) {
					if ($oImport['sProcessState'] == "OK" && !empty($oImport['iVideo']) && strpos($oImport['sInfo'], "wp_upload_post_") !== false) {
						//On le connait peut etre celui la
						foreach ($aProcessing as $oProcess) {
							if ("wp_upload_post_" . $oProcess->sToken == $oImport['sInfo']) {
								//On a trouvé un des upload
								$this->db->update_upload($oProcess->sToken, $oImport['iVideo']);
							}
						}
					}
				}
			}

			//Update de la synchro
			$serveurTime = $oApi->time();
			$localTime = time();
			$diff = ($serveurTime - $localTime);
			$this->options['vod_api_servTime'] = $diff;
			$this->options['vod_api_lastUpdate'] = time();
			update_option($this->key, $this->options);
			return true;
		}

		function fullSynchro() {
			if (!isset($this->options['vod_api_connected']) || $this->options['vod_api_connected'] != 'on') {
				return false;
			}
			//Suppression et reimportation complete des videos
			$oApi = $this->getAPI();
			$this->fastSynchro(false);
			$iNumberVideoApi = 200;
			$this->db->clean_videos();
			$iVideo = $oApi->countVideo();
			$iPageTotal = floor(($iVideo - 1) / $iNumberVideoApi);
			for ($iPage = 0; $iPage <= $iPageTotal; $iPage++) {
				$aVideos = $oApi->getLastVideo($iNumberVideoApi, $iPage * $iNumberVideoApi);
				if (!empty($aVideos)) {
					foreach ($aVideos as $oVideo) {
						$this->db->insert_video($oVideo['iFileCode'], $oVideo['iFolder'], $oVideo['sFileName'], $oVideo['sFileServerCode'], $oVideo['aEncodes'][0]['sPath'], $oVideo['aEncodes'][0]['eConteneur'], $oVideo['fFileDuration'], $oVideo['dFileUpload']);
					}
				}
			}
			return true;
		}

		function vod_admin_menu() {
			$site_url = get_option("siteurl");
            $aFolders = array();
			if (isset($_POST['submitted'])) {
				$bResult = false;

				if (empty($this->options['vod_api_callbackKey'])) {
					$this->options['vod_api_callbackKey'] = sha1(time() * rand());
				}
				if (empty($this->options['vod_api_c'])) {
					$this->options['vod_api_c'] = substr(sha1(time() * rand()), 0, 20);
				}

				$this->options['vod_api_login'] = stripslashes(htmlspecialchars($_POST['vod_api_login']));
				if (isset($_POST['vod_api_password']) && $_POST['vod_api_password'] != "XXXXXX") {
					$this->options['vod_api_password'] = $this->encrypt(stripslashes(htmlspecialchars($_POST['vod_api_password'])));
				}
				$this->options['vod_api_id'] = stripslashes(htmlspecialchars($_POST['vod_api_id']));
				$this->options['vod_api_connected'] = 'off';
                try {
					$oApi = $this->getAPI();
                	$bResult = $oApi->ping();
					if ($bResult) {
                		$this->options['vod_api_connected'] = 'on';
						$this->options['vod_api_icodeservice'] = $oApi->getServiceItemID();
						$this->options['vod_api_group'] = $oApi->getGroupID();
						$this->options['vod_api_lastUpdate'] = 0;
						$this->options['vod_filter_folder'] = "";

                        if (empty($_POST['logout']) === true) {
                            //Installation de la base de donnée seulement si elle n'est pas à jour
                            $this->update_db();
                        }

                        if (empty($this->options['vod_api_valid_callback']) || $this->options['vod_api_valid_callback'] == 'off') {
                        	//Clean de callback V1 s'il y en a encore
							$sUrl = $oApi->getCallback();
							if (!empty($sUrl) && strpos($sUrl, str_replace('http://', '', $site_url)) !== false) {
								$oApi->setCallback("");
							}

                            //On va essayer d'ajouter un callback V2
							$sUrl2 = $oApi->getCallbackV2();
							$bCallbackV2 = true;
							if ($sUrl2 != false) {
								foreach ($sUrl2 as $oCallback) {
									if (strpos($oCallback['sUrl'], $site_url) !== false) {
										$bCallbackV2 = false;
									}
								}
							}

                            if ($bCallbackV2) {
								$oApi->setCallbackV2($site_url . "/?vod_page=callback&key=" . $this->options['vod_api_callbackKey']);
							}

                            $this->options['vod_api_valid_callback'] == 'on';
						}

                        if ($this->db->count_video() == 0) {

                        	$oApi = $this->getAPI();

							//Update des videos
							$iNumberVideoApi = 200;
							$this->db->clean_videos();
							$iVideo = $oApi->countVideo();
							$iPageTotal = floor(($iVideo - 1) / $iNumberVideoApi);

                        	for ($iPage = 0; $iPage <= $iPageTotal; $iPage++) {
								$aVideos = $oApi->getLastVideo($iNumberVideoApi, $iPage * $iNumberVideoApi);
								foreach ($aVideos as $oVideo) {
									$this->db->insert_video($oVideo['iFileCode'], $oVideo['iFolder'], $oVideo['sFileName'], $oVideo['sFileServerCode'], $oVideo['aEncodes'][0]['sPath'], $oVideo['aEncodes'][0]['eConteneur'], $oVideo['fFileDuration'], $oVideo['dFileUpload']);
								}
							}
                        }
					}
				} catch (Exception $oException) {
					echo "<h4 style='color: red;'>" . __('Erreur : Impossible de se connecter', 'vod_infomaniak') . '</h4>';
				}

                if (empty($_POST['logout']) === false) {
                    $this->options['vod_api_connected'] = 'off';
                }
                update_option($this->key, $this->options);
			}

			if (isset($_POST['updateSynchro']) && $_POST['updateSynchro'] == 1) {
				$this->options['vod_api_lastUpdate'] = 0;
				$this->fastSynchro();
			}

			if (isset($_POST['updateSynchroVideo']) && $_POST['updateSynchroVideo'] == 1) {
				$this->options['vod_api_lastUpdate'] = 0;
				$this->fullSynchro();
			}

			if (isset($_POST['updateFilterFolder']) && $_POST['updateFilterFolder'] == 1) {
				if ($_POST['sFolderPath'] == -1) {
					$this->options['vod_filter_folder'] = "";
				} else {
					$this->options['vod_filter_folder'] = sanitize_text_field($_POST['sFolderPath']);
				}
				update_option($this->key, $this->options);
			}

			if (isset($_POST['updateRightPlugins']) && $_POST['updateRightPlugins'] == 1) {
				$this->options['vod_right_integration'] = $this->getRightValue($_POST['integration_role']);
				$this->options['vod_right_upload'] = $this->getRightValue($_POST['upload_role']);
				$this->options['vod_right_player'] = $this->getRightValue($_POST['player_role']);
				$this->options['vod_right_playlist'] = $this->getRightValue($_POST['playlist_role']);
				update_option($this->key, $this->options);
			}

			if ($this->options['vod_api_connected'] == "on") {
				$this->options['vod_count_player'] = $this->db->count_player();
				$this->options['vod_count_folder'] = $this->db->count_folder();
				$this->options['vod_count_video'] = $this->db->count_video();
				$this->options['vod_count_playlist'] = $this->db->count_playlists();
				$aFolders = $this->db->get_folders();
			}
			$actionurl = esc_url_raw($_SERVER['REQUEST_URI']);
			require_once("vod.template.php");
			EasyVod_Display::adminMenu($actionurl, $this->options, $site_url, $aFolders);
		}

		function plugin_ready() {
			if (empty($this->options['vod_api_connected']) || $this->options['vod_api_connected'] == 'off') {
				echo "<h2>" . __('Probleme de configuration', 'vod_infomaniak') . "</h2><p>" . __("Veuillez-vous rendre dans <a href='admin.php?page=configuration'>Videos -> Configuration</a> afin de configurer votre compte.", 'vod_infomaniak') . '</p>';
				return false;
			}
			return true;
		}

		function vod_management_menu() {
			if ($this->plugin_ready()) {

				if (isset($_REQUEST['sAction'])) {
					if ($_REQUEST['sAction'] == "rename") {
						$oVideo = $this->db->getVideo(intval($_POST['dialog-modal-id']));
						if ($oVideo != false) {
							$oApi = $this->getAPI();
							$oApi->renameVideo($oVideo->iFolder, $oVideo->sServerCode, $_POST['dialog-modal-name']);
							$this->db->rename_video(intval($_POST['dialog-modal-id']), $_POST['dialog-modal-name']);
							echo "<script>";
							echo "jQuery(document).ready(function() {";
							echo "	openVodPopup('" . $oVideo->iVideo . "', '" . $_POST['dialog-modal-name'] . "','" . $oVideo->sPath . $oVideo->sServerCode . "', '" . strtolower($oVideo->sExtension) . "');";
							echo "});";
							echo "</script>";
						}
					} else {
						if ($_REQUEST['sAction'] == "delete") {
							$oVideo = $this->db->getVideo(intval($_POST['dialog-confirm-id']));
							if ($oVideo != false) {
								$oApi = $this->getAPI();
								$oApi->deleteVideo($oVideo->iFolder, $oVideo->sServerCode);
								$this->db->delete_video(intval($_POST['dialog-confirm-id']));
							}
						} else {
							if ($_REQUEST['sAction'] == "post") {
								$oVideo = $this->db->getVideo(intval($_POST['dialog-post-id']));
								if ($oVideo != false) {
									$sBalise = "vod";
									$oFolder = $this->db->getFolder($oVideo->iFolder);
									if ($oFolder != false) {
										if (!empty($oFolder->sToken)) {
											$sBalise = "vod tokenfolder='" . $oVideo->iFolder . "'";
										}
									}

									// Create post object
									$my_post = array(
										'post_title' => $oVideo->sName,
										'post_content' => '[' . $sBalise . ']' . $oVideo->sPath . $oVideo->sServerCode . "." . strtolower($oVideo->sExtension) . '[/vod]'
									);

									// Insert the post into the database
									$id_draft = wp_insert_post($my_post);
									echo "<h3>" . __('Article correctement cree. Vous allez etre rediriger sur la page d\'edition', 'vod_infomaniak') . "</h3>";
									echo "<script type='text/javascript'>window.location = '" . admin_url('post.php?post=' . $id_draft . '&action=edit') . "';</script>";
									exit;
								}
							}
						}
					}
				}

				$iPage = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
				$iLimit = 20;
				$iVideoTotal = $this->db->count_video($this->options['vod_filter_folder']);
				$aVideos = $this->db->get_videos_byPage($iPage - 1, $iLimit, $this->options['vod_filter_folder']);
				for ($i = 0; $i < count($aVideos); $i++) {
					if (!empty($aVideos[$i]->sToken)) {
						$aVideos[$i]->sToken = $this->getTemporaryKey($aVideos[$i]->sToken, $aVideos[$i]->sServerCode);
					}
				}
				require_once("vod.template.php");
				$sPagination = EasyVod_Display::buildPagination($iPage, $iLimit, $iVideoTotal);
				$actionurl = esc_url_raw($_SERVER['REQUEST_URI']);
				EasyVod_Display::managementMenu($actionurl, $sPagination, $this->options, $aVideos);
			}
		}

		function vod_upload_menu() {
			if ($this->plugin_ready()) {
				require_once("vod.template.php");
				if (isset($_REQUEST['sAction']) && $_REQUEST['sAction'] == "popupUpload" && !empty($_REQUEST['iFolderCode'])) {
					//Affichage du popup d'upload
					$oFolder = $this->db->getFolder($_REQUEST['iFolderCode']);
					if (empty($oFolder) || empty($oFolder->sName)) {
						die(__("Il n'est pas possible d'uploader dans ce dossier.", 'vod_infomaniak'));
					}
					$oApi = $this->getAPI();
					$sToken = $oApi->initUpload($oFolder->sPath);
					delete_transient('vod_last_import');
					EasyVod_Display::uploadPopup($sToken, $oFolder);
				} else {
					if (isset($_REQUEST['sAction']) && $_REQUEST['sAction'] == "popupImport" && !empty($_REQUEST['iFolderCode'])) {
						//Affichage du popup d'import
						$bResult = false;
						$oFolder = $this->db->getFolder($_REQUEST['iFolderCode']);
						if (empty($oFolder) || empty($oFolder->sName)) {
							die(__("Il n'est pas possible d'uploader dans ce dossier.", 'vod_infomaniak'));
						}
						if ($_REQUEST['submit'] == 1) {
							$oApi = $this->getAPI();
							$aOptions = array();
							if (!empty($_REQUEST['sLogin']) && !empty($_REQUEST['sPassword'])) {
								$aOption['login'] = sanitize_user($_REQUEST['sLogin']);
								$aOption['password'] = sanitize_text_field($_REQUEST['sPassword']);
							}
							$sUrl = esc_url_raw($_REQUEST['sProtocole'] . "://" . $_REQUEST['sUrl']);
							$bResult = $oApi->importFromUrl($oFolder->sPath, $sUrl, $aOption);
						}
						$actionurl = esc_url_raw($_SERVER['REQUEST_URI']);
						delete_transient('vod_last_import');
						EasyVod_Display::ImportPopup($actionurl, $oFolder, $bResult);
					} else {
						//Affichage de la page principal
						$aFolders = $this->db->get_folders($this->options['vod_filter_folder']);

						$actionurl = esc_url_raw($_SERVER['REQUEST_URI']);
						EasyVod_Display::uploadMenu($actionurl, $this->options, $aFolders, $this->getLastImport());
					}
				}
			}
		}

		function printLastImport() {
			echo $this->getLastImport();
			die();
		}

		function getLastImport() {
			require_once("vod.template.php");
			$aLastImport = get_transient('vod_last_import');
			if (false == $aLastImport) {
				$oApi = $this->getAPI();
				$aLastImport = $oApi->getLastImportation();
				set_transient('vod_last_import', $aLastImport, 15);
			}
			return EasyVod_Display::tabLastUpload($aLastImport);
		}

		function vod_playlist_menu() {
			if ($this->plugin_ready()) {
				require_once("vod.template.php");

                if (isset($_REQUEST['create'])) {
                    $oPlaylist = $this->db->getPlaylist(intval($_REQUEST['create']));

                    if ($oPlaylist != false) {
                        $sBalise = "vod";
                        // Create post object
                        $my_post = array(
                            'post_title' => $oPlaylist->sPlaylistName,
                            'post_content' => '[' . $sBalise . ']' . $oPlaylist->iPlaylistCode . '[/vod]'
                        );

                        // Insert the post into the database
                        $id_draft = wp_insert_post($my_post);
                        echo "<h3>" . __('Article correctement cree. Vous allez etre rediriger sur la page d\'edition', 'vod_infomaniak') . "</h3>";
                        echo "<script type='text/javascript'>window.location = '" . admin_url('post.php?post=' . $id_draft . '&action=edit') . "';</script>";
                        exit;
                    }
                }


				$aPlaylist = $this->db->get_playlists();
				$actionurl = esc_url_raw($_SERVER['REQUEST_URI']);
				EasyVod_Display::playlistMenu($actionurl, $this->options, $aPlaylist);
			}
		}

		function vod_implementation_menu() {
			if ($this->plugin_ready()) {
				require_once("vod.template.php");
				if (isset($_POST['submitted'])) {
					$oPlayer = $this->db->get_player(intval($_REQUEST['selectPlayer']));
					if (!empty($oPlayer)) {
						$this->options['player'] = $oPlayer->iPlayer;
						$this->options['width'] = $oPlayer->iWidth;
						$this->options['height'] = $oPlayer->iHeight;
						update_option($this->key, $this->options);
					}
				}
				$aPlayers = $this->db->get_players();
				$actionurl = esc_url_raw($_SERVER['REQUEST_URI']);
				EasyVod_Display::implementationMenu($actionurl, $this->options, $aPlayers);
			}
		}

		function getTemporaryKey($sToken, $sVideoName) {
			$iTime = time() + intval($this->options['vod_api_servTime']);
			return md5($sToken . $sVideoName . $_SERVER['REMOTE_ADDR'] . date("YmdH", $iTime));
		}

		function getAPI() {
			require_once('vod.api.php');
			$sPassword = $this->decrypt($this->options['vod_api_password']);
			return new vod_api($this->options['vod_api_login'], $sPassword, $this->options['vod_api_id']);
		}

		function encrypt($text){
			global $encryptIv, $encryptCipher, $encryptPassword;

			if (extension_loaded('openssl')) {
				return openssl_encrypt($text, $encryptCipher, $encryptPassword, 0, $encryptIv);
			} else {
				return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, VOD_IK_SALT, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
			}
		}

		function decrypt($text){
			global $encryptIv, $encryptCipher, $encryptPassword;

			if (extension_loaded('openssl')) {
				return openssl_decrypt($text, $encryptCipher, $encryptPassword, 0, $encryptIv);
			} else {
				return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, VOD_IK_SALT, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
			}
		}		

		function vod_template_redirect() {
			global $wp_query;
			$vod_page = isset($wp_query->query_vars['vod_page']) ? $wp_query->query_vars['vod_page'] : "";
			if ($vod_page == 'callback') {
				include(plugins_url("vod-infomaniak/vod_callback.php"));
				exit;
			}
		}
	}


	/**
	 * Classe permettant la gestion des tables sql utilisé par ce plugin
	 * En cas de problemes ou de questions, veuillez contacter support-vod-wordpress@infomaniak.ch
	 *
	 * @author Destrem Kevin
	 * @link http://statslive.infomaniak.ch/vod/api/
	 * @version 1.0
	 * @copyright infomaniak.ch
	 *
	 */

	class EasyVod_db {
		var $db_table_player;
		var $db_table_folder;
		var $db_table_video;
		var $db_table_playlist;
		var $db_table_upload;
		var $db_version = "1.0.6";

		function __construct() {
			global $wpdb;
			$this->db_table_player = $wpdb->prefix . "vod_player";
			$this->db_table_folder = $wpdb->prefix . "vod_folder";
			$this->db_table_video = $wpdb->prefix . "vod_video";
			$this->db_table_playlist = $wpdb->prefix . "vod_playlist";
			$this->db_table_upload = $wpdb->prefix . "vod_upload";
		}

		function install_db() {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$sql_player = "CREATE TABLE " . $this->db_table_player . " (
		 `iPlayer` INT UNSIGNED NOT NULL ,
		 `sName` VARCHAR( 255 ) NOT NULL ,
		 `iWidth` INT UNSIGNED NOT NULL ,
		 `iHeight` INT UNSIGNED NOT NULL ,
		 `bAutoPlay` TINYINT UNSIGNED NOT NULL ,
		 `bLoop` TINYINT UNSIGNED NOT NULL,
		 `bSwitchQuality` TINYINT UNSIGNED NOT NULL,
		 `dEdit` DATETIME NOT NULL
		) CHARACTER SET utf8;";
			dbDelta($sql_player);

			$sql_folder = "CREATE TABLE " . $this->db_table_folder . " (
		 `iFolder` INT UNSIGNED NOT NULL ,
		 `sPath` VARCHAR( 255 ) NOT NULL ,
		 `sName` VARCHAR( 255 ) NOT NULL ,
		 `sAccess` VARCHAR( 255 ) NOT NULL ,
		 `sToken` VARCHAR( 255 ) NOT NULL
		) CHARACTER SET utf8;";
			dbDelta($sql_folder);

			$sql_video = "CREATE TABLE " . $this->db_table_video . " (
		 `iVideo` INT UNSIGNED NOT NULL ,
		 `iFolder` INT UNSIGNED NOT NULL ,
		 `sName` VARCHAR( 255 ) NOT NULL ,
		 `sPath` VARCHAR( 255 ) NOT NULL,
		 `sServerCode` VARCHAR( 255 ) NOT NULL,
		 `sExtension` VARCHAR( 4 ) NOT NULL,
		 `iDuration` INT UNSIGNED NOT NULL,
		 `dUpload` DATETIME NOT NULL
		) CHARACTER SET utf8;";
			dbDelta($sql_video);

			$sql_playlist = "CREATE TABLE " . $this->db_table_playlist . " (
		 `iPlaylistCode` INT UNSIGNED NOT NULL ,
		 `sPlaylistName` VARCHAR( 255 ) NOT NULL ,
		 `sPlaylistDescription` VARCHAR( 255 ) NOT NULL ,
		 `iTotal` INT UNSIGNED NOT NULL,
		 `iTotalDuration` INT UNSIGNED NOT NULL,
		 `sMode` VARCHAR( 255 ) NOT NULL,
		 `dCreated` DATETIME NOT NULL
		) CHARACTER SET utf8;";
			dbDelta($sql_playlist);

			$sql_upload = "CREATE TABLE " . $this->db_table_upload . " (
		 `iUpload` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		 `sToken` VARCHAR( 255 ) NOT NULL,
		 `iPost` INT UNSIGNED NOT NULL,
		 `iVideo` INT UNSIGNED NOT NULL,
		 PRIMARY KEY (iUpload),
		 UNIQUE KEY sToken (sToken)
		) CHARACTER SET utf8;";
			dbDelta($sql_upload);

			update_option("vod_db_version", $this->db_version);
		}

		/*
		* Gestion des players
		*/

		function get_players() {
			global $wpdb;
			return $wpdb->get_results("SELECT * FROM " . $this->db_table_player);
		}

		function get_player($iPlayer) {
			global $wpdb;
			return $wpdb->get_row("SELECT * FROM " . $this->db_table_player . " WHERE iPlayer=" . intval($iPlayer) . " LIMIT 1");
		}

		function clean_players() {
			global $wpdb;
			return $wpdb->query("DELETE FROM " . $this->db_table_player);
		}

		function insert_player($iPlayer, $sName, $iWidth, $iHeight, $bStart, $bLoop, $dEdit, $bSwitchQuality) {
			global $wpdb;
			$wpdb->insert($this->db_table_player, array('iPlayer' => $iPlayer, 'sName' => $sName, 'iWidth' => $iWidth, 'iHeight' => $iHeight, 'bAutoPlay' => $bStart, 'bLoop' => $bLoop, 'dEdit' => $dEdit, 'bSwitchQuality' => $bSwitchQuality));
		}

		function count_player() {
			global $wpdb;
			return $wpdb->get_var("SELECT COUNT(*) FROM " . $this->db_table_player);
		}

		/*
		* Gestion des playlist
		*/

		function search_playlist($sTerm, $iLimit = 6) {
			global $wpdb;
			$sql = $wpdb->prepare("SELECT * FROM " . $this->db_table_playlist . " WHERE sPlaylistName LIKE %s OR sPlaylistDescription LIKE %s ORDER BY dCreated DESC LIMIT " . intval($iLimit), "%" . $sTerm . "%", "%" . $sTerm . "%");
			return $wpdb->get_results($sql);
		}

		function get_playlists() {
			global $wpdb;
			return $wpdb->get_results("SELECT * FROM " . $this->db_table_playlist);
		}

		function clean_playlists() {
			global $wpdb;
			return $wpdb->query("DELETE FROM " . $this->db_table_playlist);
		}

		function insert_playlist($iPlaylistCode, $sPlaylistName, $sPlaylistDescription, $iTotal, $sMode, $dCreated, $iTotalDuration) {
			global $wpdb;
			$wpdb->insert($this->db_table_playlist, array('iPlaylistCode' => $iPlaylistCode, 'sPlaylistName' => $sPlaylistName, 'sPlaylistDescription' => $sPlaylistDescription, 'iTotal' => $iTotal, 'sMode' => $sMode, 'dCreated' => $dCreated, 'iTotalDuration' => $iTotalDuration));
		}

		function count_playlists() {
			global $wpdb;
			return $wpdb->get_var("SELECT COUNT(*) FROM " . $this->db_table_playlist);
		}

		/*
		* Gestion des dossiers
		*/

		function getFolder($iFolder) {
			global $wpdb;
			return $wpdb->get_row("SELECT * FROM " . $this->db_table_folder . " WHERE iFolder=" . intval($iFolder) . " LIMIT 1");
		}

		function get_folders($sFilter = "") {
			global $wpdb;
			if (!empty($sFilter)) {
				$sql = $wpdb->prepare("SELECT * FROM " . $this->db_table_folder . " WHERE sPath LIKE %s ORDER BY `sPath` ASC", $sFilter . "%");
				return $wpdb->get_results($sql);
			} else {
				return $wpdb->get_results("SELECT * FROM " . $this->db_table_folder . " ORDER BY `sPath` ASC");
			}
		}

		function clean_folders() {
			global $wpdb;
			return $wpdb->query("DELETE FROM " . $this->db_table_folder);
		}

		function insert_folder($iFolder, $sPath, $sName, $sAccess, $sToken) {
			global $wpdb;
			$wpdb->insert($this->db_table_folder, array('iFolder' => $iFolder, 'sPath' => $sPath, 'sName' => $sName, 'sAccess' => $sAccess, 'sToken' => $sToken));
		}

		function count_folder() {
			global $wpdb;
			return $wpdb->get_var("SELECT COUNT(*) FROM " . $this->db_table_folder);
		}

		/*
		* Gestion des videos
		*/

		function search_videos($sTerm, $iLimit = 6, $sFilter = "") {
			global $wpdb;
			if (!empty($sFilter)) {
				$sql = $wpdb->prepare("SELECT iFolder FROM " . $this->db_table_folder . " WHERE sPath LIKE %s ORDER BY sPath", $sFilter . '%');
				$aFolders = $wpdb->get_results($sql);
				$aFoldersList = array();
				foreach ($aFolders as $oFolder) {
					$aFoldersList[] = $oFolder->iFolder;
				}
				$sql = $wpdb->prepare("SELECT video.*, folder.sAccess, folder.sToken FROM " . $this->db_table_video . " as video
			INNER JOIN " . $this->db_table_folder . " as folder ON video.iFolder = folder.iFolder
			WHERE (video.sName LIKE %s OR sServerCode LIKE %s) AND video.iFolder IN ( " . implode(",", $aFoldersList) . ") ORDER BY dUpload DESC LIMIT " . intval($iLimit), "%" . $sTerm . "%", "%" . $sTerm . "%");
				return $wpdb->get_results($sql);
			} else {
				$sql = $wpdb->prepare("SELECT video.*, folder.sAccess, folder.sToken FROM " . $this->db_table_video . " as video
			INNER JOIN " . $this->db_table_folder . " as folder ON video.iFolder = folder.iFolder
			WHERE video.sName LIKE %s OR sServerCode LIKE %s ORDER BY dUpload DESC LIMIT " . intval($iLimit), "%" . $sTerm . "%", "%" . $sTerm . "%");
				return $wpdb->get_results($sql);
			}
		}

		function get_videos_byPage($iPage, $iLimit, $sFilter = "") {
			global $wpdb;
			if (!empty($sFilter)) {
				$sql = $wpdb->prepare("SELECT iFolder FROM " . $this->db_table_folder . " WHERE sPath LIKE %s ORDER BY sPath", $sFilter . '%');
				$aFolders = $wpdb->get_results($sql);
				$aFoldersList = array();
				foreach ($aFolders as $oFolder) {
					$aFoldersList[] = $oFolder->iFolder;
				}
				return $wpdb->get_results("SELECT video.*, folder.sAccess, folder.sToken FROM " . $this->db_table_video . " as video
			INNER JOIN " . $this->db_table_folder . " as folder ON video.iFolder = folder.iFolder
			WHERE video.iFolder IN ( " . implode(",", $aFoldersList) . ")
			ORDER BY `dUpload` DESC LIMIT " . intval($iPage * $iLimit) . ", " . intval($iLimit));
			} else {
				return $wpdb->get_results("SELECT video.*, folder.sAccess, folder.sToken FROM " . $this->db_table_video . " as video
			INNER JOIN " . $this->db_table_folder . " as folder ON video.iFolder = folder.iFolder
			ORDER BY `dUpload` DESC LIMIT " . intval($iPage * $iLimit) . ", " . intval($iLimit));
			}
		}

		function get_videos_byCodes($sServerCode, $iFolderCode) {
			global $wpdb;
			$sql = $wpdb->prepare("SELECT * FROM " . $this->db_table_video . " WHERE sServerCode=%s AND iFolder=%d", $sServerCode, $iFolderCode);
			return $wpdb->get_results($sql);
		}

		function getLastVideo() {
			global $wpdb;
			return $wpdb->get_row("SELECT * FROM " . $this->db_table_video . " ORDER BY dUpload DESC LIMIT 1");
		}

		function getVideo($iVideo) {
			global $wpdb;
			return $wpdb->get_row("SELECT * FROM " . $this->db_table_video . " WHERE iVideo=" . intval($iVideo) . " LIMIT 1");
		}

		function getPlaylist($iPlaylistCode) {
            global $wpdb;
            return $wpdb->get_row("SELECT * FROM " . $this->db_table_playlist . " WHERE iPlaylistCode=" . intval($iPlaylistCode) . " LIMIT 1");
        }

        function get_videos() {
			global $wpdb;
			return $wpdb->get_results("SELECT * FROM " . $this->db_table_video . " ORDER BY `dUpload` DESC");
		}

		function clean_videos() {
			global $wpdb;
			return $wpdb->query("DELETE FROM " . $this->db_table_video);
		}

		function rename_video($iVideo, $sName) {
			global $wpdb;
			$sql = $wpdb->prepare("UPDATE " . $this->db_table_video . " SET sName=%s WHERE iVideo=%d LIMIT 1", $sName, $iVideo);
			$wpdb->query($sql);
		}

		function insert_video($iVideo, $iFolder, $sName, $sServerCode, $sPath, $sExtension, $iDuration, $dUpload) {
			global $wpdb;
			$wpdb->insert($this->db_table_video, array('iVideo' => $iVideo, 'iFolder' => $iFolder, 'sName' => $sName, 'sServerCode' => $sServerCode, 'sPath' => $sPath, 'sExtension' => $sExtension, 'iDuration' => $iDuration, 'dUpload' => $dUpload));
		}

		function count_video($sFilter = "") {
			global $wpdb;
			if (!empty($sFilter)) {
				$sql = $wpdb->prepare("SELECT iFolder FROM " . $this->db_table_folder . " WHERE sPath LIKE %s ORDER BY sPath", $sFilter . '%');
				$aFolders = $wpdb->get_results($sql);
				$aFoldersList = array();
				foreach ($aFolders as $oFolder) {
					$aFoldersList[] = $oFolder->iFolder;
				}
				return $wpdb->get_var("SELECT COUNT(*) FROM " . $this->db_table_video . "
			WHERE iFolder IN ( " . implode(",", $aFoldersList) . ")");
			} else {
				return $wpdb->get_var("SELECT COUNT(*) FROM " . $this->db_table_video);
			}
		}

		function delete_video($iVideo = -1) {
			global $wpdb;
			return $wpdb->query("DELETE FROM " . $this->db_table_video . " WHERE iVideo = " . intval($iVideo) . " LIMIT 1");
		}

		/*
		 * Gestion des uploads
		 */

		function insert_upload($sToken, $iPost) {
			global $wpdb;
			$wpdb->insert($this->db_table_upload, array('sToken' => $sToken, 'iPost' => $iPost));
		}

		function update_upload($sToken, $iPost) {
			global $wpdb;
			return $wpdb->query($wpdb->prepare("UPDATE " . $this->db_table_upload . " SET `iVideo`=%d WHERE `wp_vod_upload`.`sToken`=%s", $iPost, $sToken));
		}

		function get_upload_video($sToken) {
			global $wpdb;
			return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->db_table_upload . " WHERE sToken=%s LIMIT 1", $sToken));
		}

		function get_upload_process() {
			global $wpdb;
			return $wpdb->get_results("SELECT * FROM " . $this->db_table_upload . " WHERE iVideo=0");
		}
	}

	function vod_query_vars($qvars) {
		$qvars[] = 'vod_page';
		return $qvars;
	}

?>
