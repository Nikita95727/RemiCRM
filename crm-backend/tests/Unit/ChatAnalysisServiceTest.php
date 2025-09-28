<?php

namespace Tests\Unit;

use App\Modules\Integration\Services\ChatAnalysisService;
use Tests\TestCase;

class ChatAnalysisServiceTest extends TestCase
{
    private ChatAnalysisService $chatAnalysisService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatAnalysisService = new ChatAnalysisService;
    }

    /**
     * Test crypto conversation analysis
     */
    public function test_analyzes_crypto_conversation_correctly(): void
    {
        $messages = [
            ['text' => 'Привет! Как дела с биткоином?', 'from' => 'user1'],
            ['text' => 'Биткоин растет! Купил еще эфириума', 'from' => 'user2'],
            ['text' => 'А я в альткоины вложился. DOGE к луне!', 'from' => 'user1'],
            ['text' => 'Крипта - это будущее! Blockchain технологии революция', 'from' => 'user2'],
        ];

        $result = $this->chatAnalysisService->analyzeChatMessages($messages);

        $this->assertEquals('crypto', $result);
    }

    /**
     * Test banking conversation analysis
     */
    public function test_analyzes_banking_conversation_correctly(): void
    {
        $messages = [
            ['text' => 'Приватбанк опять комиссию поднял', 'from' => 'user1'],
            ['text' => 'Да, я в Монобанк перешел. Там лучше условия', 'from' => 'user2'],
            ['text' => 'А кредит взять где лучше?', 'from' => 'user1'],
            ['text' => 'В Ощадбанке ставки хорошие, депозит там открыл', 'from' => 'user2'],
        ];

        $result = $this->chatAnalysisService->analyzeChatMessages($messages);

        $this->assertEquals('banking', $result);
    }

    /**
     * Test gaming conversation analysis
     */
    public function test_analyzes_gaming_conversation_correctly(): void
    {
        $messages = [
            ['text' => 'Играешь в новую игру?', 'from' => 'gamer1'],
            ['text' => 'Да! Pixel игра просто огонь', 'from' => 'gamer2'],
            ['text' => 'А я в Hamster Combat зависаю', 'from' => 'gamer1'],
            ['text' => 'Тоже классная игрушка! Геймплей затягивает', 'from' => 'gamer2'],
        ];

        $result = $this->chatAnalysisService->analyzeChatMessages($messages);

        $this->assertEquals('gaming', $result);
    }

    /**
     * Test social conversation analysis
     */
    public function test_analyzes_social_conversation_correctly(): void
    {
        $messages = [
            ['text' => 'Мама, как дела?', 'from' => 'son'],
            ['text' => 'Все хорошо, сынок. Бабушка передает привет', 'from' => 'mom'],
            ['text' => 'Передай ей, что скоро приеду в гости', 'from' => 'son'],
            ['text' => 'Дедушка тоже ждет. Будем всей семьей собираться', 'from' => 'mom'],
        ];

        $result = $this->chatAnalysisService->analyzeChatMessages($messages);

        $this->assertEquals('social', $result);
    }

    /**
     * Test bot conversation analysis
     */
    public function test_analyzes_bot_conversation_correctly(): void
    {
        $messages = [
            ['text' => '/start', 'from' => 'user'],
            ['text' => 'Привет! Я чат-бот помощник. Чем могу помочь?', 'from' => 'bot'],
            ['text' => 'Нужна информация', 'from' => 'user'],
            ['text' => 'Конечно! Я автоматизированный помощник, готов ответить на вопросы', 'from' => 'bot'],
        ];

        $result = $this->chatAnalysisService->analyzeChatMessages($messages);

        $this->assertEquals('bot', $result);
    }

    /**
     * Test empty messages return null
     */
    public function test_returns_null_for_empty_messages(): void
    {
        $result = $this->chatAnalysisService->analyzeChatMessages([]);

        $this->assertNull($result);
    }

    /**
     * Test messages with no clear category return null
     */
    public function test_returns_null_for_unclear_messages(): void
    {
        $messages = [
            ['text' => 'Привет', 'from' => 'user1'],
            ['text' => 'Привет', 'from' => 'user2'],
            ['text' => 'Как дела?', 'from' => 'user1'],
        ];

        $result = $this->chatAnalysisService->analyzeChatMessages($messages);

        $this->assertNull($result);
    }

    /**
     * Test different message text fields are handled correctly
     */
    public function test_handles_different_message_text_fields(): void
    {
        $messages = [
            ['body' => 'Биткоин растет!', 'from' => 'user1'],
            ['content' => 'Купил еще эфириума', 'from' => 'user2'],
            ['text_content' => 'Крипта - это будущее!', 'from' => 'user1'],
        ];

        $result = $this->chatAnalysisService->analyzeChatMessages($messages);

        $this->assertEquals('crypto', $result);
    }
}
