<?php

namespace Jiaxincui\QueryFilter\Console;

use Illuminate\Console\Command;
use Jiaxincui\QueryFilter\Console\BaseFilterGenerator;
use Jiaxincui\QueryFilter\Console\FileAlreadyExistsException;

class FilterMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:filter {name} {--base}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new filter class';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        if ($this->option('base')) {
            $generator = new BaseFilterGenerator($this->argument('name'), $this->options());
        } else {
            $generator = new FilterGenerator($this->argument('name'), $this->options());
        }

        try {
            $generator->handle();
            $this->info('Filter Created successfully!');
        } catch (FileAlreadyExistsException $e) {
            $this->info($e->getMessage() . ' is already exists!');
            return false;
        }
    }
}
