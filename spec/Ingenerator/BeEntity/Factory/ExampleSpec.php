<?php
/**
 * Specifications for entity factories
 *
 * @author     Andrew Coulton <andrew@ingenerator.com>
 * @copyright  2013 inGenerator Ltd
 * @licence    BSD
 */

namespace spec\Ingenerator\BeEntity\Factory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

// Use the spec fixture implementation of the entity factory and entity class
require_once(__DIR__.'/../../../support/Ingenerator/BeEntity/Factory/Example.php');

/**
 * Specifications for the Example factory - which shows a simple implementation of an entity factory instance - these
 * specs are focused on the core inherited logic of the Ingenerator/BeEntity/Factory class.
 *
 * @package spec\Ingenerator\BeEntity\Factory
 * @see \Ingenerator\BeEntity\Factory\Example
 * @see \Ingenerator\BeEntity\Factory\ExampleEntity
 */
class ExampleSpec extends ObjectBehavior
{
	/**
	 * An entity factory needs a reference to the entity manager
	 *
	 * @param \Doctrine\ORM\EntityManager $entity_manager
	 * @return void
	 */
	public function let($entity_manager)
	{
		$this->beConstructedWith($entity_manager);
	}

	/**
	 * The factory should be able to be created
	 *
	 * @return void
	 */
	public function it_is_initializable()
    {
        $this->shouldHaveType('Ingenerator\BeEntity\Factory\Example');
    }

	/**
	 * Locate should query for an entity and return it if it exists
	 *
	 * @param \Doctrine\ORM\EntityManager                 $entity_manager
	 * @param \Doctrine\ORM\EntityRepository              $repository
	 * @param \Ingenerator\BeEntity\Factory\ExampleEntity $foo_entity
	 *
	 * @return void
	 */
	public function it_locates_and_returns_an_existing_entity($entity_manager, $repository, $foo_entity)
	{
		$entity_manager->getRepository('Ingenerator\BeEntity\Factory\ExampleEntity')->willReturn($repository);
		$repository->findOneBy(array('foo_field' => 'bar'))->willReturn($foo_entity);

		$this->locate('bar', TRUE)->shouldReturn($foo_entity);
	}

	/**
	 * Locate should return null if the entity does not exist and the require argument was passed as FALSE
	 *
	 * @param \Doctrine\ORM\EntityManager    $entity_manager
	 * @param \Doctrine\ORM\EntityRepository $repository
	 *
	 * @return void
	 */
	public function it_returns_null_from_locating_a_nonexistent_entity_that_is_not_required($entity_manager, $repository)
	{
		$entity_manager->getRepository('Ingenerator\BeEntity\Factory\ExampleEntity')->willReturn($repository);
		$repository->findOneBy(array('foo_field' => 'bar'))->willReturn(NULL);

		$this->locate('bar', FALSE)->shouldReturn(NULL);
	}

	/**
	 * Locate should throw an exception if the entity does not exist and the require argument was passed as TRUE
	 *
	 * @param \Doctrine\ORM\EntityManager    $entity_manager
	 * @param \Doctrine\ORM\EntityRepository $repository
	 *
	 * @return void
	 */
	public function it_throws_exception_from_locating_a_nonexistent_entity_that_is_required($entity_manager, $repository)
	{
		$entity_manager->getRepository('Ingenerator\BeEntity\Factory\ExampleEntity')->willReturn($repository);
		$repository->findOneBy(array('foo_field' => 'bar'))->willReturn(NULL);

		$this->shouldThrow('\Ingenerator\BeEntity\Exception\MissingEntityException')->during('locate', array('bar', TRUE));
	}

	/**
	 * Create should return a new entity instance
	 *
	 * @param \Doctrine\ORM\EntityManager    $entity_manager
	 *
	 * @return void
	 */
	public function it_creates_and_persists_an_entity($entity_manager)
	{
		$new_entity = $this->create('baz');

		$new_entity->shouldBeAnInstanceOf('\Ingenerator\BeEntity\Factory\ExampleEntity');
		$entity_manager->persist($new_entity)->shouldHaveBeenCalled();
	}

	/**
	 * When an entity is created the identifier field should be set
	 *
	 * @return void
	 */
	public function it_sets_identifier_field_when_creating_an_entity()
	{
		$new_entity = $this->create('baz');
		$new_entity->get_foo_field()->shouldBe('baz');
	}

	/**
	 * When creating an entity an optional array of fields should be set to override the factory defaults
	 *
	 * @return void
	 */
	public function it_sets_optional_fields_when_creating_an_entity()
	{
		$new_entity = $this->create('baz', array('bar_field' => 'bar'));
		$new_entity->get_bar_field()->shouldReturn('bar');
	}

	/**
	 * Provide offers a shortcut for ensuring an entity exists with expected values when you don't know if it might
	 * already exist. If the entity already exists the fields are updated.
	 *
	 * @param \Doctrine\ORM\EntityManager                 $entity_manager
	 * @param \Doctrine\ORM\EntityRepository              $repository
	 * @param \Ingenerator\BeEntity\Factory\ExampleEntity $foo_entity
	 *
	 * @return void
	 */
	public function it_provides_an_existing_entity_with_specified_fields($entity_manager, $repository, $foo_entity)
	{
		$entity_manager->getRepository('Ingenerator\BeEntity\Factory\ExampleEntity')->willReturn($repository);
		$repository->findOneBy(array('foo_field' => 'bar'))->willReturn($foo_entity);

		$foo_entity->set_bar_field('new value')->shouldBeCalled();
		$entity_manager->persist($foo_entity)->shouldBeCalled();

		$this->provide('bar', array('bar_field' => 'new value'))->shouldReturn($foo_entity);
	}

	/**
	 * Provide offers a shortcut for ensuring an entity exists with expected values when you don't know if it might
	 * already exist. If the entity cannot be located then a new entity is created.
	 *
	 * @param \Doctrine\ORM\EntityManager    $entity_manager
	 * @param \Doctrine\ORM\EntityRepository $repository
	 *
	 * @return void
	 */
	public function it_provides_a_new_entity_with_specified_fields($entity_manager, $repository)
	{
		$entity_manager->getRepository('Ingenerator\BeEntity\Factory\ExampleEntity')->willReturn($repository);
		$repository->findOneBy(array('foo_field' => 'bar'))->willReturn(NULL);
		$entity_manager->persist(Argument::any())->willReturn();

		$new_entity = $this->provide('bar', array('bar_field' => 'new value'));
		$new_entity->shouldBeAnInstanceOf('Ingenerator\BeEntity\Factory\ExampleEntity');
		$new_entity->get_bar_field()->shouldBe('new value');

		$entity_manager->persist($new_entity)->shouldHaveBeenCalled();
	}

}
