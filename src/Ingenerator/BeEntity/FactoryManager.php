<?php
/**
 * Manages the creation of entity factories based on their type names
 *
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2013 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\BeEntity;

use Doctrine\ORM\EntityManager;
use Ingenerator\BeEntity\Exception\MissingFactoryException;

/**
 * The simple factory manager class maps the type name from your specification to an entity factory class - for example,
 * if your steps refer to a 'User' entity you would define an \Ingenerator\BeEntity\Factory\User class somewhere that
 * it could be loaded by an autoloader.
 *
 * @package Ingenerator\BeEntity
 */
class FactoryManager
{
	/**
	 * @var \Doctrine\ORM\EntityManager the entity manager to be passed to entity factories
	 */
	protected $entity_manager = NULL;

	/**
	 * Create an instance
	 *
	 * @param EntityManager $entity_manager the entity manager to pass to entity factories
	 *
	 * @return FactoryManager
	 */
	public function __construct(EntityManager $entity_manager)
	{
		$this->entity_manager = $entity_manager;
	}

	/**
	 * Creates a new factory for the specified entity type. You can overload this method to implement more complex
	 * type mapping if required.
	 *
	 * @param string $type the human-readable entity type - for example, User for an Ingenerator\BeEntity\Factory\User
	 *
	 * @return \Ingenerator\BeEntity\Factory
	 * @throws Exception\MissingFactoryException if there is no defined class for this type
	 */
	public function create_factory($type)
    {
	    // Check the class exists
	    $class = "Ingenerator\\BeEntity\\Factory\\$type";
	    if ( ! \class_exists($class))
	    {
		    throw new MissingFactoryException("The entity factory class '$class' is not defined (or cannot be found)");
	    }

	    // If the class exists, create an instance and inject the entity manager
	    $factory = new $class($this->entity_manager, $this);
	    return $factory;
    }
}