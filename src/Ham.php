<?php

namespace Gun;

class Ham {
	public static function ham(
		$machineState,
		$incomingState,
		$currentState,
		$incomingValue,
		$currentValue) {

		if($machineState < $incomingState) {
			return [ "defer" => true ];
		}

		if($incomingState < $currentState) {
			return [ "historical" => true ];
		}

		if($currentState < $incomingState) {
			return [ "coverage" => true, "incoming" => true ];
		}

		if($incomingState === $currentState) {
			$incomingValue = json_encode($incomingValue) ?? "";
			$currentValue = json_encode($currentValue) ?? "";

			if($incomingValue === $currentValue) {
				return [ "state" => true ];
			}

			if($incomingValue < $currentValue) {
				return [ "coverage" => true, "current" => true ];
			}

			if($currentValue < $incomingValue) {
				return [ "coverage" => true, "incoming" => true ];
			}
		}

		return [
			"err" => "Invalid CRDT Data: ". $incomingValue ." to ". $currentValue ." at ". $incomingState ." to ". $currentState ."!"
		];
	}

	public static function mix($change, &$graph) {
		$machine = abs((new \DateTime())->getTimestamp());
		$diff = null;

		foreach(array_keys($change) as $soul) {
			$node = $change[$soul];

			foreach(array_keys($node) as $key) {
				$val = $node[$key];
				// var_dump($val);
				if('_' == $key) { echo "_ === $key \n"; continue; }

				$state = $node['_']['>'][$key];
				$was = ($graph[$soul] ?? ['_' => ['>' => []]])['_']['>'][$key];
				$known = ($graph[$soul] ?? [])[$key];
				$ham = self::ham($machine, $state, $was, $val, $known);
				var_dump(["ham" => $ham]);
				if(!isset($ham["incoming"])) {
					if(isset($ham["defer"])) {
						// No need to implement this
						var_dump(["Defer", $key, $val]);
					}

					return;
				}

				if(!$diff) {
					$diff = [];
				}
				$diff[$soul] = $diff[$soul] ?? ['_' => ['#' => $soul, '>' => []]];
				$graph[$soul] = $graph[$soul] ?? ['_' => ['#' => $soul, '>' => []]];
				$graph[$soul][$key] = $diff[$soul][$key];
				$diff[$soul][$key] = $val;
				$graph[$soul]['_']['>'][$key] = $diff[$soul]['_']['>'][$key];
				$diff[$soul]['_']['>'][$key] = $state;
			}
		}

		return $diff;
	}

}
