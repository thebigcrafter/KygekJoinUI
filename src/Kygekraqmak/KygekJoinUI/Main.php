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

class Main extends PluginBase implements Listener{
	
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
	@mkdir($this->getDataFolder());
	$this->saveResource("config.yml");
	if (!$this->getConfig()->exists("config-version")){
	    $this->getLogger()->notice("§4[KygekJoinUI] Your configuration file is outdated, updating the config.yml...");
	    unlink($this->getDataFolder()."config.yml");
	    $this->getConfig()->load($this->getDataFolder()."config.yml");
	    return;
	}
	if(version_compare("1.1", $this->getConfig()->get("config-version"))){
            $this->getLogger()->notice("§4[KygekJoinUI] Your configuration file is outdated, updating the config.yml...");
	    unlink($this->getDataFolder()."config.yml");
	    $this->getConfig()->load($this->getDataFolder()."config.yml");
	    return;
	}
	if ($this->getConfig()->get("Mode") == "SimpleForm"){
        }elseif ($this->getConfig()->get("Mode") == "ModalForm"){
        }
	else{
	    $this->getLogger()->error(TextFormat::RED.("[KygekJoinUI] Please set the correct mode in the config.yml, changing the mode to SimpleForm..."));
	    $this->getConfig()->set("Mode", "SimpleForm");
	    $this->getConfig()->save();
	}
    }
		
    public function onJoin(PlayerJoinEvent $event){
	$player = $event->getPlayer();
        if($this->getConfig()->get("Mode") == "SimpleForm"){
       	    $this->kygekSimpleJoinUI($player);
	}
	if($this->getConfig()->get("Mode") == "ModalForm"){
       	    $this->kygekModalJoinUI($player);
	}
    }

    private function kygekSimpleJoinUI($player){ 
        $form = new SimpleForm(function (Player $player, int $data = null){
            if($data === null){
                return true;
            }
	        $Buttons = $this->getConfig()->getNested("Buttons.SimpleForm");
			$command = explode(":", $Buttons[$data]);
	        if (count($command) <= 1){
	    	    return;
	        }
            if ($command[1] == null){
	    	    return;
			}
			$first = true;
			foreach($command as $cmd){
				if ($first){
					$first = false;
				}else{
					$playern = str_replace("{player}", $player->getName(), $cmd);
					$comnd = $playern
					$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $comnd);
				}
	        }
        });
		$world = str_replace("{world}", $player->getLevel()->getName(),$this->getConfig()->get("title"));
		$playern = str_replace("{player}", $player->getName(), $world);
		$onlineplayers = str_replace("{online}", count($this->getServer()->getOnlinePlayers()), $playern);
		$maxplayers = str_replace("{max_online}", $this->getServer()->getMaxPlayers(), $onlineplayers);
	    $title = $maxplayers
        $form->setTitle($title);
	    $world = str_replace("{world}", $player->getLevel()->getName(), $this->getConfig()->get("content"));
		$playern = str_replace("{player}", $player->getName(), $world);
		$onlineplayers = str_replace("{online}", count($this->getServer()->getOnlinePlayers()), $playern);
		$maxplayers = str_replace("{max_online}", $this->getServer()->getMaxPlayers(), $onlineplayers);
	    $content = $maxplayers
		$form->setContent($content);
	    foreach($this->getConfig()->getNested("Buttons.SimpleForm") as $b){
			$text = explode(":", $b);
	        $world = str_replace("{world}", $player->getLevel()->getName(), $text[0]);
	        $playern = str_replace("{player}", $player->getName(), $world);
			$onlineplayers = str_replace("{online}", count($this->getServer()->getOnlinePlayers()), $playern);
			$maxplayers = str_replace("{max_online}",$this->getServer()->getMaxPlayers(), $onlineplayers);
			$text = $maxplayers
	        $form->addButton($text);
	    }
        $form->sendToPlayer($player);
        return $form;
    }
    private function kygekModalJoinUI($player){ 
        $form = new ModalForm(function (Player $player, bool $data = null){
            if($data === null){
                return true;
            }             
            switch($data){
                case true:
		            $command = $this->getConfig()->getNested("Buttons.ModalForm.B1.command");
		            if ($command !== null){
		                $this->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $player->getName(), $command));
		            }
                break;
		        case false:
		            $command = $this->getConfig()->getNested("Buttons.ModalForm.B2.command");
		            if ($command !== null){
		                $this->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $player->getName(), $command));
		            }
                break;
            }
        });
        $world = str_replace("{world}", $player->getLevel()->getName(),$this->getConfig()->get("title"));
		$playern = str_replace("{player}", $player->getName(), $world);
		$onlineplayers = str_replace("{online}", count($this->getServer()->getOnlinePlayers()), $playern);
		$maxplayers = str_replace("{max_online}", $this->getServer()->getMaxPlayers(), $onlineplayers);
	    $title = $maxplayers
        $form->setTitle($title);
		$world = str_replace("{world}", $player->getLevel()->getName(), $this->getConfig()->get("content"));
		$playern = str_replace("{player}", $player->getName(), $world);
		$onlineplayers = str_replace("{online}", count($this->getServer()->getOnlinePlayers()), $playern);
		$maxplayers = str_replace("{max_online}", $this->getServer()->getMaxPlayers(), $onlineplayers);
	    $content = $maxplayers
		$form->setContent($content);
		$world = str_replace("{world}", $player->getLevel()->getName(), $this->getConfig()->getNested("Buttons.ModalForm.B1.name"));
		$playern = str_replace("{player}", $player->getName(), $world);
		$onlineplayers = str_replace("{online}", count($this->getServer()->getOnlinePlayers()), $playern);
		$maxplayers = str_replace("{max_online}", $this->getServer()->getMaxPlayers(), $onlineplayers);
	    $B1 = $maxplayers
    	$form->setButton1($B1);
		$world = str_replace("{world}", $player->getLevel()->getName(), $this->getConfig()->getNested("Buttons.ModalForm.B2.name"));
		$playern = str_replace("{player}", $player->getName(), $world);
		$onlineplayers = str_replace("{online}", count($this->getServer()->getOnlinePlayers()), $playern);
		$maxplayers = str_replace("{max_online}", $this->getServer()->getMaxPlayers(), $onlineplayers);
	    $B2 = $maxplayers
		$form->setButton2($B2);
        $form->sendToPlayer($player);
        return $form;
    }
}
