<?php

# A plugin for PocketMine-MP that will show an UI for information and guides when players joins the server.
# Copyright (C) 2020-2022 Kygekraqmak, KygekTeam
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

use KygekTeam\KtpmplCfs\KtpmplCfs;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use Vecnavium\FormsUI\ModalForm;
use Vecnavium\FormsUI\SimpleForm;

class Main extends PluginBase implements Listener {

	private bool $isDev = false;

	public static string $mode;

	protected function onEnable(): void {
		$this->saveDefaultConfig();
		$ktpmplCfs = new KtpmplCfs($this);

		if ($this->isDev) {
			$ktpmplCfs->warnDevelopmentVersion();
		}

		$ktpmplCfs->checkUpdates();
		$ktpmplCfs->checkConfig("2.1");

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if (mb_stripos($this->getConfig()->get("Mode"), "simpleform") == true) {
			self::$mode = "SimpleForm";
			return;
		} elseif (mb_stripos($this->getConfig()->get("Mode"), "modalform") == true) {
			self::$mode = "ModalForm";
			return;
		}
		$this->ConfigFix();
	}

	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		if (!file_exists($this->getDataFolder() . "config.yml")) {
			$player->sendMessage(TextFormat::YELLOW . "[KygekJoinUI] " . TextFormat::RED . "Config file cannot be found, please restart the server!");
			return;
		}
		$this->ConfigFix();
		if (self::$mode == "SimpleForm") {
			if ($this->getConfig()->get("join-first-time", false)) {
				if (!$player->hasPlayedBefore()) $this->kygekSimpleJoinUI($player);
			} else $this->kygekSimpleJoinUI($player);
		}
		if (self::$mode == "ModalForm") {
			if ($this->getConfig()->get("join-first-time", false)) {
				if (!$player->hasPlayedBefore()) $this->kygekModalJoinUI($player);
			} else $this->kygekModalJoinUI($player);
		}
	}

	private function kygekSimpleJoinUI(Player $player) {
		$form = new SimpleForm(function (Player $player, int $data = null) {
			if ($data === null) {
				$this->dispatchCommandsOnClose($player);
				return true;
			}
			$buttons = $this->getConfig()->getNested("Buttons.SimpleForm");
			if (!empty($buttons[$data]["commands"])) {
				foreach ($buttons[$data]["commands"] as $command) {
					$playern = str_replace("{player}", $player->getName(), $command);
					$this->getServer()->dispatchCommand($this->commandMode($player), $playern);
				}
			}
		});
		$form->setTitle($this->replace($player, $this->getConfig()->get("title")));
		$form->setContent($this->replace($player, $this->getConfig()->get("content")));
		$buttons = $this->getConfig()->getNested("Buttons.SimpleForm");
		foreach ($buttons as $button) {
			if (empty($button["image"])) {
				$form->addButton($this->replace($player, $button["name"]));
			} else {
				$image = $button["image"];
				$url = str_starts_with($image, "http://") || str_starts_with($image, "https://");
				$form->addButton($this->replace($player, $button["name"]), $url ? 1 : 0, $image);
			}
		}
		$player->sendForm($form);
	}

	private function kygekModalJoinUI(Player $player) {
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
		$player->sendForm($form);
	}

	private function ConfigFix() {
		$this->getConfig()->reload();
		if (mb_stripos($this->getConfig()->get("Mode"), "simpleform") !== false) {
			self::$mode = "SimpleForm";
			return;
		} elseif (mb_stripos($this->getConfig()->get("Mode"), "modalform") !== false) {
			self::$mode = "ModalForm";
			return;
		}
		self::$mode = "SimpleForm";
		$this->getLogger()->error(TextFormat::RED . ("Incorrect mode have been set in the config.yml, changing the mode to SimpleForm..."));
		$content = file_get_contents($this->getDataFolder() . "config.yml");
		$yml = yaml_parse($content);
		$config = str_replace("Mode: " . $yml["Mode"], "Mode: SimpleForm", $content);
		unlink($this->getDataFolder() . "config.yml");
		$file = fopen($this->getDataFolder() . "config.yml", "w");
		fwrite($file, $config);
		fclose($file);
	}

	private function replace(Player $player, string $text): string {
		$from = ["{world}", "{player}", "{online}", "{max_online}", "{line}"];
		$to = [
			$player->getWorld()->getDisplayName(),
			$player->getName(),
			count($this->getServer()->getOnlinePlayers()),
			$this->getServer()->getMaxPlayers(),
			"\n"
		];
		return TextFormat::colorize(str_replace($from, $to, $text));
	}

	private function commandMode(Player $player): Player|ConsoleCommandSender {
		$server = $this->getServer();
		if (mb_stripos($this->getConfig()->get("command-mode"), "console") !== false) return new ConsoleCommandSender($server, $server->getLanguage());
		elseif (mb_stripos($this->getConfig()->get("command-mode"), "player") !== false) return $player;
		else {
			$this->getLogger()->error(TextFormat::RED . ("Incorrect command mode have been set in the config.yml, changing the command mode to console..."));
			$this->getConfig()->set("command-mode", "console");
			$this->getConfig()->save();
			return new ConsoleCommandSender($server, $server->getLanguage());
		}
	}

	private function dispatchCommandsOnClose($player) {
		foreach ($this->getConfig()->get("commands-on-close") as $command) {
			$this->getServer()->dispatchCommand($this->commandMode($player), str_replace("{player}", $player->getName(), $command));
		}
	}
}
