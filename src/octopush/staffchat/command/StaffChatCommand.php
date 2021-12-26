<?php

namespace octopush\staffchat\command;

use octopush\staffchat\StaffChat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

class StaffChatCommand extends Command implements PluginOwned
{

	/**
	 * @param StaffChat $plugin
	 */
	public function __construct(private StaffChat $plugin) {
		parent::__construct("staffchat", "Staff Chat main command", "/sc help", ['sc']);
		$this->setPermission(StaffChat::COMMAND_PERMISSION);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		if (!$this->testPermissionSilent($sender)) {
			$sender->sendMessage(TextFormat::RED . "You dont have permissions to run this command.");
			return;
		}
		if (!$sender instanceof Player) {
			return;
		}
		if (!isset($args[0])) {
			$sender->sendMessage($this->getUsage());
			return;
		}
		switch ($args[0]) {
			case 'help':
				$sender->sendMessage(TextFormat::AQUA . "> Use /sc on|off to join or leave staff chat");
				break;
			case "on":
				$this->plugin->joinStaffChat($sender);
				$sender->sendMessage(TextFormat::BLUE . "> You have joined the staff chat.");
				break;
			case "off":
				$this->plugin->leaveStaffChat($sender);
				$sender->sendMessage(TextFormat::BLUE . "> You have left the staff chat.");
				break;
		}
	}

	/**
	 * @return Plugin
	 */
	public function getOwningPlugin(): Plugin {
		return $this->plugin;
	}
}