<?php
namespace Qii\Library;

class RateLimit
{
    /**
     *
     * @var string
     */
    public $name;
    /**
     *
     * @var int
     */
    public $maxRequests;
    /**
     *
     * @var int
     */
    public $period;
    /**
     *
     * @var int
     */
    public $ttl;
    /**
     *
     * @var Adapter
     */
    private $adapter;

	public function __construct($name, $maxRequests, $period, $adapter)
	{
		$this->name = $name;
		$this->maxRequests = $maxRequests;
        $this->period = $period;
        $this->ttl = $this->period;
		$this->adapter = $adapter;
	}

	 /**
     * Rate Limiting
     * http://stackoverflow.com/a/668327/670662
     * @param string $id
     * @param float $use
     * @return boolean
     */
    public function check($id, $use = 1.0)
    {
        $rate = $this->maxRequests / $this->period;
        $tKey = $this->keyTime($id);
        $aKey = $this->keyAllow($id);
        if ($this->adapter->exists($tKey)) {
            $cTime = time();
            $timePassed = $cTime - $this->adapter->get($tKey);
            $this->adapter->set($tKey, $cTime, ['life_time' => $this->ttl]);
            $allow = $this->adapter->get($aKey);
            $allow += $timePassed * $rate;
            if ($allow > $this->maxRequests) {
                $allow = $this->maxRequests;
            }
            if ($allow < $use) {
                $this->adapter->set($aKey, $allow, ['life_time' => $this->ttl]);
                return 0;
            } else {
                $this->adapter->set($aKey, $allow - $use, ['life_time' => $this->ttl]);
                return (int) ceil($allow);
            }
        } else {
            $this->adapter->set($tKey, time(), ['life_time' => $this->ttl]);
            $this->adapter->set($aKey, $this->maxRequests - $use, ['life_time' => $this->ttl]);
            return $this->maxRequests;
        }
    }

    public function getAllow($id)
    {
        $this->check($id, 0.0);
        $aKey = $this->keyAllow($id);
        if (!$this->adapter->exists($aKey)) {
            return $this->maxRequests;
        } else {
            return max(0, floor($this->adapter->get($aKey)));
        }
    }
    /**
     * Purge rate limit record for $id
     * @param string $id
     */
    public function purge($id)
    {
        $this->adapter->del($this->keyTime($id));
        $this->adapter->del($this->keyAllow($id));
    }

    public function keyTime($id)
    {
        return $this->name . ":" . $id . ":time";
    }

    public function keyAllow($id)
    {
        return $this->name . ":" . $id . ":allow";
    }
}