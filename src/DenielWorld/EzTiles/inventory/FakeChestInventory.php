<?php

namespace DenielWorld\EzTiles\inventory;

use DenielWorld\EzTiles\tile\ContainerTile;
use pocketmine\inventory\ContainerInventory;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;

class FakeChestInventory extends ContainerInventory {

    /** @var ContainerTile */
    protected $holder;

    public function __construct(ContainerTile $tile)
    {
        parent::__construct($tile);
    }

    /* Warning: All code below this point was taken out directly from ChestInventory::class, and is here as an example &...
    ... a fallback value for ContainerTile::class */

    /**
     * @return int
     */
    public function getNetworkType() : int{
        return WindowTypes::CONTAINER;
    }

    /**
     * @return string
     */
    public function getName() : string{
        return "Chest";
    }

    /**
     * @return int
     */
    public function getDefaultSize() : int{
        return 27;
    }

    /**
     * This override is here for documentation and code completion purposes only.
     * @return ContainerTile
     */
    public function getHolder(){
        return $this->holder;
    }

    /**
     * @return int
     */
    protected function getOpenSound() : int{
        return LevelSoundEventPacket::SOUND_CHEST_OPEN;
    }

    /**
     * @return int
     */
    protected function getCloseSound() : int{
        return LevelSoundEventPacket::SOUND_CHEST_CLOSED;
    }

    /**
     * @param Player $who
     */
    public function onOpen(Player $who) : void{
        parent::onOpen($who);

        if(count($this->getViewers()) === 1 and $this->getHolder()->isValid()){
            $this->broadcastBlockEventPacket(true);
            $this->getHolder()->getLevel()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), $this->getOpenSound());
        }
    }

    /**
     * @param Player $who
     */
    public function onClose(Player $who) : void{
        if(count($this->getViewers()) === 1 and $this->getHolder()->isValid()){
            $this->broadcastBlockEventPacket(false);
            $this->getHolder()->getLevel()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), $this->getCloseSound());
        }
        parent::onClose($who);
    }

    /**
     * @param bool $isOpen
     */
    protected function broadcastBlockEventPacket(bool $isOpen) : void{
        $holder = $this->getHolder();

        $pk = new BlockEventPacket();
        $pk->x = (int) $holder->x;
        $pk->y = (int) $holder->y;
        $pk->z = (int) $holder->z;
        $pk->eventType = 1; //it's always 1 for a chest
        $pk->eventData = $isOpen ? 1 : 0;
        $holder->getLevel()->broadcastPacketToViewers($holder, $pk);
    }

}