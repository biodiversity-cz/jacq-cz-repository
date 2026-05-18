<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Contact;
use App\Model\Database\Entity\Herbaria;
use Tester\Assert;

require_once __DIR__.'/../../../../../bootstrap.php';

test('Contact entity getters, setters and traits', function (): void {
    $herbarium = new Herbaria();

    $contact = new Contact();

    // test set/get name
    $contact->setName('Jan');
    Assert::equal('Jan', $contact->name);

    // test set/get surname
    $contact->setSurname('Novák');
    Assert::equal('Novák', $contact->surname);

    // test set/get description
    $contact->setDescription('Curator');
    Assert::equal('Curator', $contact->description);

    // test set/get email
    $contact->setEmail('jan.novak@example.com');
    Assert::equal('jan.novak@example.com', $contact->email);

    // test set/get herbarium
    $contact->setHerbarium($herbarium);
    Assert::same($herbarium, $contact->herbarium);

    // test getFullname
    Assert::equal('Jan Novák', $contact->getFullname());
});
