<?php

namespace Karma\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local;
use Karma\Finder;
use Karma\Hydrator;
use Karma\Configuration\InMemoryReader;
use Karma\Application;
use Karma\Configuration;
use Karma\Configuration\ValueFilter;

class Display extends Command
{
    use \Karma\Logging\OutputAware;
    
    const
        ENV_DEV = 'dev',
        NO_FILTERING = 'karma-nofiltering';
    
    protected function configure()
    {
        $this
            ->setName('display')
            ->setDescription('Display environment variable set')
            
            ->addOption('env',     null, InputOption::VALUE_REQUIRED, 'Target environment',           self::ENV_DEV)
            ->addOption('value',   null, InputOption::VALUE_REQUIRED, 'Display only variable with this value', self::NO_FILTERING)
            ->addOption('confDir', null, InputOption::VALUE_REQUIRED, 'Configuration root directory', Application::DEFAULT_CONF_DIRECTORY)
            ->addOption('master',  null, InputOption::VALUE_REQUIRED, 'Configuration master file',    Application::DEFAULT_MASTER_FILE)
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setOutput($output);
        
        $environment = $input->getOption('env'); 
        
        $this->output->writeln(sprintf(
            '<info>Display <comment>%s</comment> values</info>',
            $environment
        ));
        
        $app = new \Karma\Application();
        $app['configuration.path']       = $input->getOption('confDir');
        $app['configuration.masterFile'] = $input->getOption('master');
        
        $reader = $app['configuration'];
        $reader->setDefaultEnvironment($input->getOption('env'));
        
        if($input->hasOption('value'))
        {
            $filter = $input->getOption('value');
        }
        
        $this->displayValues($reader, $filter);
    }
    
    private function displayValues(Configuration $reader, $filter = self::NO_FILTERING)
    {
        $values = $reader->getAllValuesForEnvironment();
        
        if($filter !== self::NO_FILTERING)
        {
            $valueFilter = new ValueFilter($values);
            $values = $valueFilter->filter($filter);    
        }
        
        $variables = array_keys($values);
        sort($variables);
        
        foreach($variables as $variable)
        {
            $this->output->writeln(sprintf(
               '<fg=cyan>%s</fg=cyan> = %s',
                $variable,
                $this->formatValue($values[$variable])
            ));
        }
    }
    
    private function formatValue($value)
    {
        if($value === false)
        {
            $value = 'false';
        }
        elseif($value === true)
        {
            $value = 'true';
        }
        elseif($value === null)
        {
            $value = '<fg=white;options=bold>NULL</fg=white;options=bold>';
        }
        elseif($value === Configuration::NOT_FOUND)
        { 
            $value = '<error>NOT FOUND</error>';
        }
        
        return $value;
    }
}