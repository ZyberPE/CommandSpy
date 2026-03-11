<?php

namespace CommandSpy;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener{

    private array $spyEnabled = [];

    public function onEnable() : void{
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

        if(!$sender instanceof Player){
            $sender->sendMessage("Run this command in-game.");
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

        switch(strtolower($args[0])){

            case "on":
                $this->spyEnabled[$sender->getName()] = true;
                $sender->sendMessage($this->getConfig()->getNested("messages.spy-enabled"));
            break;

            case "off":
                unset($this->spyEnabled[$sender->getName()]);
                $sender->sendMessage($this->getConfig()->getNested("messages.spy-disabled"));
            break;
        }

        return true;
    }

    public function onChat(PlayerChatEvent $event) : void{

        $msg = $event->getMessage();

        // Only detect commands
        if(str_starts_with($msg, "/")){

            $player = $event->getPlayer();

            $format = $this->getConfig()->getNested("messages.spy-format");

            $message = str_replace(
                ["{player}", "{command}"],
                [$player->getName(), $msg],
                $format
            );

            // Console always sees
            $this->getLogger()->info($player->getName() . " ran: " . $msg);

            foreach($this->getServer()->getOnlinePlayers() as $staff){

                if($staff->hasPermission("command.spy") && isset($this->spyEnabled[$staff->getName()])){
                    
                    if($staff->getName() !== $player->getName()){
                        $staff->sendMessage($message);
                    }

                }
            }
        }
    }
}
