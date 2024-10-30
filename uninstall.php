<?php
// WP_UNINSTALL_PLUGINが定義されているかチェック
if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();

delete_option('ieitoc_opt');
