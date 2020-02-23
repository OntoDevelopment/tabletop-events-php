<?php

namespace TabletopEvents;


class EventsGrid
{

    public $slots = [];
    public $events = [];
    public $rooms = [];
    public $dayparts = [];
    public $days = [];

    public function __construct($SDK)
    {
        $slots = $SDK->public->getConventionSlots([
            'event_id' => '>0',
            '_order_by' => 'event.start_date',
            '_include_related_objects' => ['event', 'daypart', 'conventionday', 'space']
        ])->items;
        foreach ($slots as $Slot) {
            $this->slots[$Slot->id] = $Slot;
            $this->events[$Slot->event_id] = $Slot->event;
        }
        $dayparts = $SDK->public->getDayParts('start_date', [
            '_include_related_objects' => ['conventionday']
        ])->items;
        foreach ($dayparts as $daypart) {
            $this->dayparts[$daypart->id] = $daypart;
        }
        $this->days = $this->days();
        $this->rooms = $this->rooms();
        //var_dump($this->days); exit;
        foreach ($this->dayparts as $daypart) {
            if (!isset($this->days[$daypart->conventionday_id]->parts)) {
                $this->days[$daypart->conventionday_id]->parts = [];
            }
            $this->days[$daypart->conventionday_id]->parts[$daypart->id] = $daypart;
        }
    }

    private function days()
    {
        $days = [];
        foreach ($this->slots as $Slot) {
            if (empty($Slot->conventionday)) {
                continue;
            }
            $days[$Slot->conventionday->id] = $Slot->conventionday;
        }

        return $days;
    }

    private function rooms()
    {
        $rooms = [];
        foreach ($this->slots as $Slot) {
            if (!isset($rooms[$Slot->room_id])) {
                $rooms[$Slot->room_id] = (object) [
                    'id' => $Slot->room_id,
                    'name' => '',
                    'spaces' => [],
                    'slots' => []
                ];
            }
            $rooms[$Slot->room_id]->slots[] = $Slot->id;
            $rooms[$Slot->room_id]->spaces[$Slot->space_id] = $Slot->space;

            if (!isset($rooms[$Slot->room_id]->spaces[$Slot->space_id]->slots)) {
                $rooms[$Slot->room_id]->spaces[$Slot->space_id]->slots = [];
            }
            $rooms[$Slot->room_id]->spaces[$Slot->space_id]->slots[$Slot->id] = $Slot;

            if (!empty($Slot->event)) {
                $rooms[$Slot->room_id]->name = $Slot->event->room_name;
            }
        }
        return $rooms;
    }

    public function getSpaceSlots($day_id, $space_id)
    {
        $Day = $this->days[$day_id];
        $parts = $Day->parts;
        $previous = false;
        foreach ($Day->parts as $daypart_id => $Part) {
            $Part->slot = $this->getSlot($daypart_id, $space_id);
            if (isset($previous->slot->event->id) && isset($Part->slot->event->id) && $previous->slot->event->id == $Part->slot->event->id) {
                $parts[$previous->id]->colspan++;
                unset($parts[$daypart_id]);
            } else {
                $Part->colspan = 1;
                $previous = $Part;
                $parts[$daypart_id] = $Part;
            }
        }
        return $parts;
    }

    public function getSlot($daypart_id, $space_id)
    {
        foreach ($this->slots as $Slot) {
            if ($Slot->daypart_id == $daypart_id && $Slot->space_id == $space_id) {
                return $Slot;
            }
        }
        return false;
    }

    public function sortByName($a, $b)
    {
        return strcmp($a->name, $b->name);
    }

    public function timestamp($date)
    {
        $timestamp = new \DateTime($date, new \DateTimeZone('UTC'));
        if (get_option('timezone_string')) {
            $timestamp->setTimezone(new \DateTimeZone(get_option('timezone_string')));
        }
        return $timestamp;
    }
}
