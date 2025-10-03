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
    protected $signature = 'generate:design-template {focus? : Comma-separated focus areas (e.g., user,store,product)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate backend code design template file and a customizable LLM prompt for specified focus areas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Prompt for the LLM instruction
        $llmPrompt = $this->ask('Please enter the LLM prompt for the design template (e.g., "Create migration and model for payment methods")');

        // Get the focus areas from the command argument or prompt if not provided
        $focusInput = $this->argument('focus') ?? $this->ask('Please enter the focus areas as a comma-separated list (e.g., user,store,product) or leave empty for all');

        // Split focus areas into an array, trim whitespace, and filter out empty entries
        $focusAreas = !empty($focusInput) ? array_filter(array_map('trim', explode(',', $focusInput))) : [];

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

        // Collect and append each file group's content based on focus areas
        foreach ($fileGroups as $groupName => $pattern) {
            $output .= "$groupName:";
            $files = glob($pattern);

            if (empty($files)) {
                $output .= "\n\n// No $groupName found";
                continue;
            }

            $filteredFiles = $files;
            if (!empty($focusAreas)) {
                // Filter files based on any of the focus keywords in filename or content
                $filteredFiles = array_filter($files, function ($file) use ($focusAreas) {
                    $filename = basename($file);
                    $content = File::get($file);
                    foreach ($focusAreas as $focus) {
                        if (stripos($filename, $focus) !== false || stripos($content, $focus) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            if (empty($filteredFiles)) {
                $focusList = implode(', ', $focusAreas);
                $output .= "\n\n// No $groupName found related to '$focusList'";
                continue;
            }

            foreach ($filteredFiles as $file) {
                $content = File::get($file);
                $output .= "\n\n// File: " . basename($file) . "\n$content";
            }
        }

        // Save to sample_design_template.txt in the project root
        $outputPath = base_path('sample_design_template.txt');
        File::put($outputPath, $output);

        $this->info("Design template generated successfully at: $outputPath");

        return 0;
    }
}
