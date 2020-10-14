<?php

# A plugin for PocketMine-MP that will show an UI for information and guides when players joins the server.
# Copyright (C) 2020 Kygekraqmak
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <https://www.gnu.org/licenses/>.

namespace Kygekraqmak\KygekJoinUI;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\Config;

use jojoe77777\FormAPI;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\ModalForm;

class Main extends PluginBase implements Listener {

	public static $mode;
	private $cmdmode;

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->saveResource("config.yml");
		if (!$this->getConfig()->exists("config-version")) {
			$this->getLogger()->notice("Your configuration file is outdated, updating the config.yml...");
			$this->getLogger()->notice("The old configuration file can be found at config_old.yml");
			rename($this->getDataFolder()."config.yml", $this->getDataFolder()."config_old.yml");
			$this->saveResource("config.yml");
			return;
		}
		if (version_compare("1.2", $this->getConfig()->get("config-version"))) {
			$this->getLogger()->notice("Your configuration file is outdated, updating the config.yml...");
			$this->getLogger()->notice("The old configuration file can be found at config_old.yml");
			rename($this->getDataFolder()."config.yml", $this->getDataFolder()."config_old.yml");
			$this->saveResource("config.yml");
			return;
		}
		if (stripos($this->getConfig()->get("Mode"), "simpleform") !== false) {
			self::$mode = "SimpleForm";
			return;
		} elseif (stripos($this->getConfig()->get("Mode"), "modalform") !== false) {
			self::$mode = "ModalForm";
			return;
		}
		$this->ConfigFix();
	}

	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		if (!file_exists($this->getDataFolder()."config.yml")) {
			$player->sendMessage(TextFormat::YELLOW . "[KygekJoinUI] " . TextFormat::RED . "Config file cannot be found, please restart the server!");
			return;
		}
		$this->ConfigFix();
		if (self::$mode == "SimpleForm") {
			$this->kygekSimpleJoinUI($player);
		}
		if (self::$mode == "ModalForm") {
			$this->kygekModalJoinUI($player);
		}
	}

	private function kygekSimpleJoinUI($player) {
		$form = new SimpleForm(function (Player $player, int $data = null) {
			if ($data === null) {
				$this->dispatchCommandsOnClose($player);
				return true;
			}
			$Buttons = $this->getConfig()->getNested("Buttons.SimpleForm");
			$command = explode(":", $Buttons[$data]);
			if (count($command) <= 1) {
				return;
			}
			if ($command[1] == null) {
				return;
			}
			$first = true;
			foreach ($command as $cmd) {
				if ($first) {
					$first = false;
				} else {
					$playern = str_replace("{player}", $player->getName(), $cmd);
					$this->getServer()->dispatchCommand($this->commandMode($player), $playern);
				}
			}
		});
		$form->setTitle($this->replace($player, $this->getConfig()->get("title")));
		$form->setContent($this->replace($player, $this->getConfig()->get("content")));
		foreach ($this->getConfig()->getNested("Buttons.SimpleForm") as $b) {
			$text = explode(":", $b);
			$form->addButton($this->replace($player, $text[0]));
		}
		$form->sendToPlayer($player);
		return $form;
	}

	private function kygekModalJoinUI($player) {
		$form = new ModalForm(function (Player $player, bool $data = null) {
			if ($data === null) {
				$this->dispatchCommandsOnClose($player);
				return true;
			}
			switch ($data) {
				case true:
					$command = $this->getConfig()->getNested("Buttons.ModalForm.B1.command");
					if ($command !== null) {
						$this->getServer()->dispatchCommand($this->commandMode($player), str_replace("{player}", $player->getName(), $command));
					}
					break;
				case false:
					$command = $this->getConfig()->getNested("Buttons.ModalForm.B2.command");
					if ($command !== null) {
						$this->getServer()->dispatchCommand($this->commandMode($player), str_replace("{player}", $player->getName(), $command));
					}
					break;
			}
		});
		$form->setTitle($this->replace($player, $this->getConfig()->get("title")));
		$form->setContent($this->replace($player, $this->getConfig()->get("content")));
		$form->setButton1($this->replace($player, $this->getConfig()->getNested("Buttons.ModalForm.B1.name")));
		$form->setButton2($this->replace($player, $this->getConfig()->getNested("Buttons.ModalForm.B2.name")));
		$form->sendToPlayer($player);
		return $form;
	}

	private function ConfigFix() {
		$this->getConfig()->reload();
		if (stripos($this->getConfig()->get("Mode"), "simpleform") !== false) {
			self::$mode = "SimpleForm";
			return;
		} elseif (stripos($this->getConfig()->get("Mode"), "modalform") !== false) {
			self::$mode = "ModalForm";
			return;
		}
		self::$mode = "SimpleForm";
		$this->getLogger()->error(TextFormat::RED.("Incorrect mode have been set in the config.yml, changing the mode to SimpleForm..."));
		$content = file_get_contents($this->getDataFolder()."config.yml");
		$yml = yaml_parse($content);
		$config = str_replace("Mode: ".$yml["Mode"] ,"Mode: SimpleForm" ,$content);
		unlink($this->getDataFolder()."config.yml");
		$file = fopen($this->getDataFolder()."config.yml", "w");
		fwrite($file, $config);
		fclose($file);
	}

	private function replace(Player $player, string $text) : string {
		$from = ["{world}", "{player}", "{online}", "{max_online}", "{line}"];
		$to = [
			$player->getLevel()->getName(),
			$player->getName(),
			count($this->getServer()->getOnlinePlayers()),
			$this->getServer()->getMaxPlayers(),
			"\n"
		];
		return str_replace($from, $to, $text);
	}

	private function commandMode(Player $player) {
		if (stripos($this->getConfig()->get("command-mode"), "console") !== false) return new ConsoleCommandSender();
		elseif (stripos($this->getConfig()->get("command-mode"), "player") !== false) return $player;
		else {
			$this->getLogger()->error(TextFormat::RED.("Incorrect command mode have been set in the config.yml, changing the command mode to console..."));
			$this->getConfig()->set("command-mode", "console");
			$this->getConfig()->save();
			return new ConsoleCommandSender();
		}
	}

	private function dispatchCommandsOnClose($player) {
		foreach ($this->getConfig()->get("commands-on-close") as $command) {
			$this->getServer()->dispatchCommand($this->commandMode($player), str_replace("{player}", $player->getName(), $command));
		}
	}

}
