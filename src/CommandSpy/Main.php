<?php

namespace CommandSpy;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
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

        switch(strtolower($args[0])){

            case "on":
                $this->spyEnabled[$sender->getName()] = true;
                $sender->sendMessage($this->getConfig()->getNested("messages.spy-enabled"));
            break;

            case "off":
                unset($this->spyEnabled[$sender->getName()]);
                $sender->sendMessage($this->getConfig()->getNested("messages.spy-disabled"));
            break;

            default:
                $sender->sendMessage("Usage: /commandspy <on|off>");
            break;
        }

        return true;
    }

    public function onPlayerCommand(PlayerCommandPreprocessEvent $event) : void{

        $player = $event->getPlayer();
        $command = $event->getMessage();

        $format = $this->getConfig()->getNested("messages.spy-format");
        $message = str_replace(
            ["{player}", "{command}"],
            [$player->getName(), $command],
            $format
        );

        // Send to console automatically
        $this->getServer()->getLogger()->info(str_replace("§", "", $message));

        // Send to players with spy enabled
        foreach($this->getServer()->getOnlinePlayers() as $staff){

            if($staff->hasPermission("command.spy") && isset($this->spyEnabled[$staff->getName()])){
                if($staff->getName() !== $player->getName()){
                    $staff->sendMessage($message);
                }
            }

        }
    }
}
