<?php

namespace CommandSpy;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\server\CommandEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener{

    private array $spyEnabled = [];

    public function onEnable() : void{
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

        if(!$sender instanceof Player){
            $sender->sendMessage("Use this command in-game.");
            return true;
        }

        if(!$sender->hasPermission("command.spy")){
            $sender->sendMessage($this->getConfig()->getNested("messages.no-permission"));
            return true;
        }

        if(!isset($args[0])){
            $sender->sendMessage("Usage: /commandspy <on|off>");
            return true;
        }

        if(strtolower($args[0]) === "on"){
            $this->spyEnabled[$sender->getName()] = true;
            $sender->sendMessage($this->getConfig()->getNested("messages.spy-enabled"));
        }

        if(strtolower($args[0]) === "off"){
            unset($this->spyEnabled[$sender->getName()]);
            $sender->sendMessage($this->getConfig()->getNested("messages.spy-disabled"));
        }

        return true;
    }

    public function onCommandEvent(CommandEvent $event) : void{

        $sender = $event->getSender();
        $command = $event->getCommand();

        $name = $sender instanceof Player ? $sender->getName() : "Console";

        $msg = "[CommandSpy] {$name} ran: /{$command}";

        // Console always sees
        $this->getLogger()->info($msg);

        foreach($this->getServer()->getOnlinePlayers() as $player){

            if(isset($this->spyEnabled[$player->getName()]) && $player->hasPermission("command.spy")){
                $player->sendMessage("§7[CommandSpy] §e{$name} §7ran: §f/{$command}");
            }

        }
    }
}
