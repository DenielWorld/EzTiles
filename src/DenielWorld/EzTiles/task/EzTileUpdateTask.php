<?php

namespace DenielWorld\EzTiles\task;

use DenielWorld\EzTiles\tile\SimpleTile;
use pocketmine\scheduler\Task;

class EzTileUpdateTask extends Task {

    /** @var SimpleTile */
    private SimpleTile $tile;

    public function __construct(SimpleTile $tile)
    {
        $this->tile = $tile;
    }

    /**
     * @throws \ReflectionException
     */
    public function onRun(): void
    {
        if(!$this->tile->onUpdate()) {
            $this->getHandler()->cancel();
        }
    }

}