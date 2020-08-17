<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

/**
 * Base fixture that contains base methods to handle references and random generation of large datasets using Faker.
 */
abstract class BaseFixture extends Fixture
{
    /** @var array */
    private static $referencesByClass = [];

    /** @var ObjectManager */
    protected $manager;

    protected $faker;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
    }

    /**
     * Function that must be redefined in every child class to describe how data should be loaded.
     *
     * @param ObjectManager $manager
     * @return void
     */
    abstract protected function loadData(ObjectManager $manager);

    public function load(ObjectManager $manager)
    {
        $this->loadData($manager);
    }

    /**
     * Create many objects of the same class/entity using Faker.
     *
     * @param string $className
     * @param integer $count
     * @param callable $factory Closure called for each iteration to generate ONE instance of the entity
     * @return void
     */
    protected function createMany(string $className, int $count, callable $factory)
    {
        for ($i = 0; $i < $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);

            $this->manager->persist($entity);
            // Store a reference for later use as "App\Entity\ClassName_#COUNT#"
            $this->addCustomReference($entity);
        }
    }

    /**
     * Add a reference to an object and memorize the name of every available references for each class.
     * 
     * This will allow us to retrieve a specific reference or to get a random reference for a class.
     *
     * @param mixed $object
     * @return void
     */
    public function addCustomReference($object)
    {
        $className = get_class($object);

        if (!array_key_exists($className, self::$referencesByClass)) {
            self::$referencesByClass[$className] = [];
        }

        $name = $className . '_' . count(self::$referencesByClass[$className]);
        array_push(self::$referencesByClass[$className], $name);

        parent::addReference($name, $object);
    }

    /**
     * Return a reference to an object of a given class.
     *
     * @param string $className
     * @return void
     */
    protected function getRandomClassReference(string $className)
    {
        if (array_key_exists($className, self::$referencesByClass)) {
            $referenceName = $this->faker->randomElement(self::$referencesByClass[$className]);

            return $this->getReference($referenceName);
        }
        return null;
    }

    /**
     * COunt the number of recorded refereneces to a given class.
     *
     * @param string $className
     * @return integer
     */
    protected function countClassReferences(string $className): int
    {
        if (array_key_exists($className, self::$referencesByClass)) {
            return count(self::$referencesByClass[$className]);
        }
        return 0;
    }

    /**
     * Return all the reference names recorded for a given class.
     *
     * @param string $className
     * @return array
     */
    protected function getAllClassReferenceNames(string $className): array
    {
        if (array_key_exists($className, self::$referencesByClass)) {
            return self::$referencesByClass[$className];
        }
        return [];
    }
}
