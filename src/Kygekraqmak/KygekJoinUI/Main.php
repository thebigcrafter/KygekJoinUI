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

use jojoe77777\FormAPI\CustomForm;
use Exception;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as C;
use jojoe77777\FormAPI\SimpleForm;

class Main extends PluginBase implements Listener{
    public $cfg;
	
    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->cfg = $this->getConfig();
        try {
            $cfgversion = $this->getConfig()->get("version");
            $mainversion = "1.0.1";
            if ($cfgversion !== $mainversion) {
                throw new Exception("Old Config Detected Please Delete Ur Config and a new version should generate!");
                    }
        } catch (Exception $exception) {
            $path = $this->getConfig()->getPath();
            unlink($path);
            $this->getConfig()->save();
            $this->cfg->reload();
        }
    }
		
    public function onJoin(PlayerJoinEvent $event){
	$player = $event->getPlayer();
	if ($this->getConfig()->get("mode") == 'Simple') {
        $this->kygekSimpleJoinUI($player);
        }
	if ($this->getConfig()->get("mode") == "Custom") {
	    $this->kygekCustomJoinUI($player);
    }
    }

    public function kygekSimpleJoinUI($player){
        $form = new SimpleForm(function (Player $player, int $data = null){
            $result = $data;
            if($result === null){
                return true;
            }             
            switch($result){
                case 0:
                break;
            }
            return true;
        });
        $form->setTitle(C::colorize($this->cfg->get("title")));
        $form->setContent(C::colorize($this->cfg->get("content")));
        $buttons = $this->cfg->get("buttons");
		foreach ($buttons as $button) {
		$form->addButton(C::colorize($button));
		}
        $form->sendToPlayer($player);
        return $form;
     }

    private function kygekCustomJoinUI(Player $player)
    {
        $newcaptcha = substr(str_shuffle("qwertyuiopasdfghjklzxcvbnm"),0,mt_rand(1,10));
        $form = new CustomForm(function (Player $player, array $data = null) use ($newcaptcha) {
            $result = $data;
            if($result === null){
                $this->kygekCustomJoinUI($player);
            }
            $answer = $result[1];
            if ($answer == $newcaptcha) {
                return true;
            } else {
                $player->kick(C::RED."[Captcha Bot] You Have Been Kicked For Messing Up The Captcha!", false, C::RED."[Captcha Bot] You Have Been Kicked For Messing Up The Captcha!");
            }
            return true;
        });
        $form->setTitle(C::colorize($this->cfg->get("Ctitle")));
        $form->addLabel(C::colorize($this->cfg->get("Label"). $newcaptcha));
        $dropdown = $this->cfg->get("dropdown");
        if ($dropdown !== 'null') {
            $form->addDropdown(C::colorize($dropdown),$this->cfg->get("options"));
        }
        $input = $this->cfg->get("input");
        if ($input !== 'null') {
            $form->addInput(C::colorize($input), "Enter The Captcha Here!");
        }
        $slider = $this->cfg->get("slider");
        if ($slider !== 'null') {
            $form->addSlider(C::colorize($slider),(int)$this->cfg->get("min"),(int)$this->cfg->get("max"));
        }
        $stepslider = $this->cfg->get("stepslider");
        if ($stepslider !== 'null') {
            $form->addStepSlider(C::colorize($stepslider),$this->cfg->get("steps"));
        }
        $form->sendToPlayer($player);
        return $form;
    }
}
