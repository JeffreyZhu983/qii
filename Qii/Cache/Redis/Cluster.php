<?php
namespace Qii\Cache\Redis;
/**
 * Credis, a Redis interface for the modest
 *
 * @author Justin Poliey <jdp34@njit.edu>
 * @copyright 2009 Justin Poliey <jdp34@njit.edu>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Credis
 */

/**
 * A generalized \Qii\Cache\Redis\Client interface for a cluster of Redis servers
 *
 * @deprecated
 */
class Cluster
{
  /**
   * Collection of \Qii\Cache\Redis\Client objects attached to Redis servers
   * @var \Qii\Cache\Redis\Client[]
   */
  protected $clients;
  /**
   * If a server is set as master, all write commands go to that one
   * @var \Qii\Cache\Redis\Client
   */
  protected $masterClient;
  /**
   * Aliases of \Qii\Cache\Redis\Client objects attached to Redis servers, used to route commands to specific servers
   * @see Credis_Cluster::to
   * @var array
   */
  protected $aliases;
  
  /**
   * Hash ring of Redis server nodes
   * @var array
   */
  protected $ring;
  
  /**
   * Individual nodes of pointers to Redis servers on the hash ring
   * @var array
   */
  protected $nodes;
  
  /**
   * The commands that are not subject to hashing
   * @var array
   * @access protected
   */
  protected $dont_hash;

  /**
   * Currently working cluster-wide database number.
   * @var int
   */
  protected $selectedDb = 0;

  /**
   * Creates an interface to a cluster of Redis servers
   * Each server should be in the format:
   *  array(
   *   'host' => hostname,
   *   'port' => port,
   *   'db' => db,
   *   'password' => password,
   *   'timeout' => timeout,
   *   'alias' => alias,
   *   'persistent' => persistence_identifier,
   *   'master' => master
   *   'write_only'=> true/false
   * )
   *
   * @param array $servers The Redis servers in the cluster.
   * @param int $replicas
   * @param bool $standAlone
   * @throws CredisException
   */
  public function __construct($servers, $replicas = 128, $standAlone = false)
  {
    $this->clients = array();
    $this->masterClient = null;
    $this->aliases = array();
    $this->ring = array();
    $this->replicas = (int)$replicas;
    $client = null;
    foreach ($servers as $server)
    {
      if(is_array($server)){
          $client = new \Qii\Cache\Redis\Client(
            $server['host'],
            $server['port'],
            isset($server['timeout']) ? $server['timeout'] : 2.5,
            isset($server['persistent']) ? $server['persistent'] : '',
            isset($server['db']) ? $server['db'] : 0,
            isset($server['password']) ? $server['password'] : null
          );
          if (isset($server['alias'])) {
            $this->aliases[$server['alias']] = $client;
          }
          if(isset($server['master']) && $server['master'] === true){
            $this->masterClient = $client;
            if(isset($server['write_only']) && $server['write_only'] === true){
                continue;
            }
          }
      } elseif($server instanceof \Qii\Cache\Redis\Client){
        $client = $server;
      } else {
          throw new CredisException('Server should either be an array or an instance of \Qii\Cache\Redis\Client');
      }
      if($standAlone) {
          $client->forceStandalone();
      }
      $this->clients[] = $client;
      for ($replica = 0; $replica <= $this->replicas; $replica++) {
          $md5num = hexdec(substr(md5($client->getHost().':'.$client->getPort().'-'.$replica),0,7));
          $this->ring[$md5num] = count($this->clients)-1;
      }
    }
    ksort($this->ring, SORT_NUMERIC);
    $this->nodes = array_keys($this->ring);
    $this->dont_hash = array_flip(array(
      'RANDOMKEY', 'DBSIZE', 'PIPELINE', 'EXEC',
      'SELECT',    'MOVE',    'FLUSHDB',  'FLUSHALL',
      'SAVE',      'BGSAVE',  'LASTSAVE', 'SHUTDOWN',
      'INFO',      'MONITOR', 'SLAVEOF'
    ));
    if($this->masterClient !== null && count($this->clients()) == 0){
        $this->clients[] = $this->masterClient;
        for ($replica = 0; $replica <= $this->replicas; $replica++) {
            $md5num = hexdec(substr(md5($this->masterClient->getHost().':'.$this->masterClient->getHost().'-'.$replica),0,7));
            $this->ring[$md5num] = count($this->clients)-1;
        }
        $this->nodes = array_keys($this->ring);
    }
  }

