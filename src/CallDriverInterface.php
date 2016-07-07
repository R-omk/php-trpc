<?php

namespace Romkart\Trpc;

/**
 * 
 *
 * @author r-omk
 */
interface CallDriverInterface {

  public function call($funcname, $args = NULL);
}
