<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class RequestGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        $generated = [];

        // Generate Store Request
        $storeRequestPath = app_path("Http/Requests/{$context['request_store_class']}.php");
        $storeStub = $this->getStub('request.store');
        $storeContent = $this->replaceTokens($storeStub, $context);

        if ($this->writeFile($storeRequestPath, $storeContent, $context['options']['force'] ?? false)) {
            $generated[] = $storeRequestPath;
        }

        // Generate Update Request
        $updateRequestPath = app_path("Http/Requests/{$context['request_update_class']}.php");
        $updateStub = $this->getStub('request.update');
        $updateContent = $this->replaceTokens($updateStub, $context);

        if ($this->writeFile($updateRequestPath, $updateContent, $context['options']['force'] ?? false)) {
            $generated[] = $updateRequestPath;
        }

        return $generated;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Http\\Requests';
    }

    protected function replaceTokens(string $content, array $context): string
    {
        // Get base tokens from parent
        $content = parent::replaceTokens($content, $context);

        // Add request-specific tokens
        $requestTokens = [
            '{{ schema_validation_rules }}' => $this->generateValidationRules($context),
        ];

        return str_replace(array_keys($requestTokens), array_values($requestTokens), $content);
    }

    private function generateValidationRules(array $context): string
    {
        if (isset($context['schema']) && $context['schema'] instanceof \Grazulex\LaravelTurbomaker\Schema\Schema) {
            return $context['schema']->generateValidationRulesString();
        }

        // Fallback to basic validation rules
        return "            'name' => ['required', 'string', 'max:255'],";
    }
}
