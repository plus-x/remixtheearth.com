<?php

class CG_REST_Controller extends WP_REST_Controller {
	const VERSION = '1';
	const VENDOR  = 'codeguard';

	protected static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function route_namespace() {
		return self::VENDOR . '/v' . self::VERSION;
	}

	public function can_update_plugins() {
		return current_user_can( 'update_plugins' );
	}

	public function can_update_themes() {
		return current_user_can( 'update_themes' );
	}

	public function can_update_core() {
		return current_user_can( 'update_core' );
	}

	public function can_ping() {
		return current_user_can( 'update_plugins' ) || current_user_can( 'update_themes' );
	}

	public function register_api_routes() {
		$namespace = $this->route_namespace();
		register_rest_route( $namespace, 'plugins/status', array(
			'methods' => 'GET',
			'callback' => array( $this, 'plugins_status' ),
			'permission_callback' => array( $this, 'can_update_plugins' ),
		) );

		register_rest_route( $namespace, 'themes/status', array(
			'methods' => 'GET',
			'callback' => array( $this, 'themes_status' ),
			'permission_callback' => array( $this, 'can_update_themes' ),
		) );

		register_rest_route( $namespace, 'core/status', array(
			'methods' => 'GET',
			'callback' => array( $this, 'core_status' ),
			'permission_callback' => array( $this, 'can_update_core' ),
		) );

		register_rest_route( $namespace, 'plugins/upgrade', array(
			'methods' => 'GET',
			'callback' => array( $this, 'upgrade_plugin' ),
			'permission_callback' => array( $this, 'can_update_plugins' ),
		) );

		register_rest_route( $namespace, 'themes/upgrade', array(
			'methods' => 'GET',
			'callback' => array( $this, 'upgrade_theme' ),
			'permission_callback' => array( $this, 'can_update_themes' ),
		) );

		register_rest_route( $namespace, 'core/upgrade', array(
			'methods' => 'GET',
			'callback' => array( $this, 'upgrade_core' ),
			'permission_callback' => array( $this, 'can_update_core' ),
		) );

		register_rest_route( $namespace, 'compatible', array(
			'methods' => 'GET',
			'callback' => array( $this, 'compatible' ),
			'permission_callback' => array( $this, 'can_ping' ),
		) );

		register_rest_route( $namespace, 'ping', array(
			'methods' => 'GET',
			'callback' => array( $this, 'ping' ),
			'permission_callback' => array( $this, 'can_ping' ),
		) );
	}

