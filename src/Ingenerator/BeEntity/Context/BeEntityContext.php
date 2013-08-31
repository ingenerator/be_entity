<?php
/**
 * Behat context that defines steps for working with the entity factories
 *
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2013 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\BeEntity\Context;
use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManager;
use Ingenerator\BeEntity\Exception\UnexpectedEntityException;
use Ingenerator\BeEntity\FactoryManager;

/**
 * This behat context defines the common steps for working with entities in your feature files. Integrate it in your
 * application by including it as a subcontext within your feature context. You are responsible for creating a Doctrine
 * EntityManager in whatever way suits your application and passing it in to the context class.
 *
 * For example, your main feature context might look something like this:
 *
 *     class FeatureContext extends BehatContext {
 *
 *         public function __construct()
 *         {
 *             $em = $this->get_my_doctrine_entity_manager_somehow();
 *             $this->useContext('be_entity', new \Ingenerator\BeEntity\Context\BeEntityContext($em));
 *         }
 *     }
 *
 * @package Ingenerator\BeEntity\Context
 */
class BeEntityContext extends BehatContext {

	/**
	 * @var EntityManager the entity manager to use for loading and saving entities
	 */
	protected $entity_manager = NULL;

	/**
	 * @var FactoryManager the factory manager to use when mapping types from your feature files to entity factories
	 */
	protected $factory_manager = NULL;

	/**
	 * Create an instance of the context
	 *
	 * @param EntityManager $entity_manager the entity manager to use for loading and saving entities
	 *
	 * @return \Ingenerator\BeEntity\Context\BeEntityContext
	 */
	public function __construct(EntityManager $entity_manager)
	{
		$this->entity_manager = $entity_manager;
	}

	/**
	 * Ensures that an entity exists with the value of a single field set to the expected value, creating it if required
	 *
	 * @param string $type       The type of entity to load or create
	 * @param string $identifier The identifier for this entity - which can be mapped to be any unique entity property
	 * @param string $field      The name of the field to set
	 * @param string $value      The value to set for the field
	 *
	 * @Given /^a (?P<type>.+?) entity "(?P<identifier>[^"]+)" with (?P<field>.+?) "(?P<value>[^"]+)"$/
	 */
	public function given_a_simple_entity($type, $identifier, $field, $value)
	{
		$factory = $this->get_factory($type);

		// Load the entity, or create if missing, and set the required values
		$factory->provide($identifier, array($field => $value));

		// Flush the entity manager
		$this->entity_manager->flush();
	}

	/**
	 * Ensures that a set of entities exist with the values shown in the provided table. The first column of the table
	 * should be the entity identifier column to be used by the factory class. Any missing entities will be created and
	 * existing entities will be updated - note that only the specified properties will be changed, other fields will
	 * not be reset to defaults.
	 *
	 * For example:
	 *
	 *     Given the following User entities:
	 *         | email         | password   | is_active |
	 *         | foo@foo.com   | 12345678   | yes       |
	 *         | bar@foo.com   | 12345678   | no        |
	 *
	 * @param string    $type     the type of entity to load or create
	 * @param TableNode $entities the details of the identifiers and fields to set
	 *
	 * @Given /^the following (?P<type>.+?) entities$/
	 * @return void
	 */
	public function given_entities($type, TableNode $entities)
	{
		$factory = $this->get_factory($type);

		// Iterate over the table and create the entities
		$entities = $entities->getHash();
		foreach ($entities as $entity_values)
		{
			// The identifier is the first column of the table
			$identifier = reset($entity_values);
			$factory->provide($identifier, $entity_values);
		}

		// Flush the entity manager to persist the entities
		$this->entity_manager->flush();
	}

	/**
	 * Checks that there is no entity matching the specified identifier - this step will fail rather than deleting
	 * entities if the assertion is not met.
	 *
	 * @param string $type       The type of entity to load or create
	 * @param string $identifier The identifier for this entity - which can be mapped to be any unique entity property
	 *
	 * @throws UnexpectedEntityException
	 * @Given /^no (?P<type>.+?) entity "(?P<identifier>[^"]+)"$/
	 * @Then  /^there should not be a "(<?P<identifier>[^"]+)" (<?P<type>.+?) entity$/
	 */
	public function given_no_entity($type, $identifier)
	{
		// Attempt to locate the entity
		$factory = $this->get_factory($type);
		$entity = $factory->locate($identifier, FALSE);

		// If the entity is found, throw an exception
		if ($entity !== NULL)
		{
			throw new UnexpectedEntityException("Did not expect to find a '$type' entity for '$identifier'");
		}
	}

	/**
	 * Get an entity factory for the specified type - will throw an exception if the type is not defined
	 *
	 * @param string $type the type of entity to get a factory for
	 *
	 * @return \Ingenerator\BeEntity\Factory the factory
	 */
	public function get_factory($type)
	{
		return $this->get_factory_manager()->create_factory($type);
	}

	/**
	 * Retrieve the FactoryManager instance that should be used to create entity factories. By default the class creates
	 * a basic instance, but you can inject your own (with set_factory_manager) if required for testing or custom
	 * behaviour.
	 *
	 * @return FactoryManager
	 */
	public function get_factory_manager()
    {
        if ( ! $this->factory_manager) {
	        $this->factory_manager = new FactoryManager($this->entity_manager);
        }
	    return $this->factory_manager;
    }

	/**
	 * Set a FactoryManager instance that should be used to create entity factories. By default the class creates a bare
	 * FactoryManager as required but you can inject your own here if required for testing or custom behaviour.
	 *
	 * @param FactoryManager $factory_manager the manager to set for the class
	 */
	public function set_factory_manager(FactoryManager $factory_manager)
    {
        $this->factory_manager = $factory_manager;
    }

}