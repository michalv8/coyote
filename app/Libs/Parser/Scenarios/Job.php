<?php

namespace Coyote\Parser\Scenarios;

use Coyote\Parser\Parser;
use Coyote\Parser\Providers\Censore;
use Coyote\Parser\Providers\Geshi;
use Coyote\Parser\Providers\Link;
use Coyote\Parser\Providers\Markdown;
use Coyote\Parser\Providers\Purifier;

class Job extends Scenario
{
    /**
     * Parse post
     *
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        start_measure('parsing', 'Parsing job data...');

        $isInCache = $this->isInCache($text);
        if ($isInCache) {
            $text = $this->getFromCache($text);
        }

        if (!$isInCache || $this->isSmiliesAllowed()) {
            $parser = new Parser();

            if (!$isInCache) {
                $text = $this->cache($text, function () use ($parser) {
                    $parser->attach((new Markdown($this->app['Coyote\Repositories\Eloquent\UserRepository']))->setBreaksEnabled(true));
                    $parser->attach(new Purifier());
                    $parser->attach(new Link($this->app['Coyote\Repositories\Eloquent\PageRepository'], $this->app['Illuminate\Http\Request']));
                    $parser->attach(new Censore($this->app['Coyote\Repositories\Eloquent\WordRepository']));
                    $parser->attach(new Geshi());

                    return $parser;
                });
            }
        }
        stop_measure('parsing');

        return $text;
    }
}
