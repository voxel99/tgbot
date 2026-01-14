<?php

namespace jam\app\tg;

use jam\app\tg\state\StateException;

class TgBot {
    protected $config = [];
    function __construct($config) {
        $this->config = $config;
    }

    function getWebhookUrl() {
        return sprintf('https://api.telegram.org/bot%s/setWebhook?url=%s',
            $this->config['token'],
            $this->config['webhookUrl']
        );
    }

    private function isTest() {
        return isset($_GET['test']) || request()->isCli();
    }

    function getInputData() {
        $ret = [];
        $data = file_get_contents('php://input'); // весь ввод перенаправляем в $data
        if ($data) {
            $ret = json_decode($data, true); // декодируем json-закодированные-текстовые данные в PHP-массив
        }
        if (!empty($this->config['log']) && !$this->isTest()) {
            file_put_contents($this->config['log'], date('Y-m-d H:i:s').' getInputData: '.json_encode($ret)."\n", FILE_APPEND);
        }
        if (!empty($this->config['log_last']) && !$this->isTest()) {
            file_put_contents($this->config['log_last'], "<?php\nreturn ".var_export($ret, true).";");
        }
        return $ret;
    }

    function sendAnswerCallback($callbackQueryId, TgResponse $message) {
        $ch = curl_init();
        $opt = [
            CURLOPT_URL => sprintf('https://api.telegram.org/bot%s/answerCallbackQuery', $this->config['token']),
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => [
                'callback_query_id' => $callbackQueryId,
                'show_alert' => true,
                'text' => $message->getText()
            ]
        ];
        curl_setopt_array($ch, $opt);
        $content = curl_exec($ch);
        if (!empty($this->config['log']) && !$this->isTest()) {
            file_put_contents($this->config['log'], date('Y-m-d H:i:s').' onSendAnswerCallback: '.print_r(json_decode($content, true), true), FILE_APPEND);
        }
        curl_close($ch);
    }

    function sendResponse(string $chatId, TgResponse $message) {
        $ch = curl_init();
        $markup = $message->getReplyMarkup();
        if ($markup && is_array($markup)) {
            $markup = json_encode(["inline_keyboard" => [$markup]]);
        }
        $opt = [
            CURLOPT_URL => sprintf('https://api.telegram.org/bot%s/sendMessage', $this->config['token']),
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => [
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => $message->getText(),
                'reply_markup' => $markup,
            ]
        ];
        curl_setopt_array($ch, $opt);
        $content = curl_exec($ch);
        if (!empty($this->config['log']) && !$this->isTest()) {
            file_put_contents($this->config['log'], date('Y-m-d H:i:s').' onSendResponse: '.print_r(json_decode($content, true), true)."\nMarkup: ".print_r(json_decode($markup, true), true), FILE_APPEND);
        }
        curl_close($ch);
    }

    function run() {
        $input = $this->getInputData();
        if ($this->isTest() && !empty($this->config['log_last']) && is_file($this->config['log_last'])) {
            $input = include($this->config['log_last']);
        }
        if ($input) {
            $message = new TgReceiveMessage($input);
            $state = new TgBotState($message->getChatId());
            try {
                $response = TgCommand::process($message, $state);
            } catch (StateException $e) {
                $state->setError($e->getMessage());
                $response = TgResponse::create(TgResponse::ERROR, $state);
            } catch (\Exception $e) {
                if ($this->isTest()) {
                    $state->setError("Debug: ".$e->getMessage()."\n\n".$e->getTraceAsString());
                } else {
                    $state->setError($state->langVariants("Ошибка", "Error"));
                }
                $response = TgResponse::create(TgResponse::ERROR, $state);
            }

            if ($this->isTest()) {
                echo sprintf("\nResponse to %s\nText: %s\nMarkup: %s\n",
                    $message->getChatId(),
                    $response->getText(),
                    $response->getReplyMarkup()
                );
            } else {
                if ($response->isAnswerCallbackQuery()) {
                    $this->sendAnswerCallback($message->getCallbackQueryid(), $response);
                } else {
                    $this->sendResponse($message->getChatId(), $response);
                }

            }
        }
    }
}