<?php

namespace TheProject\Handlers\Demo;

use League\Plates\Engine;

/**
 * Class DemoHandler
 * @package TheProject\Handlers\Demo
 */
class DemoHandler
{
    /** @var Engine */
    private $template;

    /**
     * DemoHandler constructor.
     * @param Engine $template
     */
    public function __construct(Engine $template)
    {
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        return $this->template->render('Demo/index');
    }
}
