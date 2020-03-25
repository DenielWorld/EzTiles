<?php

namespace DenielWorld\EzTiles\tile;

use DenielWorld\EzTiles\data\TileInfo;
use DenielWorld\EzTiles\EzTiles;
use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\tile\Spawnable;

//TODO: Organize Ids into order properly
//TODO: Afaik custom tiles should extend Tile rather than Spawnable, because Spawnable is used for vanilla stuff
class SimpleTile extends Spawnable{

    /** @var int */
    public const TAG_INT = 0, TAG_BOOL = 1, TAG_INVALID = 2, TAG_STRING = 3, TAG_SHORT = 4, TAG_LONG = 5, TAG_DOUBLE = 6, TAG_FLOAT = 7, TAG_INT_ARRAY = 8, TAG_BOOL_ARRAY = 9;

    /** @var CompoundTag */
    private $nbt;

    /** @var string */
    private $callable;

    /**
     * SimpleTile constructor.
     * @param Level $level
     * @param TileInfo|CompoundTag $tileInfo
     */
    public function __construct(Level $level, $tileInfo)
    {
        if($tileInfo instanceof TileInfo) {
            $nbt = new CompoundTag();

            $pos = $tileInfo->getPosition();
            $nbt->setInt(self::TAG_X, $pos->x);
            $nbt->setInt(self::TAG_Y, $pos->y);
            $nbt->setInt(self::TAG_Z, $pos->z);

            //TODO: Figure out if this even matters, since the "id" might be internal stuff that gets overwritten anyways
            if ($tileInfo->getDataPiece("id") == null) $nbt->setString("id", "simpleTile");
            $nbt->setString("callable", $tileInfo->getCallable());
            $this->parseToNbt($tileInfo->getData(), $nbt);

            parent::__construct($level, $nbt);

            if ($tileInfo->isUpdateScheduled() and $this->callable !== "") {
                $this->scheduleUpdate();
            }
        }
        //Don't mind this, it is for tile recreation after restart which is no longer handled by you.
        if($tileInfo instanceof CompoundTag) {
            parent::__construct($level, $tileInfo);

            //Hopefully this is executed after the data was read
            if ($this->callable !== "") {
                $this->scheduleUpdate();
            }
        }
    }

    /**
     * Used to convert received data into a CompoundTag
     * @param array $data
     * @param CompoundTag $nbt
     */
    public function parseToNbt(array $data, CompoundTag $nbt) : void{
        //TODO: Turn this into one big switch($this->>getTagType($value))
        foreach ($data as $key => $value){
            if(is_int($key)) $key = (string)$key;
            if(is_object($value) or is_callable($value)) {
                throw new \InvalidArgumentException("Callable and objects cannot be saved to NBT");
            }
            elseif(is_int($value)) $nbt->setInt($key, $value);
            elseif(is_string($value)) $nbt->setString($key, $value);
            elseif(is_bool($value)) $nbt->setByte($key, (int)$value);
            elseif(is_long($value)) $nbt->setLong($key, $value);
            elseif(is_double($value)) $nbt->setDouble($key, $value);
            elseif(is_float($value)) $nbt->setFloat($key, $value);
            elseif(!is_array($value)) $nbt->setShort($key, $value);
            elseif(is_array($value)){
                switch ($this->getArrayType($value)){
                    case self::TAG_INVALID:
                        throw new \InvalidArgumentException("Arrays can only contain one type of data, bool or int only");
                        break;
                    case self::TAG_BOOL_ARRAY:
                        $newValue = [];
                        foreach ($value as $bool){
                            $newValue[] = (int)$bool;
                        }
                        $nbt->setByteArray($key, (string)$newValue);//TODO: Find out why a byte array accepts string as value, as of now it is uncertain what kind of behavior these arrays will have
                        break;
                    case self::TAG_INT_ARRAY:
                        $nbt->setIntArray($key, $value);
                }
            }
        }
    }

