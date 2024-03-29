<?php
namespace Liquid\Builders;

use Liquid\Builders\BuilderInterface;
use Liquid\Builders\UnitBuilder;
use Liquid\Builders\ClosureBuilder;

use Liquid\Processors\BaseProcessor;
use ReflectionClass;

class ProcessorBuilder implements BuilderInterface
{
  protected $unitBuilder;
  protected $closureBuilder;

  protected $namespace = 'Liquid\Processors\\';

  protected $format = [
    'class' => 'string',
    'name' => 'string',
    'units' => 'array',
  ];

  public function __construct()
  {
    $this->unitBuilder = new UnitBuilder;
    $this->closureBuilder = new ClosureBuilder;
  }

  public function make(array $config)
  {
    $config = $this->_format($config);
    if (!$config) return;
    $class = new ReflectionClass($this->namespace . $config['class']);
    if (!$class->isSubclassOf(BaseProcessor::class)) return;
    if (!$class->isInstantiable()) return;
    $processor = $class->newInstance($config['name']);

    foreach ($config['units'] as $unitConfig) {
      if (!isset($unitConfig['class'])) continue;

      if ($unitConfig['class'] == 'Closure') {
        $unit = $this->closureBuilder->make($unitConfig);
        if ($unit) $processor->chain($unit);
      } else {
        $unit = $this->unitBuilder->make($unitConfig);
        if ($unit) $processor->stack($unit);
      }
    }
    return $processor;
  }

  protected function _format(array $config)
  {
    $output = [];
    foreach ($this->format as $key => $type) {
      if (!array_key_exists($key, $config)) return false;
      if (gettype($config[$key]) != $type) return false;
      $output[$key] = $config[$key];
    }
    return $output;
  }
}
