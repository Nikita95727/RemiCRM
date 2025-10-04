<?php

namespace App\Console\Commands;

use App\Modules\Integration\Services\ChatAnalysisService;
use Illuminate\Console\Command;

class TestChatTagging extends Command
{
    protected $signature = 'test:chat-tagging';

    protected $description = 'Test the automatic chat tagging algorithm with sample messages';

    public function handle(ChatAnalysisService $chatAnalysisService): int
    {
        $this->info('🧠 Testing Chat Analysis and Tagging Algorithm');
        $this->newLine();

        $testCases = [
            [
                'name' => 'Crypto Discussion',
                'messages' => [
                    ['text' => 'Hey, have you seen the Bitcoin price today?', 'from' => 'user1', 'timestamp' => '2025-09-25T10:00:00Z'],
                    ['text' => 'Yeah, BTC is up 5%! I bought some Ethereum too', 'from' => 'user2', 'timestamp' => '2025-09-25T10:01:00Z'],
                    ['text' => 'Nice! I am thinking about DeFi investments. What do you think about Binance?', 'from' => 'user1', 'timestamp' => '2025-09-25T10:02:00Z'],
                    ['text' => 'Binance is solid. I also use MetaMask wallet for my tokens', 'from' => 'user2', 'timestamp' => '2025-09-25T10:03:00Z'],
                ],
            ],
            [
                'name' => 'Business Meeting',
                'messages' => [
                    ['text' => 'Hi! Can we schedule a business meeting next week?', 'from' => 'client', 'timestamp' => '2025-09-25T11:00:00Z'],
                    ['text' => 'Sure! What about the partnership proposal?', 'from' => 'me', 'timestamp' => '2025-09-25T11:01:00Z'],
                    ['text' => 'We need to discuss the contract terms and revenue sharing', 'from' => 'client', 'timestamp' => '2025-09-25T11:02:00Z'],
                    ['text' => 'Great! I will prepare a presentation for our investors', 'from' => 'me', 'timestamp' => '2025-09-25T11:03:00Z'],
                ],
            ],
            [
                'name' => 'Tech Discussion',
                'messages' => [
                    ['text' => 'Are you working on any new software development projects?', 'from' => 'dev', 'timestamp' => '2025-09-25T12:00:00Z'],
                    ['text' => 'Yes! Building a mobile app with AI features', 'from' => 'me', 'timestamp' => '2025-09-25T12:01:00Z'],
                    ['text' => 'Cool! What technology stack? React Native?', 'from' => 'dev', 'timestamp' => '2025-09-25T12:02:00Z'],
                    ['text' => 'Actually using Flutter. Also working with machine learning APIs and cloud hosting', 'from' => 'me', 'timestamp' => '2025-09-25T12:03:00Z'],
                ],
            ],
            [
                'name' => 'Real Estate',
                'messages' => [
                    ['text' => 'I am looking for a new apartment to rent', 'from' => 'client', 'timestamp' => '2025-09-25T13:00:00Z'],
                    ['text' => 'What is your budget? I can help you find good properties', 'from' => 'realtor', 'timestamp' => '2025-09-25T13:01:00Z'],
                    ['text' => 'Around $2000/month. Maybe a house would be better?', 'from' => 'client', 'timestamp' => '2025-09-25T13:02:00Z'],
                    ['text' => 'I have some great listings! Are you looking to buy or lease?', 'from' => 'realtor', 'timestamp' => '2025-09-25T13:03:00Z'],
                ],
            ],
            [
                'name' => 'Mixed Topics (Russian)',
                'messages' => [
                    ['text' => 'Привет! Как дела с банковским счетом?', 'from' => 'friend', 'timestamp' => '2025-09-25T14:00:00Z'],
                    ['text' => 'Все хорошо! Получил кредит на покупку квартиры', 'from' => 'me', 'timestamp' => '2025-09-25T14:01:00Z'],
                    ['text' => 'Отлично! А как с работой в IT? Слышал ты занимаешься разработкой', 'from' => 'friend', 'timestamp' => '2025-09-25T14:02:00Z'],
                    ['text' => 'Да, разрабатываю мобильные приложения. Сейчас изучаю блокчейн технологии', 'from' => 'me', 'timestamp' => '2025-09-25T14:03:00Z'],
                ],
            ],
        ];

        foreach ($testCases as $testCase) {
            $this->info("📝 Testing: {$testCase['name']}");

            $tags = $chatAnalysisService->analyzeChatMessages($testCase['messages']);

            $allText = implode(' ', array_column($testCase['messages'], 'text'));
            $confidenceScores = $chatAnalysisService->getTagConfidenceScores($allText, $tags);

            if (empty($tags)) {
                $this->warn('  ❌ No tags detected');
            } else {
                $this->info('  ✅ Detected tags:');
                foreach ($tags as $tag) {
                    $confidence = $confidenceScores[$tag] ?? 0;
                    $this->line("    🏷️  {$tag} (confidence: {$confidence})");
                }
            }

            $this->newLine();
        }

        $this->info('🔍 Testing Edge Cases:');
        $this->newLine();

        $edgeCases = [
            [
                'name' => 'Empty Messages',
                'messages' => [],
            ],
            [
                'name' => 'Single Word Messages',
                'messages' => [
                    ['text' => 'bitcoin', 'from' => 'user', 'timestamp' => '2025-09-25T15:00:00Z'],
                    ['text' => 'ok', 'from' => 'me', 'timestamp' => '2025-09-25T15:01:00Z'],
                ],
            ],
            [
                'name' => 'No Relevant Keywords',
                'messages' => [
                    ['text' => 'Hello how are you?', 'from' => 'user', 'timestamp' => '2025-09-25T16:00:00Z'],
                    ['text' => 'I am fine, thanks! Nice weather today', 'from' => 'me', 'timestamp' => '2025-09-25T16:01:00Z'],
                ],
            ],
        ];

        foreach ($edgeCases as $edgeCase) {
            $this->info("🧪 Edge Case: {$edgeCase['name']}");
            $tags = $chatAnalysisService->analyzeChatMessages($edgeCase['messages']);

            if (empty($tags)) {
                $this->info('  ✅ No tags (expected)');
            } else {
                $this->warn('  ⚠️  Unexpected tags: '.implode(', ', $tags));
            }
            $this->newLine();
        }

        $this->info('📋 Available Tag Categories:');
        $categories = $chatAnalysisService->getAvailableTagCategories();
        foreach ($categories as $category) {
            $this->line("  • {$category}");
        }

        $this->newLine();
        $this->info('🎉 Chat tagging algorithm test completed!');

        return self::SUCCESS;
    }
}


