<?php

namespace ui\form;

use pocketmine\Player;

class SimpleForm extends Form{

		/** @var array */

	protected $data = [

		"type" => "form",

		"title" => "",

		"content" => "",

		"buttons" => []

	];

	

	/** @var array */

	protected $buttons = [];

	

	/**

	 * @return string

	 */

	

	public function getData() : string{

		$data = $this->data;

		$data["buttons"] = array_values($this->buttons);

		

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

	 * @param string $desc

	 * @return self

	 */

	

	public function setDescription(string $desc) : self{

		$this->data["content"] = $desc;

		return $this;

	}

	

	/**

	 * @param string $name

	 * @param string $image

	 * @param string|null $identifier

	 */

	

	public function addButton(string $name, string $image = "") : void{

		$button = [

			"text" => $name

		];

		

		if($image !== ""){

			$button["image"] = [

				"type" => "url",

				"data" => $image

			];

		}

		

		$this->buttons[] = $button;

	}

}
