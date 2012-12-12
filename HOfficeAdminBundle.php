<?php

namespace HOffice\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HOfficeAdminBundle extends Bundle
{
   public function getParent()
    {
        return 'ItcDocumentsBundle';
    } 
}