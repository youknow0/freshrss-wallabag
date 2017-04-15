<?php

class WallabagApiExtension extends Minz_Extension {
	public function init() {
		$this->registerTranslates();

		$this->registerController('wallabag');
		$this->registerViews();

		$this->registerHook('entry_before_display',
		                    array('WallabagApiExtension', 'addWallabagButton'));
	}

	public function handleConfigureAction() {
		$this->registerTranslates();
		if (Minz_Request::isPost()) {
			echo 'SAVING';
			FreshRSS_Context::$user_conf->wallabag_api_client_id = Minz_Request::param('api_client_id', '');
			FreshRSS_Context::$user_conf->wallabag_api_client_secret = Minz_Request::param('api_client_secret', '');
			FreshRSS_Context::$user_conf->wallabag_api_uri = Minz_Request::param('uri', '');
			FreshRSS_Context::$user_conf->wallabag_username = Minz_Request::param('username', '');
            $password = Minz_Request::param('password', '');
            if (!empty($password)) {
                FreshRSS_Context::$user_conf->wallabag_password = $password;
            }
			FreshRSS_Context::$user_conf->save();

			echo 'api client id: ', FreshRSS_Context::$user_conf->wallabag_api_client_id ;
		}
	}

	public static function addWallabagButton($entry) {
		echo '<ul class="horizontal-list flux_header">';
		echo '<li class="item manage"><a class="bookmark" href="#">Wallabag</a></li>';
		echo '</ul>';

		return $entry;
	}

}
