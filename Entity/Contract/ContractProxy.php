<?
namespace HOffice\AdminBundle\Entity\Contract;

use Itc\AdminBundle\Entity\TranslationProxy;
use Symfony\Component\Validator\Constraints as Assert;

class ContractProxy extends TranslationProxy
{
    
    public function setTitle($name)
    {
        return $this->setTranslatedValue('title', $name);
    }
    
    public function getTitle()
    {
         $title= $this->getTranslatedValue('title');
         if(null === $title)
         {
             $title = "";
         }
         return $title;
    }
}
