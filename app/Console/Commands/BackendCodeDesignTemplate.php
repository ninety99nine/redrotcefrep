<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackendCodeDesignTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:design-template';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate backend code design template file and a customizable LLM prompt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Prompt for the LLM instruction
        $llmPrompt = $this->ask('Please enter the LLM prompt for the design template (e.g., "Create migration and model for payment methods")');

        // Define the file paths to collect
        $fileGroups = [
            'Migrations' => base_path('database/migrations/*.php'),
            'Routes' => base_path('routes/api/*.php'),
            'Controllers' => app_path('Http/Controllers/*.php'),
            'Middlewares' => app_path('Http/Middleware/*.php'),
            'Casts' => app_path('Http/Casts/*.php'),
            'Enums' => app_path('Http/Enums/*.php'),
            'Requests' => app_path('Http/Requests/*/*.php'),
            'Resources' => app_path('Http/Resources/*.php'),
            'Models' => app_path('Models/*.php'),
            'Observers' => app_path('Observers/*.php'),
            'Policies' => app_path('Policies/*.php'),
            'Providers' => app_path('Providers/*.php'),
            'Services' => app_path('Services/*.php'),
            'Enums' => app_path('Enums/*.php'),
        ];

        // Initialize the output content
        $output = "This is our backend application code. Strictly follow the design syntax, writing style, layout, order of methods, commenting style, naming conventions, and overall code structure to write new code. Ensure that any new code adheres to the following:\n" .
                  "- Use UUIDs for primary keys as shown in the migrations.\n" .
                  "- Follow the exact PHP Laravel coding standards, including PSR-12.\n" .
                  "- Maintain consistent use of enums, casts, and relationships as in the models.\n" .
                  "- Use the same validation rules and request structure as in the request classes.\n" .
                  "- Match the service and controller patterns, including dependency injection and resource responses.\n" .
                  "- Preserve the authorization and policy structure as shown in the policies.\n" .
                  "- Use consistent docblocks and inline comments as seen in the existing code.\n" .
                  "The prompt for the new code is: [$llmPrompt]\n\n";

        // Collect and append each file group's content
        foreach ($fileGroups as $groupName => $pattern) {
            $output .= "$groupName:";
            $files = glob($pattern);

            if (empty($files)) {
                $output .= "\n\n// No $groupName found";
                continue;
            }

            foreach ($files as $file) {
                $content = File::get($file);
                $output .= "\n\n$content";
            }
        }

        // Save to sample-design-template.txt in the project root
        $outputPath = base_path('sample-design-template.txt');
        File::put($outputPath, $output);

        $this->info("Design template generated successfully at: $outputPath");

        return 0;
    }
}
