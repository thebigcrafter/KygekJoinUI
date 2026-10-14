<?php

/*
 * A plugin for PocketMine-MP that will show an UI for information and guides when players joins the server.
 * Copyright (C) 2020-2023 Kygekraqmak, KygekTeam
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Kygekraqmak\KygekJoinUI;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use thebigcrafter\Hydrogen\HConfig;
use thebigcrafter\Hydrogen\Hydrogen;
use Vecnavium\FormsUI\ModalForm;
use Vecnavium\FormsUI\SimpleForm;
use function array_filter;
use function array_values;
use function count;
use function fclose;
use function file_exists;
use function file_get_contents;
use function fopen;
use function fwrite;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_resource;
use function is_string;
use function mb_stripos;
use function str_replace;
use function str_starts_with;
use function unlink;
use function yaml_parse;

class Main extends PluginBase implements Listener {

	public static string $mode;

	protected function onEnable() : void {
		$this->saveDefaultConfig();

		Hydrogen::checkForUpdates($this);
		HConfig::verifyConfigVersion($this->getConfig(), "2.2.4");

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$mode = $this->getStringConfig("Mode");
		if (mb_stripos($mode, "simpleform") !== false) {
			self::$mode = "SimpleForm";
			return;
		} elseif (mb_stripos($mode, "modalform") !== false) {
			self::$mode = "ModalForm";
			return;
		}
		$this->ConfigFix();
	}

	public function onJoin(PlayerJoinEvent $event) : void {
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

	private function kygekSimpleJoinUI(Player $player) : void {
		$form = new SimpleForm(function (Player $player, ?int $data = null) : bool {
			if ($data === null) {
				$this->dispatchCommandsOnClose($player);
				return true;
			}
			$buttons = $this->getButtonList("SimpleForm");
			if (!isset($buttons[$data])) {
				return true;
			}
			$button = $buttons[$data];
			if (!empty($button["commands"])) {
				foreach ($this->getStringList($button["commands"]) as $command) {
					$playern = str_replace("{player}", $player->getName(), $command);
					$this->getServer()->dispatchCommand($this->commandMode($player), $playern);
				}
			}
			return true;
		});
		$form->setTitle($this->replace($player, $this->getStringConfig("title")));
		$form->setContent($this->replace($player, $this->getStringConfig("content")));
		foreach ($this->getButtonList("SimpleForm") as $button) {
			$name = $this->getStringValue($button["name"] ?? "");
			$image = $this->getStringValue($button["image"] ?? "");
			if ($image === "") {
				$form->addButton($this->replace($player, $name));
			} else {
				$url = str_starts_with($image, "http://") || str_starts_with($image, "https://");
				$form->addButton($this->replace($player, $name), $url ? 1 : 0, $image);
			}
		}
		$player->sendForm($form);
	}

	private function kygekModalJoinUI(Player $player) : void {
		$form = new ModalForm(function (Player $player, ?bool $data = null) : bool {
			if ($data === null) {
				$this->dispatchCommandsOnClose($player);
				return true;
			}
			switch ($data) {
				case true:
					$command = $this->getNestedStringOrNull("Buttons.ModalForm.B1.command");
					if ($command !== null) {
						$this->getServer()->dispatchCommand($this->commandMode($player), str_replace("{player}", $player->getName(), $command));
					}
					break;
				case false:
					$command = $this->getNestedStringOrNull("Buttons.ModalForm.B2.command");
					if ($command !== null) {
						$this->getServer()->dispatchCommand($this->commandMode($player), str_replace("{player}", $player->getName(), $command));
					}
					break;
			}
			return true;
		});
		$form->setTitle($this->replace($player, $this->getStringConfig("title")));
		$form->setContent($this->replace($player, $this->getStringConfig("content")));
		$form->setButton1($this->replace($player, $this->getNestedString("Buttons.ModalForm.B1.name")));
		$form->setButton2($this->replace($player, $this->getNestedString("Buttons.ModalForm.B2.name")));
		$player->sendForm($form);
	}

	private function ConfigFix() : void {
		$this->getConfig()->reload();
		$mode = $this->getStringConfig("Mode");
		if (mb_stripos($mode, "simpleform") !== false) {
			self::$mode = "SimpleForm";
			return;
		} elseif (mb_stripos($mode, "modalform") !== false) {
			self::$mode = "ModalForm";
			return;
		}
		self::$mode = "SimpleForm";
		$this->getLogger()->error(TextFormat::RED . ("Incorrect mode have been set in the config.yml, changing the mode to SimpleForm..."));
		$content = file_get_contents($this->getDataFolder() . "config.yml");
		if ($content === false) {
			return;
		}
		$yml = yaml_parse($content);
		$oldMode = is_array($yml) ? $this->getStringValue($yml["Mode"] ?? "") : "";
		$config = str_replace("Mode: " . $oldMode, "Mode: SimpleForm", $content);
		unlink($this->getDataFolder() . "config.yml");
		$file = fopen($this->getDataFolder() . "config.yml", "w");
		if (is_resource($file)) {
			fwrite($file, $config);
			fclose($file);
		}
	}

	private function replace(Player $player, string $text) : string {
		$from = ["{world}", "{player}", "{online}", "{max_online}", "{line}"];
		$to = [
			$player->getWorld()->getDisplayName(),
			$player->getName(),
			(string) count($this->getServer()->getOnlinePlayers()),
			(string) $this->getServer()->getMaxPlayers(),
			"\n"
		];
		return TextFormat::colorize(str_replace($from, $to, $text));
	}

	private function commandMode(Player $player) : Player|ConsoleCommandSender {
		$server = $this->getServer();
		$mode = $this->getStringConfig("command-mode");
		if (mb_stripos($mode, "console") !== false) return new ConsoleCommandSender($server, $server->getLanguage());
		elseif (mb_stripos($mode, "player") !== false) return $player;
		else {
			$this->getLogger()->error(TextFormat::RED . ("Incorrect command mode have been set in the config.yml, changing the command mode to console..."));
			$this->getConfig()->set("command-mode", "console");
			$this->getConfig()->save();
			return new ConsoleCommandSender($server, $server->getLanguage());
		}
	}

	private function dispatchCommandsOnClose(Player $player) : void {
		foreach ($this->getStringList($this->getConfig()->get("commands-on-close", [])) as $command) {
			$this->getServer()->dispatchCommand($this->commandMode($player), str_replace("{player}", $player->getName(), $command));
		}
	}

	private function getStringValue(mixed $value) : string {
		return is_string($value) ? $value : "";
	}

	private function getStringConfig(string $key) : string {
		return $this->getStringValue($this->getConfig()->get($key, ""));
	}

	private function getNestedString(string $key) : string {
		return $this->getStringValue($this->getConfig()->getNested($key, ""));
	}

	private function getNestedStringOrNull(string $key) : ?string {
		$value = $this->getConfig()->getNested($key);
		return is_string($value) ? $value : null;
	}

	/** @return list<string> */
	private function getStringList(mixed $value) : array {
		if (!is_array($value)) {
			return [];
		}
		return array_values(array_filter($value, is_string(...)));
	}

	/** @return list<array<string, mixed>> */
	private function getButtonList(string $form) : array {
		$value = $this->getConfig()->getNested("Buttons." . $form, []);
		if (!is_array($value)) {
			return [];
		}
		$buttons = [];
		foreach ($value as $button) {
			if (is_array($button)) {
				$normalized = [];
				foreach ($button as $key => $buttonValue) {
					if (is_string($key)) {
						$normalized[$key] = $buttonValue;
					}
				}
				$buttons[] = $normalized;
			}
		}
		return $buttons;
	}
}
