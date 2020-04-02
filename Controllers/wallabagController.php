<?php

class FreshExtension_wallabag_Controller extends Minz_ActionController {
	public function __construct() {
		$this->getConfig();
		$this->view = new StdClass;
    }

	private function getConfig() {
		$this->api_client_id = FreshRSS_Context::$user_conf->wallabag_api_client_id;
		$this->api_client_secret = FreshRSS_Context::$user_conf->wallabag_api_client_secret;
		$this->uri = FreshRSS_Context::$user_conf->wallabag_api_uri;
		$this->api_username = FreshRSS_Context::$user_conf->wallabag_username;
		$this->api_password = FreshRSS_Context::$user_conf->wallabag_password;
	}

	private function getOauthUri() {
		return $this->uri . '/oauth/v2/token';
	}

	private function getEntriesUri() {
		return $this->uri . '/api/entries.json';
	}

	protected function post($uri, $postdata, $headers = array()) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $uri);
		//curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postdata));

		$result = new StdClass;

		$result->response = curl_exec($curl);
		$result->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		return $result;
	}

	protected function authPost($uri, $postdata, $accessToken) {
		$headers = array(
			'Authorization: Bearer ' . $accessToken,
		);
		return $this->post($uri, $postdata, $headers);
	}

	private function getOauthToken() {
		$postdata = array(
			'client_secret' => $this->api_client_secret,
			'client_id' => $this->api_client_id,
			'username' => $this->api_username,
			'password' => $this->api_password,
			'grant_type' => 'password'
		);

		$headers = array(
			'Content-Type: application/x-www-form-urlencoded',
		);

		$res = $this->post($this->getOauthUri(), $postdata, $headers);
		$status = $res->status;
		$response = $res->response;

		if ($status !== 200) {
			throw new Exception("Token: Wallabag Server returned non-200 status " . $status);
		}

		$json = json_decode($response);

		if (empty($json->access_token)) {
			throw new Exception("Server did not supply an access token!");
		}

		return $json->access_token;
	}

	protected function shareToWallabag($urlToShare) {
		$token = $this->getOauthToken();
		$apiUri = $this->getEntriesUri();

		$params = array(
			'url' => $urlToShare,
		);

		$res = $this->authPost($apiUri, $params, $token);
		$status = $res->status;
		$response = $res->response;

		if ($status !== 200) {
			throw new Exception("Share: Wallabag Server returned non-200 status " . $status);
		}
	}
	
	public function shareAction() {
		$uri = $this->getEntriesUri();
		$entryId = Minz_Request::param('id');
		$entryDAO = FreshRSS_Factory::createEntryDao();
		$entry = $entryDAO->searchById($entryId);

		if ($entry === null) {
			throw new Exception("Entry ID " . $entryId . " not found!");
		}

		$this->shareToWallabag($entry->link());
		$this->view->callbackBeforeContent = function($view) {};

		if (!$this->ajax) {
			Minz_Request::forward(array(
				'c' => 'index',
				'a' => 'index',
			), true);
		}
	}
}
