<?php

namespace DenielWorld\EzTiles\data;

use pocketmine\level\Position;

/** Data storage for any tile registered in EzTiles::init() */
class TileInfo{

    /** @var Position */
    public $pos;

    /** @var array */
    public $data;

    /** @var bool */
    public $scheduleUpdate;

    /** @var string */
    public $callable;

    /**
     * TileInfo constructor.
     * @param Position $pos Location in the level where the tile should be created
     * @param array $data Insert any save data in here like this: [key => value] Objects stored in this array will not be saved.
     * Arrays must contain only one type of data, which can only be bool or int
     * @param bool $scheduleUpdate Should the tile be updated over time? If yes, a $callable method string is required
     * @param string $callable Will only work if $scheduleUpdate is true, throws an exception otherwise
     */
    public function __construct(Position $pos, array $data = ["id" => "simpleTile"], bool $scheduleUpdate = false, ?string $callable = null)
    {
        $this->pos = $pos;
        $this->data = $data;
        $this->scheduleUpdate = $scheduleUpdate;

        if($callable !== null){
            if($scheduleUpdate){
                $this->callable = $callable;
            }
            else {
                $this->callable = null;
                throw new \InvalidArgumentException("Cannot pass a callable method string if tile update is not scheduled");
            }
        }
        if($scheduleUpdate){
            if($callable == null){
                $this->scheduleUpdate = false;
                throw new \InvalidArgumentException("Cannot update tile without a callable method string");
            }
        }
    }

}