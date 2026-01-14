<?php

include "init.php";

$config = config('docusign');

function getAccessToken(array $post) {
    global $config;

    $headers = [
        'Authorization: Basic ' . base64_encode($config['integration_key'].':'.$config['secret_key'])
    ];
    $tokenInfo = \jam\engine\utils\Curl::post(
        $config['oauth_token_endpoint'], [
            'post' => $post,
            'httpheader' => $headers,
            'json' => true
        ]
    );
    if ($tokenInfo) {
        $tokenInfo->updated_at = date('Y-m-d H:i:s');
        file_put_contents(TOKENINFO_PATH, json_encode($tokenInfo));
    }
    return $tokenInfo;
}


// In cli-mode we refresh token
if (!empty($argv)) {
    if (!is_file(TOKENINFO_PATH)) {
        die('File '.TOKENINFO_PATH.' not found. First you need to auth by web interface');
    }
    $tokenInfo = json_decode(file_get_contents(TOKENINFO_PATH));
    getAccessToken([
        'grant_type' => 'refresh_token',
        'refresh_token' => $tokenInfo->refresh_token
    ]);
} else {
    // Auth by web
    if (!isset($_GET['code'])) {
        // First part
        $query = http_build_query([
            'response_type' => 'code',
            'scope' => $config['scopes'],
            'client_id' => $config['integration_key'],
            'redirect_uri' => $config['redirect_uri'],
        ]);

        $url = $config['oauth_endpoint'] . '?' . $query;
        header('Location: '.$url);
    } else {
        // Second part
        $post = [
          'grant_type' => 'authorization_code',
          'code' =>  $_GET['code']
        ];
        $tokenInfo = getAccessToken($post);
        if ($tokenInfo) {
            echo print_r($tokenInfo, true);
        } else {
            echo "FAIL";
        }
    }
}