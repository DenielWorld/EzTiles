<?php

namespace DenielWorld\EzTiles\tile;

use DenielWorld\EzTiles\data\TileInfo;
use DenielWorld\EzTiles\inventory\FakeChestInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\NameableTrait;

class ContainerTile extends SimpleTile implements InventoryHolder{
    use NameableTrait {
        addAdditionalSpawnData as addNameSpawnData;
    }
    use ContainerTrait;

    /** @var Inventory */
    protected Inventory $inventory;

    /** @var string */
    protected string $inventoryClass;

    /**
     * ContainerTile constructor.
     * @param World $level
     * @param TileInfo|CompoundTag $tileInfo
     * @param string $inventoryClass The passed Inventory class must accept exactly 1 argument in the...
     * ...constructor to function here, and that argument must be a Tile instance (such as $this)
     */
    public function __construct(World $level, $tileInfo, string $inventoryClass = FakeChestInventory::class)
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
    public function readSaveData(CompoundTag $nbt) : void
    {
        $this->inventoryClass = $nbt->getString("inventoryClass", FakeChestInventory::class);

        $this->loadName($nbt);

        $this->inventory = new $this->inventoryClass($this);
        $this->loadItems($nbt);

        parent::readSaveData($nbt);

        // TODO: Allow for plugins to pass on an InventoryEventProcessor class to automatically control certain actions on a slot change
    }

    /**
     * @param CompoundTag $nbt
     */
    public function writeSaveData(CompoundTag $nbt) : void
    {
        $nbt->setString("inventoryClass", FakeChestInventory::class);

        $this->saveName($nbt);
        $this->saveItems($nbt);

        parent::writeSaveData($nbt);
    }

    public function addAdditionalSpawnData(CompoundTag $nbt) : void
    {
        $this->addNameSpawnData($nbt);

        parent::addAdditionalSpawnData($nbt);
    }

    /**
     * Destroys the inventory object when the tile is closed
     */
    public function close() : void
    {
        if(!$this->closed){
            $this->inventory->removeAllViewers();

            parent::close();
        }
    }

    /**
     * @return Inventory
     */
    public function getInventory() : Inventory
    {
        return $this->inventory;
    }

    /**
     * @return Inventory
     */
    public function getRealInventory() : Inventory
    {
        return $this->getInventory();//TODO: Figure out if this would have problems with a double chest inventory .-.
    }

    /**
     * @return string
     */
    public function getDefaultName() : string
    {
        return "Container Tile";//This should remain as an internal method and external plugins should use NBT (data) checks instead
    }

}