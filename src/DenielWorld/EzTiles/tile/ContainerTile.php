<?php

namespace DenielWorld\EzTiles\tile;

use DenielWorld\EzTiles\data\TileInfo;
use DenielWorld\EzTiles\inventory\FakeChestInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\tile\ContainerTrait;
use pocketmine\tile\NameableTrait;

class ContainerTile extends SimpleTile implements InventoryHolder{
    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait;

    /** @var Inventory */
    protected $inventory;

    /** @var string */
    protected $inventoryClass;

    /**
     * ContainerTile constructor.
     * @param Level $level
     * @param TileInfo|CompoundTag $tileInfo
     * @param string $inventoryClass The passed Inventory class must accept exactly 1 argument in the...
     * ...constructor to function here, and that argument must be a Tile instance (such as $this)
     */
    public function __construct(Level $level, $tileInfo, string $inventoryClass = FakeChestInventory::class)
    {
        parent::__construct($level, $tileInfo);

        if($tileInfo instanceof TileInfo){//i.e. If the Tile has been initiated for the first time using EzTiles API
            $this->inventoryClass = $inventoryClass;
            $this->inventory = new $inventoryClass($this);
        }
    }

    /**
     * @param CompoundTag $nbt
     */
    public function readSaveData(CompoundTag $nbt): void
    {
        parent::readSaveData($nbt);

        $this->inventoryClass = $nbt->getString("inventoryClass", FakeChestInventory::class);

        $this->loadName($nbt);

        $this->inventory = new $this->inventoryClass($this);
        $this->loadItems($nbt);

        // TODO: Allow for plugins to pass on an InventoryEventProcessor class to automatically control certain actions on a slot change
    }

    /**
     * @param CompoundTag $nbt
     */
    public function writeSaveData(CompoundTag $nbt): void
    {
        parent::writeSaveData($nbt);

        $nbt->setString("inventoryClass", FakeChestInventory::class);

        $this->saveName($nbt);
        $this->saveItems($nbt);
    }

    public function addAdditionalSpawnData(CompoundTag $nbt): void
    {
        parent::addAdditionalSpawnData($nbt);

        $this->addNameSpawnData($nbt);
    }

    /**
     * Destroys the inventory object when the tile is closed
     */
    public function close() : void{
        if(!$this->closed){
            $this->inventory->removeAllViewers(true);
            $this->inventory = null;

            parent::close();
        }
    }

    /**
     * @return Inventory
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * @return Inventory
     */
    public function getRealInventory()
    {
        return $this->getInventory();//TODO: Figure out if this would have problems with a double chest inventory .-.
    }

    /**
     * @return string
     */
    public function getDefaultName(): string
    {
        return "Container Tile";//This should remain as an internal method and external plugins should use NBT (data) checks instead
    }

}