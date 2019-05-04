<?php

namespace TabletopEvents;

use \Curl\Curl;

class SDK {

    public $_cache = [];
    private $_curl;
    private $_baseURL = 'https://tabletop.events/api/';
    public $convention_id;
    public $api_public_key = false;
    public $skip_security = true;

    public function __construct($convention_id, $api_public_key = false) {
        $this->_curl = new Curl();
        $this->_curl->setDefaultDecoder('json');

        $this->convention_id = $convention_id;
        $this->api_public_key = $api_public_key;
        
        $this->public = new PublicSDK($this);
    }

    public $public;

    public function get($endpoint, $query = []) {
        $query['_items_per_page'] = 100;
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

class PublicSDK {
    private $SDK;
    
    public function __construct($SDK) {
        $this->SDK = $SDK;
    }

    public function getConvention() {
        return $this->SDK->get("convention/{$this->SDK->convention_id}");
    }
    
    public function getBadge($badge_id){
        return $this->SDK->get("convention/{$badge_id}");
    }
    
    public function getBadgeTickets($badge_id){
        return $this->SDK->get("convention/{$badge_id}/tickets");
    }

    public function getDays() {
        return $this->SDK->get("convention/{$this->SDK->convention_id}/days");
    }

    public function getDayParts($day_id) {
        $dayparts = $this->SDK->get("conventionday/{$day_id}/dayparts");
        foreach ($dayparts->items as $key => $DayPart) {
            $dayparts->items[$key]->day_id = $day_id;
        }
        return $dayparts;
    }

    public function getDaySlots($day_id) {
        return $this->SDK->get("conventionday/{$day_id}/slots");
    }

    public function getEventGrid() {
        return new Grid($this);
    }

    public function getEvent($event_id) {
        return $this->SDK->get("event/{$event_id}");
    }

    public function getRoom($room_id) {
        return $this->SDK->get("room/{$room_id}");
    }
    
    public function getRoomEvents($room_id) {
        return $this->SDK->get("room/{$room_id}/events");
    }

    public function getRoomSlots($room_id) {
        return $this->SDK->get("room/{$room_id}/slots");
    }

    public function getRoomSpaces($room_id) {
        return $this->SDK->get("room/{$room_id}/spaces");
    }

    public function getRooms() {
        return $this->SDK->get("convention/{$this->SDK->convention_id}/rooms");
    }
    
    public function findBadge($last_name){
        return $this->SDK->get("convention/{$this->SDK->convention_id}/badges", ['query' => $last_name]);
    }
}