    /**
     * Retrieves data from the saved NBT
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getData(string $key, $default = null){
        $data = $this->nbt->getTag($key);
        if($data == null) return $default;
        return $data;
    }

    /**
     * Sets data to the saved NBT
     * @param string $key
     * @param $value
     */
    public function setData(string $key, $value) : void{
        switch ($this->getTagType($value)){
            case self::TAG_INT:
                $tag = new IntTag($key, $value);
                break;
            case self::TAG_BOOL:
                $tag = new ByteTag($key, (int)$value);
                break;
            case self::TAG_STRING:
                $tag = new StringTag($key, $value);
                break;
            case self::TAG_SHORT:
                $tag = new ShortTag($key, $value);
                break;
            case self::TAG_LONG:
                $tag = new LongTag($key, $value);
                break;
            case self::TAG_DOUBLE:
                $tag = new DoubleTag($key, $value);
                break;
            case self::TAG_FLOAT:
                $tag = new FloatTag($key, $value);
                break;
            case self::TAG_INT_ARRAY:
                $tag = new IntArrayTag($key, $value);
                break;
            case self::TAG_BOOL_ARRAY:
                $newValue = [];
                foreach ($value as $bool){
                    $newValue[] = (int)$bool;
                }
                $tag = new ByteArrayTag($key, (string)$value);
                break;
            case self::TAG_INVALID:
                throw new \InvalidArgumentException("Invalid tag provided");
                break;
        }
        if(isset($tag)) $this->nbt->setTag($tag);
    }

    /**
     * Executed upon tile update, if it is scheduled & if a callable method string is provided
     * @return bool
     * @throws \ReflectionException
     */
    public function onUpdate(): bool
    {
        if($this->callable !== "") {
            $reflection = new \ReflectionClass(EzTiles::getRegistrant());
            $className = $reflection->getName();
            return call_user_func(array($className, $this->callable), $this);
        }
        return false;
    }

    /**
     * @param CompoundTag $nbt
     */
    public function writeSaveData(CompoundTag $nbt): void
    {
        foreach($this->nbt->getValue() as $tag){
            $nbt->setTag($tag);
        }
        $nbt->setString("callable", $this->callable);
    }

    /**
     * @param CompoundTag $nbt
     */
    public function readSaveData(CompoundTag $nbt): void
    {
        $this->callable = $nbt->getString("callable", "");
        $this->nbt = $nbt;
    }

    /**
     * @param CompoundTag $nbt
     */
    public function addAdditionalSpawnData(CompoundTag $nbt): void
    {
        foreach($this->nbt->getValue() as $tag){
            $nbt->setTag($tag);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return int Id containing type of var, so it is properly converted into tag
     * These Ids are not used anywhere else :v
     */
    public function getTagType($value) : int{
        //TODO: Use switch()
        if(is_object($value) or is_callable($value)) {
            throw new \InvalidArgumentException("Callable and objects cannot be saved to NBT");
        }
        elseif(is_int($value)) return self::TAG_INT;
        elseif(is_string($value)) return self::TAG_STRING;
        elseif(is_bool($value)) return self::TAG_BOOL;
        elseif(is_long($value)) return self::TAG_LONG;
        elseif(is_double($value)) return self::TAG_DOUBLE;
        elseif(is_float($value)) return self::TAG_FLOAT;
        //Since there is no method is_short, this type of tag will remain as the last possibility, but before is_array to avoid TAG_INVALID
        elseif(!is_array($value)) return self::TAG_SHORT;
        elseif(is_array($value)) return $this->getArrayType($value);
        return self::TAG_INVALID;
    }

    /**
     * Used to check for the array type of data, to determine if it is allowed or not
     * @param array $array
     * @return int 0 - int, 1 - bool, 2 - invalid
     */
    public function getArrayType(array $array) : int{
        $types = [];
        foreach ($array as $value){
            if(is_int($value)){
                if(array_search("int", $types) == false){
                    $types[] = "int";
                }
            }
            elseif(is_bool($value)){
                if(array_search("bool", $types) == false){
                    $types[] = "bool";
                }
            }
            else {
                if(array_search("invalid", $types) == false) {
                    $types[] = "invalid";
                }
            }
        }
        if(count($types) === 1) {
            if (array_search("bool", $types) !== false) {
                return self::TAG_BOOL_ARRAY;
            } elseif (array_search("int", $types) !== false) {
                return self::TAG_INT_ARRAY;
            }
        }
        return self::TAG_INVALID;
    }

}
