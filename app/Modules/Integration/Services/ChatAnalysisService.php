<?php

namespace App\Modules\Integration\Services;

use Illuminate\Support\Facades\Log;

class ChatAnalysisService
{
    /**
     * Keywords mapping for automatic tagging
     */
    private const TAG_KEYWORDS = [
        'crypto' => [
            'bitcoin', 'btc', 'ethereum', 'eth', 'cryptocurrency', 'crypto', 'blockchain',
            'mining', 'wallet', 'exchange', 'defi', 'nft', 'token', 'coin', 'trading',
            'binance', 'coinbase', 'metamask', 'usdt', 'usdc', 'dogecoin', 'litecoin',
            'криптовалюта', 'биткоин', 'эфириум', 'блокчейн', 'майнинг', 'кошелек',
            'биржа', 'торговля', 'токен', 'монета', 'hot wallet', 'cold wallet', 'pocketfi',
            'crypto bot', 'trading bot', 'dex', 'swap', 'yield', 'farming', 'staking',
            'hodl', 'moon', 'diamond hands', 'to the moon', 'satoshi', 'gwei', 'gas',
            'web3', 'dao', 'ico', 'ido', 'airdrop', 'whitelist', 'mint', 'burn',
            'bcoin', 'altcoin', 'shitcoin', 'memecoin', 'defi', 'cefi', 'p2p',
            'hash', 'hashrate', 'node', 'validator', 'consensus', 'proof of stake',
            'proof of work', 'cold storage', 'hardware wallet', 'seed phrase', 'private key',
            'public key', 'address', 'transaction', 'block', 'genesis', 'fork',
            'notcoin', 'not coin', 'pixel', 'hamster', 'tap', 'clicker', 'earn',
            'doge', 'dogecoin', 'шиба', 'shiba', 'вложился', 'инвестиции', 'курс', 'цена',
            'стоимость', 'рост', 'падение', 'волатильность', 'альткоины', 'децентрализация',
        ],
        'banking' => [
            'bank', 'banking', 'finance', 'financial', 'loan', 'credit', 'mortgage',
            'investment', 'insurance', 'payment', 'transfer', 'account', 'card',
            'visa', 'mastercard', 'paypal', 'swift', 'iban', 'deposit', 'withdrawal',
            'банк', 'банковский', 'финансы', 'кредит', 'займ', 'ипотека', 'инвестиции',
            'страхование', 'платеж', 'перевод', 'счет', 'карта', 'депозит', 'monobank',
            'privatbank', 'oschadbank', 'raiffeisenbank', 'ukrsibbank', 'alfa-bank',
            'приватбанк', 'ощадбанк', 'райффайзен', 'альфа-банк', 'монобанк',
        ],
        'advertising' => [
            'advertising', 'marketing', 'promotion', 'campaign', 'ad', 'ads', 'seo',
            'social media', 'facebook', 'instagram', 'google ads', 'youtube', 'tiktok',
            'influencer', 'brand', 'content', 'traffic', 'conversion', 'analytics',
            'реклама', 'маркетинг', 'продвижение', 'кампания', 'объявление', 'соцсети',
            'инфлюенсер', 'бренд', 'контент', 'трафик', 'конверсия', 'аналитика',
        ],
        'business' => [
            'business', 'company', 'startup', 'entrepreneur', 'partnership', 'deal',
            'contract', 'agreement', 'meeting', 'conference', 'presentation', 'pitch',
            'investor', 'funding', 'revenue', 'profit', 'sales', 'client', 'customer',
            'бизнес', 'компания', 'стартап', 'предприниматель', 'партнерство', 'сделка',
            'контракт', 'соглашение', 'встреча', 'конференция', 'презентация', 'инвестор',
            'финансирование', 'выручка', 'прибыль', 'продажи', 'клиент', 'заказчик',
        ],
        'technology' => [
            'technology', 'tech', 'software', 'development', 'programming', 'coding',
            'app', 'application', 'website', 'web', 'mobile', 'ai', 'artificial intelligence',
            'machine learning', 'data', 'database', 'api', 'cloud', 'server', 'hosting',
            'технологии', 'разработка', 'программирование', 'приложение', 'сайт',
            'мобильный', 'искусственный интеллект', 'данные', 'база данных', 'сервер',
        ],
        'real-estate' => [
            'real estate', 'property', 'apartment', 'house', 'rent', 'rental', 'lease',
            'buy', 'sell', 'purchase', 'mortgage', 'realtor', 'agent', 'broker',
            'недвижимость', 'квартира', 'дом', 'аренда', 'съем', 'покупка', 'продажа',
            'ипотека', 'риелтор', 'агент', 'брокер',
        ],
        'healthcare' => [
            'health', 'healthcare', 'medical', 'doctor', 'hospital', 'clinic', 'medicine',
            'treatment', 'therapy', 'diagnosis', 'patient', 'pharmacy', 'drug',
            'здоровье', 'медицина', 'врач', 'больница', 'клиника', 'лечение', 'терапия',
            'диагноз', 'пациент', 'аптека', 'лекарство',
        ],
        'education' => [
            'education', 'school', 'university', 'college', 'course', 'training', 'learning',
            'student', 'teacher', 'professor', 'degree', 'certificate', 'exam',
            'образование', 'школа', 'университет', 'колледж', 'курс', 'обучение',
            'студент', 'учитель', 'преподаватель', 'степень', 'сертификат', 'экзамен',
        ],
        'gaming' => [
            'game', 'gaming', 'gamer', 'esports', 'streamer', 'twitch', 'youtube gaming',
            'playstation', 'xbox', 'nintendo', 'steam', 'discord', 'tournament', 'clan',
            'guild', 'mmorpg', 'fps', 'moba', 'rpg', 'indie game', 'mobile game',
            'игра', 'гейминг', 'геймер', 'стример', 'турнир', 'клан', 'гильдия',
            'геймплей', 'gameplay', 'игрушка', 'поиграем', 'зависаю', 'затягивает',
            'огонь', 'новая игра', 'вечером поиграем', 'pixel', 'hamster', 'combat',
        ],
        'social' => [
            'friend', 'buddy', 'pal', 'colleague', 'coworker', 'neighbor', 'family',
            'brother', 'sister', 'mom', 'dad', 'uncle', 'aunt', 'cousin', 'girlfriend',
            'boyfriend', 'wife', 'husband', 'partner', 'bestie', 'mate',
            'друг', 'подруга', 'коллега', 'сосед', 'семья', 'брат', 'сестра', 'мама',
            'папа', 'дядя', 'тетя', 'двоюродный', 'жена', 'муж', 'партнер', 'лучший друг',
            'бабушка', 'дедушка', 'grandmother', 'grandfather', 'grandma', 'grandpa',
        ],
        'bot' => [
            'bot', 'chatbot', 'assistant', 'ai', 'artificial intelligence', 'automation',
            'script', 'telegram bot', 'discord bot', 'notification', 'alert', 'reminder',
            'бот', 'чатбот', 'помощник', 'ии', 'искусственный интеллект', 'автоматизация',
            'уведомление', 'напоминание', 'телеграм бот',
        ],
    ];

