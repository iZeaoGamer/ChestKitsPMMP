<?php

namespace kits;

use pocketmine\Player;

use pocketmine\command\Command;

use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\player\PlayerItemHeldEvent;

use pocketmine\item\Item;

use pocketmine\item\enchantment\Enchantment;

use pocketmine\item\enchantment\EnchantmentInstance;

use pocketmine\utils\TextFormat;

use ui\UIManager;

use ui\form\SimpleForm;

class Core extends PluginBase implements Listener{

		public const TIME_FORMAT = "Y-n-d H:i:s";

	

	/** @var self */

	private static $instance;

	

	/** @var array */

	protected $kits = [];

	

	/** @var UIManager */

	protected $uim;

	

	/** @var array */

	protected $cooldown = [];

	

	/**

	 * @param void

	 */

	

	public function onLoad(){

		self::$instance = $this;

		

		$this->saveDefaultConfig();

		if(file_exists($this->getDataFolder()."cooldowns.dat")){

			$this->cooldown = yaml_parse_file($this->getDataFolder()."cooldowns.dat");

		}

		

		foreach($this->getConfig()->get("kits", []) as $key => $data){

			try{

				$kit = new Kit($data);

				$this->kits[] = $kit;

				

				$this->getLogger()->info("Loaded kit: ".$kit->getName());

			}catch(\Exception $e){

				$this->getLogger()->logException($e);

				$this->getLogger()->warning("Invalid format at key: ".$key);

			}

		}

	}

	

	/**

	 * @param void

	 */

	

	public function onEnable(){

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		

		$this->uim = new UIManager($this);

	}

	

	/**

	 * @param void

	 */

	

	public function onDisable(){

		$this->kits = [];

		

		file_put_contents($this->getDataFolder()."cooldowns.dat", yaml_emit($this->cooldown));

	}

	

	/**

	 * @return UIManager

	 */

	

	public function getUIManager() : UIManager{

		return $this->uim;

	}

	/**

	 * @return array

	 */

	

	public function getKits() : array{

		return $this->kits;

	}

	

	/**

	 * @return self

	 */

	

	public static function getInstance() : self{

		return self::$instance;

	}

	

	/**

	 * @param Player $player

	 * @param Kit $kit

	 * @return false|string

	 */

	

	public function hasCooldown(Player $player, Kit $kit){

		if(($expire = $this->cooldown[$player->getName()][TextFormat::clean($kit->getName())] ?? null) == null){

			return false;

		}

		

		return version_compare(date(self::TIME_FORMAT), $expire, "<") !== false ? $expire : false;

	}

	

	/**

	 * @param Player $player

	 * @param Kit $kit

	 * @param int $seconds

	 */

	

	public function setCooldown(Player $player, Kit $kit, int $seconds) : void{

		$this->cooldown[$player->getName()][TextFormat::clean($kit->getName())] = (string) date(self::TIME_FORMAT, time() + $seconds);

	}

	

	/**

	 * @param Player $player

	 * @param ?int $data

	 * @param SimpleForm $form

	 */

	

	public function handleKitSelection(Player $player, ?int $data, SimpleForm $form) : void{

		if($data === null){

			return;

		}

		

		$kit = $form->getExtraData()[$data];

		

		$cant = false;

		$msg = "";

		

		if(($cd = $this->hasCooldown($player, $kit)) !== false){

			$msg = "&cYour in cooldown till ".date("F, jS Y - H:i:s a", strtotime($cd));

			$cant = true;

			

		}

		if($kit->hasPermission($player) == false){

			$msg = "&cYou don't have permission to use this kit";

			$cant = true;

		}

		

		$form = new SimpleForm([$this, "handleKitEquipment"]);

		

		$form->setExtraData([$cant, $kit]);

		

		$form->setTitle("[Kit Information]");

		

		$form->setDescription(TextFormat::colorize(implode("\n", [

			"&7• Name: &r".$kit->getName(),

			"&r&7• Description: &r".$kit->getDescription(),

			"&r&7• Cooldown: &r".$kit->getCooldown()." seconds",

			"&r",

			"",

			$msg

		])));

		

		$form->addButton("Proceed");

		

		$this->getUIManager()->send($player, $form);

	}

	

