<?php

namespace App\DataFixtures;

use App\Entity\Hotel;
use App\Entity\Review;
use Carbon\CarbonPeriod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Carbon\Carbon;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // use the factory to create a Faker\Generator instance
        $faker = \Faker\Factory::create();

        $this->loadHotels($manager, $faker);
    }

    private function loadHotels(ObjectManager $manager, Generator $faker): void
    {
        // generate 10 hotels randomly.
        for($i=0; $i<10; $i++) {
            $hotel = (new Hotel())
                ->setName(
                    sprintf('%s Hotel', $faker->company())
                );

            $manager->persist($hotel);
        }

        $manager->flush();

        $this->loadReviews($manager, $faker);
    }

    private function loadReviews(ObjectManager $manager, Generator $faker): void
    {
        $period = CarbonPeriod::since(
            Carbon::now()->subYear(2)
        )->until(
            Carbon::now()
        );

        // Convert the period to an array of dates
        $availableDates = $period->toArray();
        $availableHotels = $manager->getRepository(Hotel::class)->findAll();

        $batchSize = 1000;
        $total = 100000;

        for($i=0; $i<$total; $i++) {
            $hotel = $faker->randomElement($availableHotels);

            $createdDate = $faker->randomElement($availableDates);

            $review = (new Review())
                ->setScore($faker->numberBetween(1, 5))
                ->setComment($faker->text(250))
                ->setCreatedDate($createdDate->toDateTime())
                ->setHotel($hotel);

            $manager->persist($review);

            if (($i % $batchSize) === 0) {
                $manager->flush();
                $manager->clear(); // Detaches all objects from Doctrine!

                // re-fetch hotels after clearing from memory.
                $availableHotels = $manager->getRepository(Hotel::class)->findAll();
            }
        }

        $manager->flush();
        $manager->clear();
    }
}
