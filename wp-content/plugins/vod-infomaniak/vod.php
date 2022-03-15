<?php
	/*
	Plugin Name: VOD Infomaniak
	Plugin URI: http://wordpress.org/extend/plugins/vod-infomaniak/
	Description: Easily embed and manage videos from Infomaniak VOD in your posts, comments and RSS feeds. You need an Infomaniak VOD account to use this plugin.
	Author: Infomaniak Staff
	Version: 1.4.4
	Author URI: http://infomaniak.com
	*/

	if (isset($oVod)) {
		return false;
	}
	require_once(dirname(__FILE__) . '/vod.class.php');
	$oVod = new EasyVod();
