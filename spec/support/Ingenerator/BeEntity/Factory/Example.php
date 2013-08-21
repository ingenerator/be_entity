<?php
/**
 * Sample BeEntity factory class showing possible implementations of the abstract methods - used as a PHPSpec harness.
 *
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2013 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\BeEntity\Factory;

/**
 * Sample BeEntity factory class showing possible implementations of the abstract methods - used as a PHPSpec harness.
 *
 * @package Ingenerator\BeEntity\Factory
 */
class Example extends \Ingenerator\BeEntity\Factory
{

	/**
	 * Search for a single entity matching the identifier field passed from the steps, and return it or NULL
	 *
	 * @param string $identifier identifier specified in the feature file - which can map to any unique key
	 *
	 * @return ExampleEntity the located entity
	 */
	protected function _locate($identifier)
	{
		return $this->entity_manager
			   ->getRepository('Ingenerator\BeEntity\Factory\ExampleEntity')
		       ->findOneBy(array('foo_field' => $identifier));
	}

	/**
	 * Create a new entity with sensible defaults, and return it. You should not persist or flush the entity in this
	 * method - that is handled by the outer calling code once any additonal properties have been set.
	 *
	 * @param string $identifier identifier specified in the feature file
	 *
	 * @return ExampleEntity the created entity
	 */
	protected function _create($identifier)
	{
		$entity = new ExampleEntity;
		$entity->set_foo_field($identifier);
		return $entity;
	}

	/**
	 * Expose the entity manager for testing that the correct instance was passed in
	 *
	 * @return \Doctrine\ORM\EntityManager the entity manager
	 */
	public function get_entity_manager()
	{
		return $this->entity_manager;
	}

}

/**
 * Simple entity class for use in specs - your entities do not have to implement any particular class or interface, but
 * should have setter methods for any properties you want to set from you feature files.
 *
 * @package Ingenerator\BeEntity\Factory
 */
class ExampleEntity
{
	/**
	 * @var string
	 */
	protected $foo_field = NULL;

	/**
	 * @var string
	 */
	protected $bar_field = NULL;

	/**
	 * @param string $bar_field
	 */
	public function set_bar_field($bar_field)
	{
		$this->bar_field = $bar_field;
	}

	/**
	 * @return string
	 */
	public function get_bar_field()
	{
		return $this->bar_field;
	}

	/**
	 * @param string $foo_field
	 */
	public function set_foo_field($foo_field)
	{
		$this->foo_field = $foo_field;
	}

	/**
	 * @return string
	 */
	public function get_foo_field()
	{
		return $this->foo_field;
	}

}
