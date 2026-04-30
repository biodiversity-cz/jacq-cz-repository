<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Contact;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\User;
use Doctrine\Common\Collections\Collection;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('Herbaria entity getters and setters', function (): void {

    $herbaria = new Herbaria();

// test set/get acronym
    $herbaria->setAcronym('ABC');
    Assert::equal('ABC', $herbaria->acronym);

// test set/get bucket
    $herbaria->setBucket('my-bucket');
    Assert::equal('my-bucket', $herbaria->bucket);

// test set/get regexBarcode
    $herbaria->setRegexBarcode('/barcode-regex/');
    Assert::equal('/barcode-regex/', $herbaria->regexBarcode);

// test set/get regexFilename
    $herbaria->setRegexFilename('/filename-regex/');
    Assert::equal('/filename-regex/', $herbaria->regexFilename);

// test set/get fallbackFilename
    $herbaria->setFallbackFilename(true);
    Assert::true($herbaria->fallbackFilename);
    $herbaria->setFallbackFilename(false);
    Assert::false($herbaria->fallbackFilename);

// test set/get logo
    $herbaria->setLogo('https://example.com/logo.png');
    Assert::equal('https://example.com/logo.png', $herbaria->logo);

// test set/get fullname
    $herbaria->setFullname('Herbarium Example');
    Assert::equal('Herbarium Example', $herbaria->fullname);

// test set/get address
    $herbaria->setAddress('123 Green Street');
    Assert::equal('123 Green Street', $herbaria->address);

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
    Assert::count(1, $herbarium->contacts);
    Assert::same($contact, $herbarium->contacts->first());

    // odebrání
    $herbarium->removeContact($contact);
    Assert::count(0, $herbarium->contacts);
});


