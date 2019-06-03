<?php

namespace ui\form;

use pocketmine\Player;

abstract class Form{

		/** @var array */

	protected $data = [];

	

	/** @var callable */

	protected $callable = null;

	

	/** @var int */

	private $id;

	

	/** @var mixed */

	private $extraData = null;

	

	/**

	 * @param callable|null $callable

	 */

	

	public function __construct(?callable $callable = null){

		$this->callable = $callable;

	}

	

	/**

	 * @return string

	 */

	

	abstract public function getData() : string;

	

	/**

	 * @param int $id

	 * @return self

	 */

	

	public function setId(int $id) : self{

		if(isset($this->id) == false){

			$this->id = $id;

		}

		return $this;

	}

	

	/**

	 * @return int|null

	 */

	

	public function getId() : ?int{

		return $this->id;

	}

	

	/**

	 * @param $data

	 * @return self

	 */

	

	public function setExtraData($data) : self{

		$this->extraData = $data;

		return $this;

	}

	

	/**

	 * @return mixed

	 */

	

	public function getExtraData(){

		return $this->extraData;

	}

	

	/**

	 * @param Player $player

	 * @param $data

	 */

	public function response(Player $player, $data) : void{

		$call = $this->callable;

		if($call !== null){

			$call($player, $data, $this);

		}

	}

}
