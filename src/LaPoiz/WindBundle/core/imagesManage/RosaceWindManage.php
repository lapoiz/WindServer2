<?php
namespace LaPoiz\WindBundle\core\imagesManage;

use LaPoiz\WindBundle\Entity\Spot;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;

class RosaceWindManage
{
    /**
     * @param Spot $spot spot contenant les orientation de vent
     * Créer une image png de la rosace des vents, avec GD.
     * L'image sera stockée dans :  images/windRosaces/spotId.png
     */
    static function createRosaceWind(Spot $spot, Controller $controller) {
        try {
            $rosaceImg = imagecreate(200,50);
            $orange = imagecolorallocate($rosaceImg, 255, 128, 0);
            $ds = DIRECTORY_SEPARATOR;
            $urlImage=$controller->get("kernel")->getRootDir().$ds.'..'.$ds.'web'.$ds.
                'images'.$ds.'windRosaces';
            RosaceWindManage::createRoute($urlImage);
            $urlImage=$urlImage.$ds.$spot->getId().".png";
            imagepng($rosaceImg, $urlImage); // on enregistre l'image dans le dossier "images/windRosaces"
        } catch (\Exception $e) {
            $toto=$e->getMessage(); // pour debug
        }
    }
    /**
     * crée un dossier si il n'existe pas
     */
    static function createRoute($route)
    {
        $fs = new Filesystem();
        if( !is_dir($route) )
        {
            try {
                $fs->mkdir($route, 0755);
            }
            catch (IOExceptionInterface $e) {
                echo "An error occurred while creating your directory at ".$e->getPath();
            }
        }
    }


}