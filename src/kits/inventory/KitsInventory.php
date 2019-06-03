<?php

namespace kits\inventory;

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\inventory\BaseInventory;

use pocketmine\level\Position;

use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;

use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

use pocketmine\network\mcpe\protocol\ContainerClosePacket;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\nbt\NetworkLittleEndianNBTStream;

use kits\Kit;

class KitsInventory extends BaseInventory{

		/** @var Position */

	protected $holder;

	

	/**

	 * @param Position $holder

	 */

	

	public function __construct(Position $holder){

		parent::__construct([], null, "");

		

		$block = $holder->getLevel()->getBlock($holder);

		

		$holder->setComponents($block->x, $block->y + 2, $block->z);

		

		$this->holder = $holder;

	}

	

	/**

	 * @return int

	 */

	

	public function getDefaultSize() : int{

		return 27;

	}

	/**

	 * @return int

	 */

	

	public function getNetworkType() : int{

		return 0;

	}

	

	/**

	 * @return string

	 */

	

	public function getName() : string{

		return $this->name;

	}

	

	/**

	 * @param string $name

	 */

	

	public function setName(string $name) : void{

		$this->name = $this->title = $name;

	}

	

	/**

	 * @return Position

	 */

	

	public function getHolder() : Position{

		return $this->holder;

	}

	

	/**

	 * @param Player $player

	 */

	

	public function onOpen(Player $player) : void{

		parent::onOpen($player);

		

		$this->openInterface($player);

		$this->sendContents($player);

	}

	

	/**

	 * @param Player $player

	 */

	

	public function onClose(Player $player) : void{

		parent::onClose($player);

		

		foreach($this->getContents() as $k => $i){

			$d = $player->getInventory()->addItem($i);

			

			foreach($d as $dr){

				$player->getLevel()->dropItem($player->asVector3(), $dr);

			}

			

			$this->clear($k);

		}

		

		$this->closeInterface($player);

	}

	

	/**

	 * @param Player $player

	 */

	

	public function sendTile(Player $player) : void{

		$holder = $this->getHolder();

		

		$tag = new CompoundTag("", []);

		$tag->setString("id", "Chest");

		$tag->setString("CustomName", $this->getName());

		$stream = new NetworkLittleEndianNBTStream();

		

		$pk = new BlockEntityDataPacket();

		$pk->x = (int) $holder->x;

		$pk->y = (int) $holder->y;

		$pk->z = (int) $holder->z;

		$pk->namedtag = $stream->write($tag);

		

		$player->dataPacket($pk);

	}

	

	/**

	 * @param Player $player

	 */

	

	public function openInterface(Player $player) : void{

		$holder = $this->getHolder();

		

		$block = Block::get(Block::CHEST);

		$block->position($holder);

		

		$player->getLevel()->sendBlocks([$player], [$block]);

		$this->sendTile($player);

		

		$pk = new ContainerOpenPacket();

		$pk->type = $this->getNetworkType();

		$pk->windowId = $player->getWindowId($this);

		$pk->x = (int) $holder->x;

		$pk->y = (int) $holder->y;

		$pk->z = (int) $holder->z;

		

		$player->dataPacket($pk);

		

		$this->sendContents($player);

	}

	

	/**

	 * @param Player $player

	 */

	

	public function closeInterface(Player $player) : void{

		$holder = $this->getHolder();

		

		$pk = new ContainerClosePacket();

		$pk->windowId = $player->getWindowId($this);

		$player->dataPacket($pk);

		

		$player->getLevel()->sendBlocks([$player], [$player->getLevel()->getBlock($holder)]);

	}

}
