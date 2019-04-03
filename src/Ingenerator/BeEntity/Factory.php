<?php
/**
 * Base entity factory class to be extended for each type of entity you wish to create
 *
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2013 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\BeEntity;

use Doctrine\ORM\EntityManager;
use Ingenerator\BeEntity\Exception\MissingEntityException;

/**
 * The basic entity factory class defines the interface and shared logic for all entity factories. To create your own
 * factories, name them to be the same as in your feature files and extend this class. For example, to use the step:
 *
 *     Given a User entity "foo@bar.com"
 *
 * You would define the factory Ingenerator\BeEntity\Factory\User.
 *
 * If you often create the same type of entity in different states you can create multiple factories - for example an
 * Administrator and User factory could both create a User instance but initialise its fields differently.
 *
 * @package Ingenerator\BeEntity
 */
abstract class Factory {

	/**
	 * @var EntityManager the Doctrine entity manager that will be used to find and persist entities.
	 */
	protected $entity_manager = NULL;

	/**
	 * @var FactoryManager the FactoryManager to use to create any referenced entities
	 */
	protected $factory_manager = NULL;

	/**
	 * Creates an instance and stores the entity manager
	 *
	 * @param EntityManager  $entity_manager  the Doctrine entity manager that will be used to find and persist entities
	 * @param FactoryManager $factory_manager the factory manager to use to create any additional required objects
	 *
	 * @return \Ingenerator\BeEntity\Factory
	 */
	public function __construct(EntityManager $entity_manager, FactoryManager $factory_manager)
	{
		$this->entity_manager = $entity_manager;
		$this->factory_manager = $factory_manager;
	}

	/**
	 * Shorthand method for calling locate followed by create - guarantees that an entity exists with the specified
	 * details whether or not it was already in the database. Only the specified fields will be updated.
	 *
	 * [!!] This method persists but does not flush the entity manager - in case you are updating multiple entities.
	 *
	 * @param string $identifier A unique identifier for this entity, which is mapped to any field by your factory class
	 * @param array  $fields     An array of fields and values where the default factory value should be overridden
	 *
	 * @return object An entity
	 */
	public function provide($identifier, $fields = array())
	{
		$entity = $this->locate($identifier, FALSE);

		if ($entity)
		{
			// Ensure values handles persisting the object
			$this->ensure_values($entity, $fields);
		}
		else
		{
			$entity = $this->create($identifier, $fields);
		}
		return $entity;
	}

	/**
	 * Locates an entity by identifier, and optionally returns NULL or throws an exception if it cannot be found.
	 *
	 * @param string $identifier A unique identifier for this entity, which is mapped to any field by your factory class
	 * @param bool   $required   If TRUE, will throw an exception if the entity does not exist
	 *
	 * @return object An entity, or NULL
	 * @throws Exception\MissingEntityException If the entity cannot be found and $required was TRUE
	 */
	public function locate($identifier, $required)
	{
		$entity = $this->_locate($identifier);
		if ($required AND ($entity === NULL))
		{
			throw new MissingEntityException(\get_class($this)." could not locate an entity for '$identifier'");
		}
		return $entity;
	}

	/**
	 * Creates an entity, setting the identifier field and optionally any other fields that should override the factory
	 * defaults.
	 *
	 * [!!] Entities will be persisted but not flushed in case you are creating multiple entities.
	 *
	 * @param string $identifier A unique identifier for this entity, which is mapped to any field by your factory class
	 * @param array  $fields     An array of fields and values where the default factory value should be overridden
	 *
	 * @return object an entity
	 */
	public function create($identifier, $fields = array())
	{
		$entity = $this->_create($identifier);
		$this->ensure_values($entity, $fields);
		return $entity;
	}

	/**
	 * Test if an entity exists and if its fields match the provided array (only fields specified in the array will be
	 * compared).
	 *
	 * @param string $identifier the identifier used to locate an entity
	 * @param array  $fields     the fields to check
	 *
	 * @return array|bool NULL if not found, TRUE if matches, array of differences if exists but doesn't match
	 */
	public function matches($identifier, $fields = array())
	{
		// Return NULL if the entity does not exist
		if ( ! $entity = $this->locate($identifier, FALSE))
		{
			return NULL;
		}

		// Compare fields and build a difference
		//@todo: Support underscored and camelCase getter names
		$diff = array();
		foreach ($fields as $field => $expected)
		{
			$getter = 'get_'.$field;
			$actual = $entity->$getter();
			if ($actual != $expected) {
				$diff[$field] = array('exp' => $expected, 'got' => $actual);
			}
		}

		if ($diff)
		{
			return $diff;
		}
		else
		{
			return TRUE;
		}
	}

	/**
	 * Sets fields to the expected values by calling their setters. Currently expects your entities to use underscored
	 * setters - for eg if you specify a `password` field in your feature file this method will call set_password.
	 * After setting fields, persists but does not flush the entity.
	 *
	 * @param object $entity The entity to set fields on
	 * @param array $fields  An array of field name => value to set
	 *
	 * @return void
	 */
	protected function ensure_values($entity, array $fields)
	{
		foreach ($fields as $field => $value)
		{
			//@todo: Support both underscored and camelCase setter names
			$setter = "set_$field";
			$entity->$setter($value);
		}
		$this->entity_manager->persist($entity);
	}

	/**
	 * Implement this method to return an entity or NULL for the specified identifier
	 *
	 * @param string $identifier A unique identifier for this entity, which is mapped to any field by your factory class
	 *
	 * @return object the entity, or NULL
	 */
	abstract protected function _locate($identifier);

	/**
	 * Implement this method to create an entity and set the identifier to the provided value
	 *
	 * @param string $identifier A unique identifier for this entity, which is mapped to any field by your factory class
	 *
	 * @return object the entity
	 */
	abstract protected function _create($identifier);

	/**
	 * Implement this method to delete all entities of the type managed by this factory.
	 *
	 * For example, you could just issue a DQL delete in this method.
	 *
	 * @return void
	 */
	abstract public function purge();

}