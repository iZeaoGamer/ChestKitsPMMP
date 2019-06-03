<?php

namespace kits;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\utils\TextFormat;

use pocketmine\nbt\tag\ListTag;

use kits\inventory\KitInventory;

class Kit{

		/** @var string */

	protected $name;

	

	/** @var string */

	protected $description;

	

	/** @var string */

	protected $perm;

	

	/** @var string */

	protected $activator;

	

	/** @var string */

	protected $icon;

	

	/** @var array */

	protected $items = [];

	

	/** @var int */

	protected $cooldown;

	

	/**

	 * @param array $data

	 */

	

	public function __construct(array $data){

		$this->name = TextFormat::colorize($data["name"]);

		$this->description = TextFormat::colorize($data["description"]);

		$this->perm = $data["perm"];

		$this->icon = $data["icon"] ?? "https://";

		$this->cooldown = $data["cooldown"];

		$this->items = $data["items"];

		

		$kd = $data["kit.item"];

		

		$activator = Item::fromString($kd["item"]);

		$activator->setCustomName(TextFormat::colorize($kd["name"] ?? $data["name"]));

		$activator->setLore(array_map(function($str){ return TextFormat::colorize($str); }, $kd["lore"] ?? []));

		

		$nbt = $activator->getNamedTag();

		$nbt->setTag(new ListTag("ench", []));

		

		$activator->setNamedTag($nbt);

		

		$this->activator = $activator;

	}

	

	/**

	 * @return string

	 */

	

	public function getName() : string{

		return $this->name;

	}

	

	/**

	 * @return string

	 */

	

	public function getDescription() : string{

		return $this->description;

	}

	

	/**

	 * @return string

	 */

	

	public function getIcon() : string{

		return $this->icon;

	}

	

	/**

	 * @param Player $player

	 * @return bool

	 */

	

	public function hasPermission(Player $player) : bool{

		return $player->hasPermission($this->perm);

	}

	

	/**

	 * @return int

	 */

	

	public function getCooldown() : int{

		return $this->cooldown;

	}

	

	/**

	 * @return Item

	 */

	

	public function getActivator() : Item{

		return clone $this->activator;

	}

	

	/**

	 * @return array

	 */

	

	public function getItems() : array{

		$items = [];

		

		foreach($this->items as $item){

			$items[] = Core::itemFromString($item);

		}

		

		return $items;

	}

	

	/**

	 * @param Player $player

	 */

	

	public function onActivate(Player $player) : void{

		$inv = new KitInventory($player->asPosition());

		$inv->addItem(...$this->getItems());

		$inv->setName($this->name);

		

		$player->addWindow($inv);

	}

}
