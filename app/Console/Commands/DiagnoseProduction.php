<?php

namespace App\Console\Commands;

use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\ChatAnalysisService;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Console\Command;

class DiagnoseProduction extends Command
{
    protected $signature = 'diagnose:production';

    protected $description = 'Diagnose production issues with auto-tagging';

    public function handle(UnipileService $unipileService, ChatAnalysisService $chatAnalysisService): void
    {
        $this->info('=== 🔍 PRODUCTION DIAGNOSTICS ===');
        $this->newLine();

        // 1. Check Python environment
        $this->info('1️⃣ Checking Python environment...');
        $pythonVersion = shell_exec('python3 --version 2>&1');
        $this->line('Python version: ' . trim($pythonVersion));

        $crmBackendRoot = base_path();
        $venvPath = $crmBackendRoot . '/venv';
        $this->line('Venv path: ' . $venvPath);
        $this->line('Venv exists: ' . (file_exists($venvPath) ? '✅ Yes' : '❌ No'));

        if (file_exists($venvPath . '/bin/python')) {
            $venvPythonVersion = shell_exec($venvPath . '/bin/python --version 2>&1');
            $this->line('Venv Python: ' . trim($venvPythonVersion));
        }

        $this->newLine();

        // 2. Check multilingual analyzer
        $this->info('2️⃣ Checking multilingual analyzer...');
        $analyzerPath = $crmBackendRoot . '/multilingual_chat_analyzer.py';
        $this->line('Analyzer path: ' . $analyzerPath);
        $this->line('Analyzer exists: ' . (file_exists($analyzerPath) ? '✅ Yes' : '❌ No'));

        $this->newLine();

        // 3. Test venv activation
        $this->info('3️⃣ Testing venv activation...');
        
        // Method 1: source (sh)
        $testCommand1 = sprintf('cd %s && source venv/bin/activate && echo "SUCCESS" 2>&1', escapeshellarg($crmBackendRoot));
        $result1 = shell_exec($testCommand1);
        $this->line('Method 1 (source): ' . (str_contains($result1, 'SUCCESS') ? '✅ Works' : '❌ Failed'));
        if (!str_contains($result1, 'SUCCESS')) {
            $this->line('  Error: ' . trim($result1));
        }

        // Method 2: . (POSIX sh)
        $testCommand2 = sprintf('cd %s && . venv/bin/activate && echo "SUCCESS" 2>&1', escapeshellarg($crmBackendRoot));
        $result2 = shell_exec($testCommand2);
        $this->line('Method 2 (.): ' . (str_contains($result2, 'SUCCESS') ? '✅ Works' : '❌ Failed'));
        if (!str_contains($result2, 'SUCCESS')) {
            $this->line('  Error: ' . trim($result2));
        }

        // Method 3: bash -c
        $testCommand3 = sprintf('bash -c "cd %s && . venv/bin/activate && echo SUCCESS" 2>&1', escapeshellarg($crmBackendRoot));
        $result3 = shell_exec($testCommand3);
        $this->line('Method 3 (bash -c): ' . (str_contains($result3, 'SUCCESS') ? '✅ Works' : '❌ Failed'));
        if (!str_contains($result3, 'SUCCESS')) {
            $this->line('  Error: ' . trim($result3));
        }

        // Method 4: Direct python from venv
        $testCommand4 = sprintf('%s/venv/bin/python --version 2>&1', escapeshellarg($crmBackendRoot));
        $result4 = shell_exec($testCommand4);
        $this->line('Method 4 (direct venv/bin/python): ' . (str_contains($result4, 'Python') ? '✅ Works' : '❌ Failed'));
        if (str_contains($result4, 'Python')) {
            $this->line('  Version: ' . trim($result4));
        }

        $this->newLine();

        // 4. Check database state
        $this->info('4️⃣ Checking database state...');
        $totalContacts = Contact::count();
        $untaggedContacts = Contact::where(function($q) {
            $q->whereNull('tags')
              ->orWhere('tags', '[]')
              ->orWhereJsonLength('tags', 0);
        })->count();
        $taggedContacts = $totalContacts - $untaggedContacts;

        $this->line('Total contacts: ' . $totalContacts);
        $this->line('Untagged contacts: ' . $untaggedContacts);
        $this->line('Tagged contacts: ' . $taggedContacts);

        $accounts = IntegratedAccount::count();
        $this->line('Integrated accounts: ' . $accounts);

        $this->newLine();

        // 5. Test analyzer on sample data
        $this->info('5️⃣ Testing analyzer with sample messages...');
        
        $sampleMessages = [
            ['text' => 'Hello, how are you?', 'from' => 'user1', 'timestamp' => now()->toIso8601String()],
            ['text' => 'Bitcoin is going to the moon!', 'from' => 'user2', 'timestamp' => now()->toIso8601String()],
            ['text' => 'BTC price is amazing', 'from' => 'user1', 'timestamp' => now()->toIso8601String()],
        ];

        try {
            $tag = $chatAnalysisService->analyzeChatMessages($sampleMessages);
            $this->line('Sample analysis result: ' . ($tag ?? 'null'));
            if ($tag) {
                $this->line('✅ Analyzer is working correctly!');
            } else {
                $this->warn('⚠️ Analyzer returned null (expected "crypto" for these messages)');
            }
        } catch (\Exception $e) {
            $this->error('❌ Analyzer failed: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('=== 📊 DIAGNOSTICS COMPLETE ===');
        
        // Recommendations
        $this->newLine();
        $this->info('💡 RECOMMENDATIONS:');
        
        if (!str_contains($result3, 'SUCCESS')) {
            $this->warn('1. Virtual environment activation is failing');
            $this->line('   Fix: Use direct path to venv/bin/python instead of activating venv');
        }
        
        if (!file_exists($venvPath)) {
            $this->warn('2. Virtual environment not found');
            $this->line('   Fix: Run "python3 -m venv venv" and "venv/bin/pip install nltk scikit-learn numpy langdetect"');
        }
        
        if ($untaggedContacts > 0) {
            $this->warn('3. ' . $untaggedContacts . ' contacts without tags');
            $this->line('   Fix: Run "php artisan force:tagging" after fixing Python issues');
        }
    }
}

