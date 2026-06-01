<?php

declare(strict_types=1);

namespace App\Api\PubV1;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Model\Database\Entity\CetafSid;
use App\Model\Database\Entity\ExternalDatabase;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\LinkGenerator;

#[Apitte\Path('/museion')]
#[Apitte\Tag('Museion')]
class MuseionController extends BasePubV1Controller
{
    public function __construct(protected EntityManagerInterface $entityManager, protected LinkGenerator $linkGenerator)
    {
    }

    #[Apitte\OpenApi('summary: Generate CSV file for upload in Museion CSV_JinaEvidence - to store a link to the repository in Museion SW.')]
    #[Apitte\Path('/CSV_JinaEvidence/{acronym}')]
    #[Apitte\Method('GET')]
    #[Apitte\Response(description: 'Success', code: '200')]
    public function CSV_JinaEvidence(ApiRequest $request, ApiResponse $response)
    {
        $acronym = strtoupper($request->getParameter('acronym'));
        $cetafSID = $this->entityManager->getReference(ExternalDatabase::class, ExternalDatabase::INTERNAL);
        $herbarium = $this->entityManager->getRepository(Herbaria::class)->findOneBy(['acronym' => $acronym, 'externalDatabase' => $cetafSID]);
        if (null === $herbarium) {
            throw new ClientErrorException('Herbarium not found', 404);
        }
        $repository = $this->entityManager->getRepository(CetafSid::class);

        $tmp = fopen('php://temp', 'r+');
        fwrite($tmp, "\xEF\xBB\xBF");
        fputcsv($tmp, ['Sbírkový předmět', 'Číslo (JinaEvidence.cislo)', 'Název (JinaEvidence.nazev)', 'Poznámka (JinaEvidence.poznamka)'], "\t", '"', '"');

        foreach ($repository->findBy(['herbarium' => $herbarium]) as $item) {
            $url = $this->linkGenerator->link('//Front:Cetaf:sid', ['id' => $item->id]);
            fputcsv($tmp, [$item->getCatalogueNumber(), $url, 'HerbBio', 'Odkaz do národního repozitáře'], "\t", '"', '"');
        }

        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);

        $response->getBody()->write($csv);

        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="Museion_CSV_JinaEvidence_'.$herbarium->acronym.'.csv"');
    }

    #[Apitte\OpenApi('summary: Generate CSV file for upload in Museion CSV_KontextovyDokument - to store a link to the image preview from the repository in Museion SW.')]
    #[Apitte\Path('/CSV_KontextovyDokument/{acronym}')]
    #[Apitte\Method('GET')]
    #[Apitte\Response(description: 'Success', code: '200')]
    public function CSV_KontextovyDokument(ApiRequest $request, ApiResponse $response)
    {
        $acronym = strtoupper($request->getParameter('acronym'));
        $cetafSID = $this->entityManager->getReference(ExternalDatabase::class, ExternalDatabase::INTERNAL);
        $herbarium = $this->entityManager->getRepository(Herbaria::class)->findOneBy(['acronym' => $acronym, 'externalDatabase' => $cetafSID]);
        if (null === $herbarium) {
            throw new ClientErrorException('Herbarium not found', 404);
        }

        $tmp = fopen('php://temp', 'r+');
        fwrite($tmp, "\xEF\xBB\xBF");
        $header = ['Úloha', 'Číslo', 'Pořadí (KontextovyDokument.poradi)', 'Název (KontextovyDokument.nazev)', 'Typ dokumentu (KontextovyDokument.typDokumentu->TypDokumentu.nazev)', 'Soubor/URL (KontextovyDokument.url)', 'Náhled (KontextovyDokument.nahled)', 'Vodoznak', 'Tisk na pozici 1 (KontextovyDokument.tisk1)', 'Tisk na pozici 2 (KontextovyDokument.tisk2)', 'Poznámka (KontextovyDokument.poznamka)', 'Není digitalizován (KontextovyDokument.neDigitalni)', 'Umístění (KontextovyDokument.umisteni)', 'Publikovat (KontextovyDokument.publikace)', 'Použito v literární publikaci (KontextovyDokument.literatura->Literatura.nazev)', 'Literatura-slovy (KontextovyDokument.literaturaSlovy)', 'Licence Creative Commons (AutorskePravo.licenceCC)', 'Varianta licence CC (AutorskePravo.variantaCCLicence->VariantaCCLicence.oznaceni)', 'Autor (AutorskePravo.autor->Subjekt.kod)', 'Držitel licence (AutorskePravo.drzitelLicence->Subjekt.kod)', 'Typ licence (AutorskePravo.typLicence)', 'Časové omezení licence (AutorskePravo.casovaLicence)', 'Územní omezení licence (AutorskePravo.uzemniLicence)', 'Množstevní omezení licence (AutorskePravo.mnozstevniLicence)', 'Datum vypršení (AutorskePravo.datumVyprseni)', 'Poznámka (AutorskePravo.poznámka)'];
        fputcsv($tmp, $header, "\t", '"', '"');

        $repository = $this->entityManager->getRepository(Photos::class);

        foreach ($repository->getAllPublishedPhotosDatasource()->getQuery()->toIterable() as $item) {
            $url = $this->linkGenerator->link('//Front:Repository:databotThumbImage', ['id' => $item->id]);
            fputcsv($tmp, [
                'PREDMET_PRIVATE',
                $item->specimenId,
                100 + $item->id,
                '',
                '',
                $url,
                '',
                '',
                '',
                '',
                '',
                '0',
                '',
                '1',
                '',
                'náhled fotografie z repozitáře',
                '1',
                'BY',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'https://creativecommons.org/licenses/by/4.0/deed.en',
            ], "\t", '"', '"');
        }

        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);

        $response->getBody()->write($csv);

        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="Museion_CSV_KontextovyDokument'.$herbarium->acronym.'.csv"');
    }
}
