<?php

namespace Coyote\Services;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\SchemaOrg\Schema;

/**
 * Prosta klasa sluzaca do budowania elementu obecnego na kazdej podstronie, czyli breadcrumb
 *
 * Class Breadcrumb
 * @package Coyote
 */
class Breadcrumb implements \Countable, Arrayable
{
    private $breadcrumbs = [];

    /**
     * Zwraca liczbe elementow w breadcrumb
     *
     * @return int
     */
    public function count()
    {
        return count($this->breadcrumbs);
    }

    /**
     * Umozliwia dodanie kolejnego elementu do "okruszkow". Element $name moze byc tablica okruszkow
     *
     * @param $name
     * @param null $url
     */
    public function push($name, $url = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->push($key, $value);
            }
        } elseif (is_string($name)) { // we don't want to add empty value
            $this->breadcrumbs[] = ['name' => $name, 'url' => $url];
        }
    }

    /**
     * Generowanie okroszkow zgodnych z Bootstrap
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        return view(
            'components/breadcrumb',
            [
                'breadcrumbs' => $this->breadcrumbs,
                'schema' => Schema::breadcrumbList()
                    ->itemListElement(array_map(
                        function ($breadcrumb, int $index) {
                            return Schema::listItem()
                                ->position($index + 1)
                                ->identifier($breadcrumb['url'])
                                ->name($breadcrumb['name']);
                        },
                        $this->breadcrumbs,
                        array_keys($this->breadcrumbs),
                    )
                ),
            ]
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->breadcrumbs;
    }
}
