<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\BlogPost;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;


/**
 * Class LoadFixtures
 * tutoriel https://knpuniversity.com/screencast/symfony-doctrine/alice-faker-function#play
 *
 * @package AppBundle\DataFixtures\ORM
 */
class LoadFixtures implements FixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $objects = Fixtures::load(
            __DIR__.'/blog_posts.yml',
            $manager,
            [
                'providers' => [$this]
            ]
        );
    }
    public function title()
    {
        $genera = [
            'Octopus',
            'Balaena',
            'Orcinus',
            'Hippocampus',
            'Asterias',
            'Amphiprion',
            'Carcharodon',
            'Aurelia',
            'Cucumaria',
            'Balistoides',
            'Paralithodes',
            'Chelonia',
            'Trichechus',
            'Eumetopias'
        ];
        $key = array_rand($genera);
        return $genera[$key];
    }

}