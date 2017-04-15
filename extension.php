<?php

class WallabagApiExtension extends Minz_Extension {
	public function init() {
		Minz_View::appendScript($this->getFileUrl('jquerymin.js', 'js'), false, false, false);
		Minz_View::appendScript($this->getFileUrl('script.js', 'js'), false, false, false);

		$this->registerTranslates();

		$this->registerController('wallabag');
		$this->registerViews();
	}

	private function removeTrailingSlash($url) {
		$url = trim($url);
		$urlLen = strlen($url);
		$urlLastCharPos = $urlLen - 1;
		$lastChar = $url[$urlLastCharPos];

		if ($lastChar == '/') {
			return substr($url, 0, $urlLastCharPos);
		}

		return $url;
	}

	public function handleConfigureAction() {
		$this->registerTranslates();
		if (Minz_Request::isPost()) {
			FreshRSS_Context::$user_conf->wallabag_api_client_id = Minz_Request::param('api_client_id', '');
			FreshRSS_Context::$user_conf->wallabag_api_client_secret = Minz_Request::param('api_client_secret', '');
			$uri = $this->removeTrailingSlash(Minz_Request::param('uri', ''));
			FreshRSS_Context::$user_conf->wallabag_api_uri = $uri;
			FreshRSS_Context::$user_conf->wallabag_username = Minz_Request::param('username', '');
            $password = Minz_Request::param('password', '');
            if (!empty($password)) {
                FreshRSS_Context::$user_conf->wallabag_password = $password;
            }
			FreshRSS_Context::$user_conf->save();
		}
	}

}
