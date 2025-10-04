<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PythonAnalyzerTest extends TestCase
{
    private string $pythonScript;

    protected function setUp(): void
    {
        parent::setUp();

        $crmBackendRoot = dirname(dirname(__DIR__));
        $this->pythonScript = $crmBackendRoot.'/chat_analyzer.py';
    }

    /**
     * Test that Python script exists and is executable
     */
    public function test_python_script_exists_and_is_executable(): void
    {
        $this->assertFileExists($this->pythonScript, 'Python analyzer script should exist');
        $this->assertTrue(is_executable($this->pythonScript), 'Python script should be executable');
    }

    /**
     * Test Python script with crypto conversation
     */
    public function test_python_script_analyzes_crypto_correctly(): void
    {
        if (! file_exists($this->pythonScript)) {
            $this->markTestSkipped('Python script not found');
        }

        $messages = [
            [
                'text' => 'Привет! Как дела с биткоином? Биткоин растет! Купил еще эфириума. Крипта - это будущее!',
                'from' => 'user1',
            ],
        ];

        $result = $this->runPythonAnalyzer($messages);

        $this->assertArrayHasKey('category', $result);
        $this->assertEquals('crypto', $result['category']);
    }

    /**
     * Test Python script with banking conversation
     */
    public function test_python_script_analyzes_banking_correctly(): void
    {
        if (! file_exists($this->pythonScript)) {
            $this->markTestSkipped('Python script not found');
        }

        $messages = [
            [
                'text' => 'Приватбанк опять комиссию поднял. Да, я в Монобанк перешел. Там лучше условия. В Ощадбанке ставки хорошие, депозит там открыл',
                'from' => 'user1',
            ],
        ];

        $result = $this->runPythonAnalyzer($messages);

        $this->assertArrayHasKey('category', $result);
        $this->assertEquals('banking', $result['category']);
    }

    /**
     * Test Python script with gaming conversation
     */
    public function test_python_script_analyzes_gaming_correctly(): void
    {
        if (! file_exists($this->pythonScript)) {
            $this->markTestSkipped('Python script not found');
        }

        $messages = [
            [
                'text' => 'Играешь в новую игру? Да! Pixel игра просто огонь. А я в Hamster Combat зависаю. Геймплей затягивает',
                'from' => 'user1',
            ],
        ];

        $result = $this->runPythonAnalyzer($messages);

        $this->assertArrayHasKey('category', $result);
        $this->assertEquals('gaming', $result['category']);
    }

    /**
     * Test Python script with social conversation
     */
    public function test_python_script_analyzes_social_correctly(): void
    {
        if (! file_exists($this->pythonScript)) {
            $this->markTestSkipped('Python script not found');
        }

        $messages = [
            [
                'text' => 'Мама, как дела? Все хорошо, сынок. Бабушка передает привет. Будем всей семьей собираться',
                'from' => 'user1',
            ],
        ];

        $result = $this->runPythonAnalyzer($messages);

        $this->assertArrayHasKey('category', $result);
        $this->assertEquals('social', $result['category']);
    }

    /**
     * Test Python script with bot conversation
     */
    public function test_python_script_analyzes_bot_correctly(): void
    {
        if (! file_exists($this->pythonScript)) {
            $this->markTestSkipped('Python script not found');
        }

        $messages = [
            [
                'text' => '/start Привет! Я чат-бот помощник. Чем могу помочь? Я автоматизированный помощник, готов ответить на вопросы',
                'from' => 'bot',
            ],
        ];

        $result = $this->runPythonAnalyzer($messages);

        $this->assertArrayHasKey('category', $result);
        $this->assertEquals('bot', $result['category']);
    }

    /**
     * Test Python script with empty messages
     */
    public function test_python_script_handles_empty_messages(): void
    {
        if (! file_exists($this->pythonScript)) {
            $this->markTestSkipped('Python script not found');
        }

        $result = $this->runPythonAnalyzer([]);

        $this->assertArrayHasKey('category', $result);
        $this->assertNull($result['category']);
    }

    /**
     * Test Python script with unclear messages
     */
    public function test_python_script_handles_unclear_messages(): void
    {
        if (! file_exists($this->pythonScript)) {
            $this->markTestSkipped('Python script not found');
        }

        $messages = [
            ['text' => 'Привет', 'from' => 'user1'],
            ['text' => 'Как дела?', 'from' => 'user2'],
        ];

        $result = $this->runPythonAnalyzer($messages);

        $this->assertArrayHasKey('category', $result);

        $this->assertNull($result['category']);
    }

    /**
     * Test Python script performance
     */
    public function test_python_script_performance(): void
    {
        if (! file_exists($this->pythonScript)) {
            $this->markTestSkipped('Python script not found');
        }

        $messages = [
            ['text' => 'Биткоин растет! Купил еще эфириума. Крипта - это будущее!', 'from' => 'user1'],
        ];

        $startTime = microtime(true);
        $result = $this->runPythonAnalyzer($messages);
        $endTime = microtime(true);

        $duration = ($endTime - $startTime) * 1000;

        $this->assertLessThan(5000, $duration, 'Python script should complete within 5 seconds');
        $this->assertEquals('crypto', $result['category']);
    }

    /**
     * Helper method to run Python analyzer
     */
    private function runPythonAnalyzer(array $messages): array
    {

        $tempFile = tempnam(sys_get_temp_dir(), 'test_messages_');
        file_put_contents($tempFile, json_encode($messages, JSON_UNESCAPED_UNICODE));

        $crmBackendRoot = dirname($this->pythonScript);
        $command = sprintf(
            'cd %s && python3 %s --file %s 2>&1',
            escapeshellarg($crmBackendRoot),
            escapeshellarg($this->pythonScript),
            escapeshellarg($tempFile)
        );

        $output = shell_exec($command);

        @unlink($tempFile);

        $lines = explode("\n", trim($output));
        $jsonLines = [];
        $inJson = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '{')) {
                $inJson = true;
                $jsonLines[] = $line;
            } elseif ($inJson) {
                $jsonLines[] = $line;
                if (str_ends_with($line, '}')) {
                    break;
                }
            }
        }

        $this->assertNotEmpty($jsonLines, 'Should find JSON in Python output: '.$output);

        $jsonString = implode("\n", $jsonLines);
        $result = json_decode($jsonString, true);

        $this->assertIsArray($result, 'Python output should be valid JSON: '.$jsonString);

        return $result;
    }
}
