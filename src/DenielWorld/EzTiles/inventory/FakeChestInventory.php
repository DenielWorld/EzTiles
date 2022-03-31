<?php

namespace DenielWorld\EzTiles\inventory;

use DenielWorld\EzTiles\tile\ContainerTile;
use pocketmine\block\inventory\ChestInventory;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\world\Position;

class FakeChestInventory extends ChestInventory {

    /** @var ContainerTile */
    protected ContainerTile $tile;

    public function __construct(ContainerTile $tile)
    {
        parent::__construct($tile->getPosition());
        $this->tile = $tile;
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
     * @return Position
     */
    public function getHolder() : Position{
        return $this->holder;
    }

    /**
     * @param Player $who
     */
    public function onOpen(Player $who) : void{
        parent::onOpen($who);

        if(count($this->getViewers()) === 1 and $this->getHolder()->isValid()){
            $this->broadcastBlockEventPacket(true);
            $this->getHolder()->getWorld()->addSound($this->getHolder()->add(0.5, 0.5, 0.5), $this->getOpenSound());
        }
    }

    /**
     * @param Player $who
     */
    public function onClose(Player $who) : void{
        if(count($this->getViewers()) === 1 and $this->getHolder()->isValid()){
            $this->broadcastBlockEventPacket(false);
            $this->getHolder()->getWorld()->addSound($this->getHolder()->add(0.5, 0.5, 0.5), $this->getCloseSound());
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
        $holder->getWorld()->broadcastPacketToViewers($holder, $pk);
    }

}