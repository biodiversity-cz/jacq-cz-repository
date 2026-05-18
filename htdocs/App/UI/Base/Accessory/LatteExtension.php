<?php

declare(strict_types=1);

namespace App\UI\Base\Accessory;

use Latte\Extension;
use Nette\Utils\Html;

final class LatteExtension extends Extension
{
    /**
     * @return array|callable[]
     */
    public function getFilters(): array
    {
        return [
            'status' => [$this, 'status'],
            'email' => [$this, 'email'],
            'timeInterval' => [$this, 'formatSeconds'],
            'dumpArray' => [$this, 'dumpArray'],
            'ordinalSuffix' => [$this, 'ordinalSuffix'],
        ];
    }

    /**
     * @return array|callable[]
     */
    public function getFunctions(): array
    {
        return [];
    }

    public function status(mixed $status): Html
    {
        $el = Html::el('b');
        if (true === (bool) $status) {
            $el->style('color', 'green');
            $el->setText('✓');
        } else {
            $el->style('color', 'red');
            $el->setText('𐄂');
        }

        return $el;
    }

    public function email(string $email): Html
    {
        $link = Html::el('a')
            ->href('mailto:'.$email)
            ->setText($email);

        $icon = Html::el('i')
            ->class('fa fa-envelope');

        return Html::el('span')
            ->addHtml($link)
            ->addHtml(' ')
            ->addHtml($icon);
    }

    public function formatSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.' sec';
        }

        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes.' min';
        }

        $hours = floor($minutes / 60);
        $minutes %= 60;
        if ($hours < 24) {
            if (1 == $hours) {
                return $minutes > 0 ? $hours.' hour '.$minutes.' min.' : $hours.' hour';
            }

            return $minutes > 0 ? $hours.' hours '.$minutes.' min' : $hours.' hours';
        }

        $days = floor($hours / 24);
        $hours %= 24;
        if (1 == $days) {
            return $hours > 0 ? $days.' day '.$hours.' hours' : $days.' day';
        }

        return $hours > 0 ? $days.' days '.$hours.' hours' : $days.' days';
    }

    public function dumpArray(?array $data): string
    {
        return json_encode($this->toTree($data), constant('JSON_UNESCAPED_UNICODE'));
    }

    public function toTree(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->toTree($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function ordinalSuffix(int $number): Html
    {
        $absNumber = abs($number);
        $suffix = 'th';

        if ($absNumber % 100 < 11 || $absNumber % 100 > 13) {
            switch ($absNumber % 10) {
                case 1: $suffix = 'st';
                    break;
                case 2: $suffix = 'nd';
                    break;
                case 3: $suffix = 'rd';
                    break;
            }
        }
        $html = Html::el()->setHtml($number.'<sup>'.$suffix.'</sup>');

        return $html;
    }
}
