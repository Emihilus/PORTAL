<?php

namespace App\DataFixtures;

use DirectoryIterator;
use Doctrine\Persistence\ObjectManager;
use App\ControllerTools\ImagesProcessor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PrepareDummyImages extends Fixture implements FixtureGroupInterface
{
    private ContainerInterface $container;
    private ImagesProcessor $imagesProcessor;

    public function __construct(ContainerInterface $container, ImagesProcessor $imagesProcessor)
    {
        $this->container = $container;
        $this->imagesProcessor = $imagesProcessor;
    }

    public function load(ObjectManager $manager)
    {
        // $this->generateThmbnails();
    }

    private function generateThmbnails()
    {
        foreach (new DirectoryIterator($this->container->getParameter('auctionImagePath')) as $file) 
        {
            if ($file->isFile()) 
            {
                print "Generating thumbnail for ".$file->getFilename() . " ...\n";
                $this->imagesProcessor->processToThumbnail(
                    $this->container->getParameter('auctionImagePath'). $file,
                    $this->container->getParameter('auctionImagePathThumbnail').'th-'. $file
                );
            }
          }
    }

    public static function getGroups(): array
     {
        return ['1'];
     }
}
