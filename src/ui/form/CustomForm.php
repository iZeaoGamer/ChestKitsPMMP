<?php

namespace ui\form;

use pocketmine\Player;

class CustomForm extends Form{

		/** @var array */

	protected $data = [

		"type" => "custom_form",

		"title" => "",

		"content" => []

	];

	

	/** @var array */

	protected $elements = [];

	

	/**

	 * @return string

	 */

	

	public function getData() : string{

		$data = $this->data;

		$data["content"] = $this->elements;

		

		return json_encode($data);

	}

	

	/**

	 * @param string $title

	 * @return self

	 */

	

	public function setTitle(string $title) : self{

		$this->data["title"] = $title;

		return $this;

	}

	

	/**

	 * @param string $label

	 */

	

	public function addLabel(string $label) : void{

		$push = ["type" => "label", "text" => $label];

		

		$this->elements[] = $push;

	}

	

	/**

	 * @param string $name

	 * @param bool $default

	 */

	

	public function addToggle(string $name, bool $default = false) : void{

		$push = ["type" => "toggle", "text" => $name, "default" => $default];

		

		$this->elements[] = $push;

	}

	

	/**

	 * @param string $name

	 * @param array $options

	 * @param int $default

	 */

	

	public function addDropDown(string $name, array $options = [], int $default = 0) : void{

		$push = ["type" => "dropdown", "text" => $name, "options" => $options, "default" => $default];

		

		$this->elements[] = $push;

	}

	

	/**

	 * @param string $name

	 * @param string $placeholder

	 * @param string $default

	 */

	

	public function addInput(string $name, string $placeholder = "", string $default = "") : void{

		$push = ["type" => "input", "text" => $name, "placeholder" => $placeholder, "default" => $default];

		

		$this->elements[] = $push;

	}

	

	/**

	 * @param string $name

	 * @param int $min

	 * @param int $max

	 * @param int $step

	 * @param int $default

	 */

	

	public function addSlider(string $name, int $min = 0, int $max = 0, int $step = 0, int $default = 0) : void{

		$push = ["type" => "slider", "text" => $name, "min" => $min, "max" => $max, "step" => $step, "default" => $default];

		

		$this->elements[] = $push;

	}

	

	/**

	 * @param string $name

	 * @param array $steps

	 * @param int $default

	 */

	

	public function addStepSlider(string $name, array $steps = [], int $default = 0) : void{

		$push = ["type" => "step_slider", "text" => $name, "steps" => $steps, "default" => $default];

		

		$this->elements[] = $push;

	}

}
