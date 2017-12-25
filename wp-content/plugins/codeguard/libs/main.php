<?php

include_once("beacon.php");
include_once("authenticate.php");
@set_time_limit(0);

if (!class_exists('main')) {

    add_action('admin_menu', array('main', 'admin_inc_menu'),999);
	add_action( 'init', array( 'main', 'cg_load_text_domain' ) );
    add_action('wp_ajax_amazon-s3-backup-as3b-recover', array('main', 'restore_backup') );
    add_action('wp_ajax_amazon-s3-backup_logs', array('main', 'get_log') );
    add_action('wp_ajax_amazon-s3-backup_local_backup', array('main', 'localBackup') );
    add_action('wp_ajax_amazon-s3-backup_create', array('main', 'backup_s3') );
    add_action('wp_ajax_amazon-s3-backup_set_user_mail', array('main', 'setUserMail') );
    add_action('admin_post_amazon-s3-backup_delete_backup', array('main', 'delete_backup') );
    add_action( 'codeguard_daily_backup', array('main','backup_s3' ));
    register_uninstall_hook("../uninstall.php", "codeguard_uninstall_beacon");

    class main {

        public static $pathPlugin = '';

        public static $pl_name = '';

        public static $log_file = '';

        public static $plugins = array();

		public static function cg_load_text_domain() {
			load_plugin_textdomain("codeguard", false, '/' . basename(dirname(__FILE__)) . '/languages/');
		}

        public static function setPluginName($plugin)
        {
            self::$pl_name = $plugin;
        }

        public static function setPluginDir($dir)
        {
            self::$pathPlugin = $dir;
        }

        public static function getPluginDir()
        {
            return self::$pathPlugin;
        }
        public static function setIndex($_dir)
        {
            if (is_dir($_dir)) {
                file_put_contents($_dir . "/index.php", "<?php \n echo 'plugins';");
            }
        }

        public static function mkdir($dir)
        {
            if (!is_dir($dir)) {
                mkdir($dir, 0755);
                self::setIndex($dir);
            }
        }
        public static function getTmpDir()
        {
            $tmp = self::$pathPlugin . "/temp";
            if ( !is_dir( $tmp ) ) {
                mkdir($tmp, 0755);
                self::setIndex($tmp);
            }
            return $tmp;
        }

        static function log($msg)
        {
            $log_time = date_format(new DateTime('NOW'),"Y/m/d H:i:s ");
            $log_dir = self::$pathPlugin . "/logs";
            if ( !is_dir( $log_dir ) ) {
                mkdir($log_dir, 0755);
                self::setIndex($log_dir);
            }
            file_put_contents($log_dir . "/log", $log_time . $msg ."\n", FILE_APPEND);
        }

        static function getLog()
        {
            $log_dir = self::$pathPlugin . "/logs";
            if (file_exists($log_dir . '/log')) {
                return file_get_contents($log_dir . '/log');
            }
            return '';
        }

        public static function getNameBackup($time_create = 0, $time_ret = false, $name_return = false)
        {
            $name = get_option('siteurl');

            $name = str_replace("http://", '', $name);
            $name = str_replace("https://", '', $name);
            $name = preg_replace("|\W|", "_", $name);

            if ($name_return) {
                return $name;
            }
            if ($time_create > 0) {
                $time = $time_create;
            } else {
                $time = time();
            }
            $name .= '-full-' . date("Y_m_d_H_i", $time);
            if ($time_ret) {
                return date("d.m.Y H:i", $time);
            } else {
                return $name;
            }
        }
        public static function run()
        {
            $request_name =  self::$pl_name . '_request';
            if( isset( $_POST[$request_name] ) && ! empty ( $_POST[$request_name] ) ) {
                $data = plugin_unpack($_POST[$request_name]);
                if (isset($data['method'])) {
                    self::init();
                    $model_run = new core($data['method'], $data, true);
                    echo '<wpadm>' . $model_run . '</wpadm>';
                    exit;
                }
            }
        }
        public static function checkLog()
        {
            $log_dir = self::$pathPlugin . "/logs";
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755);
                self::setIndex($log_dir);
            }
            self::$log_file = $log_dir . "/log";
        }
        public static function get_log()
        {
            @session_write_close();
            $log = self::getLog();
            $log2 = self::getPluginDir() . "/logs/logs2";
            if (file_exists($log2)) {
                $text = @file_get_contents($log2);
                file_put_contents($log2, $log);
                $log = str_replace($text, "", $log);
            } else {
                file_put_contents($log2, $log);
            }
            $log = explode("\n", $log);
            krsort($log);
            @session_start();
            echo json_encode(array('log' => $log));
            wp_die();
        }
        public static function init()
        {

            self::checkLog();
            include 'sender.php';
            include 'lang.php';
            include 'core.php';
            include 'result.php';
            include 'func.php';
            include 'define.php';
            include 'module_as3b.php';
            self::includesPlugins();
        }
        private static function includesPlugins()
        {
            include 'inc_plugins.php';
            self::$plugins = $inc_plugins;
        }


        public static function activate()
        {
            codeguard_beacon('activate', func_get_args());
            if ( ! wp_next_scheduled( 'codeguard_daily_backup' ) ) {
                wp_schedule_event( time(), 'daily', 'codeguard_daily_backup' );
            }

        }

        public static function deactivate()
        {

            codeguard_beacon('deactivate', func_get_args());
            wp_clear_scheduled_hook('codeguard_daily_backup');

        }



        public static function include_admins_script()
        {
            wp_enqueue_style('css-amazon-s3-backup', plugins_url( "/css/style.css", dirname(__FILE__) ) );
            wp_enqueue_script('js-amazon-s3-backup', plugins_url( "/js/main.js",  dirname(__FILE__) ) );

            wp_enqueue_script('js-amazon-s3-backup-js1', plugins_url( "/js/jquery.arcticmodal-0.3.min.js",  dirname(__FILE__) ) );
            wp_enqueue_script('js-amazon-s3-backup-js2', plugins_url( "/js/bootstrap.min.js",  dirname(__FILE__) ) );

            wp_enqueue_style('js-amazon-s3-backup-css1', plugins_url( "/css/jquery.arcticmodal-0.3.css", dirname(__FILE__) ) );

            wp_enqueue_script('postbox');
        }

        public static function setError($msg)
        {
            $_SESSION['codeguard-error'] = $msg;
        }

        public static function getError()
        {
            $error = '';
            if (isset($_SESSION['codeguard-error'])) {
                $error = $_SESSION['codeguard-error'];
                unset($_SESSION['codeguard-error']);
            }
            return $error;
        }

        public static function setMessage($msg)
        {
            $_SESSION['codeguard-message'] = $msg;
        }

        public static function getMessage()
        {
            $messge = '';
            if (isset($_SESSION['codeguard-message'])) {
                $messge = $_SESSION['codeguard-message'];
                unset($_SESSION['codeguard-message']);
            }
            return $messge;
        }

        public static function admin_inc_menu()
        {
            self::includesPlugins();
            $page =

			add_menu_page(
                'CodeGuard',
                'CodeGuard',
                'update_core',
                'amazon-s3-backup',
                array('main', 'backups_view'),
                plugins_url('/images/cg-logo.png', dirname(__FILE__))
            );

			add_submenu_page(
                'amazon-s3-backup',
                'Support',
                'Support',
                'update_core',
                'codeguard-support',
                array('main', 'support_view')
            );
        }

        public static function remove($dir)
        {
            if (is_dir($dir)) {
                $diropen = opendir($dir);
                while($d = readdir($diropen)) {
                    if ($d!= '.' && $d != '..') {
                        self::remove($dir . "/$d");
                    }
                }
                @rmdir($dir);
            } elseif (is_file($dir)) {
                @unlink($dir);
            }
        }

        public static function localBackup()
        {
            @session_write_close();
            self::remove(self::$log_file);
            $core = new core('backup', array('params' => array( ), 'sets' => 1));
            @session_start();
            echo json_encode( plugin_unpack((string)$core) );
            wp_die();
        }

        private static function getBackups()
        {
            $res = array();
            if (is_dir(BACKUP_DIR)) {
                $dir_open = opendir(BACKUP_DIR);
                if ($dir_open) {
                    $i = 0;
                    while( $d = readdir($dir_open) ) {
                        if ($d != '.' && $d != '..' && is_dir(BACKUP_DIR . "/$d") ) {
                            $res['data'][$i]['name'] = $d;
                            $res['data'][$i]['dt'] = self::getDateInName($d);

                            $res['data'][$i]['type'] = 'local';
                            $parts = readDirectrory(BACKUP_DIR . "/" .$d, array('.zip', '.md5') );
                            $res['data'][$i]['count'] = count($parts);
                            $res['data'][$i]['size'] = 0;
                            for($j = 0; $j < $res['data'][$i]['count']; $j++) {
                                $res['data'][$i]['size'] += @filesize($parts[$j]);
                                $parts[$j] = basename($parts[$j]);
                            }
                            $res['data'][$i]['files'] = implode(',', $parts);
                            $i++;
                        }
                    }
                    if (isset($res['data'])) {
                        $res['md5'] = md5( print_r($res['data'], 1) );
                    }
                }
            }
            return $res;
        }
        public static function getDateInName($name)
        {
            $date_ = explode('full' . '-', $name);
            if (isset($date_[1])) {
                $date = explode('_', $date_[1]);
                $d = "{$date[0]}-{$date[1]}-{$date[2]} {$date[3]}:" . preg_replace("/\([0-9]+\)/", '', $date[4]);
            }
            return $d;

        }

        private static function read_backup_s3($setting)
        {

            require_once self::getPluginDir() . '/libs/classes/S3.php';
            $s3 = new S3($setting['access_key_id'], $setting['secret_access_key']);
            $data = array('data' => array(), 'md5' => md5( print_r(array(), 1) ) );
            try {
                $project = self::getNameBackup(0, false, true);
                $keys = $s3->getBucket($setting['bucket'],$setting['prefix'] . '/' . $project . '-full');
                //print_r($keys);
                if (isset($keys)) {
                    $n = count($keys);

                    $j = 0;
                    $backups = array();
                    for($i = 0; $i < $n; $i++) {
                        if (isset($keys[$i]['Key'])) {
                            $backup = explode('/', $keys[$i]['Key']);
                            if (isset($backup[1]) && isset($backup[2]) && !empty($backup[2])) {
                                if (!isset($backups[$backup[1]])) {
                                    $backups[$backup[1]] = $j;
                                    $data['data'][$j]['name'] = $backup[1];
                                    $data['data'][$j]['dt'] = self::getDateInName($backup[1]);
                                    $data['data'][$j]['size'] = $keys[$i]['size'];
                                    $data['data'][$j]['files'] = $backup[2];
                                    $data['data'][$j]['type'] = 's3';
                                    $data['data'][$j]['count'] = 1;
                                    $j++;
                                } else {
                                    $data['data'][$backups[$backup[1]]]['files'] .= ',' . $backup[2];
                                    $data['data'][$backups[$backup[1]]]['size'] += $keys[$i]['Size'];
                                    $data['data'][$backups[$backup[1]]]['count'] += 1;
                                }
                            }
                        }

                    }
                }
            } catch (Exception $e) {
                return $data;
            }
            return $data;
        }

        public static function update_codeguard_creds($codeguard_key)
        {
            $creds = array();

            try {
                $json_string = json_decode(stripslashes(base64_decode(trim($codeguard_key))), true);

                $creds['access_key_id'] = $json_string['AccessKey'];
                $creds['secret_access_key'] = $json_string['SecretKey'];
                $creds['bucket'] = $json_string['S3Bucket'];
                $creds['prefix'] = $json_string['S3Path'];
                $creds['codeguard_key'] = $codeguard_key;

                if (! isset($creds['access_key_id']) || ! isset($creds['secret_access_key']) || ! isset($creds['bucket']) || ! isset($creds['prefix']) || ! isset($creds['codeguard_key'])) {
                    throw new Exception("Missing required authentication credentials.");
                }

                // Test 'em
                require_once self::getPluginDir() . '/libs/classes/S3.php';

                if (get_option(PREFIX_CODEGUARD . 'setting')) {
                    update_option(PREFIX_CODEGUARD . 'setting', $creds);
                } else {
                    add_option(PREFIX_CODEGUARD . 'setting', $creds);
                }

                if (get_option(PREFIX_CODEGUARD . 'error_count')) {
                    update_option(PREFIX_CODEGUARD . 'error_count', 0);
                }
            } catch (Exception $e) {
                self::setError( "Invalid CodeGuard key. ( " . $e->getMessage() . ")");
            }
            $fga = func_get_args();
            codeguard_beacon('updatekey', $fga);
        }

        public static function backups_view()
        {
            if (isset($_POST['codeguard_key'])) {
                self::update_codeguard_creds($_POST['codeguard_key']);
            }

            $amazon_option = get_option(PREFIX_CODEGUARD . 'setting');
            if ($amazon_option) {
                $data = self::read_backup_s3($amazon_option);
            }
            if (isset($data['data'])) {
                $data['md5'] = md5( print_r( $data['data'] , 1 ) );
            }
            $error = self::getError();
            $msg = self::getMessage();
            $error_count = get_option(PREFIX_CODEGUARD . 'error_count');
            ob_start();
            include self::getPluginDir() . "/template/view-backup.php";
            echo ob_get_clean();
        }


		public static function support_view()
        {
            ob_start();
            include self::getPluginDir() . "/template/view-support.php";
            echo ob_get_clean();
        }

        public static function delete_backup()
        {
            if (isset($_POST['backup-name']) && isset($_POST['backup-type'])) {
                if ($_POST['backup-type'] == 'local') {
                    self::remove(BACKUP_DIR . "/" . $_POST['backup-name']);
                } elseif ($_POST['backup-type'] == 's3') {
                    $amazon_option = get_option(PREFIX_CODEGUARD . 'setting');
                    if ($amazon_option) {

                        require_once self::getPluginDir() . '/libs/classes/S3.php';
                        $s3 = new S3($amazon_option['access_key_id'], $amazon_option['secret_access_key']);
                        try {
                            $keys = $s3->getBucket($amazon_option['bucket'],$amazon_option['prefix'] . '/' . $_POST['backup-name']);
                            if (isset($keys)) {
                                $n = count($keys);
                                for($i = 0; $i < $n; $i++) {
                                   $s3->deleteObject($amazon_option['bucket'], $keys[$i]['Key']);
                                }
                            }
                        } catch (Exception $e) {
                            self::setError( $e->getMessage() );
                        } catch(S3Exception $e) {
                            self::setError( $e->getMessage() );
                        }
                    }
                }
            }
            Header("location: " . admin_url( 'admin.php?page=amazon-s3-backup' ) );
        }
        public static function backup_s3()
        {
            @session_write_close();
            self::remove(self::$log_file);
            $core = new core('backup', array('params' => array(), 'sets' => 1 ) );
            $res = plugin_unpack((string)$core);
            if ($res['size'] > 0) {
                $setting = get_option(PREFIX_CODEGUARD . 'setting');
                if (isset($setting['access_key_id']) && isset($setting['secret_access_key']) && isset($setting['bucket'])) {
                    $core = new core('send-to-s3', array('params' => array( 'files' => $res['data'], 'access_details' =>
                        array(
                            'bucket' => $setting['bucket'],
                            'prefix' => $setting['prefix'],
                            'AccessKeyId' => $setting['access_key_id'],
                            'SecretAccessKey' => $setting['secret_access_key'],
                            'dir' => $res['name'],
                            'mkdir_for_backup' => 1,
                            'backup_id' => $res['name'],
                        )
                    )));
                    $res2 = plugin_unpack((string)$core);
                    if ($res2['result'] == 'success') {
                        $res['type'] = 's3';
                    } else {
                        $res['data'] = array();
                        $res['error'] = lang::get('Error sending to CodeGuard Backup', false);
                        $res['result'] = RESULT_ERROR;
                    }
                } else {
                    $res['data'] = array();
                    $res['error'] = lang::get('Set Access Key for CodeGuard', false);
                    $res['result'] = RESULT_ERROR;
                }
            } else {
                $res['data'] = array();
                $res['error'] = lang::get('Could not create backup', false);
                $res['result'] = RESULT_ERROR;
            }
            $error_count = get_option(PREFIX_CODEGUARD . 'error_count');
            if ($res2['result'] == 'success') {
                if (isset($error_count)) {
                    update_option(PREFIX_CODEGUARD . 'error_count', 0);
                }
            } else {
                if (isset($error_count)) {
                    update_option(PREFIX_CODEGUARD . 'error_count', $error_count + 1);
                } else {
                    add_option(PREFIX_CODEGUARD . 'error_count', 1);
                }
                $res['error_count'] = get_option(PREFIX_CODEGUARD . 'error_count');
            }
            if (isset($res['name'])) {
                self::remove(BACKUP_DIR . '/' . $res['name']);
            }
            echo json_encode( $res );
            @session_start();
            wp_die();
        }
        public static function restore_backup()
        {
            if (isset($_POST['type']) && isset($_POST['name'])) {
                self::remove(self::$log_file);
                $core = new core('recover', array('params' => array( 'name' => $_POST['name'], 'type' => $_POST['type'] ) ) );
                echo json_encode( plugin_unpack((string)$core) );
            }
            wp_die();
        }
    }

}
