<?php

namespace CommandSpy;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener{

    public function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("CommandSpy enabled!");
    }

    public function onCommandRun(CommandEvent $event) : void{
        $sender = $event->getSender();

        if(!$sender instanceof Player){
            return;
        }

        $command = $event->getCommand();
        $name = $sender->getName();

        foreach($this->getServer()->getOnlinePlayers() as $player){
            if($player->hasPermission("command.spy")){
                $player->sendMessage("§8[§cCommandSpy§8] §e{$name} §7ran: §f/{$command}");
            }
        }
    }
}
