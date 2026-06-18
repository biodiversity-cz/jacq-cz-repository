<?php

declare(strict_types=1);

namespace Database\Fixtures;

use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;

class FixtureFakeVvView extends FixtureBase
{
    public function load(ObjectManager $manager): void
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
            'scientificNameAuthorship_value' AS scientific_name_authorship
           FROM photos p;
        ";
        $manager->getConnection()->executeQuery($sql);
        $sql = '
        ALTER TABLE databots.vv_transcription
            OWNER TO jacq;
        ';
        $manager->getConnection()->executeQuery($sql);
        $sql = '
        GRANT INSERT, SELECT, UPDATE, DELETE ON TABLE databots.vv_transcription TO databot;
        ';
        $manager->getConnection()->executeQuery($sql);
        $sql = '
        GRANT ALL ON TABLE databots.vv_transcription TO jacq;

        ';
        $manager->getConnection()->executeQuery($sql);
    }

    public function getOrder(): int
    {
        return 100;
    }
}
