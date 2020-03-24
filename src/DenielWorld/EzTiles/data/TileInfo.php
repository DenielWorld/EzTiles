<?php

namespace DenielWorld\EzTiles\data;

use pocketmine\level\Position;

/** Data storage for any tile registered in EzTiles::init() */
class TileInfo{

    /** @var Position */
    private $pos;

    /** @var array */
    private $data;

    /** @var bool */
    private $scheduleUpdate;

    /** @var string */
    private $callable;

    /**
     * TileInfo constructor.
     * Warning: $data keys such as "x", "y", "z", or "id" should not be messed with since they save internal data of the tile
     * @param Position $pos Location in the level where the tile should be created
     * @param array $data Insert any save data in here like this: [key => value] Objects stored in this array will not be saved.
     * Arrays must contain only one type of data, which can only be bool or int. Bools will be converted to integer type...
     * ... since NBT cannot save bools, but I am unsure if it works properly as of now.
     * Keys should only be strings, because that is how NBT interprets them.
     * @param bool $scheduleUpdate Should the tile be updated over time? If yes, a $callable method string is required
     * @param string $callable Will only work if $scheduleUpdate is true, and is also required in that case
     */
    public function __construct(Position $pos, array $data = ["id" => "simpleTile"], bool $scheduleUpdate = false, string $callable = "")
    {
        $this->pos = $pos;
        $this->data = $data;
        $this->scheduleUpdate = $scheduleUpdate;
        $this->callable = $callable;
        if($callable !== ""){
            if(!$scheduleUpdate){
                $this->callable = "";
                throw new \InvalidArgumentException("Cannot pass a callable method string if tile update is not scheduled");
            }
        }
        if($scheduleUpdate){
            if($callable == ""){
                $this->scheduleUpdate = false;
                throw new \InvalidArgumentException("Cannot update tile without a callable method string");
            }
        }
    }

    /**
     * @return Position position where the tile will be placed
     */
    public function getPosition() : Position{
        return $this->pos;
    }

    /**
     * @return array array of data to be saved in the tile
     */
    public function getData() : array{
        return $this->data;
    }

    /**
     * Used for getting specific data pieces from the data array. Returns null if the piece wasn't found.
     * @param string $key
     * @return mixed
     */
    public function getDataPiece(string $key){
        if(isset($this->data[$key])) return $this->data[$key];
        return null;
    }

    /**
     * @return bool if the tile should be updated from time to time
     */
    public function isUpdateScheduled() : bool{
        return $this->scheduleUpdate;
    }

    /**
     * @return string string which refers to a static function in the registrant, which will be called on every update the tile has
     */
    public function getCallable() : string{
        return $this->callable;
    }

}
