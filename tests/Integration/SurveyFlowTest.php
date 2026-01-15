<?php

namespace Tests\Integration;

use jam\app\tg\Questions;
use jam\app\tg\TgBotState;
use jam\app\tg\TgCommand;
use jam\app\tg\TgReceiveMessage;
use jam\app\tg\TgResponse;
use jam\app\tg\state\User;
use PHPUnit\Framework\TestCase;

/**
 * Интеграционный тест полного прохождения опроса
 */
class SurveyFlowTest extends TestCase {
    private int $testChatId;
    private TgBotState $state;

    protected function setUp (): void {
        parent::setUp();

        // Используем уникальный chat_id для каждого теста
        $this->testChatId = 999000000 + random_int(1, 999999);
        $this->state = new TgBotState($this->testChatId);

        // Очищаем тестовые данные перед каждым тестом
        $this->cleanupTestUser();
    }

    protected function tearDown (): void {
        // Очищаем тестовые данные после теста
        $this->cleanupTestUser();
        parent::tearDown();
    }

    private function cleanupTestUser (): void {
        try {
            $user = $this->state->getUser();
            if ($user->exists()) {
                db()->query('DELETE FROM ?_answer WHERE user_id = ?d', $user->id);
                db()->query('DELETE FROM ?_user WHERE id = ?d', $user->id);
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки при очистке
        }
    }

    /**
     * Вспомогательный метод для создания сообщения от пользователя
     */
    private function createMessage (string $text, ?string $phone = null): TgReceiveMessage {
        $data = [
            'message' => [
                'text' => $text,
                'from' => [
                    'id' => $this->testChatId,
                    'username' => 'testuser',
                    'first_name' => 'Test',
                    'last_name' => 'User'
                ]
            ]
        ];

        if ($phone) {
            $data['message']['contact'] = [
                'phone_number' => $phone
            ];
        }

        return new TgReceiveMessage($data);
    }

    /**
     * Тест: полное прохождение опроса от начала до конца
     */
    public function testCompleteUserJourney (): void {
        // 1. Начало - пользователь отправляет /start
        $message = $this->createMessage('/start');
        $response = TgCommand::process($message, $this->state);

        $this->assertInstanceOf(TgResponse::class, $response);
        $this->assertEquals(TgResponse::START, $this->getResponseType($response));

        // 2. Выбор языка - пользователь выбирает русский
        $message = $this->createMessage('/begin ru');
        $response = TgCommand::process($message, $this->state);

        $this->assertEquals('ru', $this->state->getLang());
        $this->assertEquals(TgResponse::PHONE, $this->getResponseType($response));

        // 3. Отправка телефона
        $message = $this->createMessage('', '+79991234567');
        $response = TgCommand::process($message, $this->state);

        $this->assertEquals('+79991234567', $this->state->getUser()->getPhone());
        $this->assertEquals(TgResponse::ANSWER, $this->getResponseType($response));

        // 4. Ответ на первый вопрос (ID_NAME)
        $this->assertEquals(Questions::ID_NAME, $this->state->getUser()->question_id);
        $message = $this->createMessage('Ivan Petrov');
        $response = TgCommand::process($message, $this->state);

        $this->assertEquals('Ivan Petrov', $this->state->getAnswerValue(Questions::ID_NAME));
        $this->assertEquals(Questions::ID_COUNTRY, $this->state->getUser()->question_id);

        // 5. Ответ на второй вопрос (ID_COUNTRY)
        $message = $this->createMessage('Moscow, Russia');
        $response = TgCommand::process($message, $this->state);

        $this->assertEquals('Moscow, Russia', $this->state->getAnswerValue(Questions::ID_COUNTRY));
        $this->assertEquals(Questions::ID_BIRTHDATE, $this->state->getUser()->question_id);

        // 6. Ответ на третий вопрос (ID_BIRTHDATE)
        $message = $this->createMessage('01.01.1990');
        $response = TgCommand::process($message, $this->state);

        $this->assertEquals('01.01.1990', $this->state->getAnswerValue(Questions::ID_BIRTHDATE));
        $this->assertEquals(Questions::ID_EMAIL, $this->state->getUser()->question_id);

        // 7. Ответ на четвертый вопрос (ID_EMAIL)
        $message = $this->createMessage('ivan@example.com');
        $response = TgCommand::process($message, $this->state);

        $this->assertEquals('ivan@example.com', $this->state->getAnswerValue(Questions::ID_EMAIL));
        $this->assertEquals(Questions::ID_COMPANY, $this->state->getUser()->question_id);

        // 8. Ответ на пятый вопрос (ID_COMPANY)
        $message = $this->createMessage('Senior Developer at Tech Corp');
        $response = TgCommand::process($message, $this->state);

        $this->assertEquals('Senior Developer at Tech Corp', $this->state->getAnswerValue(Questions::ID_COMPANY));
        $this->assertEquals(Questions::ID_COMPANY_WEBSITE, $this->state->getUser()->question_id);

        // 9. Пропуск шестого вопроса (ID_COMPANY_WEBSITE) - can_skip = true
        $message = $this->createMessage('/skip-empty');
        $response = TgCommand::process($message, $this->state);

        $this->assertEquals('', $this->state->getAnswerValue(Questions::ID_COMPANY_WEBSITE));
        $this->assertEquals(Questions::ID_NDA, $this->state->getUser()->question_id);

        // 10. Ответ на седьмой вопрос (ID_NDA)
        $message = $this->createMessage('Согласен');
        $response = TgCommand::process($message, $this->state);

        $this->assertEquals('Согласен', $this->state->getAnswerValue(Questions::ID_NDA));
        $this->assertEquals(Questions::FINISH, $this->state->getUser()->question_id);

        // 11. Проверка, что опрос завершен
        $this->assertTrue($this->state->isFinish());
        $this->assertEquals(TgResponse::FINISH, $this->getResponseType($response));

        // 12. Проверка, что все ответы сохранены в БД
        $answers = db()->select('SELECT * FROM ?_answer WHERE user_id = ?d', $this->state->getUser()->id);
        $this->assertCount(7, $answers); // 7 вопросов: 6 с ответами + 1 пропущен с пустым значением
    }

    /**
     * Тест: навигация назад по вопросам
     */
    public function testNavigationBackward (): void {
        $this->setupUserAtQuestion(Questions::ID_COMPANY);

        // Текущий вопрос - ID_COMPANY
        $this->assertEquals(Questions::ID_COMPANY, $this->state->getUser()->question_id);

        // Нажимаем "Назад"
        $message = $this->createMessage('/prev');
        TgCommand::process($message, $this->state);

        // Должны вернуться к ID_EMAIL
        $this->assertEquals(Questions::ID_EMAIL, $this->state->getUser()->question_id);

        // Еще раз "Назад"
        $message = $this->createMessage('/prev');
        TgCommand::process($message, $this->state);

        // Должны вернуться к ID_BIRTHDATE
        $this->assertEquals(Questions::ID_BIRTHDATE, $this->state->getUser()->question_id);
    }

    /**
     * Тест: пропуск вопроса с уже заполненным ответом
     */
    public function testSkipQuestionWithExistingAnswer (): void {
        $this->setupUserAtQuestion(Questions::ID_EMAIL);

        // Заполняем email
        $message = $this->createMessage('test@example.com');
        TgCommand::process($message, $this->state);

        $this->assertEquals(Questions::ID_COMPANY, $this->state->getUser()->question_id);

        // Возвращаемся назад
        $message = $this->createMessage('/prev');
        TgCommand::process($message, $this->state);

        $this->assertEquals(Questions::ID_EMAIL, $this->state->getUser()->question_id);
        $this->assertEquals('test@example.com', $this->state->getAnswerValue(Questions::ID_EMAIL));

        // Пропускаем вопрос (ответ уже есть)
        $message = $this->createMessage('/skip');
        TgCommand::process($message, $this->state);

        // Должны перейти к следующему вопросу
        $this->assertEquals(Questions::ID_COMPANY, $this->state->getUser()->question_id);
        // Ответ должен остаться прежним
        $this->assertEquals('test@example.com', $this->state->getAnswerValue(Questions::ID_EMAIL));
    }

    /**
     * Тест: перезапуск опроса
     */
    public function testSurveyReset (): void {
        // Заполняем несколько вопросов
        $this->setupUserAtQuestion(Questions::ID_COMPANY);
        $this->state->getUser()->setAnswer(Questions::ID_NAME, 'John Doe');
        $this->state->getUser()->setAnswer(Questions::ID_EMAIL, 'john@example.com');
        $this->state->getUser()->save();

        // Проверяем, что ответы есть
        $this->assertEquals('John Doe', $this->state->getAnswerValue(Questions::ID_NAME));
        $this->assertEquals('john@example.com', $this->state->getAnswerValue(Questions::ID_EMAIL));

        // Перезапускаем опрос
        $message = $this->createMessage('/begin ru');
        TgCommand::process($message, $this->state);

        // Должны вернуться к началу
        $this->assertEquals(Questions::FIRST_QUESTION_ID, $this->state->getUser()->question_id);

        // Старые ответы должны сохраниться (не удаляются)
        $this->assertEquals('John Doe', $this->state->getAnswerValue(Questions::ID_NAME));
    }

    /**
     * Тест: попытка ответить без выбора языка
     */
    public function testAnswerWithoutLanguage (): void {
        // Пользователь пытается ответить без выбора языка
        $message = $this->createMessage('Test answer');
        $response = TgCommand::process($message, $this->state);

        // Должен получить START ответ
        $this->assertEquals(TgResponse::START, $this->getResponseType($response));
        $this->assertNull($this->state->getLang());
    }

    /**
     * Тест: попытка ответить без указания телефона
     */
    public function testAnswerWithoutPhone (): void {
        // Выбираем язык
        $this->state->setLang('ru');

        // Пытаемся ответить на вопрос без указания телефона
        $message = $this->createMessage('Test answer');
        $response = TgCommand::process($message, $this->state);

        // Должен получить PHONE ответ
        $this->assertEquals(TgResponse::PHONE, $this->getResponseType($response));
    }

    /**
     * Тест: сохранение всех данных пользователя
     */
    public function testUserDataPersistence (): void {
        $chatId = $this->testChatId;

        // Создаем пользователя и заполняем данные
        $state1 = new TgBotState($chatId);
        $state1->setLang('en');
        $state1->setPhone('+1234567890');
        $state1->getUser()->setAnswer(Questions::ID_NAME, 'Alice Smith');
        $state1->getUser()->setAnswer(Questions::ID_EMAIL, 'alice@test.com');
        $state1->getUser()->save();

        // Создаем новый экземпляр состояния с тем же chat_id
        $state2 = new TgBotState($chatId);

        // Проверяем, что все данные сохранились
        $this->assertEquals('en', $state2->getLang());
        $this->assertEquals('+1234567890', $state2->getUser()->getPhone());
        $this->assertEquals('Alice Smith', $state2->getAnswerValue(Questions::ID_NAME));
        $this->assertEquals('alice@test.com', $state2->getAnswerValue(Questions::ID_EMAIL));
    }

    /**
     * Вспомогательный метод: настраивает пользователя на конкретном вопросе
     */
    private function setupUserAtQuestion (int $questionId): void {
        $this->state->setLang('ru');
        $this->state->setPhone('+79991234567');
        $this->state->getUser()->question_id = $questionId;
        $this->state->getUser()->save();
    }

    /**
     * Вспомогательный метод: получает тип ответа из объекта TgResponse
     */
    private function getResponseType (TgResponse $response): string {
        $class = get_class($response);
        $shortClass = substr($class, strrpos($class, '\\') + 1);

        return match($shortClass) {
            'Start' => TgResponse::START,
            'Phone' => TgResponse::PHONE,
            'Answer' => TgResponse::ANSWER,
            'Finish' => TgResponse::FINISH,
            'Reset' => TgResponse::RESET,
            'MyError' => TgResponse::ERROR,
            'Hint' => TgResponse::HINT,
            'Nda' => TgResponse::NDA,
            'NdaSend' => TgResponse::NDA_SEND,
            'NdaComplete' => TgResponse::NDA_COMPLETE,
            default => 'unknown'
        };
    }
}
