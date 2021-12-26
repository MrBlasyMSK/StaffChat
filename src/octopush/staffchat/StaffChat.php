<?php

namespace octopush\staffchat;

use octopush\staffchat\command\StaffChatCommand;
use octopush\staffchat\util\Utils;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use PrefixedLogger;

class StaffChat extends PluginBase implements Listener
{
	use SingletonTrait;

	public const COMMAND_PERMISSION = "staffchat.cmd";
	public const CHAT_PERMISSION = "staffchat.chat";
	public const READ_PERMISSION = "staffchat.read";

	private array $staffChat = [];
	private PrefixedLogger $logger;

	public function onEnable(): void {
		self::setInstance($this);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->register($this->getName(), new StaffChatCommand($this));
		$this->logger = new PrefixedLogger($this->getLogger(), $this->getName());
	}

	/**
	 * @param CommandSender $sender
	 * @return void
	 */
	public function joinStaffChat(CommandSender $sender): void {
		$this->staffChat[$sender->getName()] = true;
		$announce = $this->getConfig()->get('announce-state', true);
		if (!$announce) return;
		$joinFormat = $this->getConfig()->get('join', "&b%player% &fjoined the staff chat");
		$joinFormat = str_replace("%player%", $sender->getName(), $joinFormat);
		$joinMessage = $this->colorize($joinFormat);
		foreach ($this->staffChat as $staff => $value) {
			$staff = $this->getServer()->getPlayerByPrefix($staff);
			if ($staff->isOnline() && $staff->hasPermission(self::READ_PERMISSION)) {
				$staff->sendMessage($joinMessage);
			}
		}
	}

	/**
	 * @param CommandSender $sender
	 * @return void
	 */
	public function leaveStaffChat(CommandSender $sender): void {
		if (isset($this->staffChat[$sender->getName()])) unset($this->staffChat[$sender->getName()]);
		$announce = $this->getConfig()->get('announce-state', true);
		if (!$announce) return;
		$leaveFormat = $this->getConfig()->get('join',"&b%player% &fleft the staff chat");
		$leaveFormat = str_replace("%player%", $sender->getName(), $leaveFormat);
		$leaveMessage = $this->colorize($leaveFormat);
		foreach ($this->staffChat as $staff) {
			$staff = $this->getServer()->getPlayerExact($staff);
			if ($staff->isOnline() && $staff->hasPermission(self::READ_PERMISSION)) {
				$staff->sendMessage($leaveMessage);
			}
		}
	}

	/**
	 * @param CommandSender $sender
	 * @return bool
	 */
	public function inStaffChat(CommandSender $sender): bool {
		return isset($this->staffChat[$sender->getName()]);
	}

	/**
	 * @param Player $player
	 * @param string $text
	 * @return string
	 */
	public function formatMessage(Player $player, string $text): string {
		$prefix = $this->getConfig()->get('prefix', "&b[SC]&r ");
		$format = $this->getConfig()->get('staffchat-format', "%prefix% &b%player%: &f%message%");
		$format = str_replace("%prefix%", $prefix, $format);
		$format = str_replace("%player%", $player->getName(), $format);
		$message = str_replace("%message%", $text, $format);
		$message = $this->replaceCommands($player, $message);
		return $this->colorize($message);
	}

	/**
	 * @param string $message
	 * @return string
	 */
	public function colorize(string $message): string {
		return str_replace('&', '§', $message);
	}

	/**
	 * @param Player $player
	 * @param string $message
	 * @return string
	 */
	public function replaceCommands(Player $player, string $message): string {
		$functionsEnabled = $this->getConfig()->get('enable-functions', true);
		if (!$functionsEnabled){
			return $message;
		}
		$execSym = $this->getConfig()->get('functions-executor', '!');
		$regex = "~($execSym\w+)~";
		if (preg_match_all($regex, $message, $matches, PREG_PATTERN_ORDER)) {
			foreach ($matches[1] as $function) {
				switch ($function) {
					case "{$execSym}pos":
						$message = str_replace("{$execSym}pos", $this->getStringPosition($player), $message);
						break;
					case "{$execSym}near":
						$message = str_replace("{$execSym}near", $this->getNearPlayers($player), $message);
						break;
					case "{$execSym}focus":
						$message = str_replace("{$execSym}focus", $this->getPlayerLookingAt($player), $message);
						break;
				}
			}
		}
		return $message;
	}

	/**
	 * @param Player $player
	 * @return string
	 */
	public function getNearPlayers(Player $player): string {
		$players = "> ";
		foreach ($player->getLocation()->getWorld()->getNearbyEntities($player->boundingBox->expandedCopy(3, 3, 3), $player) as $entity) {
			if ($entity instanceof Player) {
				$players .= "{$entity->getName()}, ";
			}
		}
		return $players;
	}

	/**
	 * @param Player $player
	 * @return string
	 */
	public function getStringPosition(Player $player): string {
		$loc = $player->getLocation();
		return "{$loc->getX()}, {$loc->getY()}, {$loc->getZ()}, {$loc->getWorld()->getFolderName()}";
	}

	/**
	 * @param Player $player
	 * @return string
	 */
	public function getPlayerLookingAt(Player $player): string {
		$lookingAt = Utils::getEntityPlayerLookingAt($player);
		return $lookingAt instanceof Player ? $lookingAt->getName() : "";
	}

	/**
	 * @param PlayerChatEvent $event
	 * @return void
	 */
	public function onPlayerChat(PlayerChatEvent $event): void {
		$player = $event->getPlayer();
		$message = $event->getMessage();
		$consoleAttach = $this->getConfig()->get('console-attach', true);
		if ($this->inStaffChat($player) && $player->hasPermission(self::CHAT_PERMISSION)) {
			$event->cancel();
			foreach ($this->staffChat as $staff => $value) {
				$staff = $this->getServer()->getPlayerExact($staff);
				if ($staff->isOnline() && $staff->hasPermission(self::READ_PERMISSION)) {
					$staff->sendMessage($this->formatMessage($player, $message));
					if ($consoleAttach){
						$this->logger->info($message);
					}
				}
			}
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 * @return void
	 */
	public function onPlayerQuit(PlayerQuitEvent $event): void {
		$player = $event->getPlayer();
		$this->leaveStaffChat($player);
	}
}