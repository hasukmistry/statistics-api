<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Hotel;
use App\Entity\Review;
use App\Repository\HotelRepository;
use App\Repository\ReviewRepository;
use App\Tests\BaseFunctionalTestCase;
use Carbon\Carbon;
use Faker\Generator;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Request;

class OvertimeApiTest extends BaseFunctionalTestCase
{
    private Generator $faker;
    private HotelRepository $hotelRepository;
    private ReviewRepository $reviewRepository;

    private const OVERTIME_ENDPOINT = '/overtime/%d/%d';

    protected AbstractBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        parent::setUp();

        // use the factory to create a Faker\Generator instance
        $this->faker = \Faker\Factory::create();

        $this->hotelRepository = $this->em->getRepository(Hotel::class);
        $this->reviewRepository = $this->em->getRepository(Review::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->client,
            $this->faker,
            $this->hotelRepository,
            $this->reviewRepository
        );

        parent::tearDown();
    }


    public function testEndpoint(): void
    {
        $hotel = (new Hotel())
            ->setName(
                sprintf('%s Hotel', $this->faker->company())
            )
            ->addReview(
                (new Review())
                    ->setScore(4)
                    ->setComment($this->faker->text(250))
                    ->setCreatedDate(Carbon::now()->toDateTime())
            )
            ->addReview(
                (new Review())
                    ->setScore(3)
                    ->setComment($this->faker->text(250))
                    ->setCreatedDate(Carbon::now()->toDateTime())
            )
            ->addReview(
                (new Review())
                    ->setScore(2.99)
                    ->setComment($this->faker->text(250))
                    ->setCreatedDate(Carbon::now()->subDays(2)->toDateTime())
            );

        $this->em->persist($hotel);

        $this->em->flush();

        self::assertCount(1, $this->hotelRepository->findAll());
        self::assertCount(3, $this->reviewRepository->findAll());

        $apiRoute = sprintf(self::OVERTIME_ENDPOINT, $hotel->getId(), 2);

        $this->client->request(Request::METHOD_GET, $apiRoute);

        $response = $this->client->getResponse()->getContent();

        $this->assertResponseIsSuccessful();

        self::assertEquals(
            json_encode([
                'reviewCount' => 3,
                'averageScore' => 1.62,
                'dateGroup' => 'day',
            ]),
            $response
        );
    }
}