    /**
     * Analyze chat messages using Python multilingual semantic analyzer
     *
     * @param  array<array{text: string, from: string, timestamp: string}>  $messages
     * @return string|null Single most relevant tag or null if no match
     */
    public function analyzeChatMessages(array $messages): ?string
    {
        if (empty($messages)) {
            return null;
        }

        try {
            $result = $this->callPythonAnalyzer($messages);

            if ($result && isset($result['category'])) {
                Log::info('ChatAnalysisService: Successful analysis', [
                    'category' => $result['category'],
                    'language' => $result['detected_language'] ?? 'unknown',
                    'confidence' => $result['confidence'] ?? 0,
                    'message_count' => count($messages),
                ]);
                
                return $result['category'];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('ChatAnalysisService: Python analyzer failed', [
                'error' => $e->getMessage(),
                'message_count' => count($messages),
            ]);

            return null;
        }
    }

    /**
     * Get detailed analysis with language detection and confidence scores
     *
     * @param  array<array{text: string, from: string, timestamp: string}>  $messages
     * @return array<string, mixed>|null
     */
    public function getDetailedAnalysis(array $messages): ?array
    {
        if (empty($messages)) {
            return null;
        }

        try {
            $result = $this->callPythonAnalyzerWithDetails($messages);
            
            if ($result && !isset($result['error'])) {
                return $result;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('ChatAnalysisService: Detailed analysis failed', [
                'error' => $e->getMessage(),
                'message_count' => count($messages),
            ]);

            return null;
        }
    }

    /**
     * Analyze contact name and extract relevant tags (fallback when messages are not available)
     *
     * @return array<string>
     */
    public function analyzeContactInfo(string $contactName, ?string $notes = null): array
    {

        $combinedText = $contactName.' '.($notes ?? '');
        $tags = $this->extractTagsFromText($combinedText);

        return array_unique($tags);
    }

    /**
     * Extract and combine text from all messages
     *
     * @param  array<array{text: string, from: string, timestamp: string}>  $messages
     */
    private function extractTextFromMessages(array $messages): string
    {
        $texts = [];

        foreach ($messages as $message) {

            $text = $message['text'] ?? $message['body'] ?? $message['content'] ?? null;

            if ($text && is_string($text)) {
                $texts[] = $text;
            }

            if (isset($message['text_content']) && is_string($message['text_content'])) {
                $texts[] = $message['text_content'];
            }
        }

        $combinedText = implode(' ', $texts);

        return $combinedText;
    }

    /**
     * Call Python multilingual semantic analyzer
     */
    private function callPythonAnalyzer(array $messages): ?array
    {
        $crmBackendRoot = dirname(dirname(dirname(dirname(__DIR__))));
        
        // Try new multilingual analyzer first
        $multilingualScript = $crmBackendRoot.'/multilingual_chat_analyzer.py';
        $fallbackScript = $crmBackendRoot.'/chat_analyzer.py';
        
        $pythonScript = file_exists($multilingualScript) ? $multilingualScript : $fallbackScript;
        
        if (! file_exists($pythonScript)) {
            Log::warning('ChatAnalysisService: No Python analyzer script found', [
                'multilingual_path' => $multilingualScript,
                'fallback_path' => $fallbackScript
            ]);
            return null;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'chat_messages_');
        file_put_contents($tempFile, json_encode($messages, JSON_UNESCAPED_UNICODE));

        // Use virtual environment for production, direct python3 for other environments
        if (app()->environment('production')) {
            // Use direct path to venv python (more reliable than activating venv)
            $venvPython = $crmBackendRoot . '/venv/bin/python';
            if (file_exists($venvPython)) {
                // Set NLTK_DATA path for production (www-data user needs access to NLTK data)
                $command = sprintf(
                    'NLTK_DATA=/var/www/nltk_data %s %s --file %s 2>&1',
                    escapeshellarg($venvPython),
                    escapeshellarg($pythonScript),
                    escapeshellarg($tempFile)
                );
            } else {
                // Fallback to python3 if venv not found
                Log::warning('ChatAnalysisService: venv not found, falling back to python3', [
                    'expected_venv_path' => $venvPython
                ]);
                $command = sprintf(
                    'NLTK_DATA=/var/www/nltk_data python3 %s --file %s 2>&1',
                    escapeshellarg($pythonScript),
                    escapeshellarg($tempFile)
                );
            }
        } else {
            $command = sprintf(
                'cd %s && python3 %s --file %s 2>&1',
                escapeshellarg($crmBackendRoot),
                escapeshellarg($pythonScript),
                escapeshellarg($tempFile)
            );
        }

        $output = shell_exec($command);

        @unlink($tempFile);

        if ($output === null) {
            return null;
        }

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

        if (empty($jsonLines)) {
            return null;
        }

        $jsonString = implode("\n", $jsonLines);

        $result = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $result;
    }

    /**
     * Call Python analyzer with detailed output
     */
    private function callPythonAnalyzerWithDetails(array $messages): ?array
    {
        $crmBackendRoot = dirname(dirname(dirname(dirname(__DIR__))));
        
        // Try new multilingual analyzer first
        $multilingualScript = $crmBackendRoot.'/multilingual_chat_analyzer.py';
        $fallbackScript = $crmBackendRoot.'/chat_analyzer.py';
        
        $pythonScript = file_exists($multilingualScript) ? $multilingualScript : $fallbackScript;
        
        if (! file_exists($pythonScript)) {
            return null;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'chat_messages_');
        file_put_contents($tempFile, json_encode($messages, JSON_UNESCAPED_UNICODE));

        $command = sprintf(
            'cd %s && python3 %s --file %s --debug 2>&1',
            escapeshellarg($crmBackendRoot),
            escapeshellarg($pythonScript),
            escapeshellarg($tempFile)
        );

        $output = shell_exec($command);

        @unlink($tempFile);

        if ($output === null) {
            return null;
        }

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

        if (empty($jsonLines)) {
            return null;
        }

        $jsonString = implode("\n", $jsonLines);

        $result = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $result;
    }

    /**
     * Find the most relevant tag using semantic analysis with TF-IDF-like scoring
     */
    private function findMostRelevantTag(string $text): ?string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $textLength = strlen($text);
        $wordCount = str_word_count($text);

        if ($wordCount < 3) {
            return null;
        }

        $tagScores = [];

        foreach (self::TAG_KEYWORDS as $tag => $keywords) {
            $score = 0;
            $keywordMatches = 0;
            $totalKeywordLength = 0;

            foreach ($keywords as $keyword) {
                $keyword = mb_strtolower($keyword, 'UTF-8');
                $keywordLength = strlen($keyword);
                $totalKeywordLength += $keywordLength;

                $occurrences = $this->countKeywordOccurrences($text, $keyword);

                if ($occurrences > 0) {
                    $keywordMatches++;

                    $termFrequency = $occurrences / $wordCount;
                    $lengthBonus = $keywordLength / 20;
                    $positionBonus = $this->getPositionBonus($text, $keyword);

                    $keywordScore = ($termFrequency * 10) + $lengthBonus + $positionBonus;
                    $score += $keywordScore;
                }
            }

            if ($keywordMatches > 0) {
                $diversityBonus = min($keywordMatches / count($keywords), 0.5);
                $normalizedScore = ($score / count($keywords)) + $diversityBonus;
                $tagScores[$tag] = $normalizedScore;
            }
        }

        if (empty($tagScores)) {
            return null;
        }

        arsort($tagScores);
        $bestTag = array_key_first($tagScores);
        $bestScore = $tagScores[$bestTag];

        if ($bestScore < 0.1) {
            return null;
        }

        return $bestTag;
    }

    /**
     * Count keyword occurrences with word boundary awareness
     */
    private function countKeywordOccurrences(string $text, string $keyword): int
    {

        if (str_contains($keyword, ' ')) {
            return substr_count($text, $keyword);
        }

        $pattern = '/\b'.preg_quote($keyword, '/').'\b/u';
        preg_match_all($pattern, $text, $matches);

        return count($matches[0]);
    }

    /**
     * Calculate position bonus for keywords appearing early in text
     */
    private function getPositionBonus(string $text, string $keyword): float
    {
        $position = mb_strpos($text, $keyword);
        if ($position === false) {
            return 0;
        }

        $textLength = mb_strlen($text);
        if ($textLength === 0) {
            return 0;
        }

        $relativePosition = $position / $textLength;

        if ($relativePosition <= 0.2) {
            return 0.5;
        } elseif ($relativePosition <= 0.5) {
            return 0.2;
        } else {
            return 0;
        }
    }

    /**
     * Get confidence score for tags based on frequency
     *
     * @param  array<string>  $tags
     * @return array<string, float>
     */
    public function getTagConfidenceScores(string $text, array $tags): array
    {
        $scores = [];
        $text = mb_strtolower($text, 'UTF-8');

        foreach ($tags as $tag) {
            $keywordCount = 0;
            $keywords = self::TAG_KEYWORDS[$tag] ?? [];

            foreach ($keywords as $keyword) {
                $keyword = mb_strtolower($keyword, 'UTF-8');
                $keywordCount += substr_count($text, $keyword);
            }

            $textLength = strlen($text);
            $confidence = $textLength > 0 ? min(($keywordCount / ($textLength / 100)), 1.0) : 0.0;
            $scores[$tag] = round($confidence, 2);
        }

        return $scores;
    }

    /**
     * Get available tag categories
     *
     * @return array<string>
     */
    public function getAvailableTagCategories(): array
    {
        return array_keys(self::TAG_KEYWORDS);
    }

    /**
     * Get supported languages for multilingual analysis
     *
     * @return array<string, string>
     */
    public function getSupportedLanguages(): array
    {
        return [
            'en' => 'English',
            'zh' => 'Chinese', 
            'hi' => 'Hindi',
            'es' => 'Spanish',
            'fr' => 'French',
            'ar' => 'Arabic',
            'bn' => 'Bengali',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'ja' => 'Japanese',
            'uk' => 'Ukrainian',
            'be' => 'Belarusian'
        ];
    }

    /**
     * Check if multilingual analyzer is available
     */
    public function isMultilingualAnalyzerAvailable(): bool
    {
        $crmBackendRoot = dirname(dirname(dirname(dirname(__DIR__))));
        $multilingualScript = $crmBackendRoot.'/multilingual_chat_analyzer.py';
        
        return file_exists($multilingualScript);
    }
}