  /**
   * @param \Qii\Cache\Redis\Client $masterClient
   * @param bool $writeOnly
   * @return Credis_Cluster
   */
  public function setMasterClient(\Qii\Cache\Redis\Client $masterClient, $writeOnly=false)
  {
    if(!$masterClient instanceof \Qii\Cache\Redis\Client){
        throw new CredisException('Master client should be an instance of \Qii\Cache\Redis\Client');
    }
    $this->masterClient = $masterClient;
    if (!isset($this->aliases['master'])) {
        $this->aliases['master'] = $masterClient;
    }
    if(!$writeOnly){
        $this->clients[] = $this->masterClient;
        for ($replica = 0; $replica <= $this->replicas; $replica++) {
            $md5num = hexdec(substr(md5($this->masterClient->getHost().':'.$this->masterClient->getHost().'-'.$replica),0,7));
            $this->ring[$md5num] = count($this->clients)-1;
        }
        $this->nodes = array_keys($this->ring);
    }
    return $this;
  }
  /**
   * Get a client by index or alias.
   *
   * @param string|int $alias
   * @throws CredisException
   * @return \Qii\Cache\Redis\Client
   */
  public function client($alias)
  {
    if (is_int($alias) && isset($this->clients[$alias])) {
      return $this->clients[$alias];
    }
    else if (isset($this->aliases[$alias])) {
      return $this->aliases[$alias];
    }
    throw new CredisException("Client $alias does not exist.");
  }

  /**
   * Get an array of all clients
   *
   * @return array|\Qii\Cache\Redis\Client[]
   */
  public function clients()
  {
    return $this->clients;
  }

  /**
   * Execute a command on all clients
   *
   * @return array
   */
  public function all()
  {
    $args = func_get_args();
    $name = array_shift($args);
    $results = array();
    foreach($this->clients as $client) {
      $results[] = call_user_func_array([$client, $name], $args);
    }
    return $results;
  }

  /**
   * Get the client that the key would hash to.
   *
   * @param string $key
   * @return \\Qii\Cache\Redis\Client
   */
  public function byHash($key)
  {
    return $this->clients[$this->hash($key)];
  }

  /**
   * @param int $index
   * @return void
   */
  public function select($index)
  {
      $this->selectedDb = (int) $index;
  }

  /**
   * Execute a Redis command on the cluster with automatic consistent hashing and read/write splitting
   *
   * @param string $name
   * @param array $args
   * @return mixed
   */
  public function __call($name, $args)
  {
    if($this->masterClient !== null && !$this->isReadOnlyCommand($name)){
        $client = $this->masterClient;
    }elseif (count($this->clients()) == 1 || isset($this->dont_hash[strtoupper($name)]) || !isset($args[0])) {
      $client = $this->clients[0];
    }
    else {
      $client = $this->byHash($args[0]);
    }
    // Ensure that current client is working on the same database as expected.
    if ($client->getSelectedDb() != $this->selectedDb) {
      $client->select($this->selectedDb);
    }
    return call_user_func_array([$client, $name], $args);
  }

  /**
   * Get client index for a key by searching ring with binary search
   *
   * @param string $key The key to hash
   * @return int The index of the client object associated with the hash of the key
   */
  public function hash($key)
  {
    $needle = hexdec(substr(md5($key),0,7));
    $server = $min = 0;
    $max = count($this->nodes) - 1;
    while ($max >= $min) {
      $position = (int) (($min + $max) / 2);
      $server = $this->nodes[$position];
      if ($needle < $server) {
        $max = $position - 1;
      }
      else if ($needle > $server) {
        $min = $position + 1;
      }
      else {
        break;
      }
    }
    return $this->ring[$server];
  }

  public function isReadOnlyCommand($command)
  {
      $readOnlyCommands = array(
          'DBSIZE',
          'INFO',
          'MONITOR',
          'EXISTS',
          'TYPE',
          'KEYS',
          'SCAN',
          'RANDOMKEY',
          'TTL',
          'GET',
          'MGET',
          'SUBSTR',
          'STRLEN',
          'GETRANGE',
          'GETBIT',
          'LLEN',
          'LRANGE',
          'LINDEX',
          'SCARD',
          'SISMEMBER',
          'SINTER',
          'SUNION',
          'SDIFF',
          'SMEMBERS',
          'SSCAN',
          'SRANDMEMBER',
          'ZRANGE',
          'ZREVRANGE',
          'ZRANGEBYSCORE',
          'ZREVRANGEBYSCORE',
          'ZCARD',
          'ZSCORE',
          'ZCOUNT',
          'ZRANK',
          'ZREVRANK',
          'ZSCAN',
          'HGET',
          'HMGET',
          'HEXISTS',
          'HLEN',
          'HKEYS',
          'HVALS',
          'HGETALL',
          'HSCAN',
          'PING',
          'AUTH',
          'SELECT',
          'ECHO',
          'QUIT',
          'OBJECT',
          'BITCOUNT',
          'TIME',
          'SORT'
      );
      return in_array(strtoupper($command),$readOnlyCommands);
  }
}

