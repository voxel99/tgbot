<?php

namespace Tests\Unit;

use jam\app\tg\Questions;
use PHPUnit\Framework\TestCase;

class QuestionsOrderTest extends TestCase {
    private Questions $questions;

    protected function setUp (): void {
        parent::setUp();
        $this->questions = new Questions();
    }

    /**
     * Тест проверяет, что вопросы следуют в правильном порядке
     */
    public function testQuestionsFollowCorrectOrder (): void {
        // Проверяем порядок: NAME -> COUNTRY -> BIRTHDATE -> EMAIL -> COMPANY -> COMPANY_WEBSITE -> NDA
        $expectedOrder = [
            Questions::ID_NAME,
            Questions::ID_COUNTRY,
            Questions::ID_BIRTHDATE,
            Questions::ID_EMAIL,
            Questions::ID_COMPANY,
            Questions::ID_COMPANY_WEBSITE,
            Questions::ID_NDA
        ];

        $currentQuestionId = Questions::ID_NAME;
        $actualOrder = [$currentQuestionId];

        // Проходим по цепочке вопросов
        while ($nextQuestion = $this->questions->getNextQuestion($currentQuestionId)) {
            $currentQuestionId = $nextQuestion->id;
            $actualOrder[] = $currentQuestionId;
        }

        $this->assertEquals(
            $expectedOrder,
            $actualOrder,
            sprintf(
                "Порядок вопросов нарушен.\nОжидалось: %s\nПолучено: %s",
                implode(' -> ', $expectedOrder),
                implode(' -> ', $actualOrder)
            )
        );
    }

    /**
     * Тест проверяет конкретные переходы между вопросами
     */
    public function testSpecificQuestionTransitions (): void {
        // После ID_BIRTHDATE должен идти ID_EMAIL
        $nextAfterBirthdate = $this->questions->getNextQuestion(Questions::ID_BIRTHDATE);
        $this->assertNotNull($nextAfterBirthdate, 'После вопроса ID_BIRTHDATE должен быть следующий вопрос');
        $this->assertEquals(
            Questions::ID_EMAIL,
            $nextAfterBirthdate->id,
            sprintf(
                'После ID_BIRTHDATE (30) должен идти ID_EMAIL (40), получен: %d',
                $nextAfterBirthdate->id
            )
        );

        // После ID_EMAIL должен идти ID_COMPANY
        $nextAfterEmail = $this->questions->getNextQuestion(Questions::ID_EMAIL);
        $this->assertNotNull($nextAfterEmail, 'После вопроса ID_EMAIL должен быть следующий вопрос');
        $this->assertEquals(
            Questions::ID_COMPANY,
            $nextAfterEmail->id,
            sprintf(
                'После ID_EMAIL (40) должен идти ID_COMPANY (50), получен: %d',
                $nextAfterEmail->id
            )
        );

        // После ID_COMPANY должен идти ID_COMPANY_WEBSITE
        $nextAfterCompany = $this->questions->getNextQuestion(Questions::ID_COMPANY);
        $this->assertNotNull($nextAfterCompany, 'После вопроса ID_COMPANY должен быть следующий вопрос');
        $this->assertEquals(
            Questions::ID_COMPANY_WEBSITE,
            $nextAfterCompany->id,
            sprintf(
                'После ID_COMPANY (50) должен идти ID_COMPANY_WEBSITE (60), получен: %d',
                $nextAfterCompany->id
            )
        );

        // После ID_COMPANY_WEBSITE должен идти ID_NDA
        $nextAfterWebsite = $this->questions->getNextQuestion(Questions::ID_COMPANY_WEBSITE);
        $this->assertNotNull($nextAfterWebsite, 'После вопроса ID_COMPANY_WEBSITE должен быть следующий вопрос');
        $this->assertEquals(
            Questions::ID_NDA,
            $nextAfterWebsite->id,
            sprintf(
                'После ID_COMPANY_WEBSITE (60) должен идти ID_NDA (100), получен: %d',
                $nextAfterWebsite->id
            )
        );
    }

    /**
     * Тест проверяет обратный порядок (prevQuestion)
     */
    public function testPreviousQuestionOrder (): void {
        // Перед ID_EMAIL должен быть ID_BIRTHDATE
        $prevBeforeEmail = $this->questions->getPrevQuestion(Questions::ID_EMAIL);
        $this->assertNotNull($prevBeforeEmail, 'Перед вопросом ID_EMAIL должен быть предыдущий вопрос');
        $this->assertEquals(
            Questions::ID_BIRTHDATE,
            $prevBeforeEmail->id,
            sprintf(
                'Перед ID_EMAIL (40) должен быть ID_BIRTHDATE (30), получен: %d',
                $prevBeforeEmail->id
            )
        );

        // Перед ID_NDA должен быть ID_COMPANY_WEBSITE
        $prevBeforeNda = $this->questions->getPrevQuestion(Questions::ID_NDA);
        $this->assertNotNull($prevBeforeNda, 'Перед вопросом ID_NDA должен быть предыдущий вопрос');
        $this->assertEquals(
            Questions::ID_COMPANY_WEBSITE,
            $prevBeforeNda->id,
            sprintf(
                'Перед ID_NDA (100) должен быть ID_COMPANY_WEBSITE (60), получен: %d',
                $prevBeforeNda->id
            )
        );
    }

    /**
     * Тест проверяет, что после последнего вопроса нет следующего
     */
    public function testNoQuestionAfterLast (): void {
        $nextAfterNda = $this->questions->getNextQuestion(Questions::ID_NDA);
        $this->assertNull($nextAfterNda, 'После последнего вопроса ID_NDA не должно быть следующего вопроса');
    }

    /**
     * Тест проверяет, что перед первым вопросом нет предыдущего
     */
    public function testNoQuestionBeforeFirst (): void {
        $prevBeforeName = $this->questions->getPrevQuestion(Questions::ID_NAME);
        $this->assertNull($prevBeforeName, 'Перед первым вопросом ID_NAME не должно быть предыдущего вопроса');
    }

    /**
     * Тест проверяет количество вопросов
     */
    public function testQuestionsCount (): void {
        $this->assertEquals(7, $this->questions->getCountQuestions(), 'Должно быть 7 вопросов');
    }

    /**
     * Тест проверяет получение первого вопроса
     */
    public function testGetFirstQuestion (): void {
        $firstQuestion = $this->questions->getFirstQuestion();
        $this->assertNotNull($firstQuestion, 'Первый вопрос должен существовать');
        $this->assertEquals(Questions::ID_NAME, $firstQuestion['id'], 'Первый вопрос должен быть ID_NAME');
    }
}
