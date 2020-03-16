<?php

namespace Repository\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name} {--m|model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * @var ServiceContainerMakeCommand
     */
    protected $query_make_command;

    /**
     * RepositoryMakeCommand constructor.
     * @param Filesystem $files
     * @param ServiceContainerMakeCommand $query_make_command
     */
    public function __construct(Filesystem $files, ServiceContainerMakeCommand $query_make_command)
    {
        parent::__construct($files);

        $this->query_make_command = $query_make_command;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . "/stubs/repository_facede.stub";
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return "{$rootNamespace}\Repositories";
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
        $repository_name = $this->extractRepositoryName();
        $replace = $this->buildServiceContainer($repository_name);

        return str_replace(array_keys($replace), array_values($replace), parent::buildClass($name));
    }

    /**
     * @return string
     */
    protected function extractRepositoryName(): string
    {
        $repository_name = $this->getNameInput();
        return Str::before($repository_name, 'Repository');
    }

    /**
     * @param string $model_class
     * @return array
     */
    protected function buildServiceContainer(string &$model_class): array
    {
        if ($model_option = $this->option('model')) {
            $model_class = $model_option;
        }

        $this->call('make:repository-container', ['name' => "{$model_class}ServiceContainer"]);

        return [
            '{{ model }}' => $model_class,
            '{{model}}'   => $model_class,
            '{{ serviceContainerNamespace }}' => $this->getServiceContainrtNamespace(),
            '{{ serviceContainerClass }}'     => "{$model_class}ServiceContainer"
        ];
    }

    /**
     * Get repository service container namespace.
     *
     * @return string
     */
    protected function getServiceContainrtNamespace(): string
    {
        $root_namespace = $this->rootNamespace();
        return $this->query_make_command->getDefaultNamespace(trim($root_namespace, '\\'));
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        // TODO: nesto nece da radi, mozda mora jos negde da se nesto podesava
        return [
            'model', 'm', InputOption::VALUE_OPTIONAL, 'Generate repository servise container class for provided model'
        ];
    }
}
