<?php

namespace Tests\Unit;

use jam\app\tg\state\Answer;
use jam\app\tg\state\User;
use jam\app\tg\state\UserLog;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase {
    /**
     * Тест проверяет работу метода init()
     */
    public function testModelInit (): void {
        $answer = new Answer([
            'id' => 1,
            'user_id' => 100,
            'question_id' => 10,
            'value' => 'Test Answer',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-02 00:00:00'
        ]);

        $this->assertEquals(1, $answer->id);
        $this->assertEquals(100, $answer->user_id);
        $this->assertEquals(10, $answer->question_id);
        $this->assertEquals('Test Answer', $answer->value);
        $this->assertEquals('2024-01-01 00:00:00', $answer->created_at);
        $this->assertEquals('2024-01-02 00:00:00', $answer->updated_at);
    }

    /**
     * Тест проверяет работу метода merge()
     */
    public function testModelMerge (): void {
        $user = new User([
            'id' => 1,
            'chat_id' => 123456,
            'lang' => 'en',
            'phone' => '+1234567890'
        ]);

        $this->assertEquals('en', $user->lang);
        $this->assertEquals('+1234567890', $user->phone);

        $user->merge([
            'lang' => 'ru',
            'question_id' => 20
        ]);

        $this->assertEquals('ru', $user->lang);
        $this->assertEquals(20, $user->question_id);
        $this->assertEquals('+1234567890', $user->phone); // Должен сохраниться
    }

    /**
     * Тест проверяет работу метода exists()
     */
    public function testModelExists (): void {
        $answer1 = new Answer();
        $this->assertFalse($answer1->exists());

        $answer2 = new Answer(['id' => 1]);
        $this->assertTrue($answer2->exists());
    }

    /**
     * Тест проверяет работу метода table()
     */
    public function testModelTable (): void {
        $user = new User();
        $this->assertEquals('user', $user->table());

        $answer = new Answer();
        $this->assertEquals('answer', $answer->table());

        $userLog = new UserLog();
        $this->assertEquals('user_log', $userLog->table());
    }

    /**
     * Тест проверяет, что свойства действительно публичные и доступны напрямую
     */
    public function testPublicProperties (): void {
        $answer = new Answer();
        $answer->user_id = 100;
        $answer->question_id = 20;
        $answer->value = 'Direct assignment';

        $this->assertEquals(100, $answer->user_id);
        $this->assertEquals(20, $answer->question_id);
        $this->assertEquals('Direct assignment', $answer->value);
    }

    /**
     * Тест проверяет, что несуществующие свойства не устанавливаются через init()
     */
    public function testInitIgnoresInvalidProperties (): void {
        $answer = new Answer([
            'id' => 1,
            'user_id' => 100,
            'invalid_property' => 'should be ignored'
        ]);

        $this->assertEquals(1, $answer->id);
        $this->assertEquals(100, $answer->user_id);
        $this->assertFalse(property_exists($answer, 'invalid_property'));
    }

    /**
     * Тест проверяет значения по умолчанию для User
     */
    public function testUserDefaultValues (): void {
        $user = new User();

        $this->assertNull($user->id);
        $this->assertNull($user->chat_id);
        $this->assertNull($user->lang);
        $this->assertNull($user->question_id);
        $this->assertNull($user->phone);
        $this->assertNull($user->referrer);
        $this->assertNull($user->envelope_id);
        $this->assertEquals(0, $user->envelope_sign);
        $this->assertEquals(0, $user->envelope_send);
        $this->assertNull($user->pipedrive_id);
    }
}
