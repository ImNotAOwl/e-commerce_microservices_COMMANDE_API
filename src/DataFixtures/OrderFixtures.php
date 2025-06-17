<?php

namespace App\DataFixtures;

use App\Domain\Entity\Order;
use App\Domain\Entity\OrderLine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Commande 1
        $order1 = new Order();
        $order1->setUserId('user123');  // Utilisateur avec ID 1

        // Ligne de commande pour la commande 1
        $orderLine1 = new OrderLine();
        $orderLine1->setArticleId('aa101'); // ID de l'article 101
        $orderLine1->setName('Article 1');
        $orderLine1->setQuantity(2); // Quantité 2
        $orderLine1->setPrice(500); // Prix 500

        // Lier la ligne de commande à la commande
        $orderLine1->setOrder($order1);
        $manager->persist($orderLine1);

        // Ligne de commande 2 pour la commande 1
        $orderLine2 = new OrderLine();
        $orderLine2->setArticleId('aa102');
        $orderLine2->setName('Article 2');
        $orderLine2->setQuantity(1);
        $orderLine2->setPrice(150);

        // Lier la ligne de commande à la commande
        $orderLine2->setOrder($order1);
        $manager->persist($orderLine2);

        $manager->persist($order1);

        // Commande 2
        $order2 = new Order();
        $order2->setUserId('user456');

        // Ligne de commande pour la commande 2
        $orderLine3 = new OrderLine();
        $orderLine3->setArticleId('aa103');
        $orderLine3->setName('Article 3');
        $orderLine3->setQuantity(5);
        $orderLine3->setPrice(300);

        // Lier la ligne de commande à la commande
        $orderLine3->setOrder($order2);
        $manager->persist($orderLine3);

        // Ligne de commande 2 pour la commande 2
        $orderLine4 = new OrderLine();
        $orderLine4->setArticleId('aa104');
        $orderLine4->setName('Article 4');
        $orderLine4->setQuantity(3);
        $orderLine4->setPrice(25);

        // Lier la ligne de commande à la commande
        $orderLine4->setOrder($order2);
        $manager->persist($orderLine4);

        // Ligne de commande 3 pour la commande 2
        $orderLine5 = new OrderLine();
        $orderLine5->setArticleId('aa105');
        $orderLine5->setName('Article 5');
        $orderLine5->setQuantity(1);
        $orderLine5->setPrice(55);

        // Lier la ligne de commande à la commande
        $orderLine5->setOrder($order2);
        $manager->persist($orderLine5);

        // Persister la commande 2
        $manager->persist($order2);

        // Flusher les entités dans la base de données
        $manager->flush();
    }
}
