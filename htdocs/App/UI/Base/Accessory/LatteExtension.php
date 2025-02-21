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
        return ['status' => [$this, 'status'],
            'email' => [$this, 'email'],
            'timeInterval' => [$this, 'formatSeconds']
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
            ->href('mailto:'.$email)
            ->setText($email);

        $icon = Html::el('i')
            ->class('fa fa-envelope')
            ->aria('hidden', 'true');
        $span = Html::el('span')
            ->addHtml($link)
            ->addHtml(' ')
            ->addHtml($icon);

        return $span;
    }

    public function formatSeconds(int $seconds): string {
        if ($seconds < 60) {
            return "$seconds sec";
        }

        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return "$minutes min";
        }

        $hours = floor($minutes / 60);
        $minutes %= 60;
        if ($hours < 24) {
            if ($hours == 1) {
                return $minutes > 0 ? "$hours hour $minutes min" : "$hours hour";
            }else{
                return $minutes > 0 ? "$hours hours $minutes min" : "$hours hours";
            }
        }

        $days = floor($hours / 24);
        $hours %= 24;
        if ($days == 1) {
            return $hours > 0 ? "$days day $hours hours" : "$days day";
        }else{
            return $hours > 0 ? "$days days $hours hours" : "$days days";
        }

    }


}
