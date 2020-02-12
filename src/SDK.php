<?php
/**
 * This class provides simple access to the tabletop.events API public endpoints.
 * In the future it may include the endpoints requiring authorization (API Key).
 * @license https://github.com/OntoDevelopment/tabletop-events-php/blob/master/LICENSE
 * @author Brian Wendt
 * @access public
 * @package tabletop-events-php
 * @see https://github.com/OntoDevelopment/tabletop-events-php
 */

namespace TabletopEvents;

use \Curl\Curl;

class SDK {

    /**
     * @var string
     */
    public $convention_id;

    /*
     * Skips host and peer SSL verification.
     * Seting to `false` may not work on some server configurations.
     * @var boolean
     */
    public $skip_security = true;
    
    /**
     * @var object[]
     */
    public $_stored = [];
    
    /**
     * @var \TabletopEvents\PublicSDK
     */
    public $public;
    
    /**
     * @var \Curl\Curl 
     */
    private $_curl;
    
    /**
     * Base URL for the API endpoints
     * @var string
     */
    private $_baseURL = 'https://tabletop.events/api/';

    /**
     * 
     * @param string $convention_id The tabletop.events convention id
     */
    public function __construct($convention_id) {
        $this->_curl = new Curl();
        $this->_curl->setDefaultDecoder('json');
        $this->convention_id = $convention_id;
        $this->public = new PublicSDK($this);
    }

    /**
     * 
     * @param type $option
     * @param type $value
     */
    public function setOpt($option, $value) {
        $this->_curl->setOpt($option, $value);
    }

    /**
     * This method should not be used directly in an APP.
     * @param string $endpoint API endpoint
     * @param mixed[] $query
     * @return object API result
     */
    public function get($endpoint, $query = []) {
        $query['_items_per_page'] = 100;
        $cache_key = $endpoint . '?' . http_build_query($query);
        if (isset($this->_stored[$cache_key])) {
            //if the endpoint has been previously fetched, fetch it from the store
            return $this->_stored[$cache_key];
        }
        $response = $this->_get($endpoint, $query);

        $result = $response->result;
        if (isset($result->paging) && $result->paging->total_pages > 1) {
            //if more than one page, fetch all pages.
            $page_number = 1;
            while ($page_number < $result->paging->total_pages) {
                $page_number++;
                $query['_page_number'] = $page_number;
                $result->items = array_merge($result->items, $this->_get($endpoint, $query)->result->items);
            }
        }
        //record the resutls of the endpoint for reuse
        $this->_stored[$cache_key] = $result;
        return $result;
    }

    /**
     * 
     * @param type $endpoint
     * @param type $query
     * @return type
     */
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

}

class PublicSDK {

    /**
     * @var \TabletopEvents\SDK
     */
    private $SDK;

    /**
     * @param \TabletopEvents\SDK $SDK instance of the SDK by reference
     */
    public function __construct(&$SDK) {
        $this->SDK = $SDK;
    }

    /**
     * @return object endpoint results
     */
    public function getConvention() {
        return $this->SDK->get("convention/{$this->SDK->convention_id}");
    }

    /**
     * @param string $badge_id Convention Badge ID
     * @return object endpoint results
     */
    public function getBadge($badge_id) {
        return $this->SDK->get("convention/{$badge_id}");
    }

    /**
     * @param string $badge_id Convention Badge ID
     * @return object endpoint results
     */
    public function getBadgeTickets($badge_id) {
        return $this->SDK->get("convention/{$badge_id}/tickets");
    }

    /**
     * @return object endpoint results
     */
    public function getDays() {
        return $this->SDK->get("convention/{$this->SDK->convention_id}/days", ['_order_by' => 'start_date']);
    }

    /**
     * @param sting $day_id Convention Day ID
     * @return object endpoint results
     */
    public function getDayParts($day_id) {
        $dayparts = $this->SDK->get("conventionday/{$day_id}/dayparts");
        foreach ($dayparts->items as $key => $DayPart) {
            $dayparts->items[$key]->day_id = $day_id;
        }
        return $dayparts;
    }

    /**
     * @param sting $day_id Convention Day ID
     * @return object endpoint results
     */
    public function getDaySlots($day_id) {
        return $this->SDK->get("conventionday/{$day_id}/slots");
    }

    /**
     * @return object endpoint results
     */
    public function getEventGrid() {
        return new Grid($this);
    }

    /**
     * @param string $event_id Convention Event ID
     * @return object endpoint results
     */
    public function getEvent($event_id) {
        return $this->SDK->get("event/{$event_id}");
    }

    /**
     * @param string $library_id Library ID
     * @return object endpoint results
     */
    public function getLibrary($library_id) {
        return $this->SDK->get("library/{$library_id}");
    }
    
    /**
     * @param string $library_id Library ID
     * @return object endpoint results
     */
    public function getLibraryGames($library_id) {
        return $this->SDK->get("library/{$library_id}/librarygames");
    }

    /**
     * @param string $room_id Convention Room ID
     * @return object endpoint results
     */
    public function getRoom($room_id) {
        return $this->SDK->get("room/{$room_id}");
    }

    /**
     * @param string $room_id Convention Room ID
     * @return object endpoint results
     */
    public function getRoomEvents($room_id) {
        return $this->SDK->get("room/{$room_id}/events");
    }

    /**
     * @param string $room_id Convention Room ID
     * @return object endpoint results
     */
    public function getRoomSlots($room_id) {
        return $this->SDK->get("room/{$room_id}/slots");
    }

    /**
     * @param string $room_id Convention Room ID
     * @return object endpoint results
     */
    public function getRoomSpaces($room_id) {
        return $this->SDK->get("room/{$room_id}/spaces");
    }

    /**
     * @return object endpoint results
     */
    public function getRooms() {
        return $this->SDK->get("convention/{$this->SDK->convention_id}/rooms");
    }

    /**
     * This method isn't fully functional
     * @return string $last_name Last name on Convention Badge
     * @return object endpoint results
     */
    public function findBadge($last_name) {
        return $this->SDK->get("convention/{$this->SDK->convention_id}/badges", ['query' => $last_name]);
    }

}
