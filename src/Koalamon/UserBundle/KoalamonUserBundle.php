<?php

namespace Koalamon\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class KoalamonUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
