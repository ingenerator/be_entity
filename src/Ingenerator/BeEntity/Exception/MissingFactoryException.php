<?php
/**
 * Thrown when the FactoryManager cannot locate a factory class for the specified entity type
 *
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2013 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\BeEntity\Exception;

use Ingenerator\BeEntity\Exception;

/**
 * Thrown when the FactoryManager cannot locate a factory class for the specified entity type
 *
 * @package Ingenerator\BeEntity\Exception
 */
class MissingFactoryException extends Exception {}