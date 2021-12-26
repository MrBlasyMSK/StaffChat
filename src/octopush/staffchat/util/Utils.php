<?php

namespace octopush\staffchat\util;

use Exception;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\math\VoxelRayTrace;
use pocketmine\player\Player;

class Utils
{
	/**
	 * Returns the Entity the player is looking at currently
	 *
	 * @param Player $player the player to check
	 * @param int $maxDistance the maximum distance to check for entities
	 * @param bool $useCorrection define if correction should be used or not (if so, the matching will increase but is more unprecise and it will consume more performance)
	 * @return null|Entity    either NULL if no entity is found or an instance of the entity
	 */
	public static function getEntityPlayerLookingAt(Player $player, int $maxDistance = 30, bool $useCorrection = false): Entity|null {
		if ($player->isClosed() or !$player->isOnline() or !$player->spawned) {
			return null;
		}
		/** @var Entity */
		$entity = null;

		// just a fix because player MAY not be fully initialized
		$nearbyEntities = $player->getWorld()->getNearbyEntities($player->boundingBox->expandedCopy($maxDistance, $maxDistance, $maxDistance), $player);

		// get all blocks in looking direction until the max interact distance is reached (it's possible that startblock isn't found!)
		try {
			foreach (VoxelRayTrace::inDirection($player->getLocation()->add(0, $player->getEyeHeight(), 0), $player->getDirectionVector(), $maxDistance) as $vector3) {

				$block = $player->getWorld()->getBlockAt($vector3->x, $vector3->y, $vector3->z);
				$entity = self::getEntityAtPosition($nearbyEntities, $block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ(), $useCorrection);
				if ($entity !== null and $entity instanceof Living) {
					break;
				}
			}
		} catch (Exception $e) {
			// nothing to log here!
		}

		return $entity;
	}

	/**
	 * Returns the entity at the given position from the array of nearby entities
	 *
	 * @param array $nearbyEntities an array of entity which are close to the player
	 * @param int   $x the x coordinate to search for any of the given entities coordinates to match
	 * @param int   $y the y coordinate to search for any of the given entities coordinates to match
	 * @param int   $z the z coordinate to search for any of the given entities coordinates to match
	 * @param bool  $useCorrection set this to true if the matching should be extended by -1 / +1 (in x, y, z directions)
	 * @return null|Entity        NULL when none of the given entities matched or the first entity matching found
	 */
	private static function getEntityAtPosition(array $nearbyEntities, int $x, int $y, int $z, bool $useCorrection): null|Entity {
		/** @var Entity $nearbyEntity */
		foreach($nearbyEntities as $nearbyEntity){
			if($nearbyEntity->getLocation()->getFloorX() === $x and $nearbyEntity->getLocation()->getFloorY() === $y and $nearbyEntity->getLocation()->getFloorZ() === $z){
				return $nearbyEntity;
			}else if($useCorrection){ // when using correction, we search not only in front also 1 block up/down/left/right etc. pp
				return self::getCorrectedEntity($nearbyEntity, $x, $y, $z);
			}
		}
		return null;
	}

	/**
	 * Searches around the given x, y, z coordinates (-1/+1) for the given entity coordinates to match.
	 *
	 * @param Entity $entity the entity to check coordinates with
	 * @param int    $x the starting x position
	 * @param int    $y the starting y position
	 * @param int    $z the starting z position
	 * @return null|Entity      NULL when entity position doesn't match, an instance of entity if it matches
	 */
	private static function getCorrectedEntity(Entity $entity, int $x, int $y, int $z): Entity|null {
		$entityX = $entity->getLocation()->getFloorX();
		$entityY = $entity->getLocation()->getFloorY();
		$entityZ = $entity->getLocation()->getFloorZ();

		for($searchX = ($x - 1); $searchX <= ($x + 1); $searchX++){
			for($searchY = ($y - 1); $searchY <= ($y + 1); $searchY++){
				for($searchZ = ($z - 1); $searchZ <= ($z + 1); $searchZ++){
					if($entityX === $searchX and $entityY === $searchY and $entityZ === $searchZ){
						return $entity;
					}
				}
			}
		}
		return null;
	}
}