<?php

declare(strict_types=1);

namespace Grazulex\LaravelTurbomaker\Generators;

final class ActionGenerator extends BaseGenerator
{
    public function generate(array $context): array
    {
        if (! ($context['options']['generate_actions'] ?? false)) {
            return [];
        }

        $generated = [];

        // Generate basic CRUD actions
        $actions = [
            'Create'.$context['studly_name'].'Action',
            'Update'.$context['studly_name'].'Action',
            'Delete'.$context['studly_name'].'Action',
            'Get'.$context['studly_name'].'Action',
        ];

        foreach ($actions as $actionClass) {
            $actionPath = app_path("Actions/{$actionClass}.php");
            $actionType = $this->getActionType($actionClass);
            $stub = $this->getStub("action.{$actionType}");

            $actionContext = array_merge($context, [
                'action_class' => $actionClass,
                'action_type' => $actionType,
            ]);

            $content = $this->replaceTokens($stub, $actionContext);

            if ($this->writeFile($actionPath, $content, $context['options']['force'] ?? false)) {
                $generated[] = $actionPath;
            }
        }

        return $generated;
    }

    protected function getNamespace(array $context): string
    {
        return 'App\\Actions';
    }

    private function getActionType(string $actionClass): string
    {
        if (str_contains($actionClass, 'Create')) {
            return 'create';
        }
        if (str_contains($actionClass, 'Update')) {
            return 'update';
        }
        if (str_contains($actionClass, 'Delete')) {
            return 'delete';
        }
        if (str_contains($actionClass, 'Get')) {
            return 'get';
        }

        return 'base';
    }
}
