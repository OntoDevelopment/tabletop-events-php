<?php

namespace TabletopEvents;

class Grid {

    public $days = [];
    public $rooms = [];
    public $spaces = [];

    public function __construct($SDK) {
        foreach ($SDK->getRooms()->items as $Room) {
            $Room->spaces = [];
            foreach ($SDK->getRoomSpaces($Room->id)->items as $Space) {
                $Room->spaces[$Space->id] = $Space;
                //index
                $this->spaces[$Space->id] = $Space;
            }
            uasort($Room->spaces, [$this, 'sortSpaces']);
            $this->rooms[$Room->id] = $Room;
        }

        uasort($this->spaces, [$this, 'sortSpaces']);

        foreach ($SDK->getDays()->items as $Day) {
            $Day->spaces = [];
            foreach ($SDK->getDaySlots($Day->id)->items as $Slot) {
                $Slot->colspan = 1;
                if ($Slot->event_id) {
                    $Slot->Event = $SDK->getEvent($Slot->event_id);
                } else {
                    $Slot->Event = false;
                }
                if (!isset($Day->spaces[$Slot->space_id])) {
                    $Day->spaces[$Slot->space_id] = $this->spaces[$Slot->space_id];
                    $Day->spaces[$Slot->space_id]->slots = [];
                }
                $Day->spaces[$Slot->space_id]->slots[$Slot->id] = $Slot;
            }
            $Day->parts = $SDK->getDayParts($Day->id)->items;
            $Day->parts_count = count($Day->parts);
            $this->days[$Day->id] = $Day;
        }
    }

    public function days() {
        $days = [];
        foreach ($this->days as $Day) {
            $days[$Day->id] = $this->day($Day->id);
        }
        return $days;
    }

    public function day($day_id) {
        $Day = $this->days[$day_id];
        $Day->rooms = [];
        foreach ($Day->spaces as $Space) {
            $Room = $this->rooms[$Space->room_id];
            foreach($Room->spaces as $RSpace){
                $event_id = 0;
                $event_slot_id = false;
                foreach($RSpace->slots as $Slot){
                    if($event_slot_id && $event_id && $Slot->event_id === $event_id){
                        
                        $RSpace->slots[$event_slot_id]->colspan++;
                        
                        unset($RSpace->slots[$Slot->id]);
                        
                    } else {
                        $event_id = $Slot->event_id;
                        $event_slot_id = $Slot->id;
                    }
                }
                $Room->spaces[$RSpace->id] = $RSpace;
            }
            $Day->rooms[$Space->room_id] = $Room;
        }
        return $Day;
    }

    public function sortSpaces($a, $b) {
        return strcmp($a->name, $b->name);
    }

}