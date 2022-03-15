<?php
    /**
     * Classe permettant de supprimer tables et options du plugin VOD lors de sa desinstallation dans wp admin
     * Note: il existe un register_uninstall_hook dans vod.class.php mais celui-ci ne se déclenche pas, du moins
     * pas avec la configuration actuel, dans tous les cas, lors de la desinstallation, wp déclenchera uninstall.php
     *
     * En cas de problèmes ou de questions, veuillez contacter support-vod-wordpress@infomaniak.ch
     *
     * @author VOD TEAM
     * @link http://statslive.infomaniak.ch/vod/api/
     * @version 1.2.2
     * @copyright infomaniak.ch
     *
     */

    require_once('vod.class.php');

    if (!defined('WP_UNINSTALL_PLUGIN'))
        exit();

    $db = new EasyVod_db();

    delete_option('vod_db_version');

    global $wpdb;

    $wpdb->query("DROP TABLE IF EXISTS " . $db->db_table_player);
    $wpdb->query("DROP TABLE IF EXISTS " . $db->db_table_folder);
    $wpdb->query("DROP TABLE IF EXISTS " . $db->db_table_video);
    $wpdb->query("DROP TABLE IF EXISTS " . $db->db_table_playlist);
    $wpdb->query("DROP TABLE IF EXISTS " . $db->db_table_upload);

?>