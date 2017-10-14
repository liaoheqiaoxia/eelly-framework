<?php

declare(strict_types=1);

/*
 * This file is part of eelly package.
 *
 * (c) eelly.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eelly\Mvc;

use Phalcon\Mvc\Application as MvcApplication;

/**
 * Class Application.
 *
 * @author hehui<hehui@eelly.net>
 */
class Application extends MvcApplication
{
    public function __construct()
    {
        $this->useImplicitView(false);
    }

    /**
     * Is implicit view.
     *
     * @return bool
     */
    public function isImplicitView()
    {
        return $this->_implicitView;
    }

    /**
     * Handles a MVC request.
     */
    public function handle(string $uri = null)
    {
        if (APP['env'] == 'swoole') {
        } else {
            return parent::handle($uri);
        }
    }
}
