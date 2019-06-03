<?php

namespace ui;

use pocketmine\Player;

use pocketmine\event\Listener;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket as MSPacket;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket as MRPacket;

use pocketmine\plugin\Plugin;

use ui\form\Form;

class UIManager implements Listener{

		/** @var Core */

	private $plugin;

	

	/** @var array */

	private $id_map = [];

	

	/**

	 * @param Plugin $core

	 */

	

	public function __construct(Plugin $plugin){

		$this->plugin = $plugin;

		

		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);

	}

	

	/**

	 * @return Plugin

	 */

	public function getPlugin() : Plugin{

		return $this->plugin;

	}

	

	/**

	 * @return int|null

	 */

	

	private function getUsableId() : ?int{

		$id = mt_rand(1, 36000);

		

		while($this->inRange($id)){

			$id = mt_rand(1, 36000);

		}

		

		return $id;

	}

	

	/**

	 * @param int $int

	 * @return bool

	 */

	

	private function inRange(int $int) : bool{

		return isset($this->id_map[$int]);

	}

	

	/**

	 * @param Player $player

	 * @param Form $form

	 * @return bool

	 */

	

	public function send(Player $player, Form $form) : bool{

		$id = $this->getUsableId();

		

		$pk = new MSPacket();

		$pk->formId = $id;

		$pk->formData = $form->setId($id)->getData();

		

		$this->id_map[$id] = $form;

		

		$player->dataPacket($pk);

		return true;

	}

	

	/**

	 * @param DataPacketReceiveEvent $event

	 * @priority LOWEST

	 * @ignoreCancelled false

	 */

	

	public function onDataReceive(DataPacketReceiveEvent $event) : void{

		$player = $event->getPlayer();

		$pk = $event->getPacket();

		

		if($pk instanceof MRPacket){

			if($this->inRange($pk->formId)){

				$form = $this->id_map[$pk->formId];

				$data = json_decode($pk->formData, true);

				

				$form->response($player, $data);

				$event->setCancelled();

				

				unset($this->id_map[$pk->formId]);

			}

		}

	}

}
