<?php

namespace Gun;

class Dup {
	public $dup;
	public $opt;

	public function __construct() {
		$this->dup = [ "s" => [], "to" => null ];
		$this->opt = [ "max" => 1000, "age" => (1000 * 9) ];
		$this->to = null;
	}

	public function check($id) {
		if(isset($this->dup["s"][$id])) {
			return $this->track($id);
		}

		return false;
	}

	public function track($id) {
		$this->dup["s"][$id] = abs((new \DateTime())->getTimestamp());

		if(!$this->dup["to"]) {
			$this->dup["to"] = true;
			foreach(array_keys($this->dup["s"]) as $id) {
				$time = $this->dup["s"][$id];
				if($this->opt["age"] > abs((new \DateTime())->getTimestamp() - $time)) {
					return;
				}
				unset($this->dup["s"][$id]);
			}
			$this->dup["to"] = null;
			sleep($this->opt["age"]);
		}

		return $id;
	}

	public function random() {
		return substr(md5(uniqid(mt_rand(), true)), 0, 3);
	}
}
