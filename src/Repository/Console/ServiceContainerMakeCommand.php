<?php

namespace Repository\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ServiceContainerMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository-container {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository service container';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository service container';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . "/stubs/repository_service_container.stub";
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    public function getDefaultNamespace($rootNamespace)
    {
        return "{$rootNamespace}\Repositories\ServiceContainer";
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $model_class = $this->extractModelClass();
        $model_class = $this->parseModel($model_class);

        if (! class_exists($model_class)) {
            if ($this->confirm("A {$model_class} model does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => $model_class]);
            }
        }

        $replace = [
            '{{ namespacedModel }}' => $model_class,
            '{{namespacedModel}}'   => $model_class,
            '{{ model }}'           => class_basename($model_class),
            '{{model}}'             => class_basename($model_class)
        ];

        return str_replace(array_keys($replace), array_values($replace), parent::buildClass($name));
    }


    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new \InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (! Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace())) {
            $model = $rootNamespace . $model;
        }

        return $model;
    }

    /**
     * @return string
     */
    protected function extractModelClass(): string
    {
        $service_container_class = $this->getNameInput();
        return Str::before($service_container_class, 'ServiceContainer');
    }
}
