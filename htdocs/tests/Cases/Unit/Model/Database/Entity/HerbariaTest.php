<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Contact;
use App\Model\Database\Entity\Herbaria;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('Herbaria entity getters and setters', function (): void {

    $herbaria = new Herbaria();

// test set/get acronym
    $herbaria->setAcronym('ABC');
    Assert::equal('ABC', $herbaria->getAcronym());

// test set/get bucket
    $herbaria->setBucket('my-bucket');
    Assert::equal('my-bucket', $herbaria->getBucket());

// test set/get regexBarcode
    $herbaria->setRegexBarcode('/barcode-regex/');
    Assert::equal('/barcode-regex/', $herbaria->getRegexBarcode());

// test set/get regexFilename
    $herbaria->setRegexFilename('/filename-regex/');
    Assert::equal('/filename-regex/', $herbaria->getRegexFilename());

// test set/get fallbackFilename
    $herbaria->setFallbackFilename(true);
    Assert::true($herbaria->usesFilenameFallback());
    $herbaria->setFallbackFilename(false);
    Assert::false($herbaria->usesFilenameFallback());

// test set/get logo
    $herbaria->setLogo('https://example.com/logo.png');
    Assert::equal('https://example.com/logo.png', $herbaria->getLogo());

// test set/get fullname
    $herbaria->setFullname('Herbarium Example');
    Assert::equal('Herbarium Example', $herbaria->getFullname());

// test set/get address
    $herbaria->setAddress('123 Green Street');
    Assert::equal('123 Green Street', $herbaria->getAddress());

});

test('Herbaria entity AddAndRemoveContact', function (): void {
    $herbarium = new Herbaria();
    $herbarium->setAcronym('XYZ')
        ->setBucket('xyz-bucket')
        ->setRegexBarcode('/\d+/')
        ->setRegexFilename('/IMG_\d+\.tif/')
        ->setFallbackFilename(true);

    $contact = new Contact();
    $contact->setName('John')->setSurname('Doe')->setEmail('john@example.com')->setHerbarium($herbarium);

    // přidání
    $herbarium->addContact($contact);
    Assert::count(1, $herbarium->getContacts());
    Assert::same($contact, $herbarium->getContacts()->first());

    // odebrání
    $herbarium->removeContact($contact);
    Assert::count(0, $herbarium->getContacts());
});
