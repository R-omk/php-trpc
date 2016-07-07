<?php

namespace Romkart\Trpc;

/**
 * 
 *
 * @author r-omk
 */
class BasicCallDriver implements CallDriverInterface {

  /**
   * 
   * @param string $funcname  Name of a function or ClassNanme::MethodName
   * @param array $args
   * @return mixed
   * @throws \Romkart\Trpc\Exception\CallException
   */
  public function call($funcname, $args = []) {

    if (!is_array($args)) {
      throw new \Romkart\Trpc\Exception\CallException('Args must be an array');
    }

    if (strpos($funcname, '::') !== FALSE) {
      list($class, $method) = explode('::', $funcname);
    }
    else {
      $class = $funcname;
    }

    if (is_null($class) || empty($class)) {
      throw new \Romkart\Trpc\Exception\CallException('Bad function name');
    }

    if (!isset($method)) {

      $func = $class;

      if (!function_exists($func)) {
        throw new \Romkart\Trpc\Exception\CallException(printf('Function "%s" is not exists', $func));
      }

      try {

        return call_user_func_array($func, $args);
      } catch (Throwable $exc) {
        throw new \Romkart\Trpc\Exception\CallException($exc->getMesage(), 0, $exc);
      }
    }
    else {

      if (!class_exists($class)) {
        throw new \Romkart\Trpc\Exception\CallException(printf('Class "%s" is not exists', $class));
      }

      if (!method_exists($class, $method)) {
        throw new \Romkart\Trpc\Exception\CallException(printf('Method "%s" is not exists in class "%s"', $method, $class));
      }

      try {

        return call_user_func_array([$class, $method], $args);
      } catch (Throwable $exc) {
        throw new \Romkart\Trpc\Exception\CallException($exc->getMesage(), 0, $exc);
      }
    }
  }

}
