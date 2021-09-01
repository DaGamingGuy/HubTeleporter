<?php

declare(strict_types=1);

namespace DaGamingGuy\HubTP;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\event\PlayerJoinEevent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {

	private $lastExec = [];

	private $config = [];

	public function onEnable() {
		$this->saveDefaultConfig();
		$this->config = $this->getConfig()->getAll();

		if (is_numeric($this->config["time"])) {
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		} else {
			$this->getServer()->getLogger()->error("[HubTeleporter] Plugin disable");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		switch ($cmd->getName()) {
			case 'hub':
				if ($sender instanceof Player) {
					$name = $sender->getName();
					if ((isset($this->lastExec[$name])) && (($this->lastExec[$name] + 5 + $this->config["time"]) > (microtime(true)))) {
						$sender->sendMessage($this->config["msg_too_fast"]);
					} else {
						$this->getScheduler()->scheduleDelayedTask(new HubTask($this, $sender->getName()), (20*$this->config["time"]));
						$message = str_replace("{time}", $this->config["time"], $this->config["msg_being_teleported"]);
						$sender->sendMessage($message);
						$this->lastExec[$name] = microtime(true);
					}
					if (!isset($this->lastExec[$name])) {
						$this->lastExec[$name] = microtime(true);
					}
				} else {
					$sender->sendMessage("§cPlease run this command in-game!");
				}
			break;
		}
		return true;
	}

	public function onPlayerQuit(PlayerQuitEvent $evt) {
		unset($this->lastExec[$evt->getPlayer()->getName()]);
	}
}
