<?php

declare(strict_types=1);

namespace Database\Fixtures;

use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;

class FixtureFakeDatabotViews extends FixtureBase
{
    public function load(ObjectManager $manager): void
    {
        $this->vv_view($manager);
        $this->cetaf_view($manager);
    }

    private function vv_view(ObjectManager $manager): void
    {
        $sql = "
        CREATE OR REPLACE VIEW databots.vv_transcription
         AS
         SELECT p.id as photo_id,
            'catalog_number_value' AS catalog_number,
            'country_value' AS country,
            'county_value' AS county,
            'locality_value' AS locality,
            'continent_value' AS continent,
            'identifiedBy_value' AS identified_by,
            'dateIdentified_value' AS date_identified,
            'recordedBy_value' AS recorded_by,
            'stateProvince_value' AS state_province,
            'eventDate_value' AS event_date,
            'verbatimEventDate_value' AS verbatim_event_date,
            'occurrenceRemarks_value' AS occurrence_remarks,
            'decimalLatitude_value' AS decimal_latitude,
            'decimalLongitude_value' AS decimal_longitude,
            'verbatimCoordinates_value' AS verbatim_coordinates,
            'minimumElevationInMeters_value' AS minimum_elevation_in_meters,
            'genus_value' AS genus,
            'scientificName_value' AS scientific_name,
            'specificEpithet_value' AS specific_epithet,
            'scientificNameAuthorship_value' AS scientific_name_authorship,
            'handwritten' AS handwritten,
            'typus' AS typus
           FROM photos p;
        ";
        $manager->getConnection()->executeQuery($sql);
        $sql = '
        ALTER TABLE databots.vv_transcription
            OWNER TO herbarium_app;
        ';
        $manager->getConnection()->executeQuery($sql);
        $sql = '
        GRANT INSERT, SELECT, UPDATE, DELETE ON TABLE databots.vv_transcription TO herbarium_databot;
        ';
        $manager->getConnection()->executeQuery($sql);
        $sql = '
        GRANT ALL ON TABLE databots.vv_transcription TO herbarium_app;

        ';
        $manager->getConnection()->executeQuery($sql);
    }

    private function cetaf_view(ObjectManager $manager): void
    {
        $sql = "
       CREATE OR REPLACE VIEW databots.cetaf_harvest
    AS
    SELECT
    photo_id,
    now() AS lastedit_timestamp,
    result_data ->> 'http://purl.org/dc/terms/title' AS title,
    result_data ->> 'http://purl.org/dc/terms/created' AS created,
    result_data ->> 'http://purl.org/dc/terms/creator' AS creator,
    result_data ->> 'http://purl.org/dc/terms/publisher' AS publisher,
    result_data ->> 'http://purl.org/dc/terms/thumbnail' AS thumbnail,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/genus' AS genus,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/family' AS family,
    result_data ->> 'http://purl.org/dc/terms/description' AS description,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/country' AS country,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/locality' AS locality,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/eventDate' AS event_date,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/recordedBy' AS recorded_by,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/countryCode' AS country_code,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/fieldNumber' AS field_number,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/recordNumber' AS record_number,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/basisOfRecord' AS basis_of_record,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/catalogNumber' AS catalog_number,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/collectionCode' AS collection_code,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/scientificName' AS scientific_name,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/associatedMedia' AS associated_media,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/specificEpithet' AS specific_epithet,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/materialSampleID' AS material_sample_id,
    result_data ->> 'http://rs.tdwg.org/dwc/terms/previousIdentifications' AS previous_identifications
FROM databots.databot_results r
WHERE databot_id = 3
  AND status = 'ok'::databots.enum_databot_result_status;
        ";
        $manager->getConnection()->executeQuery($sql);
        $sql = '
        ALTER TABLE databots.cetaf_harvest
            OWNER TO herbarium_app;
        ';
        $manager->getConnection()->executeQuery($sql);
        $sql = '
        GRANT INSERT, SELECT, UPDATE, DELETE ON TABLE databots.cetaf_harvest TO herbarium_databot;
        ';
        $manager->getConnection()->executeQuery($sql);
        $sql = '
        GRANT ALL ON TABLE databots.cetaf_harvest TO herbarium_app;

        ';
        $manager->getConnection()->executeQuery($sql);
    }

    public function getOrder(): int
    {
        return 100;
    }
}
