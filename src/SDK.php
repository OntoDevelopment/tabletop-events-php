<?php

namespace TabletopEvents;

use \Curl\Curl;

class SDK {

    public $_cache = [];
    private $_curl;
    private $_baseURL = 'https://tabletop.events/api/';
    public $convention_id;
    public $skip_security = true;
    public $_items_per_page = 100;

    public function __construct($convention_id) {
        $this->_curl = new Curl();
        $this->_curl->setDefaultDecoder('json');

        $this->convention_id = $convention_id;
    }

    public function getConvention() {
        return $this->get("convention/{$this->convention_id}");
    }

    public function getDays() {
        return $this->get("convention/{$this->convention_id}/days");
    }

    public function getDayParts($day_id) {
        $dayparts = $this->get("conventionday/{$day_id}/dayparts");
        foreach ($dayparts->items as $key => $DayPart) {
            $dayparts->items[$key]->day_id = $day_id;
        }
        return $dayparts;
    }

    public function getDaySlots($day_id) {
        return $this->get("conventionday/{$day_id}/slots");
    }

    public function getEventGrid() {
        return new Grid($this);
    }

    public function getEvent($event_id) {
        return $this->get("event/{$event_id}");
    }

    public function getRoom($room_id) {
        return $this->get("room/{$room_id}");
    }

    public function getRoomSlots($room_id) {
        return $this->get("room/{$room_id}/slots");
    }

    public function getRoomSpaces($room_id) {
        return $this->get("room/{$room_id}/spaces");
    }

    public function getRooms() {
        return $this->get("convention/{$this->convention_id}/rooms");
    }

    private function get($endpoint, $query = [], $include = []) {
        $query['_items_per_page'] = $this->_items_per_page;
        $cache_key = $endpoint . '?' . http_build_query($query);
        if (isset($this->_cache[$cache_key])) {
            return $this->_cache[$cache_key];
        }
        $response = $this->_get($endpoint, $query);

        $result = $response->result;
        if (isset($result->paging) && $result->paging->total_pages > 1) {

            $page_number = 1;
            while ($page_number < $result->paging->total_pages) {
                $page_number++;
                //echo "page $page_number for $cache_key\n";
                $query['_page_number'] = $page_number;
                $result->items = array_merge($result->items, $this->_get($endpoint, $query)->result->items);
            }
        }

        $this->writeCache($cache_key, $result);
        return $result;
    }

    private function _get($endpoint, $query = []) {
        if ($this->skip_security) {
            $this->_curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
            $this->_curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        }
        $this->_curl->get($this->_baseURL . $endpoint, $query);
        if ($this->_curl->error) {
            return ["errorCode" => $this->_curl->errorCode, "errorMessage" => $this->_curl->errorMessage];
        }
        return $this->_curl->response;
    }

    private function writeCache($key, $value) {
        $this->_cache[$key] = $value;
    }

}