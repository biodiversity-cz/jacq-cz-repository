<?php declare(strict_types=1);

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
        if ((bool)$status === true) {
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
            ->href('mailto:' . $email)
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
            return $seconds . ' sec';
        }

        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $minutes %= 60;
        if ($hours < 24) {
            if ($hours == 1) {
                return $minutes > 0 ? $hours . ' hour ' . $minutes . ' min.' : $hours . ' hour';
            } else {
                return $minutes > 0 ? $hours . ' hours ' . $minutes . ' min' : $hours . ' hours';
            }
        }

        $days = floor($hours / 24);
        $hours %= 24;
        if ($days == 1) {
            return $hours > 0 ? $days . ' day ' . $hours . ' hours' : $days . ' day';
        } else {
            return $hours > 0 ? $days . ' days ' . $hours . ' hours' : $days . ' days';
        }
    }

    public function dumpArray(?array $data, int $level = 0): string
    {
        if ($data === null) {
            return '';
        }

        static $idCounter = 0;
        $output = '<ul class="list-unstyled">';

        foreach ($data as $key => $value) {
            $uniqueId = 'collapse-' . (++$idCounter);

            if (is_array($value)) {
                $output .= '<li>';
                $output .= '<button class="btn btn-link p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#' . $uniqueId . '">';
                $output .= '<i class="fas fa-folder"></i> ' . $key;
                $output .= '</button>';
                $output .= '<div class="collapse" id="' . $uniqueId . '">';
//                $output .= '<div class="collapse ' . ($level === 0 ? 'show' : '') . '" id="' . $uniqueId . '">';
                $output .= '<ul class="list-unstyled ps-3">';
                $output .= $this->dumpArray($value, $level + 1);
                $output .= '</ul>';
                $output .= '</div>';
                $output .= '</li>';
            } else {
                $output .= '<li> <strong>' . $key . ':</strong> ' . $value . '</li>';
            }
        }

        $output .= '</ul>';
        return $output;
    }

    public function ordinalSuffix(int $number): Html
    {
        $absNumber = abs($number);
        $suffix = 'th';

        if ($absNumber % 100 < 11 || $absNumber % 100 > 13) {
            switch ($absNumber % 10) {
                case 1: $suffix = 'st'; break;
                case 2: $suffix = 'nd'; break;
                case 3: $suffix = 'rd'; break;
            }
        }
        $html = Html::el()->setHtml($number . '<sup>' . $suffix . '</sup>');
        return $html;
    }

}
