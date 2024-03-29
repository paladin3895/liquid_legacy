<?php
namespace Liquid\Builders;

use Liquid\Builders\BuilderInterface;
use Liquid\Units\ProcessUnitInterface;
use ReflectionClass;

class UnitBuilder implements BuilderInterface
{
  protected $format = [
    'class' => 'string',
  ];

  protected $namespace = 'Liquid\Units\\';

  public static function getFormats()
  {
    $units_path = dirname(__DIR__) . "/Units/*.php";
    foreach (glob($units_path) as $filename)
    {
      include_once $filename;
    }

    $formats = [];
    foreach (get_declared_classes() as $class) {
      if (!preg_match('#^Liquid\\\Units\\\(\w+)#', $class, $matches)) continue;
      if (!is_callable([$class, 'getFormat'])) continue;
      $formats[$matches[1]] = $class::getFormat();
    }
    return $formats;
  }

  public function make(array $config)
  {
    $config = $this->_format($config);
    if (!$config) return;
    $class = new ReflectionClass($this->namespace . $config['class']);
    if (!$class->implementsInterface(ProcessUnitInterface::class)) return;
    if (!$class->isInstantiable()) return;

    $unit = $class->newInstanceArgs($config['arguments'] ? : []);
    return $unit;
  }

  protected function _format(array $config)
  {
    if (!isset($config['class'])) return false;
    if (!is_callable([$this->namespace . $config['class'], 'getFormat'])) return false;
    $this->format['arguments'] = call_user_func(
      [$this->namespace . $config['class'], 'getFormat']);

    $output['class'] = $config['class'];
    foreach ($this->format['arguments'] as $key => $type) {
      if (!array_key_exists($key, $config)) return false;
      if (gettype($config[$key]) != $type) return false;
      $output['arguments'][$key] = $config[$key];
    }
    return $output;
  }
}
