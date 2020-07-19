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
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use jojoe77777\FormAPI;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\ModalForm;

class Main extends PluginBase implements Listener{
	
    public function onEnable(){
	$this->getServer()->getPluginManager()->registerEvents($this, $this);
	@mkdir($this->getDataFolder());
	$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
	$this->saveResource("config.yml");
	if (!$api) {
	    $this->getLogger()->error(TextFormat::RED.("[ERROR] KygekJoinUI cannot be enabled because FormAPI plugin cannot be found."));
        $this->getLogger()->error(TextFormat::RED.("Please install FormAPI plugin first at https://poggit.pmmp.io/p/FormAPI."));
	    Server::getInstance()->getPluginManager()->disablePlugin($this);
            return;
        }
		if ($this->getConfig()->get("Mode") == "SimpleForm"){
			//u can add a something here idk
		}elseif ($this->getConfig()->get("Mode") == "ModalForm"){
			// u can add a something here too =P
		}
		else{
			$this->getLogger()->error(TextFormat::RED.("Please set the correct mode in the config. changing the Mode to SimpleForm..."));
	    	$this->getConfig()->set("Mode", "SimpleForm");
			$this->getConfig()->save();
		}
		@mkdir($this->getDataFolder());
		$this->saveResource("config.yml");
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
	        if (count($command) >= 3){
		        //sorry bad english Change it please idk what should i write in the error
		        $this->getLogger()->error(TextFormat::RED.("[ERROR] KygekJoinUI cannot be enabled because there are too many arguments in the command part in the config.yml."));
		        Server::getInstance()->getPluginManager()->disablePlugin($this);
		        return;
	        }
	        if (count($command) <= 1){
	    	    return;
	        }
            if ($command[1] == null){
	    	    return;
	        }
	        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command[1]);
        });
        $form->setTitle($this->getConfig()->get("title"));
	    $world = str_replace("{World}", $player->getLevel()->getName(), $this->getConfig()->get("content"));
	    $playern = str_replace("{player}", $player->getName(), $world);
	    foreach($player->getLevel()->getEntities() as $entity){
	        if($entity instanceof Player){
	    	    $players = [];
		    $players[] = $entity;
	        }
	    }
	    $worldcount = str_replace("{worldplayercount}", count($players), $playern);
	    $onlineplayers = str_replace("{onlineplayers}", count($this->getServer()->getOnlinePlayers()), $playern);
	    $content = str_replace("{line}", "\n", $onlineplayers);
        $form->setContent($content);
	    foreach($this->getConfig()->getNested("Buttons.SimpleForm") as $b){
			$text = explode(":", $b);
	        $world = str_replace("{World}", $player->getLevel()->getName(), $text[0]);
	        $playern = str_replace("{player}", $player->getName(), $world);
	        foreach($player->getLevel()->getEntities() as $entity){
	            if($entity instanceof Player){
	    	        $players = [];
		            $players[] = $entity;
	            }
	        }
	        $worldcount = str_replace("{worldplayercount}", count($players), $playern);
	        $onlineplayers = str_replace("{onlineplayers}", count($this->getServer()->getOnlinePlayers()), $playern);
			$text = str_replace("{line}", "\n", $onlineplayers);
	        $form->addButton($text);
	    }
        $form->sendToPlayer($player);
        return $form;
    }
    private function kygekModalJoinUI($player){ 
        $form = new ModalForm(function (Player $player, bool $data){
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
        $form->setTitle($this->getConfig()->get("title"));
		$world = str_replace("{World}", $player->getLevel()->getName(), $this->getConfig()->get("content"));
		$playern = str_replace("{player}", $player->getName(), $world);
		foreach($player->getLevel()->getEntities() as $entity){
		    if($entity instanceof Player){
	    		$players = [];
				$players[] = $entity;
		    }
		}
		$worldcount = str_replace("{worldplayercount}", count($players), $playern);
		$onlineplayers = str_replace("{onlineplayers}", count($this->getServer()->getOnlinePlayers()), $playern);
		$content = str_replace("{line}", "\n", $onlineplayers);
    	$form->setContent($content);
    	$form->setButton1($this->getConfig()->getNested("Buttons.ModalForm.B1.name"));
		$form->setButton2($this->getConfig()->getNested("Buttons.ModalForm.B2.name"));
        $form->sendToPlayer($player);
        return $form;
    }
}
