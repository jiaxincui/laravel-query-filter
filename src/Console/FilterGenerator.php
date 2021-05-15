<?php


namespace Jiaxincui\QueryFilter\Console;

class FilterGenerator extends Generator
{

    public function __construct(string $name, array $options = [])
    {
        $name = $this->qualifyName($name);
        parent::__construct($name, $options);
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/filter.stub';
    }

    protected function qualifyName($name)
    {
        $name = trim($name, '\\/') . 'Filter';
        return 'Filters/' . $name;
    }

    protected function getReplaces()
    {
        $replaces = [];

        $replaces['className'] = $this->className;
        $replaces['namespace'] = $this->namespace;

        return $replaces;
    }
}
