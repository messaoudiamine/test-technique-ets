<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Document\Article;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(DocumentManager $manager): void
    {
        $faker = Factory::create();

        // Create admin user
        $admin = new User();
        $admin->setName('Admin User');
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // Create 20 regular users
        $users = [];
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setName($faker->name());
            $user->setEmail($faker->unique()->email());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

            $users[] = $user;
            $manager->persist($user);
        }

        // Flush users first to ensure they have IDs
        $manager->flush();

        // Create 5-15 articles per user (including admin)
        $allUsers = array_merge([$admin], $users);
        foreach ($allUsers as $user) {
            $articleCount = $faker->numberBetween(5, 15);
            
            for ($j = 0; $j < $articleCount; $j++) {
                $article = new Article();
                $article->setTitle($faker->sentence(6));
                $article->setContent($faker->paragraphs(3, true));
                $article->setAuteur($user);
                $article->setPublicationDate($faker->dateTimeBetween('-1 year', 'now'));

                $manager->persist($article);
            }
        }

        $manager->flush();
    }
}