	/**

	 * @param Player $player

	 * @param ?int $data

	 * @param SimpleForm $form

	 */

	

	public function handleKitEquipment(Player $player, ?int $data, SimpleForm $form) : void{

		if($data === null or $form->getExtraData()[0]){

			return;

		}

		

		$kit = $form->getExtraData()[1];

		

		$player->getInventory()->addItem($kit->getActivator());

		$this->setCooldown($player, $kit, $kit->getCooldown());

		

		$player->sendTip(TextFormat::GREEN."Added the kit to your inventory");

	}

	

	/**

	 * @param PlayerInteractEvent $event

	 * @ignoreCancelled true

	 */

	

	public function onInteract(PlayerInteractEvent $event) : void{

		$player = $event->getPlayer();

		$item = $event->getItem();

		

		foreach($this->kits as $kit){

			if($item->equals($kit->getActivator(), true, false)){

				$event->setCancelled();

				

				$item->pop();

				$player->getInventory()->setItemInHand($item);

				

				$kit->onActivate($player);

				break;

			}

		}

	}

	

	/**

	 * @param PlayerItemHeldEvent $event

	 * @ignoreCancelled true

	 */

	

	public function onItemHeld(PlayerItemHeldEvent $event) : void{

		$player = $event->getPlayer();

		

		foreach($player->getInventory()->getContents() as $key => $item){

			foreach($this->kits as $kit){

				$av = $kit->getActivator();

				

				if($item->equals($av, true, false) and $item->getName() !== $av->getName()){

					$av->setCount($item->getCount());

					

					$player->getInventory()->setItem($key, $av);

				}

			}

		}

	}

	

	/**

	 * @param string $str

	 * @return Item

	 */

	

	public static function itemFromString(string $str) : Item{

		if(trim($str) == ""){

			return Item::get(Item::AIR);

		}

		

		$i = explode(":", $str);

		

		try{

			$item = Item::fromString($i[0].":".$i[1]);

			$item->setCount((int) $i[2]);

			

			unset($i[0], $i[1], $i[2]);

			

			if(isset($i[3])){

				if(trim($i[3]) !== "" and in_array($i[3], ["default", "none", "d", "~"]) == false){

					$item->setCustomName(str_replace("\n", "\n", TextFormat::colorize($i[3])));

				}

				unset($i[3]);

			}

			

			$i = array_values($i); // No need technically

			

			foreach($i as $k => $d){

				if(($k % 2) == 0){

					if(is_numeric($d)){

						$type = Enchantment::getEnchantment((int) $d);

					}elseif(is_string($d)){

						$type = Enchantment::getEnchantmentByName($d);

					}

				}elseif($k % 2 == 1 and isset($type)){

					$item->addEnchantment(new EnchantmentInstance($type, (int) $d));

					$type = null;

				}

			}

			

			return $item;

		}catch(\Throwable $t){

			self::getInstance()->getLogger()->logException($t);

			self::getInstance()->getLogger()->info("Returned air item (null) to prevent compatibility issues");

			return Item::get(Item::AIR);

		}

	}

	

	/**

	 * @param CommandSender $sender

	 * @param Command $cmd

	 * @param string $label

	 * @param array $args

	 * @return bool

	 */

	

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{

		if(($args[0] ?? "") == "reload" and $sender->isOp()){

			$this->onDisable();

			$this->onLoad();

			

			$sender->sendMessage("Reloaded kits from configuration file.");

			

			return true;

		}

		

		if($sender instanceof Player){

			$form = new SimpleForm([$this, "handleKitSelection"]);

			

			$form->setTitle(TextFormat::colorize($this->getConfig()->get("prefix")));

			$form->setDescription("Choose your desired kit");

			

			$form->setExtraData($this->kits);

			

			foreach($this->kits as $kit){

				$form->addButton($kit->getName(), $kit->getIcon());

			}

			

			$this->getUIManager()->send($sender, $form);

		}

		

		return true;

	}

}
