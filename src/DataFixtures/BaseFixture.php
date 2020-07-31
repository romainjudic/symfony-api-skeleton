<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

/**
 * Fixture de base avec méthodes pour gérer les références
 * et la génération via Faker.
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
     * Fonction à redéfinir dans toutes les fixtures filles pour décrire comment sont chargées les données d'une entité donnée.
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
     * Permet de créer un grand nombre d'objets d'une même entité en utilisant Faker.
     *
     * @param string $className
     * @param integer $count
     * @param callable $factory Closure qui sera appelée à chaque itération pour générer UNE instance de l'entité
     * @return void
     */
    protected function createMany(string $className, int $count, callable $factory)
    {
        for ($i = 0; $i < $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);

            $this->manager->persist($entity);
            // On stocke une référence pour plus tard en tant que App\Entity\ClassName_#COUNT#
            $this->addCustomReference($entity);
        }
    }

    /**
     * Ajoute une référence à un objet et garde en mémoire le nom de toutes les références disponibles pour chaque classe.
     * 
     * Permettra plus tard de retrouver une référence spécifique ou d'obtenir une référence aléatoire d'une classe.
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
     * Retourne une référence aléatoire à un objet d'une classe donnée.
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
     * Compte le nombre de références déjà  enregistrées pour une classe données.
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
     * Retourne tous les noms des références à une classe donnée.
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
