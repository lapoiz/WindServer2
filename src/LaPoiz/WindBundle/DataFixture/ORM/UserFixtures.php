<?php	
namespace LaPoiz\WindBundle\DataFixtures\ORM;
 

use LaPoiz\WindBundle\Entity\User;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
 
 
class UserFixtures implements FixtureInterface
{
 
    public function load(ObjectManager $manager)
    {
    	$user = new User();
    	$user->setUsername("admin");
    	
    	// encode the password
    	//$encoder = new MessageDigestPasswordEncoder('sha512', false, 1);
    	//$password = $encoder->encodePassword('admin', $user->getSalt());
    	$password = "admin";
    	
    	$user->setPassword($password);
    	$manager->persist($user);
    	$manager->flush();
    	
    }
}