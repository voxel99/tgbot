<?php

namespace jam\engine\utils;

use Exception;

class Curl {
    private static function init (string $url, array $data) {
        $c = curl_init();
        curl_setopt_array($c, array(
            CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $data['timeout'] ?? 5,
        ));

        if (!empty($data['noverify'])) {
            curl_setopt_array($c, array(
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_SSL_VERIFYHOST => 0,
            ));
        }

        if (isset($data['post'])) { // POST запрос
            curl_setopt_array($c, array(
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => is_array($data['post']) ? http_build_query($data['post']) : $data['post'],
            ));
        }

        if (isset($data['delete'])) { // DELETE запрос
            curl_setopt_array($c, array(
                CURLOPT_CUSTOMREQUEST => "DELETE",
                CURLOPT_POSTFIELDS => is_array($data['delete']) ? http_build_query($data['delete']) : $data['delete'],
            ));
        }

        if (!empty($data['sid'])) {
            curl_setopt($c, CURLOPT_COOKIE, 'PHPSESSID=' . $data['sid']);
        }

        if (!empty($data['cookie'])) {
            curl_setopt($c, CURLOPT_COOKIE, is_array($data['cookie']) ? http_build_query($data['cookie']) : $data['cookie']);
        }

        if (!empty($data['httpheader'])) {
            $headers = [];
            foreach ($data['httpheader'] as $k => $v) {
                if (is_numeric($k)) {
                    $headers[] = $v;
                } else {
                    $headers[] = $k.': '.$v;
                }
            }
            curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($data['useragent'])) {
            curl_setopt($c, CURLOPT_USERAGENT, $data['useragent']);
        }

        if (!empty($data['return_header'])) {
            curl_setopt($c, CURLOPT_HEADER, true);
        }

        if (!empty($data['user'])) { //запрос авторизации
            curl_setopt($c, CURLOPT_USERPWD, $data['user'] . ":" . $data['pass']);
        }

        if (isset($data['proxy'])) {
            curl_setopt($c, CURLOPT_PROXY, $data['proxy']);
        }

        if (!empty($data['referrer'])) {
            curl_setopt($c, CURLOPT_REFERER, $data['referrer']);
        }

        if (!empty($data['nobody'])) {
            curl_setopt($c, CURLOPT_HEADER, true);
        }
        return $c;
    }

    static function post ($url, $data = []) {
        return static::get($url, \array_merge(['post' => []], $data));
    }

    static function get ($url, $data = []) {
        if (str_starts_with($url, '//')) {
            $url = 'http:' . $url;
        }
        $data += array('useragent' => $data['ua'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36');
        $c = self::init($url, $data);

        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $size = curl_getinfo($c, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $content = curl_exec($c);

        if (!empty($data['json'])) {
            $content = \json_decode($content);
        }

        $ret = $content;
        if (empty($size)) {
            $size = strlen($content);
        }
        if (!empty($data['nobody'])) {
            $ret = $size;
        } elseif (!empty($data['info'])) {
            $ret = [
                'code' => $code ?? 0,
                'content' => $content,
                'size' => $size,
                'error' => $content === false && !empty($code) ? curl_error($c) : false
            ];
        } elseif (!empty($code) && $code >= 300){
            $ret = false;
        }
        return $ret;
    }

    static function multi (array $urlData): array {
        $urlData = array_values($urlData);
        if (count($urlData) === 1) {
            $url = $urlData[0]['url'];
            if (empty($url)) {
                throw new \Exception('Curl multi: Url is empty');
            }
            unset($urlData[0]['url']);
            return [self::get($url, $urlData[0])];
        }

        $cList = [];
        foreach ($urlData as $data) {
            if (empty($data['url'])) {
                throw new \Exception('Curl multi: Url is empty');
            }
            $url = $data['url'];
            unset($data['url']);
            $cList[] = self::init($url, $data);
        }
        $mh = curl_multi_init();
        foreach ($cList as $c) {
            curl_multi_add_handle($mh, $c);
        }

        do {
            $status = curl_multi_exec($mh, $active);
            if ($active) {
                curl_multi_select($mh);
            }
        } while ($active && $status == CURLM_OK);

        foreach ($cList as $c) {
            curl_multi_remove_handle($mh, $c);
        }
        curl_multi_close($mh);
        $ret = [];
        foreach ($urlData as $i => $data) {
            $content = curl_multi_getcontent($cList[$i]);
            if (!empty($data['json'])) {
                $content = \json_decode($content);
            }
            $ret[] = $content;

        }
        return $ret;
    }
}