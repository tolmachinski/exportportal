<?php

declare(strict_types=1);

use TinyMVC_PDO as ConnectionHandler;

/***
 * Name:       TinyMVC
 * About:      An MVC application framework for PHP
 * Copyright:  (C) 2007-2008 Monte Ohrt, All rights reserved.
 * Author:     Monte Ohrt, monte [at] ohrt [dot] com
 * License:    LGPL, see included license file
 ***/

// ------------------------------------------------------------------------

/**
 * TinyMVC_Model.
 *
 * @author		Monte Ohrt
 */
class TinyMVC_Model
{
    /**
     * The DB handler instance
	 *
     * @var \TinyMVC_PDO
     */
    public $db;

    /**
     * Create the model instance
     */
    public function __construct(ConnectionHandler $connectionHandler)
    {
        $this->db = $connectionHandler;
    }
}