	public function plugins_status() {
		wp_update_plugins();
		$data = array();
		$updates = get_site_transient( 'update_plugins' );
		$active_plugins = get_option( 'active_plugins' );

		foreach ( get_plugins() as $slug => $info ) {
			$plugin_response = array(
				'slug'              => $slug,
				'name'              => $info['Name'],
				'author'            => $info['Author'],
				'description'       => $info['Description'],
				'active'            => in_array( $slug, $active_plugins, true ),
				'installed_version' => $info['Version'],
				'latest_version'    => $info['Version'],
			);
			$update = $updates->response[ $slug ];
			if ( isset( $update ) ) {
				if ( ! is_object( $update ) ) {
					$update = (object) $update;
				}
				$plugin_response['latest_version'] = $update->new_version;
			}
			array_push( $data, $plugin_response );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Theme status information
	 */
	public function themes_status() {
		wp_update_themes();
		$data = array();
		$updates = get_site_transient( 'update_themes' );
		$active_theme = get_option( 'stylesheet' );

		foreach ( wp_get_themes() as $slug => $info ) {
			$theme_response = array(
				'slug'              => $slug,
				'name'              => $info['Name'],
				'author'            => $info['Author'],
				'description'       => $info['Description'],
				'active'            => $slug === $active_theme,
				'installed_version' => $info['Version'],
				'latest_version'    => $info['Version'],
			);
			$update = $updates->response[ $slug ];
			if ( isset( $update ) ) {
				if ( ! is_object( $update ) ) {
					$update = (object) $update;
				}
				$theme_response['latest_version'] = $update->new_version;
			}
			array_push( $data, $theme_response );
		}

		return new WP_REST_Response( $data, 200 );
	}

	public function core_status( WP_REST_Request $request ) {
		require_once( ABSPATH . 'wp-admin/includes/update.php' );
		wp_version_check();
		$version = get_bloginfo( 'version' );
		$data = array(
			'installed_version' => $version,
			'latest_version'    => $version,
		);

		$update = $this->core_update( $request->get_params() );
		if ( $update ) {
			$data['latest_version'] = $update->version;
		}

		return new WP_REST_Response( $data, 200 );
	}

	public function upgrade_plugin( WP_REST_Request $request ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin );
		$plugin = $request->get_param( 'plugin' );
		$upgraded = $upgrader->upgrade( $plugin );
		$result = array(
			'upgraded' => $upgraded,
			'messages' => $upgrader->skin->get_upgrade_messages(),
		);
		return new WP_REST_Response( $result, 200 );
	}

	public function upgrade_theme( WP_REST_Request $request ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		$upgrader = new Theme_Upgrader( new Automatic_Upgrader_Skin );
		$theme = $request->get_param( 'theme' );
		$upgraded = $upgrader->upgrade( $theme );
		$result = array(
			'upgraded' => $upgraded,
			'messages' => $upgrader->skin->get_upgrade_messages(),
		);
		return new WP_REST_Response( $result, 200 );
	}

	public function upgrade_core( WP_REST_Request $request ) {
		$update = $this->core_update( $request->get_params() );
		if ( $update ) {
			$upgrader = new Core_Upgrader( new Automatic_Upgrader_Skin );
			$upgraded = $upgrader->upgrade( $update );
			$result = array(
				'upgraded' => $upgraded,
				'messages' => $upgrader->skin->get_upgrade_messages(),
			);
		} else {
			$result = array(
				'upgraded' => false,
				'messages' => array( 'Already up-to-date.' ),
			);
		}
		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Get core update
	 *
	 * @param array $opts {
	 *   Update options.
	 *
	 *   @type mixed $major Whether to perform major upgrades.
	 *   @type mixed $minor Whether to perform minor upgrades.
	 *   @type mixed $dev   Whether to perform dev upgrades.
	 * }
	 *
	 * @return Object|false Core update offering or false if up-to-date.
	 */
	public function core_update( $opts = array() ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/update.php' );

		$default_opts = array(
			'major' => false,
			'minor' => true,
			'dev'   => false,
		);
		$opts = array_merge( $default_opts, $opts );

		if ( filter_var( $opts['major'], FILTER_VALIDATE_BOOLEAN ) ) {
			add_filter( 'allow_major_auto_core_updates', '__return_true' );
		} else {
			add_filter( 'allow_major_auto_core_updates', '__return_false' );
		}

		if ( filter_var( $opts['minor'], FILTER_VALIDATE_BOOLEAN ) ) {
			add_filter( 'allow_minor_auto_core_updates', '__return_true' );
		} else {
			add_filter( 'allow_minor_auto_core_updates', '__return_false' );
		}

		if ( filter_var( $opts['dev'], FILTER_VALIDATE_BOOLEAN ) ) {
			add_filter( 'allow_dev_auto_core_updates', '__return_true' );
		} else {
			add_filter( 'allow_dev_auto_core_updates', '__return_false' );
		}

		$update = find_core_auto_update();
		remove_filter( 'allow_major_auto_core_updates' );
		remove_filter( 'allow_minor_auto_core_updates' );
		remove_filter( 'allow_dev_auto_core_updates' );

		return $update;
	}

	public function ping() {
		return new WP_REST_Response( array(
			'result' => 'pong',
		), 200 );
	}

	public function compatible() {
		return new WP_REST_Response( array(
			'compatible' => ! cg_site_size_exceeded(),
		), 200 );
	}
}
