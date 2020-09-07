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

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\Plugin;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class UICommand extends Command implements PluginIdentifiableCommand{

    private $p;

    public function __construct(Main $p, string $name, string $description){ 
        $this->p = $p;       
        parent::__construct($name, $description);
    }

    public function getPlugin() : Plugin{
        return $this->p;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(Main::$mode == "SimpleForm"){
            $this->p->kygekSimpleJoinUI($sender);
            return;
        }
        $this->p->kygekModalJoinUI($sender);
    }
}
