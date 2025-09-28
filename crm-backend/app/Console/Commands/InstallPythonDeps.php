<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallPythonDeps extends Command
{
    protected $signature = 'install:python-deps {--force : Force reinstallation}';

    protected $description = 'Install Python dependencies for multilingual chat analysis';

    public function handle(): void
    {
        $this->info('🐍 Installing Python Dependencies for Multilingual Analysis');
        $this->newLine();

        $force = $this->option('force');
        
        // Check if Python is available
        $pythonVersion = shell_exec('python3 --version 2>&1');
        if (!$pythonVersion) {
            $this->error('❌ Python 3 is not installed or not in PATH');
            $this->line('Please install Python 3.8+ to use multilingual analysis');
            return;
        }
        
        $this->info("✅ Found Python: " . trim($pythonVersion));
        
        // Check if pip is available
        $pipVersion = shell_exec('pip3 --version 2>&1');
        if (!$pipVersion) {
            $this->error('❌ pip3 is not installed or not in PATH');
            return;
        }
        
        $this->info("✅ Found pip: " . trim($pipVersion));
        $this->newLine();

        // Install requirements
        $requirementsFile = base_path('requirements.txt');
        
        if (!file_exists($requirementsFile)) {
            $this->error('❌ requirements.txt not found in project root');
            return;
        }

        $this->info('📦 Installing packages from requirements.txt...');
        
        $forceFlag = $force ? ' --force-reinstall' : '';
        $command = "pip3 install -r {$requirementsFile}{$forceFlag} 2>&1";
        
        $this->line("Running: {$command}");
        $this->newLine();
        
        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->info('✅ Python dependencies installed successfully!');
            $this->newLine();
            
            // Test imports
            $this->info('🧪 Testing package imports...');
            $testScript = base_path('multilingual_chat_analyzer.py');
            
            if (file_exists($testScript)) {
                $testCommand = "python3 -c \"
import sys
try:
    import nltk, sklearn, numpy, langdetect, sentence_transformers
    print('✅ All packages imported successfully')
    sys.exit(0)
except ImportError as e:
    print(f'❌ Import error: {e}')
    sys.exit(1)
\" 2>&1";
                
                $testOutput = shell_exec($testCommand);
                $this->line($testOutput);
                
                if (strpos($testOutput, '✅') !== false) {
                    $this->info('🎉 Multilingual analyzer is ready to use!');
                } else {
                    $this->warn('⚠️  Some packages may not be properly installed');
                }
            }
            
        } else {
            $this->error('❌ Failed to install Python dependencies');
            $this->newLine();
            $this->line('Error output:');
            foreach ($output as $line) {
                $this->line("  {$line}");
            }
            
            $this->newLine();
            $this->info('💡 Troubleshooting tips:');
            $this->line('  • Make sure you have Python 3.8+ installed');
            $this->line('  • Try running: pip3 install --upgrade pip');
            $this->line('  • On macOS, you might need: xcode-select --install');
            $this->line('  • Consider using a virtual environment');
        }
        
        $this->newLine();
    }
}
