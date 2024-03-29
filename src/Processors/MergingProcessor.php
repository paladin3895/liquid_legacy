<?php
namespace Liquid\Processors;

use Liquid\Processors\BaseProcessor;
use Liquid\Units\ProcessUnitInterface;

class MergingProcessor extends BaseProcessor
{
	public static $alias = 'merging';

	public function process(array $data, array $result_input)
	{
    $input = [];
    foreach ($data as $record) {
      $input = array_merge($input, $record);
    }
		$output = [];
		$result_output = $result_input;
		foreach ($this->processUnits as $unit) {
			if ($unit instanceof ProcessUnitInterface) {
				$output = $unit->process($input);
			} elseif (is_callable($unit)) {
				$result_output = $unit($input, $result_input);
			}
		}

		$this->setOutput($output);
		$this->setResult($result_output);
	}
}
