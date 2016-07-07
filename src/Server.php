<?php

namespace Romkart\Trpc;

use Romkart\Trpc\Exception\CallException;
use Romkart\Trpc\Exception\Exception;
use Tarantool\Client\Client;
use Tarantool\Client\Exception\Exception as TarantoolException;

/**
 * 
 *
 * @author r-omk
 */
class Server {

  const ERROR_PULL_RETRY = 901;

  /**
   *
   * @var Client
   */
  private $client;

  /**
   *
   * @var string
   */
  private $namespace;

  /**
   *
   * @var string
   */
  private $entry = 'trpc';

  /**
   *
   * @var CallDriverInterface
   */
  private $callDriver;
  private $maxEverLoops = 100;

  public function __construct(Client $client, string $namespace) {
    $this->client = $client;
    $this->namespace = $namespace;

    $this->setCallDriver(new BasicCallDriver());
  }

  public function setMaxEverLoops($maxEverLoops) {
    $this->maxEverLoops = $maxEverLoops;
  }

  /**
   * 
   * @param CallDriverInterface $callDriver
   */
  public function setCallDriver(CallDriverInterface $callDriver) {
    $this->callDriver = $callDriver;
  }

  /**
   * 
   * @param string $entry
   */
  public function setEntry(string $entry) {
    $this->entry = $entry;
  }

  private function pull() {

    $doretry = TRUE;
    while ($doretry) {
      $doretry = FALSE;

      try {

        $res = $this->client->call($this->entry . ':pull', [$this->namespace]);

        $res = $res->getData()[0];
        if (!isset($res[0])) {
          throw new Exception('pull id is not provided');
        }

        $id = $res[0];

        if (!isset($res[1])) {
          throw new Exception('function name is not provided');
        }

        $func = $res[1];
        $args = isset($res[2]) ? $res[2] : NULL;

        try {
          $ret = $this->callDriver->call($func, $args);

          $pushres = $this->client->call($this->entry . ":push", [$id, $ret]);
        } catch (CallException $exc) {
          $ret = $exc->getMessage();
          $pushres = $this->client->call($this->entry . ":pusherr", [$id, $ret]);
          $prev = $exc->getPrevious();
          if (isset($prev)) {
            throw $prev;
          }
        }
      } catch (TarantoolException $e) {

        $code = $e->getCode();
        if ($code == static::ERROR_PULL_RETRY) {
          $doretry = TRUE;
        }
        else {
          throw $e;
        }
      }
    }
  }

  public function Ever() {

    while ($this->maxEverLoops > 0) {
      $this->pull();

      --$this->maxEverLoops;
    }
  }

  public function Once() {
    $this->pull();
  }

}
