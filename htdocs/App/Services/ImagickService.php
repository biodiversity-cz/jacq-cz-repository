<?php

declare(strict_types=1);

namespace App\Services;

use Imagick;

class ImagickService
{
    /**
     * creates Imagick instance with the largest page of file activated.
     */
    public function createImagick(string $path): \Imagick
    {
        $imagick = new \Imagick($path);
        $imagick->setIteratorIndex($this->getLargestImageIndex($imagick));

        return $imagick;
    }

    /**
     * in case the image is "mulipage", like a TIF containing a thumb,
     * this helps to find the largest
     * and returns index that Imagick need to be set to.
     */
    public function getLargestImageIndex(\Imagick $imagick): int
    {
        $numberOfImages = $imagick->getNumberImages();
        $maxWidth = 0;
        $maxHeight = 0;
        $largestImageIndex = null;
        for ($i = 0; $i < $numberOfImages; ++$i) {
            $imagick->setIteratorIndex($i);
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();

            if ($width * $height > $maxWidth * $maxHeight) {
                $maxWidth = $width;
                $maxHeight = $height;
                $largestImageIndex = $i;
            }
        }

        return $largestImageIndex;
    }

    public function resizeImage(\Imagick $imagick, int $maxEdgeLength): \Imagick
    {
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        if ($width > $maxEdgeLength || $height > $maxEdgeLength) {
            if ($width > $height) {
                $newWidth = $maxEdgeLength;
                $newHeight = intval(($maxEdgeLength / $width) * $height);
            } else {
                $newHeight = $maxEdgeLength;
                $newWidth = intval(($maxEdgeLength / $height) * $width);
            }

            $imagick->resizeImage($newWidth, $newHeight, \Imagick::FILTER_GAUSSIAN, 1);
        }

        return $imagick;
    }

    /**
     * Thumbs devoted for AI and Databots,s tored in S3.
     */
    public function preparePngThumb(\Imagick $imagick, int $maxEdgeLength = 640): \Imagick
    {
        $imagick = $this->resizeImage($imagick, $maxEdgeLength);
        $imagick->setImageFormat('png');
        $imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
        $imagick->setImageCompressionQuality(90);
        $imagick->setImageDepth(8);
        $imagick->stripImage();

        return $imagick;
    }

    /**
     * @return mixed[]
     */
    public function readIdentify(\Imagick $imagick): array
    {
        $identify = $imagick->identifyImage(true);
        if (isset($identify['rawOutput'])) {
            $identify['rawOutput'] = $this->parseIdentify($identify['rawOutput']);
        }

        return $identify;
    }

    /**
     * @return mixed[]
     */
    public function readExif(\Imagick $imagick): array
    {
        return $imagick->getImageProperties();
    }

    /**
     * from https://www.php.net/manual/en/imagick.identifyimage.php
     * $identify = $this->parseIdentify($identify['rawOutput']);.
     */
    protected function parseIdentify(string $info): mixed
    {
        $lines = explode("\n", $info);

        $outputs = [];
        $output = [];
        $keys = [];

        $currSpaces = 0;
        $raw = false;

        foreach ($lines as $line) {
            $trimLine = $this->sanitizeUtf8(trim($line));

            if (empty($trimLine)) {
                continue;
            }

            if ($raw) {
                preg_match('/^[0-9]+:\s/', $trimLine, $match);

                if (!empty($match)) {
                    $regex = '/([\d]+):';
                    $regex .= '\s(\([\d|\s]{1,3},[\d|\s]{1,3},[\d|\s]{1,3},[\d|\s]{1,3}\))';
                    $regex .= '\s(#\w+)';
                    $regex .= '\s(srgba\([\d|\s]{1,3},[\d|\s]{1,3},[\d|\s]{1,3},[\d|\s|.]+\)|\w+)/';

                    preg_match($regex, $trimLine, $matches);
                    array_shift($matches);

                    $output['Image'][$raw][] = array_map([$this, 'sanitizeUtf8'], $matches);

                    continue;
                }
                $raw = false;
                array_pop($keys);
            }

            preg_match('/^\s+/', $line, $match);

            $spaces = isset($match[0]) ? strlen($match[0]) : $spaces = 0;
            $parts = preg_split('/:\s/', $trimLine, 2);

            $_key = ucwords($parts[0]);
            $_key = str_replace(' ', '', $_key);
            $_val = isset($parts[1]) ? $this->sanitizeUtf8($parts[1]) : [];

            if ('Image' === $_key) {
                if (!empty($output)) {
                    $outputs[] = $output['Image'];
                    $output = [];
                }

                $_val = [];
            }

            if ($spaces < $currSpaces) {
                for ($i = 0; $i < ($currSpaces - $spaces) / 2; ++$i) {
                    array_pop($keys);
                }
            }

            if (is_array($_val)) {
                $_key = rtrim($_key, ':');
                $keys[] = $_key;

                if ('Histogram' === $_key || 'Colormap' === $_key) {
                    $raw = $_key;
                }
            }

            // phpcs:disable SlevomatCodingStandard.PHP.DisallowReference.DisallowedAssigningByReference
            $currSpaces = $spaces;
            $arr = &$output;

            foreach ($keys as $key) {
                if (!isset($arr[$key])) {
                    $arr[$key] = $_val;
                }

                $arr = &$arr[$key];
            }

            // phpcs:enable
            if (!is_array($_val)) {
                $arr[$_key] = $_val;
            }
        }

        $outputs[] = $output['Image'];

        return count($outputs) > 1 ? $outputs : $outputs[0];
    }

    private function sanitizeUtf8(string $text): string
    {
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }

        $converted = mb_convert_encoding($text, 'UTF-8', 'UTF-8, ISO-8859-2, WINDOWS-1252');
        if (false !== $converted) {
            return $converted;
        }

        $converted = iconv('ISO-8859-2', 'UTF-8//IGNORE', $text);
        if (false !== $converted) {
            return $converted;
        }

        return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $text) ?: '';
    }
}
